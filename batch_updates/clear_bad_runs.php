<?php


// checks for files/runs fidelity - clears them if they fail vertain tests

// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include("./xajax_modeling.element.php");
include("./lib_verify.php");
//$rundate = '2010-08-13 03:30:00';
$rundate = '2010-09-16 15:30:00';
$starttime = '1984-01-01 00:00:00';
$endtime = '2005-12-31 00:00:00';
//$endrun = '2010-08-05 12:00:00';
$anal_start = '1984-10-01';
$anal_end = '2005-09-30';
$runids = "1";
$debug = 0;
$target_order = 1;
$overwrite = 0;
$loval = 0.5;
$hival = 1.9;

if (isset($argv[1])) {
   $tests = $argv[1];
} else {
   $tests = '1';
}
if (isset($argv[2])) {
   $elid = $argv[2];
} else {
   $elid = '';
}
if (isset($argv[3])) {
   $runids = $argv[3];
} 
if (isset($argv[4])) {
   $target_order = $argv[4];
} 
if (isset($argv[5])) {
   $overwrite = $argv[5];
} 

error_reporting(E_ALL);

// create link to summary table
// create a linkage to the deq2 database in order to use the plR stats package
$dbip2 = 'localhost';
$dbname2 = 'cbp';
$dbconn = pg_connect("host=$dbip2 port=5432 dbname=$dbname2 user=$dbuser password=$dbpass");
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;


// obtain data file
$listobject->querystring = "  select a.elementid, a.elemname, d.unique_id, ";
$listobject->querystring .= "    b.output_file as run_file,  ";
$listobject->querystring .= "    b.run_date as run_date, b.runid ";
$listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b, ";
$listobject->querystring .= "    icprb_watersheds as d ";
$listobject->querystring .= " where a.scenarioid = 28 ";
$listobject->querystring .= " and a.objectclass = 'modelContainer' ";
$listobject->querystring .= " and a.elementid = b.elementid ";
if ($elid <> '') {
   $listobject->querystring .= " and a.elementid = $elid ";
}
$listobject->querystring .= " and b.runid in ( $runids) ";
$listobject->querystring .= " and b.starttime = '$starttime' ";
$listobject->querystring .= " and b.endtime = '$endtime' ";
$listobject->querystring .= " and b.run_date >= '$rundate' ";
$listobject->querystring .= " and a.elemname = d.shed_merge ";
$listobject->querystring .= " and d.mainstem_segment <> 'Y' ";
$listobject->querystring .= " order by elemname ";
print("$listobject->querystring \n");
$listobject->performQuery();
print("Records: " . count($listobject->queryrecords) . "<br>\n");
$file_recs = $listobject->queryrecords;
$total = count($file_recs);
$numbad = 0;
$i =0;

$run_names = array(1=>'baseline', 2=>'current');
// insert into R-enabled db on deq2 
foreach ($file_recs as $this_filerec) {
   $elementid = $this_filerec['elementid'];
   $elemname = $this_filerec['elemname'];
   $run_file = $this_filerec['run_file'];
   $run_date = $this_filerec['run_date'];
   $runid = $this_filerec['runid'];
   $sname = $run_names[$runid];

   $order = getElementOrder($listobject, $elementid);
   error_log ("Element: $elementid = $order order \n");
   if ( ($target_order > 0) and ($target_order == $order) ) {
      $testar = split(",", $tests);
      foreach ($testar as $t) {
         error_log("Performing test $t \n");
         switch ($t) {
            case 1:
            // numeric boundary test for cfs/sqmi
            // go ahead and check to see if this element OR any of its children are bad
            $container_tree = getNestedContainers($listobject, $elementid);
            foreach ($container_tree as $thisone) {
               $thisid = $thisone['elementid'];
               $uid = getUID($listobject,$thisid);
               $isbad = checkUnitFlow($cbp_listobject, $uid, $sname, $loval, $hival, $debug);
               if ($isbad > 0) {
                  // if this or any child is bad, we must assume this is bad
                  print("Element $elemname - $thisid ($uid - $order order) of tree $elementid is bad - deleting run record from $run_date\nRun File: $run_file \n");
                  if ($overwrite) {
                     deleteRunRecord($listobject, $elementid, $runid);
                  }
                  $info = getRunAnalysisData($cbp_listobject, $uid, $sname);
                  $data = $info['data'];
                  $ua = $data['runfile_cfssqmi'];
                  $fl = $data['runfile_cfs'];
                  $ar = $data['runfile_areasqmi'];
                  $rv = $data['verified_runmode'];
                  print("Area: $ar, Flow: $fl, Unit-Flow: $ua - Run Mode Verified? $rv\n");
                  if ($debug) { print_r($data); }
                  break;
               }
            }
            break;
         }
      }
   } 
   
}

?>
