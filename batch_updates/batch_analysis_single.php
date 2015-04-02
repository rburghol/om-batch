<?php
// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include("./xajax_modeling.element.php");
include("./lib_verify.php");
//$rundate = '2010-08-13 03:30:00';
$rundate = '2010-10-18';
$starttime = '1984-01-01 00:00:00';
$endtime = '2005-12-31 00:00:00';
//$endrun = '2010-08-05 12:00:00';
$anal_start = '1984-10-01';
$anal_end = '2005-09-30';
$runids = '1';
$force_overwrite = 1;
error_reporting(E_ERROR);
$limit = -1;
$target_order = 1;
$use_gini = 0;
$quick_check = 1; // quick check will only scan the first $quick_num lines of the file and calculate metrics from there
$quick_num = 1000;

if (isset($argv[1])) {
   $runids = $argv[1];
} 

if (isset($argv[2])) {
   $quick_check = $argv[2];
} 
if (isset($argv[3])) {
   // only valid if quick_check = 0
   $use_gini = $argv[3];
} 
if (isset($argv[4])) {
   $one_element = $argv[4];
} else {
   $one_element = '';
}

// create a linkage to the deq2 database in order to use the plR stats package
$dbip2 = 'deq2.bse.vt.edu';
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
$listobject->querystring = "  select a.elementid, a.elemname, d.unique_id, ";
$listobject->querystring .= "    b.output_file as run_file,  ";
$listobject->querystring .= "    b.run_date as run_date, b.runid ";
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
$listobject->querystring .= " and d.mainstem_segment <> 'Y' ";
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

