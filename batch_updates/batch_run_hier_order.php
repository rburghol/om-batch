<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");

$scid = 28;
$resume = 1; // set this to 1 if we are resuming an interupted batch, this will cause it to check the run log since the rundate
$rundate = '2010-09-17';
$run_ids = array(1,2);
$startdate = '1984-01-01';
$enddate = '2005-12-31';
$cache_date = '2010-09-16';
$years = 20;
// specify max models to run at a time
$max_simultaneous = 9;
$sleep_factor = $years * 10; // give it some time to accumulate  a cache

foreach ($run_ids as $thisid) {
   $listobject->querystring = "  select elementid from scen_model_element ";
   $listobject->querystring .= " where scenarioid = $scid ";
   $listobject->querystring .= "    and objectclass = 'modelContainer' ";
   // this gets us only ICPRB segments, we can do the CBP ones later when we need to
   $listobject->querystring .= "    and length(elemname) >= 16 ";
   // these segments which cross model boundaries will be run as part of a CBP container later
   $listobject->querystring .= "    and elemname not in ( ";
   $listobject->querystring .= "       select shed_merge from icprb_watersheds where mainstem_segment = 'Y' ";
   $listobject->querystring .= "    ) ";
   print("$listobject->querystring \n");
   $listobject->performQuery();
   $heap = $listobject->queryrecords;
   
   $current_order = 1;
   
   while ( count($heap) > 0 ) {
      // if it is a 1st order segment, we should have no need to re-run it
      $thisrec = array_shift($heap);
      $recid = $thisrec['elementid'];
      // need to check to see if we need to increment the value of $current_order
      if (in_array($recid, $checked)) {
         // if it is already been checked at this level, we go ahead and increment the order level
         print("Ran " . count($checked) . " of Order $current_order \n");
         $current_order++;
         $checked = array();
      }
      $order = getElementOrder($listobject, $recid);
      // check to see if it is on the order that we are on
      if ($order == $current_order) {
         // run it, unless it is 1st order, AND it has existing files for each of the runs that we want
         
         // check to see if it has already been run, if we are resuming a run that was stopped
         if ($resume and checkRunDate($listobject, $recid, $thisid, $rundate)) {
            print("Already ran $recid in this batch. \n");
         } else {
         
            print("Run $thisid, Order $current_order running $recid \n");

            $waiting = 1;
            while ( $waiting )  {
               $active_models = returnPids('php');
               $num_active = count($active_models);
               //print("Only $num_active \n");
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

      } else {
         // stick it back on the heap
         $heap[] = $thisrec;
         $checked[] = $recid;
      }
   }
}

print("Done.\n");
   
?>