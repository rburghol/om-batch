<?php
// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include("./xajax_modeling.element.php");
include("./lib_verify.php");
//$rundate = '2010-08-13 03:30:00';
$rundate = '2010-09-30';
$starttime = '1984-01-01 00:00:00';
$endtime = '2005-12-31 00:00:00';
//$endrun = '2010-08-05 12:00:00';
$anal_start = '1984-10-01';
$anal_end = '2005-09-30';
$runids = '1';
$target_order = 1;
$force_overwrite = 1;
error_reporting(E_ERROR);
$limit = -1;
$quick_num = 100;

if (isset($argv[1])) {
   $runids = $argv[1];
} 
if (isset($argv[2])) {
   $target_order = $argv[2];
} 
if (isset($argv[3])) {
   $one_element = $argv[3];
} else {
   $one_element = '';
}

// create a linkage to the deq2 database in order to use the plR stats package
$dbip2 = 'localhost';
$dbname2 = 'analysis';
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

$run_names = array(
   1=>'baseline',
   2=>'current'
);

// obtain data file
$listobject->querystring = "  select a.elementid, a.elemname, d.unique_id, d.local_area, ";
$listobject->querystring .= "    b.output_file as run_file, b.run_date, b.runid  ";
$listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b, ";
$listobject->querystring .= "    icprb_watersheds as d ";
$listobject->querystring .= " where a.scenarioid = 28 ";
$listobject->querystring .= " and a.objectclass = 'modelContainer' ";
$listobject->querystring .= " and a.elementid = b.elementid ";
$listobject->querystring .= " and b.runid in ($runids) ";
$listobject->querystring .= " and b.starttime = '$starttime' ";
$listobject->querystring .= " and b.endtime = '$endtime' ";
$listobject->querystring .= " and b.run_date >= '$rundate' ";
$listobject->querystring .= " and a.elemname = d.shed_merge ";
if ($one_element <> '') {
   $listobject->querystring .= " and a.elementid = '$one_element'  ";
}
$listobject->querystring .= " order by elemname ";
if ($limit > 0) {
   $listobject->querystring .= " LIMIT $limit  ";
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
   $run_file = $this_filerec['run_file'];
   $pname = $this_filerec['elemname'];
   $runid = $this_filerec['runid'];
   $model_run_date = $this_filerec['run_date'];
   $local_area = $this_filerec['local_area'];
   $run_name = $run_names[$runid];
   $order = getElementOrder($listobject, $elementid);
   if ( ($target_order > 0) and ($target_order == $order) ) {
      $cbp_listobject->querystring = " select run_date from iha_run_analyzed where elementid = $elementid and runid = $runid";
      $cbp_listobject->performQuery();
      $skip = 0;
      if ($skip == 0) {
         // copy these files to our data analysis directory for IHA processing
         $uid = $this_filerec['unique_id'];

         $flow_check = verifyRunVars($run_file,array('withdrawal_mgd','discharge_mgd','Qout','area_sqmi','run_mode'),$quick_num);
         $g1 = -1;// cant do gini with quick check
         $wd = $flow_check['withdrawal_mgd']['mean'];
         if ($wd == '') {
            // this is caused by a bad record linkage
            $wd = 0;
         }
         $cfs = $flow_check['Qout']['mean'];
         $ps = $flow_check['discharge_mgd']['mean'];
         $area_sqmi = $flow_check['area_sqmi']['mean'];
         $prm = $flow_check['run_mode']['last'];
         $cfssqmi = $cfs / $area_sqmi;
         // checking to see if run mode is set in children of type USGSChannelGeomObject
         $children = array('CBPLandDataConnection','dataConnectionObject');
         $failed = '';
         $passed = '';
         $cd = '';
         $pd = '';
         $child_area = 0;
         if ($runid <> $prm) {
            // parent run mode disagrees
            $failed .= "Parent Run mode $prm disagrees with runid $runid \n";
         }
         foreach ($children as $thischildtype) {
            $child_rec = getChildComponentType($listobject, $elementid, $thischildtype, 1);
            foreach($child_rec as $thischild) {
               $cid = $thischild['elementid'];
               $child_file = getRunFile($listobject, $cid, $runid);
               $childname = $child_file['elemname'];
               $cv = verifyRunVars($child_file['output_file'],array('run_mode'),10);
               if ($cv['run_mode']['last'] <> $prm) {
                  $failed .= "$cd$childname ($cid) - run_mode = " . $cv['run_mode']['last'];
                  $cd = ',';
               } else {
                  $passed .= "$pd$childname ($cid) - run_mode = " . $cv['run_mode']['last'];
                  $pd = ',';
               }
               if ($thischildtype == 'CBPLandDataConnection') {
                  // add up child area
                  $child_area += $cv['area_sqmi']['last'];
               }
            }
         }
         if (strlen($failed) > 0) {
            $bad_recs++;
            print("Run Mode of Parent $elementid : $pname : $uid = $prm:\n ");
            print(" - FAILED $failed\n");
            print(" - PASSED $passed\n");
            print(" - Info area_sqmi = $area_sqmi, local_area = $local_area, child_area = $child_area \n");
            $cbp_listobject->querystring = "  update iha_karst_analysis set verified_runmode = 0 ";
            $cbp_listobject->querystring .= " where unique_id = '$uid' and scenario = '$run_name' ";
   error_log($cbp_listobject->querystring);
            $cbp_listobject->performQuery();
         } else {
            $cbp_listobject->querystring = "  update iha_karst_analysis set verified_runmode = 1 ";
            $cbp_listobject->querystring .= " where unique_id = '$uid' and scenario = '$run_name' ";
            $cbp_listobject->performQuery();
         }
      } 
      $k++;
      error_log("Complete $k of $total records ($bad_recs failed, $skip_recs skipped)\n");
   } else {
      $skip_recs++;
   }
}

?>
