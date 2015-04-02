<?php
// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include("../xajax_modeling.element.php");
//$rundate = '2010-08-13 03:30:00';
$rundate = '2010-09-16 15:30:00';
$starttime = '1984-01-01 00:00:00';
$endtime = '2005-12-31 00:00:00';
//$endrun = '2010-08-05 12:00:00';
$anal_start = '1984-10-01';
$anal_end = '2005-09-30';
$runid1 = 1;
$runid2 = 2;
error_reporting(E_ALL);

if (isset($argv[1])) {
   $elname = $argv[1];
} else {
   die "You must submit an element name, i.e,.: php single_analysis.php element_name \n";
}

// create a linkage to the deq2 database in order to use the plR stats package
$dbip2 = '128.173.217.24';
$dbname2 = 'model';
$dbconn = pg_connect("host=$dbip2 port=5432 dbname=$dbname2 user=$dbuser password=$dbpass");
$analysis_db = new pgsql_QueryObject;
$analysis_db->dbconn = $dbconn;
// linkage to cbp database with ICPRB info
$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

/*
$analysis_db->querystring = "select * from project";
$analysis_db->performQuery();
$analysis_db->showList();
*/

// obtain data files
$listobject->querystring = "  select a.elementid, a.elemname, d.uniq_id as unique_id, ";
$listobject->querystring .= "    b.output_file as run1_file, c.output_file as run2_file ";
$listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b, ";
$listobject->querystring .= "    scen_model_run_elements as c, tmp_icprb_localshapes as d ";
$listobject->querystring .= " where a.scenarioid = 28 ";
$listobject->querystring .= " and a.elemname = '$elname' ";
$listobject->querystring .= " and a.objectclass = 'modelContainer' ";
$listobject->querystring .= " and a.elementid = b.elementid ";
$listobject->querystring .= " and b.runid = $runid1 ";
$listobject->querystring .= " and b.starttime = '$starttime' ";
$listobject->querystring .= " and b.endtime = '$endtime' ";
$listobject->querystring .= " and b.run_date >= '$rundate' ";
$listobject->querystring .= " and a.elementid = c.elementid ";
$listobject->querystring .= " and c.runid = $runid2 ";
$listobject->querystring .= " and c.starttime = '$starttime' ";
$listobject->querystring .= " and c.endtime = '$endtime' ";
$listobject->querystring .= " and c.run_date >= '$rundate' ";
$listobject->querystring .= " and a.elemname = d.shed_merge ";
$listobject->querystring .= " order by elemname ";
print("$listobject->querystring \n");
$listobject->performQuery();
print("Records: " . count($listobject->queryrecords) . "<br>\n");
$file_recs = $listobject->queryrecords;

