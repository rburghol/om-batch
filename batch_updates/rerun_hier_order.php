<?php

include('./xajax_modeling.element.php');

function getElementOrder($listobject, $elementid) {
   
   $order = 0;
   $listobject->querystring = "  select src_id from map_model_linkages where dest_id = $elementid and linktype = 1";
   //print("$listobject->querystring \n");
   $listobject->performQuery();
   $child_recs = $listobject->queryrecords;
   
   $biggest_child = -1;
   foreach ($child_recs as $this_child) {
      $child_id = $this_child['src_id'];
      $child_order = getElementOrder($listobject, $child_id);
      if ($child_order > $biggest_child) {
         $biggest_child = $child_order;
      }
   }
   
   if ($biggest_child >= $order) {
      $order = $biggest_child + 1;
   }
   return $order;
}

function checkRunDate($listobject, $elementid, $runid, $rundate) {
   $listobject->querystring = "  select count(*) as numruns from scen_model_run_elements where runid = $runid and elementid = $elementid and run_date >= '$rundate' ";
   print("$listobject->querystring \n");
   $listobject->performQuery();
   $runs = $listobject->getRecordValue(1,'numruns');
   if ($runs == 0) {
      return FALSE;
   } else {
      return TRUE;
   }
}

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

// set up connection to the analysis database
$dbname2 = 'model';
$dbconn = pg_connect("host=$dbip2 port=5432 dbname=$dbname2 user=$dbuser password=$dbpass");
$analysis_db = new pgsql_QueryObject;
$analysis_db->dbconn = $dbconn;

// get the list of elements that needs to be re-run
$analysis_db->querystring = "  select d.shed_merge, a.gini_coeff as new_base_g, "; 
$analysis_db->querystring .= " from iha_karst_analysis as a, iha_karst_analysis as b, "; 
$analysis_db->querystring .= " iha_karst_analysis as c, icprb_watersheds as d, "; 
$analysis_db->querystring .= " iha_karst_analysis as e "; 
$analysis_db->querystring .= " where a.unique_id = b.unique_id "; 
$analysis_db->querystring .= " and a.unique_id = e.unique_id "; 
$analysis_db->querystring .= " and e.scenario = 'old_current' "; 
$analysis_db->querystring .= " and a.scenario = 'baseline' "; 
$analysis_db->querystring .= " and b.scenario = 'old_baseline' "; 
$analysis_db->querystring .= " and c.scenario = 'current' "; 
$analysis_db->querystring .= " and a.unique_id = c.unique_id "; 
$analysis_db->querystring .= " and a.gini_coeff = c.gini_coeff "; 
$analysis_db->querystring .= " and b.gini_coeff <> e.gini_coeff "; 
$analysis_db->querystring .= " and  a.unique_id = d.unique_id "; 
$analysis_db->querystring .= " and a.gini_ts6 is not null"; 
print("$analysis_db->querystring ; <br>\n");
$analysis_db->performQuery();
$elemlist = '';
foreach ($analysis_db->queryrecords as $thisrec) {
   $elemlist .= $eldel . $thisrec['shed_merge'];
   $eldel = ',';
}

foreach ($run_ids as $thisid) {
   $listobject->querystring = "  select elementid from scen_model_element ";
   $listobject->querystring .= " where scenarioid = $scid ";
   $listobject->querystring .= "    and objectclass = 'modelContainer' ";
   // this gets us only ICPRB segments, we can do the CBP ones later when we need to
   $listobject->querystring .= "    and elemname in ( ";
   // these segments which cross model boundaries will be run as part of a CBP container later
   $listobject->querystring .= "    $elemlist ";
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
