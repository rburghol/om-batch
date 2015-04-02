<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");

$scid = 28;
$resume = 1; // set this to 1 if we are resuming an interupted batch, this will cause it to check the run log since the rundate
$rundate = '2010-10-01 12:00:00';
$run_ids = array(1);
$startdate = '1984-01-01';
//$enddate = '1984-12-31';
$enddate = '2005-12-31';
$cache_date = '2010-10-18 12:00:00';
$start_year = intval(date('Y', strtotime($startdate)));
$end_year = intval(date('Y', strtotime($enddate)));
$years = $end_year - $start_year;
// specify max models to run at a time
$max_simultaneous = 9;
$sleep_factor = $years * 10; // give it some time to accumulate  a cache
$target_order = 1;
$limit = -1;

if (isset($argv[1])) {
   $operation = $argv[1];
} else {
   $operation = 0;
}
if (isset($argv[2])) {
   $elemname = $argv[2];
} else {
   $elemname = '';
}
if (isset($argv[3])) {
   $run_ids = split(',',$argv[3]);
}
if (isset($argv[4])) {
   $target_order = $argv[4];
}

$run_names = array(
   1=>'baseline',
   2=>'current'
);

$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;


foreach ($run_ids as $thisid) {
   $run_name = $run_names[$thisid];
   if ($operation == 3) {
      $elemlist = getZeroFlowElements($cbp_listobject, $run_name, 1);
   }
   print($elemlist . "\n");


   $elems = getICPRBElements($listobject, $scid, 1, 1);
   $order_elems = screenTargetOrder($listobject, $elems, $target_order, 1);
   // get only bad runs, set t_rm = 0, else set it to 1
   print_r($order_elems);
   $t_rm = 0;
   $rm_elems = screenRunMode($listobject, $cbp_listobject, $order_elems, $t_rm, $run_name, 0);
   print_r($rm_elems);
   die;
   
   $listobject->querystring = "  select elementid, elemname from scen_model_element ";
   $listobject->querystring .= " where scenarioid = $scid ";
   $listobject->querystring .= "    and objectclass = 'modelContainer' ";
   // this gets us only ICPRB segments, we can do the CBP ones later when we need to
   $listobject->querystring .= "    and elemname not in (";
   $listobject->querystring .= "       select shed_merge  ";
   $listobject->querystring .= "       from icprb_watersheds ";
   $listobject->querystring .= "       where mainstem_segment = 'Y' ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= "    and elementid in (";
   $listobject->querystring .= "       select dest_id ";
   $listobject->querystring .= "       from map_model_linkages ";
   $listobject->querystring .= "       where scenarioid = $scid and linktype = 1";
   $listobject->querystring .= "    ) ";
   // screens for icprb segments ONLY
   $listobject->querystring .= "    and length(elemname) >= 16 ";
   if ( ($operation == 2) and (strlen($elemname) > 0) ) {
      $listobject->querystring .= "    and elemname = '$elemname' ";
   }
   if ( ($operation == 3) and (strlen($elemlist) > 0) ) {
      $listobject->querystring .= "    and elemname in ($elemlist) ";
   }
   if ( ($operation == 4) and (strlen($elemname) > 0) ) {
      $listobject->querystring .= "    and elementid in ($elemname) ";
   }
   if ($limit > 0) {
      $listobject->querystring .= "    LIMIT $limit ";
   }
   print("$listobject->querystring \n");
   $listobject->performQuery();
   $allrecs = $listobject->queryrecords;
   $heap = array();
   foreach ($allrecs as $thisrec) {
      if ( ($elemname <> '') or ($target_order == getElementOrder($listobject, $thisrec['elementid'])) ) {
         $heap[] = $thisrec;
      }
   }
   
   if ($operation == 1) {
      // just return the status of the current run in progress
      $comp = 0;
      $running = 0;
      $zombie = 0;
      $zombies = array();
      $finishing = 0;
      $total = count($heap);
      $numchecked = 0;
      print("<br>\n Checking .");
      foreach ($heap as $thisrec) {
         $recid = $thisrec['elementid'];
         if (checkRunDate($listobject, $recid, $thisid, $cache_date)) {
            $comp++;
         }
         $numchecked++;
         if ( ($numchecked / 10) == intval($numchecked/10)) {
            print(" . ");
            flush();
         }
         $status_vars = verifyRunStatus($listobject, $recid);
         $status = $status_vars['status_flag'];
         switch ($status) {
            case -1:
            $zombie++;
            $zombies[] = array('element_id'=>$recid, 'last_message'=>$status_vars['status_mesg']);
            break;
            
            case 1:
            $running++;
            break;
            
            case 2:
            $finishing++;
            break;
         }
      }
      print("<br>\n");
      print("Level: $target_order <br>\n");
      print("Total: $total <br>\n");
      print("Completed: $comp <br>\n");
      print("Running: $running <br>\n");
      print("Finishing: $finishing <br>\n");
      print("Zombie: $zombie <br>\n");
      print(print_r($zombies,1) . "<br>\n");
      die;
   }
   
//   foreach ( $heap as $thisrec ) {
   while ( count($heap) > 0 ) {
      $thisrec = array_pop($heap);
      // check to see if it has already been run, if we are resuming a run that was stopped
      $recid = $thisrec['elementid'];
      $recname = $thisrec['elemname'];
      
      if (($operation <> 2) and $resume and checkRunDate($listobject, $recid, $thisid, $cache_date)) {
         print("Already ran $recid / $recname in this batch. \n");
      } else {

         $now = date('r');
         print("$now : Run $thisid / $recname, Order $current_order running $recid \n");

         $waiting = 1;
         while ( $waiting )  {
            $active_models = returnPids('php');
            $num_active = count($active_models);
            $status_vars = verifyRunStatus($modeldb, $recid);
            if ($status_vars['status'] == 1) {
               // this model is running, put it back on the heap
               print("Model container $recid is currently running in another job. Putting back on heap.\n");
               array_push($heap, $thisrec);
               break;
            }
            //print("Only $num_active \n");
            // need to modify this to check to make sure this element is not already running
            // for now, we just assume that it is not
            if ($num_active < $max_simultaneous) {
               // spawn a new one
               $prop_array = array('run_mode' => $thisid, 'debug' => 0);
               updateObjectProps($projectid, $recid, $prop_array);
               $arrOutput = array();
               print("Only $num_active out of $max_simultaneous models running.  Spawning model run for element $recid.<br>\n");
               $command = "$php_exe -f $basedir/test_modelrun.php $recid $startdate $enddate $thisid $cache_date";
               print( "Spawning process for $recid <br>");
               print("$command > /dev/null &");
               error_log("$command > /dev/null &");
               $forkout = exec( "$command > /dev/null &", $arrOutput );
               $waiting = 0;
            }
            sleep($sleep_factor);   
         }
      }
   }
}

print("Done.\n");
   
?>
