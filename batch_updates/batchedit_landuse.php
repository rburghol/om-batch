<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;
$cbp_scenario = 3;

$userid = 1;
$usergroupids = '1,3';

include_once('./xajax_modeling.element.php');
include_once('./lib_verify.php');
$noajax = 1;
$projectid = 3;
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
if (isset($argv[1])) {
   $elemname = $argv[1];
} else {
   print("You must give an element name to run this function.");
   die;
}

$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");

// get a list of land uses
$cbp_listobject->querystring = "  select b.id3 as luname  ";
$cbp_listobject->querystring .= " from cbp_model_location as b ";
$cbp_listobject->querystring .= " where b.scenarioid = $cbp_scenario ";
$cbp_listobject->querystring .= " and b.id1 = 'land' ";
$cbp_listobject->querystring .= " group by b.id3 ";
$cbp_listobject->querystring .= " order by b.id3 ";
print("$cbp_listobject->querystring ; <br>");
$cbp_listobject->performQuery();
$lunames = $cbp_listobject->queryrecords;

$listobject->querystring = " select elementid, elemname from scen_model_element where scenarioid = $scid and objectclass = 'modelContainer' and elemname = '$elemname' ";
print("$listobject->querystring ; <br>\n");
$listobject->performQuery();

$elrecs = $listobject->queryrecords;
$debug = 0;
$i = 0;
// element ID of our land use template object
$landuse_el = 73535;
$serializer = new XML_Serializer();
foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];
   $uid = getUID($listobject, $elid);
   print("Parsing: " .  $thisrec['elemname'] . "($elid / $uid)\n");
   // get all children of type CBPLandDataConnection
   $child_recs = getChildComponentType($listobject, $elid, 'CBPLandDataConnection');
   // delete existing land use objects
   // and just overwrite them with our standard template object
   foreach($child_recs as $thischild) {
      $cid = $thischild['elementid'];
      $delres = deleteModelElement($cid);
      print("Trying to delete $cid - result: " . $delres['innerHTML'] . "<br>\n");
   }
   
   // get land segments matching this unique_id
   $cbp_listobject->querystring = " select \"FIPSAB\" as landseg from tmp_icprb_landuse_baseline where uniqid = '$uid' ";
   print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   $lus = $cbp_listobject->queryrecords;
   $nolu = 0;
   // do updates to the land use child
   foreach ($lus as $thislu) {
      $landseg = $thislu['landseg'];
      print("Cloning $landseg ; <br>");
      $cloneresult = cloneModelElement($scid, $landuse_el);
      $cid = $cloneresult['elementid'];
      print("Updating $cid $landseg ; <br>");

      $msg = runObjectCreate($projectid, $cid);
      updateObjectProps($projectid, $cid, array('name'=>"Land Segment $landseg", 'id2'=>$landseg, 'debug'=>0));
      $linkhtml = createObjectLink($projectid, $scid, $cid, $elid, 1);
      print(print_r($linkhtml,1) . " <br>");   
      // now, select all land segments and update
      $lu_tables = array('baseline', 'current');
      foreach ($lu_tables as $thislu_tab) {

         $cbp_listobject->querystring = " select * from tmp_icprb_landuse_$thislu_tab where uniqid = '$uid' and \"FIPSAB\" = '$landseg' ";
         print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();
         $lutabs = $cbp_listobject->queryrecords;
         foreach ($lutabs as $thistab) {
            // now apply land use
            $landuse = array();
            $k = 0;
            foreach ($lunames as $thislunamerec) {
               $thisluname = $thislunamerec['luname'];
               $landuse[$k]['luname'] = $thisluname;
               $landuse[$k]['1980'] = number_format(floatval($thistab[$thisluname]),2, '.', '');
               $landuse[$k]['2010'] = number_format(floatval($thistab[$thisluname]),2, '.', '');
               $k++;
            }
            print("Trying to apply land use matrix: " . print_r($landuse,1) . "<br>\n");
            $loadres = unSerializeSingleModelObject($cid);
            $thisobject = $loadres['object'];
            if (is_object($thisobject)) {
               //print("$cid object retrieved<br>\n");
               if (is_object($thisobject->processors["landuse_$thislu_tab"])) {
                  print("$cid object landuse found landuse_$thislu_tab<br>\n");
                  if (method_exists($thisobject->processors["landuse_$thislu_tab"], 'assocArrayToMatrix')) {
                     print("$cid object assocArrayToMatrix() exists<br>\n");
                     $thisobject->processors["landuse_$thislu_tab"]->assocArrayToMatrix($landuse);
                     saveObjectSubComponents($listobject, $thisobject, $cid, 1);
                  }
               }
            }
         }
         $i++;
      }
   }
}

print("Finished.  Updated $i landseg/scenario combos.<br>");

?>
</body>

</html>