// insert into R-enabled db on deq2 
foreach ($file_recs as $this_filerec) {
   $elementid = $this_filerec['elementid'];
   $run1_file = $this_filerec['run1_file'];
   $run2_file = $this_filerec['run2_file'];
   // copy these files to our data analysis directory for IHA processing
   $uid = $this_filerec['unique_id'];
   copy($run1_file, "./output_runid1/$uid" . ".csv");
   copy($run2_file, "./output_runid2/$uid" . ".csv");
   $unser = unserializeSingleModelObject($elementid);
   $thisobject = $unser['object'];
   //$thisobject->listobject = $analysis_db;
   $dbcoltypes = $thisobject->dbcolumntypes;
   print("Loading $run1_file .<br>\n");
   //$analysis_db->debug = 1;
   $darr = delimitedFileToTable($analysis_db, $run1_file, ',', 'tmp_analysis_tbl1', 0, -1, array(), $dbcoltypes);
   print("Loaded " . count($darr) . " data lines from file 1.<br>\n");
   print("Loading $run2_file .<br>\n");
   $darr = delimitedFileToTable($analysis_db, $run2_file, ',', 'tmp_analysis_tbl2', 0, -1, array(), $dbcoltypes);
   print("Loaded " . count($darr) . " data lines from file 2.<br>\n");
   //$analysis_db->debug = 0;
   
   $analysis_db->querystring = "  select gini(array_accum(qout)) from ( ";
   $analysis_db->querystring .= "    select thisdate, avg(\"Qout\") as qout from tmp_analysis_tbl1 ";
   $analysis_db->querystring .= "    where thisdate >= '$anal_start' and thisdate <= '$anal_end' ";
   $analysis_db->querystring .= "    group by thisdate ";
   $analysis_db->querystring .= " ) as foo ";
   print("$analysis_db->querystring \n");
   $analysis_db->performQuery();
   $g1 = $analysis_db->getRecordValue(1,'gini');
   $analysis_db->querystring = "  select gini(array_accum(qout)) from ( ";
   $analysis_db->querystring .= "    select thisdate, avg(\"Qout\") as qout from tmp_analysis_tbl2 ";
   $analysis_db->querystring .= "    where thisdate >= '$anal_start' and thisdate <= '$anal_end' ";
   $analysis_db->querystring .= "    group by thisdate ";
   $analysis_db->querystring .= " ) as foo ";
   print("$analysis_db->querystring \n");
   $analysis_db->performQuery();
   $g2 = $analysis_db->getRecordValue(1,'gini');
   
   $analysis_db->querystring = "  drop table tmp_analysis_tbl1 ";
   $analysis_db->performQuery();
   $analysis_db->querystring = "  drop table tmp_analysis_tbl2 ";
   $analysis_db->performQuery();
   $analysis_db->temptables = array();
   
   print("Element $elementid g1 = $g1, g2 = $g2 \n");
   $cbp_listobject->querystring = "  select count(*) as numrecs from iha_karst_analysis ";
   $cbp_listobject->querystring .= " where unique_id = '$uid' ";
   $cbp_listobject->querystring .= "    and scenario = 'baseline' ";
   print("$cbp_listobject->querystring \n");
   $cbp_listobject->performQuery();
   $num = $cbp_listobject->getRecordValue(1,'numrecs');
   if ($num > 0) {
      $cbp_listobject->querystring = "  update iha_karst_analysis set gini_coeff = $g1 ";
      $cbp_listobject->querystring .= " where unique_id = '$uid' ";
      $cbp_listobject->querystring .= "    and scenario = 'baseline' ";
   } else {
      $cbp_listobject->querystring = "  insert into iha_karst_analysis (unique_id, scenario, gini_coeff) ";
      $cbp_listobject->querystring .= " values ('$uid', 'baseline', $g1) ";
   }
   print("$cbp_listobject->querystring \n");
   $cbp_listobject->performQuery();
   $cbp_listobject->querystring = "  select count(*) as numrecs from iha_karst_analysis ";
   $cbp_listobject->querystring .= " where unique_id = '$uid' ";
   $cbp_listobject->querystring .= "    and scenario = 'current' ";
   print("$cbp_listobject->querystring \n");
   $cbp_listobject->performQuery();
   $num = $cbp_listobject->getRecordValue(1,'numrecs');
   if ($num > 0) {
      $cbp_listobject->querystring = "  update iha_karst_analysis set gini_coeff = $g2 ";
      $cbp_listobject->querystring .= " where unique_id = '$uid' ";
      $cbp_listobject->querystring .= "    and scenario = 'current' ";
   } else {
      $cbp_listobject->querystring = "  insert into iha_karst_analysis (unique_id, scenario, gini_coeff) ";
      $cbp_listobject->querystring .= " values ('$uid', 'current', $g2) ";
   }
   print("$cbp_listobject->querystring \n");
   $cbp_listobject->performQuery();
   
}

   // summarize time series by date
   // perform gini, add to iha_karst_analysis table on deq1
   // update area_sqmi column and recalculate cfs_sqmi on iha_karst_analysis

?>
