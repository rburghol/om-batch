<?php
// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include("./xajax_modeling.element.php");
include("./lib_verify.php");

$startdate = '1984-01-01';
$enddate = '2005-12-31';

if ( ($argv[1] == '--help') or (count($argv) < 2)) {
   print("Usage: php verify_runfiles.php runid rundate [element_name use \"\" for all] [startdate] [enddate] \n");
   die;
}
if (isset($argv[1])) {
   $runids = $argv[1];
} 
if (isset($argv[2])) {
   $rundate = $argv[2];
} 
if (isset($argv[3])) {
   $one_element = $argv[3];
} else {
   $one_element = '';
}
if (isset($argv[4])) {
   $startdate = $argv[4];
}
if (isset($argv[5])) {
   $enddate = $argv[5];
}


// obtain data file
if ($one_element <> '') {
   $listobject->querystring = "  select a.elementid, a.elemname, ";
   $listobject->querystring .= "    b.output_file as run_file, b.run_date, b.runid  ";
   $listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b ";
   $listobject->querystring .= " where a.scenarioid = 28 ";
   $listobject->querystring .= " and a.objectclass = 'modelContainer' ";
   $listobject->querystring .= " and a.elementid = b.elementid ";
   $listobject->querystring .= " and b.runid in ($runids) ";
   $listobject->querystring .= " and b.starttime <= '$startdate' ";
   $listobject->querystring .= " and b.endtime >= '$enddate' ";
   $listobject->querystring .= " and a.elementid = '$one_element'  ";
} else {
   $listobject->querystring = "  select a.elementid, a.elemname, d.unique_id, d.local_area, ";
   $listobject->querystring .= "    b.output_file as run_file, b.run_date, b.runid  ";
   $listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b, ";
   $listobject->querystring .= "    icprb_watersheds as d ";
   $listobject->querystring .= " where a.scenarioid = 28 ";
   $listobject->querystring .= " and a.objectclass = 'modelContainer' ";
   $listobject->querystring .= " and a.elementid = b.elementid ";
   $listobject->querystring .= " and b.runid in ($runids) ";
   $listobject->querystring .= " and b.starttime <= '$startdate' ";
   $listobject->querystring .= " and b.endtime >= '$enddate' ";
   $listobject->querystring .= " and b.run_date >= '$rundate' ";
   //$listobject->querystring .= " and d.mainstem_segment <> 'Y' ";
   $listobject->querystring .= " and ( ";
   //$listobject->querystring .= "    (a.elemname = d.shed_merge) ";
  // $listobject->querystring .= "    OR ";
   $listobject->querystring .= "    (a.elemname = d.cbp_segmentid and d.shed_merge is null) ";
   $listobject->querystring .= " ) ";
   $listobject->querystring .= " order by elemname ";
   if ($limit > 0) {
      $listobject->querystring .= " LIMIT $limit  ";
   }
}
print("$listobject->querystring \n");

$listobject->performQuery();
$total = count($listobject->queryrecords);
print("Records: " . count($listobject->queryrecords) . "<br>\n");
$file_recs = $listobject->queryrecords;
$bad_recs = 0;
$skip_recs = 0;

// insert into R-enabled db on deq2 
foreach ($file_recs as $this_filerec) {
   $elementid = $this_filerec['elementid'];
   $runid = $this_filerec['runid'];
   print("Summarizing " . $this_filerec['elemname'] . " ($elementid) ... ");
   summarizeRun($listobject, $elementid, $runid, $startdate, $enddate, 1);
   //$rundata = retrieveRunSummary($listobject, $recid, $runid);
   print("Done. \n");

}

?>
