<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('./xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/

$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");

$listobject->querystring = " select elementid, elemname from scen_model_element where scenarioid = $scid and objectclass = 'USGSChannelGeomObject' and elemname not like '%adjustable%' ";
print("$listobject->querystring ; <br>\n");
$listobject->performQuery();

$elrecs = $listobject->queryrecords;
$debug = 0;
$i = 0;
$serializer = new XML_Serializer();
$bad_elems = array();

foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];
   print("Parsing: " .  $thisrec['elemname'] . "\n");
   list($t1, $t2, $sm) = explode(' ', $thisrec['elemname']);
   $cbp_listobject->querystring = "  select total_area, local_area, local_length, local_slope from icprb_watersheds ";
   $cbp_listobject->querystring .= " where shed_merge = '$sm' ";
   $cbp_listobject->performQuery();
   if (count($cbp_listobject->queryrecords) == 0) {
      print("$cbp_listobject->querystring ; \n");
      $bad_elems[] = $elid;
      $i++;
   }
   /*
   $total_area = $cbp_listobject->getRecordValue(1,'total_area');
   $local_area = $cbp_listobject->getRecordValue(1,'local_area');
   $local_length = $cbp_listobject->getRecordValue(1,'local_length');
   $local_slope = $cbp_listobject->getRecordValue(1,'local_slope');
   print("Updating: " .  $thisrec['elemname'] . " with total_area = $drainage_area and local_area = $local_area length = (5280.0 * $local_length), slope = ($local_slope / 100.0)\n");
   $prop_array = array('drainage_area'=>$total_area, 'area_sqmi'=>$local_area, 'length'=>(5280.0 * $local_length), 'slope'=>($local_slope / 100.0));
   updateObjectProps($projectid, $elid, $prop_array);
   
   $i++;
   //break;
   */
}

print(print_r($bad_elems,1) . "<br>\n");

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
