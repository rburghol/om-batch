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

error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");

$listobject->querystring = " select elementid, elemname from scen_model_element where scenarioid = $scid and objectclass = 'modelContainer' and length(elemname) = 13 ";
if (isset($argv[1])) {
   $listobject->querystring .= " and elementid = " . $argv[1];
}
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
   $sm = $thisrec['elemname'];
   $listobject->querystring = "  select a.contrib_area_sqmi as total_area, ";
   $listobject->querystring .= "    CASE WHEN b.local_area IS NOT NULL THEN b.local_area ";
   $listobject->querystring .= "    ELSE a.local_area_sqmi END as local_area, ";
   $listobject->querystring .= "    CASE WHEN b.local_slope IS NOT NULL THEN b.local_slope ";
   $listobject->querystring .= "    ELSE 0.01 END as local_slope, ";
   $listobject->querystring .= "    CASE WHEN b.local_length IS NOT NULL THEN b.local_length ";
   $listobject->querystring .= "    ELSE a.channel_length_ft END as local_length ";
   $listobject->querystring .= " from sc_cbp5 as a left outer join icprb_watersheds as b ";
   $listobject->querystring .= " on (a.catcode2 = b.cbp_segmentid and b.unique_id is null ) ";
   $listobject->querystring .= " where a.catcode2 = '$sm' ";
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   if (count($listobject->queryrecords) == 0) {
      $bad_elems[] = $elid;
   } else {
      $total_area = $listobject->getRecordValue(1,'total_area');
      $local_area = $listobject->getRecordValue(1,'local_area');
      $local_length = $listobject->getRecordValue(1,'local_length');
      $slope = $listobject->getRecordValue(1,'local_slope') / 100.0;
      $child_rec = getChildComponentType($listobject, $elid, 'USGSChannelGeomObject', 1);
      $thischild = $child_rec[0];
      $cid = $thischild['elementid'];
      print("Updating: " .  $thisrec['elemname'] . "( $cid ) with total_area = $total_area and local_area = $local_area length = $local_length \n");
      $prop_array = array('drainage_area'=>$total_area, 'area'=>$local_area, 'length'=>$local_length, 'slope'=>$slope);
      updateObjectProps($projectid, $cid, $prop_array);
      
      $i++;
   }
}

print(print_r($bad_elems,1) . "<br>\n");

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