// insert into R-enabled db on deq2 
foreach ($file_recs as $this_filerec) {
   $elementid = $this_filerec['elementid'];
   $eo = getElementOrder($listobject,$elementid);
   if ( ($eo == $target_order) and ($target_order <> -1) ) {
      $run_file = $this_filerec['run_file'];
      $runid = $this_filerec['runid'];
      $model_run_date = $this_filerec['run_date'];
      $run_name = $run_names[$runid];
      $cbp_listobject->querystring = " select run_date from iha_run_analyzed where elementid = $elementid and runid = $runid";
      print("$cbp_listobject->querystring ; \n");
      $cbp_listobject->performQuery();
      $skip = 0;
      $new_rec = 1;
      if (count($cbp_listobject->queryrecords) > 0) {
         // check if the dates match
         $new_rec = 0;
         $last_date = $cbp_listobject->getRecordValue(1,'run_date');
         if ( ($last_date == $model_run_date) and !$force_overwrite) {
            $skip = 1;
            print("Already analyzed model run $run_name ($runid) for $elementid at $last_date, skipping.\n");
         } else {
            print("Model Run Date: $model_run_date, file modified date: $last_date.\n");
         }
      }

      if ($skip == 0) {
         // copy these files to our data analysis directory for IHA processing
         $uid = $this_filerec['unique_id'];

         copy($run_file, "./output_runid$runid/$uid" . ".csv");
         if ($quick_check) {
            $flow_check = verifyRunVars($run_file,array('withdrawal_mgd','discharge_mgd','Qout','area_sqmi'),$quick_num);
            $area_check = getLandUseArea($cbp_listobject, $uid, $run_name);
            $g1 = -1;// cant do gini with quick check
            $g1_6hr = -1;
            $wd = $flow_check['withdrawal_mgd']['mean'];
            if ($wd == '') {
               // this is caused by a bad record linkage
               $wd = 0;
            }
            $cfs = $flow_check['Qout']['mean'];
            $ps = $flow_check['discharge_mgd']['mean'];
            $area_sqmi = $area_check['ls_area'];
            $cfssqmi = $cfs / $area_sqmi;
         } else {

            $unser = unserializeSingleModelObject($elementid);
            $thisobject = $unser['object'];
            //$thisobject->listobject = $analysis_db;
            $dbcoltypes = $thisobject->dbcolumntypes;
            print("Loading $run_file .<br>\n");
           //$analysis_db->debug = 1;
            $darr = delimitedFileToTable($analysis_db, $run_file, ',', 'tmp_analysis_tbl1', 0, -1, array(), $dbcoltypes);
            print("Loaded " . count($darr) . " data lines from file for run $runid.<br>\n");
            //$analysis_db->debug = 0;

            $analysis_db->querystring = "  select ";
            if ($use_gini == 1) {
               $analysis_db->querystring .= " gini(array_accum(qout)) as gini, ";
            } else {
               $analysis_db->querystring .= " -1 as gini, ";
            }
            $analysis_db->querystring .= "    avg(area_sqmi) as area_sqmi, avg(qout) as qout, ";
            $analysis_db->querystring .= "    avg(wd) as wd, avg(ps) as ps ";
            $analysis_db->querystring .= " from ( ";
            $analysis_db->querystring .= "    select thisdate, avg(\"Qout\") as qout, avg(area_sqmi) as area_sqmi, ";
            $analysis_db->querystring .= "       avg(demand_mgd) as wd, avg(discharge_mgd) as ps";
            $analysis_db->querystring .= "    from tmp_analysis_tbl1 ";
            $analysis_db->querystring .= "    where thisdate >= '$anal_start' and thisdate <= '$anal_end' ";
            $analysis_db->querystring .= "    group by thisdate ";
            $analysis_db->querystring .= " ) as foo ";
            //print("$analysis_db->querystring \n");
            $analysis_db->performQuery();
            $g1 = $analysis_db->getRecordValue(1,'gini');
            $wd = $analysis_db->getRecordValue(1,'wd');
            $ps = $analysis_db->getRecordValue(1,'ps');
            $cfs = $analysis_db->getRecordValue(1,'qout');
            $area_sqmi = $analysis_db->getRecordValue(1,'area_sqmi');
            $cfssqmi = $cfs / $area_sqmi;
            if ($use_gini == 1) {
               $analysis_db->querystring = "  select gini(array_accum(qout)) ";
               $analysis_db->querystring .= " from ( ";
               $analysis_db->querystring .= "    select thisdate, \"Qout\" as qout ";
               $analysis_db->querystring .= "    from tmp_analysis_tbl1 ";
               $analysis_db->querystring .= "    where thisdate >= '$anal_start' and thisdate <= '$anal_end' ";
               $analysis_db->querystring .= " ) as foo ";
              // print("$analysis_db->querystring \n");
               $analysis_db->performQuery();
               $g1_6hr = $analysis_db->getRecordValue(1,'gini');
            } else {
               $g1_6hr = -1;
            } 

            // clear old flow data
            $analysis_db->querystring = "  delete from run_data where runid = $runid and elementid = $elementid ";
            print("$analysis_db->querystring \n");
            $analysis_db->performQuery();
            // insert flow data
            $analysis_db->querystring = "  insert into run_data (runid, elementid, thisdate, qout) ";
            $analysis_db->querystring .= " select $runid, $elementid, to_timestamp(\"timestamp\"), \"Qout\" from tmp_analysis_tbl1 ";
            print("$analysis_db->querystring \n");
            $analysis_db->performQuery();
            // remove the temp table
            $analysis_db->querystring = "  drop table tmp_analysis_tbl1 ";
            print("$analysis_db->querystring \n");
            $analysis_db->performQuery();
            $analysis_db->temptables = array();

         }
         print("Element $elementid g1 = $g1 \n");
         $cbp_listobject->querystring = "  select count(*) as numrecs from iha_karst_analysis ";
         $cbp_listobject->querystring .= " where unique_id = '$uid' ";
         $cbp_listobject->querystring .= "    and scenario = '$run_name' ";
         //print("$cbp_listobject->querystring \n");
         $cbp_listobject->performQuery();
         $num = $cbp_listobject->getRecordValue(1,'numrecs');

         // make sure that all numbers are numeric, if not, set to 0/-1
         if ($g1 == '') $g1 = -1;
         if ($gini_ts6 == '') $gini_ts6 = -1;
         if ($cfs == '') $cfs = 0;
         if ($cfssqmi == '') $cfssqmi = 0;
         if ($area_sqmi == '') $area_sqmi = 0;
         if ($ps == '') $ps = 0;
         if ($wd == '') $wd = 0;

         if ($num > 0) {
            $cbp_listobject->querystring = "  update iha_karst_analysis set gini_coeff = $g1, gini_ts6 = $g1_6hr, last_updated = now(), ";
            $cbp_listobject->querystring .= "    runfile_areasqmi = $area_sqmi, runfile_cfssqmi = $cfssqmi, runfile_cfs = $cfs,  ";
            $cbp_listobject->querystring .= "    runfile_ps = $ps, runfile_wd = $wd ";
            $cbp_listobject->querystring .= " where unique_id = '$uid' ";
            $cbp_listobject->querystring .= "    and scenario = '$run_name' ";
         } else {
            $cbp_listobject->querystring = "  insert into iha_karst_analysis (unique_id, scenario, gini_coeff, gini_ts6, last_updated, ";
            $cbp_listobject->querystring .= "    runfile_areasqmi, runfile_cfssqmi, runfile_cfs,  ";
            $cbp_listobject->querystring .= "    runfile_ps, runfile_wd) ";
            $cbp_listobject->querystring .= " values ('$uid', '$run_name', $g1, $g1_6hr, now(), ";
            $cbp_listobject->querystring .= "    $area_sqmi, $cfssqmi, $cfs,  ";
            $cbp_listobject->querystring .= "    $ps, $wd) ";
         }

         print("Gini: $g1\n");
         print("Wd: $wd\n"); 
         print("PS: $ps\n"); 
         print("cfs: $cfs\n"); 
         print("area_sqmi: $area_sqmi\n"); 
         print("cfs/sqmi: $cfssqmi \n");
         $k++;
         print("Complete $k of $total records\n");
         print("$cbp_listobject->querystring \n");
         $cbp_listobject->performQuery();
         if ($new_rec) {
            $cbp_listobject->querystring = "  insert into iha_run_analyzed (elementid, runid, run_date) ";
            $cbp_listobject->querystring .= " values ('$elementid', '$runid', '$model_run_date') ";
         } else {
            $cbp_listobject->querystring = " update iha_run_analyzed set run_date = '$model_run_date' where elementid = $elementid and runid = $runid";
         }
         print("$cbp_listobject->querystring \n");
         $cbp_listobject->performQuery();
      } else {
         $k++; //skipped so just increment the counter
      }
   } 
}

   // summarize time series by date
   // perform gini, add to iha_karst_analysis table on deq1
   // update area_sqmi column and recalculate cfs_sqmi on iha_karst_analysis

?>
