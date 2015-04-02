<?php
// functions that support the use of the wooomm model and modeling interface

function runModelBackground($formValues) {
   global $basedir, $php_exe, $scenarioid, $serverip;
   $objResponse = new xajaxResponse();
   $controlHTML = '';
   $arrOutput = array();
   $controlHTML = forkRun($formValues);
   $status = "<b>Model Run Requested ... contacting server.<br>";
   $objResponse->assign("status_bar","innerHTML",$status);
   $objResponse->assign("commandresult","innerHTML",$controlHTML);
   return $objResponse;
}

function forkRun($formValues) {
   global $listobject, $basedir, $php_exe, $scenarioid, $serverip;
   if (isset($formValues['elements'])) {
      $elementid = $formValues['elements'];
      $runid = $formValues['runid'];
      $startdate = '""';
      if (isset($formValues['startdate'])) {
         $startdate = $formValues['startdate'];
      } 
      $enddate= '""';
      if (isset($formValues['enddate'])) {
         $enddate = $formValues['enddate'];
      } 
      if (strlen(trim($runid)) == 0) {
         $runid = -1;
      }
      if (isset($formValues['cache_level'])) {
         $cache_level = $formValues['cache_level'];
      } else {
         $cache_level = -1; // -1 means do not use cached runs
      }
      $command = "$php_exe -f $basedir/test_modelrun.php $elementid $startdate $enddate $runid $cache_level";
      $controlHTML .= "Spawning process for $elementid <br>";
      error_log("$command > /dev/null &");
      setStatus($listobject, $elementid, "Model Run for Element $elementid Forked", $serverip, 1, $runid);
      $forkout = exec( "$command > /dev/null &", $arrOutput );
      $controlHTML .= $command;
      //$forkout = launchBackgroundProcess($command);
      //$forkout = pclose(popen("start /b $command ", "r"));
      $controlHTML .= "Command result: " . $forkout;
      //$controlHTML .= print_r($arrOutput,1) . "<br>";
   } else {
      $controlHTML .= "Could not run element ID undefined .<br>";
   }
   
   return $controlHTML;
}


function getChildComponentType($listobject, $elementid, $childtype, $limit = -1, $debug = 0) {
   $listobject->querystring = "  select a.elementid, a.elemname, a.custom1, a.custom2, a.objectclass from scen_model_element as a, map_model_linkages as b ";
   $listobject->querystring .= " where b.dest_id = $elementid and a.elementid = b.src_id and b.linktype = 1 ";
   if ($childtype <> '') {
      $listobject->querystring .= "    and a.objectclass = '$childtype'";
   }
   if ($limit <> - 1) {
      $listobject->querystring .= " LIMIT $limit ";
   }
   if ($debug) {
      print("$listobject->querystring <br>\n");
   }
   $listobject->performQuery();
   
   return $listobject->queryrecords;
}


function getChildComponentCustom1($listobject, $elementid, $custom1, $limit = -1, $debug = 0) {
   $listobject->querystring = "  select a.elementid, a.elemname, a.custom1, a.custom2, a.objectclass from scen_model_element as a, map_model_linkages as b ";
   $listobject->querystring .= " where b.dest_id = $elementid and a.elementid = b.src_id and b.linktype = 1 ";
   $listobject->querystring .= "    and a.custom1 = '$custom1'";
   if ($limit <> - 1) {
      $listobject->querystring .= " LIMIT $limit ";
   }
   if ($debug) {
      print("$listobject->querystring <br>\n");
   }
   $listobject->performQuery();
   
   return $listobject->queryrecords;
}


function getComponentCustom($listobject, $scenarioid, $custom1, $custom2 = '', $limit = -1, $fromlist = array()) {
   $listobject->querystring = "  select a.elementid, a.elemname, a.custom1, a.custom2, a.objectclass from scen_model_element as a ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.custom1 = '$custom1' ";
   if (strlen($custom2) > 0) {
      $listobject->querystring .= " and a.custom2 = '$custom2' ";
   }
   if (count($fromlist) > 0) {
      $fl = join(',', $fromlist);
      $listobject->querystring .= " and a.elementid in ($fl) ";
   }
   if ($limit <> - 1) {
      $listobject->querystring .= " LIMIT $limit ";
   }
   error_log("$listobject->querystring <br>\n");
   $listobject->performQuery();
   
   return $listobject->queryrecords;
}

function getRunFile($listobject, $elementid, $runid, $debug = 0) {
   $listobject->querystring = "  select a.elementid, a.elemname, b.output_file, b.run_date, b.starttime, b.endtime, b.run_summary, b.run_verified from scen_model_element as a, scen_model_run_elements as b ";
   $listobject->querystring .= " where b.elementid = $elementid and a.elementid = b.elementid and b.runid = $runid ";
   
   if ($debug) {
      print("$listobject->querystring\n");
   }
   
   $listobject->performQuery();
   if (count($listobject->queryrecords)) {
      return $listobject->queryrecords[0];
   } else {
      return FALSE;
   }
}

function getElementName($listobject, $elementid) {
   $listobject->querystring = "  select a.elemname from scen_model_element as a ";
   $listobject->querystring .= " where a.elementid = $elementid ";
   $listobject->performQuery();
   
   if (count($listobject->queryrecords) > 0) {
      return $listobject->queryrecords[0]['elemname'];
   } else {
      return FALSE;
   }
}

function getCustom1($listobject, $elementid) {
   $listobject->querystring = "  select a.custom1 from scen_model_element as a ";
   $listobject->querystring .= " where a.elementid = $elementid ";
   $listobject->performQuery();
   
   if (count($listobject->queryrecords) > 0) {
      return $listobject->queryrecords[0]['custom1'];
   } else {
      return FALSE;
   }
}

function getCustom2($listobject, $elementid) {
   $listobject->querystring = "  select a.custom2 from scen_model_element as a ";
   $listobject->querystring .= " where a.elementid = $elementid ";
   $listobject->performQuery();
   
   if (count($listobject->queryrecords) > 0) {
      return $listobject->queryrecords[0]['custom2'];
   } else {
      return FALSE;
   }
}

function getElementsContainingPoint($listobject, $scenarioid, $latdd, $londd) {
   $listobject->querystring = "  select elemname, elementid from scen_model_element ";
   $listobject->querystring .= " where contains(poly_geom, setsrid(makePoint($londd, $latdd), 4326) ) ";
   $listobject->querystring .= "    and ( (scenarioid = $scenarioid) or ($scenarioid = -1) ) ";
   $listobject->performQuery();
   
   if (count($listobject->queryrecords) > 0) {
      return $listobject->queryrecords;
   } else {
      return FALSE;
   }
}

function getElementID($listobject, $scenarioid, $elemname) {
   $listobject->querystring = "  select a.elementid from scen_model_element as a ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= " and a.elemname = '$elemname' ";
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      return $listobject->queryrecords[0]['elementid'];
   } else {
      return FALSE;
   }
}


function getElementOrder($listobject, $elementid) {
   
   $order = 0;
   // if we have set this object as non-cacheable, 
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

function getNextContainers($listobject, $elementid) {
   // only returns children of the very next level with children (containers), otherwise they are disregarded
   $order = 0;
   $container_info = array();
   
   // now get next level children
   $listobject->querystring = "  select a.src_id, b.elemname from map_model_linkages as a, scen_model_element as b where a.dest_id = $elementid and a.linktype = 1 and b.elementid = a.src_id ";
   //$container_info[$elementid]['query'] = $listobject->querystring;
   $listobject->performQuery();
   $child_recs = $listobject->queryrecords;
   //$props = getElementPropertyList($elementid);
   
   foreach ($child_recs as $this_child) {
      $child_id = $this_child['src_id'];
      $child_order = getElementOrder($listobject, $child_id);
      if ($child_order > 0) {
         $container_info[$child_id]['elementid'] = $child_id;
         $container_info[$child_id]['order'] = $child_order;
         $container_info[$child_id]['elemname'] = $this_child['elemname'];
      }
   }
   
   return $container_info;
}

// stash elements with children, along with their order in an array
function getNestedContainers($listobject, $elementid) {
   // only returns children with children (containers), otherwise they are disregarded
   $order = 0;
   $container_info = array();
   $listobject->querystring = "  select a.src_id, b.elemname from map_model_linkages as a, scen_model_element as b where a.dest_id = $elementid and a.linktype = 1 and b.elementid = a.src_id ";
   //$container_info[$elementid]['query'] = $listobject->querystring;
   $listobject->performQuery();
   $child_recs = $listobject->queryrecords;
   //$props = getElementPropertyList($elementid);
   $elemname = getElementName($listobject, $elementid);
   $biggest_child = -1;
   foreach ($child_recs as $this_child) {
      $child_id = $this_child['src_id'];
      $child_info = getNestedContainers($listobject, $child_id);
      //print("Child info result: " . print_r($child_info,1) . "\n");
      foreach ($child_info as $this_info) {
         $child_order = $this_info['order'];
         $child_id = $this_info['elementid'];
         //print("Child $child_id order $child_order \n");
         if ($child_order > $biggest_child) {
            $biggest_child = $child_order;
         }
         if ($child_order > 0) {
            $container_info[$child_id]['elementid'] = $child_id;
            $container_info[$child_id]['order'] = $child_order;
            $container_info[$child_id]['elemname'] = $this_info['elemname'];
         }
      }
   }
   
   if ($biggest_child >= $order) {
      $order = $biggest_child + 1;
   }
   //print("Container $elementid order $order \n");
   $container_info[$elementid]['elementid'] = $elementid;
   $container_info[$elementid]['order'] = $order;
   $container_info[$elementid]['elemname'] = $elemname;
   return $container_info;
}

// stash elements with children, along with their order in an array
function getStatusTree($listobject, $elementid, $runid = '', $sip = '') {
   // only returns children with children (containers), otherwise they are disregarded
   $container_info = getStatusSingle($listobject, $elementid, $runid, $sip);
   $order = $container_info['order'];
   if ($order > 0) {
      // get this elements children
      $listobject->querystring = "  select a.src_id, b.elemname from map_model_linkages as a, scen_model_element as b where a.dest_id = $elementid and a.linktype = 1 and b.elementid = a.src_id ";
      $listobject->performQuery();
      $child_recs = $listobject->queryrecords;

      foreach ($child_recs as $this_child) {
         $child_id = $this_child['src_id'];
         $child_info = getStatusTree($listobject, $child_id, $runid, $sip);
         if ($child_info['order'] > 0) {
            $container_info['children'][] = $child_info;
         }
      }
   }
   
   return $container_info;
}

function getStatusSingle($listobject, $elementid, $runid = '', $sip = '') {
   $container_info = array();
   $order = getElementOrder($listobject, $elementid);
   $status_vars = verifyRunStatus($listobject, $elementid, $runid, $sip);
   $status = $status_vars['status_flag'];
   $container_info['elementid'] = $elementid;
   $container_info['order'] = $order;
   $container_info['elemname'] = getElementName($listobject,$elementid);
   $container_info['run_status'] = $status;
   $container_info['query'] = $status_vars['query'];
   return $container_info;
}


function checkRunDate($listobject, $elementid, $runid, $rundate, $startdate = '', $enddate = '', $debug = 0) {
   $listobject->querystring = "  select count(*) as numruns from scen_model_run_elements ";
   $listobject->querystring .= " where runid = $runid ";
   $listobject->querystring .= " and elementid = $elementid ";
   $listobject->querystring .= " and run_date >= '$rundate' ";
   if ($startdate <> '') {
      $listobject->querystring .= " and starttime <= '$startdate' ";
   }
   if ($enddate <> '') {
      $listobject->querystring .= " and endtime >= '$enddate' ";
   }
   if ($debug) {
      print("$listobject->querystring \n");
   }
   $listobject->performQuery();
   $runs = $listobject->getRecordValue(1,'numruns');
   if ($runs == 0) {
      return 0;
   } else {
      return 1;
   }
}

function verifyRunStatus($listobject, $elementid, $qrunid = '', $qhost = '', $timeout = 900) {
   // run status_flags -1 - run failed/zombied, 0 - not running/run completed successfully, 1 - running, 2 - finishing
   $return_vals = array();
   $status_flag = '';
   $status_mesg = '';
   $interval = '';
   $elemname = '';
   
   $listobject->querystring = "  select a.status_flag, a.status_mesg, a.last_updated, b.elemname, ";
   $listobject->querystring .= "    now() as thistime, host, runid ";
   $listobject->querystring .= " from system_status as a, scen_model_element as b ";
   $listobject->querystring .= " where a.element_name = 'model_run' ";
   $listobject->querystring .= "    and a.element_key = $elementid  ";
   $listobject->querystring .= "    and a.element_key = b.elementid ";
   if ( ($qhost <> '') and ($qrunid == '') ) {
      $listobject->querystring .= "    and a.host = '$qhost' ";
   }
   if ( ($qhost <> '') and ($qrunid <> '') ) {
      $listobject->querystring .= "    and a.host = '$qhost' ";
      $listobject->querystring .= "    and a.runid = '$qrunid' ";
   }
   if ( ($qhost == '') and ($qrunid <> '') ) {
      $listobject->querystring .= "    and a.runid = '$qrunid' ";
   }
   //print("$listobject->querystring <br>");
   $return_vals['query'] = "VerifyRunStatus SQL:" . $listobject->querystring;
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $last_updated = $listobject->getRecordValue(1,'last_updated');
      $thistime = $listobject->getRecordValue(1,'thistime');
      $status_flag = $listobject->getRecordValue(1,'status_flag');
      $status_mesg = $listobject->getRecordValue(1,'status_mesg');
      $runid = $listobject->getRecordValue(1,'runid');
      $host = $listobject->getRecordValue(1,'host');
      $elemname = $listobject->getRecordValue(1,'elemname');
      $last_secs = intval(date('U', strtotime($last_updated)));
      $current_secs = intval(date('U', strtotime($thistime)));
      $interval = $current_secs - $last_secs;
      // runs that have not updated within the timeout that are NOT either
      // 0 - finished, or 3 - queued but waiting, are considered to be zombied
      if ( ($interval >= $timeout) and !(in_array($status_flag,array( 0, 3)) ) ) {
         // this indicates that run is zombied/failed
         $status_flag = -1;
      }
      // this checks to see if a model on another remote machine is running a DIFFERENT runid, which is OK
      // this only will fire if ALL information has been provided to this routine (host and runid), 
      // otherwise, it assumes that for safety sake we do NOT allow multiple runs
      if ( in_array($status_flag, array(1,2)) 
            and ($qrunid <> $runid) 
            and ($qrunid <> '') 
            and ($runid <> '') 
            and ($qhost <> $host) 
            and ($qhost <> '') 
            and ($host <> '') 
         ) {
         // this indicates that run is OK to go because it is a DIFFERENT runid and a DIFFERENT host
         $status_flag = 0;
      }
   } else {
      error_log ($listobject->querystring);
      $status_flag = NULL;
   }
   $return_vals['elemname'] = $elemname;
   $return_vals['status_flag'] = $status_flag;
   $return_vals['status_mesg'] = $status_mesg;
   $return_vals['interval'] = $interval;
   
   return $return_vals;
}

function removeRunCache($listobject, $recid, $run_id) {
   $listobject->querystring = " delete from scen_model_run_elements ";
   $listobject->querystring .= " where elementid = $recid ";
   $listobject->querystring .= "    and runid = $run_id ";
   $listobject->performQuery();
   $listobject->querystring = " delete from system_status ";
   $listobject->querystring .= " where element_key = $recid ";
   $listobject->querystring .= "    and runid = $run_id ";
   $listobject->performQuery();
}

function removeTreeCache($listobject, $recid, $run_id) {
   $parts = getNestedContainers($listobject, $recid);
   foreach ($parts as $piece) {
      removeRunCache($listobject, $piece['elementid'], $run_id);
   }
}
   

function setStatus($listobject, $elementid, $mesg, $sip, $status_flag=1, $runid = -1) {
   $pid = -1; // do not set a valid pid here, since we can not know it (I don't think)
   if (is_object($listobject)) {
   
      $listobject->querystring = " select count(*) as numrecs from system_status ";
      $listobject->querystring .= " where element_name = 'model_run' ";
      $listobject->querystring .= "    and element_key = $elementid  ";
      $listobject->querystring .= "    and host = '$sip' ";
      $listobject->querystring .= "    and runid = '$runid' ";
      $listobject->performQuery();
      $syslogrec = $listobject->getRecordValue(1,'numrecs');

      if ($syslogrec == 0) {
         $listobject->querystring = " insert into system_status (element_name, element_key, ";
         $listobject->querystring .= "    status_mesg, status_flag, pid, last_updated, host, runid ) ";
         $listobject->querystring .= " values ('model_run', $elementid, '$mesg', $status_flag, $pid, now(), '$sip', $runid) ";
         $listobject->performQuery();
      } else {
         $listobject->querystring = " update system_status ";
         $listobject->querystring .= " set status_mesg = '$mesg', ";
         $listobject->querystring .= "    status_flag = $status_flag, ";
         $listobject->querystring .= "    pid = $pid, ";
         $listobject->querystring .= "    last_updated = now(), ";
         $listobject->querystring .= "    host = '$sip', ";
         $listobject->querystring .= "    runid = '$runid' ";
         $listobject->querystring .= " where element_name = 'model_run' ";
         $listobject->querystring .= "    and element_key = $elementid ";
         $listobject->querystring .= "    and host = '$sip' ";
         $listobject->querystring .= "    and runid = '$runid' ";
         $listobject->performQuery();
      }
      //error_log($listobject->querystring);
   }
}


function checkTreeRunDate($listobject, $recid, $run_id, $startdate, $enddate, $cache_date, $debug = 0) {
   // checks whether the given tree has been run TO THE OUTLET since the desired cache_date
   // also requires that the run be completed successfully... according to the run_verified flag
   $elements = getNestedContainers($listobject, $recid);
   $root_info = getRunFile($listobject, $recid, $run_id);
   $root_date = $root_info['run_date'];
   $root_time = strtotime($root_date);
   $cache_time = strtotime($cache_date);
   $status = 1;
   $i = 0;
   // now check any branches, are they younger than the parent?
   foreach ($elements as $thiselement) {
      $branchid = $thiselement['elementid'];
      $cacheable = getElementCacheable($listobject, $branchid);
      $en = getElementName($listobject, $branchid);
      if ($debug) {
         print("Evaluation Branch $en \n");
      }
      if ($cacheable == 0) {
         $check = checkRunDate($listobject, $branchid, $run_id, $cache_date, $startdate, $enddate, $debug);
         $status = $status & $check;
         if ($debug) {
            print("checkRunDate returned - $check *(status = $status) \n");
         }
         $branch_info = getRunFile($listobject, $branchid, $run_id, $debug);
         $branch_date = $branch_info['run_date'];
         $branch_time = strtotime($branch_date);
         if ($debug) {
            print("Comparing Branch $en ($branchid) time $branch_time to root $root_time \n");
         }
         if ($branch_time > $root_time) {
            //print("Branch $branchid time $branch_time > root $root_time \n");
            $i++;
            $status = 0;
         }
         /*
         if ($branch_info['run_verified'] <> 1) {
            $status = 0;
         }
         */
      } else {
         if ($debug) {
            print("Branch $en not cacheable - skipping \n");
         }
      }
   }
   /*
   if ($root_time < $cache_time) {
      $status = 0;
   }
   */
   if ($debug) {
      print("cache time = $cache_time, root time = $root_time  \n");
      print("$i branches with time > root #$recid (status = $status) \n");
   }
   return $status;
}

function checkStandAlone($listobject, $recid) {
   $props = getElementPropertyList($recid);
   //print("Properties " . print_r($props,1) . "\n");
   if (isset($props['standalone'])) {
      return $props['standalone'];
   } else {
      return 0;
   }
}

function runTree($listobject, $elementid, $sleep_factor, $max_simultaneous, $startdate, $enddate, $thisid, $cache_date, $prop_array = array()) {
   global $php_exe, $basedir, $outdir;
   $heap = getNestedContainers($listobject, $elementid);
   $batch_ptr = fopen ("./batch_$elementid" . ".$thisid" . ".out", 'w');
   //$batch_ptr = fopen ("$outdir/batch_$elementid" . ".$thisid" . ".out", 'w');
   //print("Heap: " . print_r($heap,1) . "<br>\n");
   $running = array();
   $checked = array();
   $current_order = 1;
   while ( count($heap) > 0 ) {
      // if it is a 1st order segment, we should have no need to re-run it
      $thisrec = array_shift($heap);
      $recid = $thisrec['elementid'];
      $order = $thisrec['order'];
      //print("Checking $recid order $order<br>\n");
      // need to check to see if we need to increment the value of $current_order
      if (in_array($recid, $checked)) {
         // if it is already been checked at this level, we go ahead and increment the order level
         fwrite($batch_ptr, "Ran " . count($checked) . " of Order $current_order \n");
         $current_order++;
         $checked = array();
         // now we have to wait for all members of the previous level to complete
         while (count($running) > 0) {
            foreach ($running as $thisone) {
               $still_running = array();
               $status = verifyRunStatus($listobject, $thisone, 3600);
               if ( !in_array($status, array(-1,0)) ) {
                  $still_running[] = $thisone;
               }
               if ( ($status == -1) and $abort_zombie ) {
                  fwrite($batch_ptr, "Member $thisone died during execution - batch job aborted.<br>\n");
                  fclose($batch_ptr);
                  return;
               }
               if ( $status == 0 ) {
                  fwrite($batch_ptr, "Member $thisone completed successfully.<br>\n");
               }
            }
            sleep($sleep_factor);
            $running = $still_running;
         }
      }
      // check to see if it is on the order that we are on
      if ($order == $current_order) {
         //print("$order = $current_order - chcking run date<br>\n");
         // run it, unless it is 1st order, AND it has existing files for each of the runs that we want

         // check to see if it has already been run since the cache date, or if it is the parent container
         if (!checkRunDate($listobject, $recid, $thisid, $cache_date) or ($thisid == $elementid)) {
            $waiting = 1;
            while ( $waiting )  {
               $active_models = returnPids('php');
               $num_active = count($active_models);
               //print("Only $num_active \n");
               if ($num_active < $max_simultaneous) {
                  // spawn a new one
                  updateObjectProps($projectid, $recid, $prop_array);
                  $arrOutput = array();
                  
                  $command = "$php_exe -f $basedir/test_modelrun.php $recid $startdate $enddate $thisid $cache_date";
                  fwrite($batch_ptr, "Spawning process for $recid, run # $thisid, Order: $current_order <br>\n");
                  fwrite($batch_ptr, "$command > /dev/null &\n");
                  error_log("$command > /dev/null &");
                  $forkout = exec( "$command > /dev/null &", $arrOutput );
                  $waiting = 0;
                  $running[] = $recid;
               }
               sleep($sleep_factor);   
            }
         } else {
            fwrite($batch_ptr, "Member $recid already run since $cache_date - skipping.<br>\n");
         }

      } else {
         // stick it back on the heap
         $heap[] = $thisrec;
         $checked[] = $recid;
      }
   }
   // include this afterwards so that we follow the batch run till the end
   while (count($running) > 0) {
      foreach ($running as $thisone) {
         $still_running = array();
         $status = verifyRunStatus($listobject, $thisone, 3600);
         if ( !in_array($status, array(-1,0)) ) {
            $still_running[] = $thisone;
         }
         if ( ($status == -1) and $abort_zombie ) {
            fwrite($batch_ptr, "Member $thisone died during execution - batch job aborted.<br>\n");
            fclose($batch_ptr);
            return;
         }
         if ( $status == 0 ) {
            fwrite($batch_ptr, "Member $thisone completed successfully.<br>\n");
         }
      }
      sleep($sleep_factor);
      $running = $still_running;
   }
   fclose($batch_ptr);
   fwrite($batch_ptr, "Batch run for parent $elementid completed successfully.<br>\n");
   fwrite($batch_ptr, "Done.<br>\n");
}


function showRedrawGraphs($formValues) {
   global $libpath, $adminsetuparray;
   include_once("adminsetup.php");
   #include_once("who_xmlobjects.php");
   $objResponse = new xajaxResponse();
   $innerHTML = redrawGraphs($formValues);
   $objResponse->assign("modelout_data2","innerHTML",$innerHTML);
   return $objResponse;
}

function showImportModelElementForm($formValues) {
   include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $controlHTML = importModelElementForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function deleteObject($formValues) {
   global $listobject, $icons, $projectid, $scenarioid, $userid, $usergroupids, $debug, $libpath, $adminsetuparray;
   $objResponse = new xajaxResponse();
   if (isset($formValues['elementid'])) {
      $elementid = $formValues['elementid'];
      # get the scenario in case we can't get the container
      $listobject->querystring = " select a.scenarioid, b.scenario from scen_model_element as a, scenario as b where a.elementid = $elementid and b.scenarioid = a.scenarioid ";
      $listobject->performQuery();
      $scenarioid = $listobject->getRecordValue(1,'scenarioid');
      $scenario = $listobject->getRecordValue(1,'scenario');
      # see if we can get the container of this guy
      $listobject->querystring = " select dest_id from map_model_linkages where src_id = $elementid ";
      $listobject->performQuery();
      $containerid = $listobject->getRecordValue(1,'dest_id');
      #$debug = 1;
      $deleteresult = deleteModelElement($formValues['elementid']);
      #$debug = 0;
      $innerHTML .= $deleteresult['innerHTML'];
      if ($containerid > 0) {
         # we know what folder this is in on the menu, so only refresh that particular DIV
         $menuobject = getChildInfo($containerid, $listobject);
         //$browserHTML = formatMenuObject($menuobject, 1);
         $objResponse->assign("ob$containerid","innerHTML",$browserHTML);
         $innerHTML .= "Trying to update ONLY the container of the object that was deleted";
      } else {
         $menuobject = getScenarioRoots($listobject, $scenarioid, $scenario);
         //$browserHTML = formatMenuObject($menuobject, 1);
         $objResponse->assign("sc$scenarioid","innerHTML",$browserHTML);
         $innerHTML .= "Trying to update the Root Scenario of the object that was deleted";
      }
   } else {
      $innerHTML = "Delete Command Failed.";
   }
   // this refreshes the whole menu, in place of refreshing only components (formatMenuObject commented out above and -1 for scenarioid forced here)
   //$browserHTML = showHierarchicalMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, 0);
   $browserHTML = showHierarchicalMenu($listobject, $projectid, -1, $userid, $usergroupids, 0);
   $objResponse->assign("objectbrowser","innerHTML",$browserHTML);
   // END - refresh menu
   $objResponse->assign("commandresult","innerHTML",$innerHTML);
   return $objResponse;
}
     

function showImportModelElementResult($formValues) {
   include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $innerHTML = importModelElementResult($formValues);
   $controlHTML = importModelElementForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showCopyModelGroupForm($formValues) {
   include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $controlHTML = copyModelGroupForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showCopyModelGroupForm2($formValues) {
   include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $controlHTML = copyModelGroupForm2($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showCopyModelGroupResult($formValues) {
   include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $copyresult = copyModelGroupFull($formValues);
   
   //$innerHTML = $copyresult['innerHTML'];
   $controlHTML = copyModelGroupForm($formValues);
   $controlHTML .= "<hr>" . print_r($formValues, 1) . "<br>";
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showRefreshWHOObjectsForm($formValues) {
   include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $controlHTML = refreshWHOObjectsForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showRefreshWHOObjectsResult($formValues) {
   include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $innerHTML = refreshWHOObjectsResult($formValues);
   $controlHTML = refreshWHOObjectsForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showAddElementForm($formValues) {
   global $libpath, $scenarioid, $ucitables, $listobject, $timer, $adminsetuparray;
   #include_once("who_xmlobjects.php");
   $objResponse = new xajaxResponse();
   $controlHTML = addElementFormPanel($formValues,$who_xmlobjects);
   #$controlHTML = addElementForm($formValues,$who_xmlobjects);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   # adding call to fileNice
   $objResponse->call('parent.setFNFunctions','');
   #$objResponse->call('this.setFNFunctions','');
   return $objResponse;
}


function showScenarioEditor($formValues) {
   global $libpath, $scenarioid, $ucitables, $listobject, $timer, $adminsetuparray;
   #include_once("who_xmlobjects.php");
   $objResponse = new xajaxResponse();
   $controlHTML = createEditDomainForm($formValues);;
   $workspaceHTML = 'Editing Scenario';
   $modelstatusHTML = getModelStatus($formValues);
   $objResponse->assign("model_status","innerHTML",$modelstatusHTML);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$workspaceHTML);
   # adding call to fileNice
   $objResponse->call('parent.setFNFunctions','');
   #$objResponse->call('this.setFNFunctions','');
   return $objResponse;
}



function showModelDesktopView($formValues) {
   global $projectid, $libpath, $scenarioid, $ucitables, $listobject, $timer, $adminsetuparray;
   #include_once("who_xmlobjects.php");
   $objResponse = new xajaxResponse();
   $controlHTML = addElementFormPanel($formValues,$who_xmlobjects);
   
   $workspaceHTML = modelControlForm($formValues['projectid'], $formValues['scenarioid'], $formValues['elementid']);
   $workspaceHTML .= showCachedModelOutput($formValues['elementid']);
   if (strlen(rtrim(ltrim($workspaceHTML))) == 0 ) {
      $workspaceHTML = showModelControlButtons($formValues['elementid'], $formValues['projectid'], $formValues['scenarioid']);
   }
   $modelstatusHTML = getModelStatus($formValues);
   $objResponse->assign("model_status","innerHTML",$modelstatusHTML);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$workspaceHTML);
   # adding call to fileNice
   $objResponse->call('parent.setFNFunctions','');
   #$objResponse->call('this.setFNFunctions','');
   return $objResponse;
}

function getModelStatus($formValues) {
   global $libpath, $scenarioid, $ucitables, $userid, $listobject, $timer, $adminsetuparray, $iconurl;
   $innerHTML = '';
   
   $elementid = $formValues['elementid'];
   $containerid = $formValues['activecontainerid'];
   $getdefault = 1;
   if (isset($formValues['scenarioid'])) {
      $scenarioid = $formValues['scenarioid'];
      $listobject->querystring = "  select scenario from scenario ";
      $listobject->querystring .= " where scenarioid = $scenarioid ";
      $listobject->performQuery();
      if (count($listobject->queryrecords) >= 1) {
         $getdefault = 0;
      }
   }
   $nextcontainer = $containerid;
   if ($containerid > 0) {
      $listobject->querystring = "  select dest_id from map_model_linkages ";
      $listobject->querystring .= " where src_id = $containerid and linktype = 1 ";
      $listobject->performQuery();
      if (count($listobject->queryrecords) >= 1) {
         $nextcontainer = $listobject->getRecordValue(1,'dest_id');
      }
   }
   
   if ($getdefault) {
      # retrieve users default scenario
      $listobject->querystring = "  select min(scenarioid) as scenarioid from scenario ";
      $listobject->querystring .= " where ownerid = $userid ";
      $listobject->performQuery();
      $scenarioid = $listobject->getRecordValue(1,'scenarioid');
   }
   
   # retrieve active scenario information
   $listobject->querystring = "  select scenario from scenario ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->performQuery();
   $scname = $listobject->getRecordValue(1,'scenario');
   $innerHTML .= "<span class=\"mDashBoard\">";
   $innerHTML .= "<ul class=\"mDashBoard\"><b>Active Domain: </b><i>$scname</i><br>";
      
   if (!($containerid > 0)) {
      $containerid = $elementid;
   }
   $listobject->querystring = "  select geomtype ";
   $listobject->querystring .= " from scen_model_element ";
   $listobject->querystring .= " where elementid = $containerid ";
   $listobject->performQuery();
   $geomtype = $listobject->getRecordValue(1,'geomtype');
   switch ($geomtype) {
      case 1:
      $geomcol = 'point_geom';
      break;
      
      case 2:
      $geomcol = 'line_geom';
      break;
      
      case 3:
      $geomcol = 'poly_geom';
      break;
   }
   
   
   $listobject->querystring = "  select elemname, xmin(extent($geomcol)) as x1, ";
   $listobject->querystring .= "    ymin(extent($geomcol)) as y1, ";
   $listobject->querystring .= "    xmax(extent($geomcol)) as x2, ";
   $listobject->querystring .= "    ymax(extent($geomcol)) as y2 ";
   $listobject->querystring .= " FROM scen_model_element   ";
   $listobject->querystring .= " WHERE elementid = $containerid ";
   $listobject->querystring .= " group by elemname ";
   //$innerHTML .= "$listobject->querystring ; <br>";
   $listobject->performQuery();
   $innerHTML .= "<li class=\"mDashBoard\"><b>Active Model: </b>";
   if (count($listobject->queryrecords) > 0) {
      $contname = $listobject->getRecordValue(1,'elemname');
      $formname = 'elementtree';
      $clickscript = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$containerid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$nextcontainer; document.forms['$formname'].elements.scenarioid.value=$scenarioid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
      // this is identical to single-click, except that it sets the current object to the the active container, as well as the 
      // item to be edited
      $dbl_clickscript = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$containerid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$containerid; document.forms['$formname'].elements.scenarioid.value=$scenarioid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
      $innerHTML .= "<i><a onclick=\"$clickscript ;\" onDblclick=\"$dbl_clickscript ;\">$contname</a></i>";
      $x1 = $listobject->getRecordValue(1,'x1');
      $y1 = $listobject->getRecordValue(1,'y1');
      $x2 = $listobject->getRecordValue(1,'x2');
      $y2 = $listobject->getRecordValue(1,'y2');
      $tol = 0.02;
      if ( ($x1 <> '') and ($y1 <> '') and ($x2 <> '') and ($y2 <> '') ) {
         if ( (abs($x2 - $x1) < $tol) and (abs($y2 - $y1) < $tol) ) {
            $x1 += -1.0 * $tol;
            $x2 += $tol;
            $y1 += -1.0 * $tol;
            $y2 += $tol;
            //$controlHTML .= "<b>Notice:</b> Zooming to fixed distance from single selected point.<br>";
         }
         if ( ($x1 <> '') and ($y1 <> '') and ($x2 <> '') and ($y2 <> '') ) {
            $zoomclick = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; show_next('map_window_data2', 'map_window_2', 'map_window'); alert('gmapZoom($x1, $y1, $x2, $y2)'); gmapZoom($x1, $y1, $x2, $y2) ;";
            $innerHTML .= "<img src='$iconurl/zoomextent-16x16.png' onClick=\"$zoomclick\" >";
         }
      }
   
      $listobject->querystring = "  select xmin(extent(the_extent)) as x1, ";
      $listobject->querystring .= "    ymin(extent(the_extent)) as y1, ";
      $listobject->querystring .= "    xmax(extent(the_extent)) as x2, ";
      $listobject->querystring .= "    ymax(extent(the_extent)) as y2 ";
      $listobject->querystring .= " FROM ";
      $listobject->querystring .= " (";
      $listobject->querystring .= "    select extent(geomfromtext(the_extent)) as the_extent from (";
      $listobject->querystring .= "    ( select astext(extent(poly_geom)) as the_extent ";
      $listobject->querystring .= "      from scen_model_element where isvalid(poly_geom) ";
      $listobject->querystring .= "         AND elementid in (select src_id from map_model_linkages ";
      $listobject->querystring .= "                           where linktype = 1 and dest_id = $containerid ) ";
      $listobject->querystring .= "         )";
      $listobject->querystring .= "         UNION ";
      $listobject->querystring .= "         ( select astext(extent(point_geom)) as the_extent ";
      $listobject->querystring .= "           from scen_model_element where isvalid(point_geom) ";
      $listobject->querystring .= "         AND elementid in (select src_id from map_model_linkages ";
      $listobject->querystring .= "                           where linktype = 1 and dest_id = $containerid ) ";
      $listobject->querystring .= "         )";
      $listobject->querystring .= "         UNION ";
      $listobject->querystring .= "         ( select astext(extent(line_geom)) as the_extent ";
      $listobject->querystring .= "           from scen_model_element where isvalid(line_geom) ";
      $listobject->querystring .= "         AND elementid in (select src_id from map_model_linkages ";
      $listobject->querystring .= "                           where linktype = 1 and dest_id = $containerid ) ";
      $listobject->querystring .= "         )";
      $listobject->querystring .= "     ) as foo";
      $listobject->querystring .= "  ) as bar    ";
      //$innerHTML .= "$listobject->querystring ; <br>";
      $listobject->performQuery();
      $x1 = $listobject->getRecordValue(1,'x1');
      $y1 = $listobject->getRecordValue(1,'y1');
      $x2 = $listobject->getRecordValue(1,'x2');
      $y2 = $listobject->getRecordValue(1,'y2');
      # Show a Zoom To Extent Link
      if ( ($x1 <> '') and ($y1 <> '') and ($x2 <> '') and ($y2 <> '') ) {
         $zoomclick = "gmapZoom($x1, $y1, $x2, $y2) ;";
         $innerHTML .= "<img src='$iconurl/zoom_model_container.bmp' onClick=\"$zoomclick\" >";
      }
   } else {
      $contname = 'None Selected';
      $innerHTML .= "<i>$contname</i>";
      list($x1,$x2,$y1,$y2) = array('','','','');
   }
   
   $listobject->querystring = "  select a.elemname, b.type from scen_model_element as a left outer join who_xmlobjects as b on (a.objectclass = b.classname) where elementid = $elementid ";
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $childname = $listobject->getRecordValue(1,'elemname');
      $type = $listobject->getRecordValue(1,'type');
   } else {
      $childname = 'None Selected';
      $type = -1;
   }
   $onclick = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$elementid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$containerid; document.forms['$formname'].elements.scenarioid.value=$scenarioid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
   $innerHTML .= "<ul class=\"mDashBoard\"><li class=\"mDashBoard\">&nbsp;&nbsp;&nbsp;<b>Editing: </b><i><a onclick=\"$onclick ;\">$childname</a></i>";
   /*
   if ($type == 3) {
      # can be a container, offer a "promote to active model link
      $onclick = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.scenarioid.value=$scenarioid; document.forms['$formname'].elements.actiontype.value='edit';  document.forms['$formname'].elements.activecontainerid.value=$elementid; document.forms['$formname'].elements.elementid.value=$elementid; xajax_showModelDesktopView(xajax.getFormValues('elementbrowser')); ";
      
      $innerHTML .= "<img src='$iconurl/icon_eye.gif' onClick=\"$onclick\" >";
   }
   */
   
   $innerHTML .= showContainedElementBrowser2($elementid);
   $innerHTML .= "</ul>"; # finish active element indent
   $innerHTML .= "</ul>"; # finish active container indent
   $innerHTML .= "</span>";
   
   
   
   return $innerHTML;
   
}

function createEditDomainForm($formValues) {
   global $listobject, $userid, $userinfo;

   $groupid = $userinfo['defaultgroup'];
   #$debug = 1;

   $innerHTML = '';
   $projectid = $formValues['projectid'];
   if (isset($formValues['actiontype'])) {
      $actiontype = $formValues['actiontype'];
   } else {
      $actiontype = 'edit';
   }

   if (isset($formValues['scenarioid'])) {
      $scenarioid = $formValues['scenarioid'];
   } else {
      $scenarioid = -1;
   }
   
   if (isset($formValues['scenario'])) {
      $scenario = $formValues['scenario'];
      $shortname = $formValues['shortname'];
      $otherscen = $formValues['otherscen'];
      $src_scenario = $formValues['src_scenario'];
      $groupid = $formValues['groupid'];
      $operms = $formValues['operms'];
      $gperms = $formValues['gperms'];
      $pperms = $formValues['pperms'];
   } else {
      $operms = 7;
      $gperms = 4;
      $pperms = 0;
   }
   
   switch ($actiontype) {
      
      case 'processcreatedomain':
         $newdomain = createDomain($listobject, $projectid, $userid, $scenario, $shortname, $groupid, $operms, $pperms, $gperms, 1, 1);
         if ($newdomain['error']) {
            $innerHTML .= $newdomain['error_msg'];
            $innerHTML .= $newdomain['debug'];
         } else {
            $newscenarioid = $newdomain['scenarioid'];
            $innerHTML .= $newdomain['message'];
         }
         $action_text = 'Edit Domain';
      break;
      
      case 'edit':
         if ($scenarioid == -1) {
            # get users default scenario
            # retrieve users default scenario
            $listobject->querystring = "  select min(scenarioid) as scenarioid from scenario ";
            $listobject->querystring .= " where ownerid = $userid ";
            //$innerHTML .= "$listobject->querystring ; <br>";
            $listobject->performQuery();
            $scenarioid = $listobject->getRecordValue(1,'scenarioid');
         }
         # retrieve active scenario information
         $listobject->querystring = "  select projectid, scenario, shortname, groupid, ";
         $listobject->querystring .= "    operms, pperms, gperms from scenario ";
         $listobject->querystring .= " where scenarioid = $scenarioid ";
         //$innerHTML .= "$listobject->querystring ; <br>";
         $listobject->performQuery();
         $scenario = $listobject->getRecordValue(1,'scenario');
         $shortname = $listobject->getRecordValue(1,'shortname');
         $otherscen = $listobject->getRecordValue(1,'otherscen');
         $src_scenario = $listobject->getRecordValue(1,'src_scenario');
         $actiontype = 'save';
         $groupid = $listobject->getRecordValue(1,'groupid');
         $operms = $listobject->getRecordValue(1,'operms');
         $gperms = $listobject->getRecordValue(1,'gperms');
         $pperms = $listobject->getRecordValue(1,'pperms');
         $action_text = 'Edit Domain';
      break;
      
      case 'save':
      $rdmesg = "Saving Model Domain attributes is currently disabled in this view.  Go to the 'Maintenance' page to perform edits to Modeling Domains.";
         if ($scenarioid == -1) {
            # get users default scenario
            # retrieve users default scenario
            $listobject->querystring = "  select min(scenarioid) as scenarioid from scenario ";
            $listobject->querystring .= " where ownerid = $userid ";
            //$innerHTML .= "$listobject->querystring ; <br>";
            $listobject->performQuery();
            $scenarioid = $listobject->getRecordValue(1,'scenarioid');
         }
         # retrieve active scenario information
         $listobject->querystring = "  select projectid, scenario, shortname, groupid, ";
         $listobject->querystring .= "    operms, pperms, gperms from scenario ";
         $listobject->querystring .= " where scenarioid = $scenarioid ";
         //$innerHTML .= "$listobject->querystring ; <br>";
         $listobject->performQuery();
         $scenario = $listobject->getRecordValue(1,'scenario');
         $shortname = $listobject->getRecordValue(1,'shortname');
         $otherscen = $listobject->getRecordValue(1,'otherscen');
         $src_scenario = $listobject->getRecordValue(1,'src_scenario');
         $groupid = $listobject->getRecordValue(1,'groupid');
         $operms = $listobject->getRecordValue(1,'operms');
         $gperms = $listobject->getRecordValue(1,'gperms');
         $pperms = $listobject->getRecordValue(1,'pperms');
         $action_text = 'Edit Domain';
      break;
      
      case 'createdomain':
         $newdomain = createDomain($listobject, $projectid, $userid, $scenario, $shortname, $groupid, $operms, $pperms, $gperms, 1, 1);
         if ($newdomain['error']) {
            $innerHTML .= $newdomain['error_msg'];
            $innerHTML .= $newdomain['debug'];
         } else {
            $newscenarioid = $newdomain['scenarioid'];
            $innerHTML .= $newdomain['message'];
         }
         $action_text = 'Create New Domain';
      break;
      
   }

   $innerHTML .= "<h3>$action_text</h3>";
   $innerHTML .= "<br>$rdmesg";
   $innerHTML .= "<form id=control name=control>";
   $innerHTML .= "<br><b>Domain Name: </b> ";
   $innerHTML .= showWidthTextField('scenario', $scenario, 30, '', 1);
   $innerHTML .= "<br><b>Domain Short Name (abbrev. for model input files,less than 12 chars): </b> ";
   $innerHTML .= showWidthTextField('shortname', $shortname, 10, '', 1);
   $innerHTML .= showHiddenField('projectid',$projectid, 1);
   $innerHTML .= showHiddenField('actiontype',$actiontype, 1);
   $innerHTML .= showHiddenField('scenarioid',$scenarioid, 1);
   
   $innerHTML .= "<br>";
   $innerHTML .= "<b>Select a Group for this Domain: </b>";
   $innerHTML .= showList($listobject, 'groupid', 'groups', 'groupname', 'groupid', "groupid in (select groupid from mapusergroups where userid = $userid)", $groupid, $debug, 1);
   $innerHTML .= "<br>";
   $innerHTML .= "<b>Set Owner Permisssions for this Domain: </b>";
   $innerHTML .= showList($listobject, 'operms', 'perms', 'permdesc', 'permno', '', $operms, $debug, 1);
   $innerHTML .= "<br>";
   $innerHTML .= "<b>Set Group Permisssions for this Domain: </b>";
   $innerHTML .= showList($listobject, 'gperms', 'perms', 'permdesc', 'permno', '', $gperms, $debug, 1);
   $innerHTML .= "<br>";
   $innerHTML .= "<b>Set Public Permisssions for this Scenario: </b>";
   $innerHTML .= showList($listobject, 'pperms', 'perms', 'permdesc', 'permno', '', $pperms, $debug, 1);
   $innerHTML .= "<br>";
   $innerHTML .= showGenericButton('createscenario','Save Domain', "xajax_showScenarioEditor(xajax.getFormValues(\"control\"))", 1);
   $innerHTML .= "</form> ";

   return $innerHTML;

}

function showAddElementResult($formValues) {
   global $libpath, $scenarioid, $timer, $adminsetuparray, $ucitables, $listobject;
   include_once("adminsetup.php");
   #include_once("who_xmlobjects.php");
   $objResponse = new xajaxResponse();
   $innerHTML = addElementResult($formValues);
   #$controlHTML = addElementForm($formValues,$who_xmlobjects);
   $controlHTML = addElementFormPanel($formValues,$who_xmlobjects);
   $modelstatusHTML = getModelStatus($formValues);
   $objResponse->assign("model_status","innerHTML",$modelstatusHTML);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("status_bar","innerHTML",$innerHTML);
   return $objResponse;
}


function refreshWHOObjectsForm($formValues) {
   global $userid;

   $controlHTML = '';

   if ($userid <> 1) {
      $controlHTML .= "<b>Error:</b> You are not allowed to access this function.<br>";
      return $controlHTML;
   }

   $controlHTML .= "<form name='reloadwhoobjects' id='reloadwhoobjects'>";
   $controlHTML .= showHiddenField('actiontype', 'reloadwhoobjects', 1);
   $controlHTML .= showCheckBox('reload', 1, $reload, "", 1);
   $controlHTML .= ' Check to confirm reload of WHO XML Objects from template file';
   $controlHTML .= showGenericButton('reloadwhoobjects', 'Reload Objects', "xajax_showRefreshWHOObjectsResult(xajax.getFormValues(\"reloadwhoobjects\"))", 1);
   $controlHTML .= "</form>";

   return $controlHTML;


}

function insertBlankComponent($formValues) {
   global $projectid, $listobject, $userid, $usergroupids, $defscenarioid, $debug;
   
   $insertresult = array();
   
   $activecontainerid = $formValues['activecontainerid'];
   $scenarioid = $formValues['scenarioid'];
   if ($scenarioid == -1) {
      $scenarioid = getDefaultScenario($listobject, $userid);
   }
   $classname = $formValues['newcomponenttype'];
   if (isset($formValues['name'])) {
      $name = $formValues['name'];
   } else {
      $name = 'Un-Named';
   }
   $innerHTML .= "Insert called $projectid, $userid, $usergroupids, $defscenarioid, $debug; <br>";
   //$innerHTML .= print_r($formValues,1) . "<br>";
   
   # if we have not returned, then go ahead and insert this object

   # if we have a activecontainerid we set the component group to be the same as the activecontainerid group.
   # with equivvalent permissions
   # otherwise, then we set the component group to be the users private group.
   if ( ($activecontainerid > 0) ) {
      $listobject->querystring = " select groupid, operms, gperms, pperms from scen_model_element where elementid = $activecontainerid ";
   } else {
      $listobject->querystring = " select groupid, 7 as operms, 6 as gperms, 0 as pperms from users where userid = $userid ";
   }
   $listobject->performQuery();
   $groupid = $listobject->getRecordValue(1,'groupid');
   $operms = $listobject->getRecordValue(1,'operms');
   $gperms = $listobject->getRecordValue(1,'gperms');
   $pperms = $listobject->getRecordValue(1,'pperms');
   $listobject->adminsetup = $adminsetuparray['scen_model_element'];
   $listobject->querystring = "  insert into scen_model_element (scenarioid, groupid, elemname, elem_xml,";
   $listobject->querystring .= "     objectClass, component_type, operms, gperms, pperms, ownerid ) ";
   $listobject->querystring .= " select $scenarioid, $groupid, '$name', classxml, ";
   $listobject->querystring .= "    classname, type, $operms, $gperms, $pperms, $userid ";
   $listobject->querystring .= " FROM who_xmlobjects ";
   $listobject->querystring .= " WHERE classname = '$classname' ";
   $innerHTML .= "$listobject->querystring<br>";
   $listobject->performQuery();
   $listobject->querystring = "SELECT currval('scen_model_element_elementid_seq') ";
   $listobject->performQuery();
   $listobject->show = 0;
   $listobject->showList();
   #$innerHTML .= "$listobject->outstring <br>";
   $newelid = $listobject->getRecordValue(1,'currval');
   if ($newelid > 0) {
      $innerHTML .= "Added Type $classname with ID = $newelid<br>";
   } else {
      $innerHTML .= "Failed to Add Type $classname .<br>";
   }
   # if we have a activecontainerid value set, we go ahead and insert a linkage for this new element
   # then get the object, apply starting properties (from formValues) and return
   if ( ($activecontainerid > 0) and ($newelid > 0) ) {
      createObjectLink($projectid, $scenarioid, $newelid, $activecontainerid, 1);
      
   } else {
      $innerHTML .= "Could not link new component $newelid to Container $activecontainerid .<br>";
   }
   
   if ($newelid > 0) {
      // update object property lists, and run the create routine. then save object.
      # get the object back
      $listobject->querystring = " select elem_xml from scen_model_element where elementid = $newelid ";
      if ($debug) {
         $innerHTML .= "$listobject->querystring<br>";
      }
      $listobject->performQuery();
      $elem_xml = $listobject->getRecordValue(1,'elem_xml');
      $loadres = loadElement($elem_xml);
      $thisobject = $loadres['object'];
      foreach ($formValues as $varkey=>$varval) {
         if (property_exists($thisobject, $varkey)) {
            //$innerHTML .= "Setting $varkey - $varval <br>";
            $thisobject->$varkey = $varval;
         }
      }
      // this runs any special creation rotines for this object.  
      // Some objects need to add custom sub-components when they are created,
      // or need to compile data for their internal use.
      $cres = $thisobject->create();
      $innerHTML .= $cres['innerHTML']; 
      
      
      // now, if there were any sub-components automatically created during the create() execution
      // we will serialize them and save them
      $innerHTML .= saveObjectSubComponents($listobject, $thisobject, $newelid );
      
      
      
      $compres = compactSerializeObject($thisobject);
      if (!$compres['error']) {
         $object_xml = $compres['object_xml'];
         $props_xml = $compres['props_xml'];
         $inputs_xml = $compres['inputs_xml'];

         # get the object back
         $listobject->querystring = " update scen_model_element set elem_xml = '$object_xml', ";
         $listobject->querystring .= " elemprops = '$props_xml', eleminputs = '$inputs_xml' ";
         $listobject->querystring .= " where elementid = $newelid ";
         if ($debug) {
            $innerHTML .= "$listobject->querystring<br>";
         }
         $listobject->performQuery();
         if ( isset($formValues['geomtype']) and isset($formValues['the_geom']) ) {
            setElementGeometry($newelid, $formValues['geomtype'], $formValues['the_geom']);
         }

      } else {
         $innerHTML .= $compres['errorHTML'];
      }
   }
      
   $insertresult['elementid'] = $newelid;
   // pass this back in case we selected the default scenarioid for the user
   $insertresult['scenarioid'] = $scenarioid;
   $insertresult['innerHTML'] = $innerHTML;
   return $insertresult;
}

function saveObjectSubComponents($listobject, $thisobject, $elid, $overwrite=0, $debug = 0 ) {
   $innerHTML = '';
   if (is_object($thisobject)) {
      $k = 1;
      if (property_exists($thisobject, 'processors')) {
         $numprocs = count($thisobject->processors);

         $listobject->querystring = " update scen_model_element set elemoperators = ARRAY[''::text] ";
         $listobject->querystring .= " where elementid = $elid ";
         if ($debug) {
            error_log("$listobject->querystring<br>");
         }
         $listobject->performQuery();
         $innerHTML .= "New object has $numprocs sub-components added to it <br>";
         $innerHTML .= "Processor Names: " . print_r($thisobject->procnames,1) . " <br>";
         //error_log("Iterating through processors");
         foreach ($thisobject->processors as $thisproc) {
            # compact up processors if they are valid

            if (is_object($thisproc)) {
               $sct = get_class($thisproc);
               $whoprops = getWHOXML($sct);
               //error_log("Handling type: $sct <br>");
               if ( isset($whoprops['xml']) ) {
                  // create object
                  $sub_xml = $whoprops['xml'];
                  //error_log("Loading XML <br>");
                  $thisload = loadElement($sub_xml);
                  //error_log("XML Loaded<br>");
                  $subobject = $thisload['object'];
                  $innerHTML .= $thisload['debug'];
                  if (is_object($subobject)) {
                     $props = (array)$subobject;
                  } else {
                     $props = array();
                  }
                  $propstr = '';
                  $pa = 0;
                  # now set the object properties with the information passed in
                  $dont_set = array('parentobject', 'componentid');
                  foreach (array_keys($props) as $thisprop) {
                     $propstr .= ',' . $thisprop;
                     if (property_exists($thisproc, $thisprop) and (!in_array($thisprop, $dont_set)) ) {
                        # scalar prop
                        //error_log("getting value of property $thisprop <br>");
                        if ($thisprop <> '') {
                           $propval = $thisproc->$thisprop;
                           //error_log("$thisprop retrieved<br>");
                           /*
                           if (is_array($propval[0])) {
                              $innerHTML .= " $thisprop is nested array.<br>";
                              $propval = $formValues[$thisprop][0];
                              //$innerHTML .= " $thisprop " . print_r($propval,1) . "<br>";
                           }
                           */
                           $subobject->setProp($thisprop, $propval);
                        }
                        $pa++;
                     }
                  }
                  //error_log("$pa properties applied to sub-component <br> ");
                  $procomp = compactSerializeObject($subobject);
                  $proxml = $procomp['object_xml'];
                  $innerHTML .= "$thisprocname converted to XML <br>";
                  $listobject->querystring = " update scen_model_element set elemoperators[$k] = '$proxml' ";
                  $listobject->querystring .= " where elementid = $elid ";
                  if ($debug) {
                     error_log("$listobject->querystring<br>");
                  }
                  $listobject->performQuery();
               } else {
                  $innerHTML .= "Problem storing $procname <br>";
               }
            } else {
               $innerHTML .= "$thisprocname is not an object <br>";
            }

            $k++;
         }
      }
      $innerHTML .= "$k procs stored <br>";
   }
   
   return $innerHTML;
}

function setElementGeometry($elid, $geomtype, $wkt_geom) {
   global $listobject;
   # should auto-determine geom type if we pass -1 as geomtype (but I will do this later)
   switch ($geomtype) {
      case 1:
         $geomcol = 'point_geom';
         $geomexp = " GeomFromText('$wkt_geom', 4326) ";
      break;
      
      case 2:
         $geomcol = 'line_geom';
         $geomexp = " Multi(GeomFromText('$wkt_geom', 4326)) ";
      break;
      
      case 3:
         $geomcol = 'poly_geom';
         $geomexp = " Multi(GeomFromText('$wkt_geom', 4326)) ";
      break;
      
   }
   
   $listobject->querystring = "  update scen_model_element set geomtype = $geomtype, $geomcol = $geomexp ";
   $listobject->querystring .= " where elementid = $elid ";
   
   $listobject->performQuery();
   
}

function refreshWHOObjectsResult($formValues) {
   global $listobject, $debug, $userid;
   if ($userid <> 1) {
      $controlHTML .= "<b>Error:</b> You are not allowed to access this function.<br>";
      return $controlHTML;
   }
   include_once("who_xmlobjects.php");
   $reload = 0;
   if (isset($formValues['reload'])) {
      $reload = $formValues['reload'];
   }

   if ($reload == 1) {
      $listobject->querystring = "  delete from who_xmlobjects ";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ; <br>";
      }
      $listobject->performQuery();

      foreach ($who_xmlobjects as $classname => $classprops) {
         $classxml = $classprops['xml'];
         $name = $classprops['name'];
         if (isset($classprops['parent'])) {
            $parent = serialize($classprops['parent']);
         } else {
            $parent = '';
         }
         if (isset($classprops['parentprops'])) {
            $parentprops = serialize($classprops['parentprops']);
         } else {
            $parentprops = '';
         }
         $description = $classprops['description'];
         if (isset($classprops['localprops'])) {
            $localprops = serialize($classprops['localprops']);
         } else {
            $localprops = '';
         }
         if (isset($classprops['toolgroup'])) {
            $toolgroup = $classprops['toolgroup'];
         } else {
            $toolgroup = 7;
         }
         $type = $classprops['type'];
         $listobject->querystring = "  insert into who_xmlobjects (classname, name, classxml, parent, parentprops, ";
         $listobject->querystring .= "    localprops, description, type, toolgroup) ";
         $listobject->querystring .= " values ('$classname', '$name', '$classxml', '$parent', '$parentprops', ";
         $listobject->querystring .= "    '$localprops', '$description', $type, $toolgroup) ";
         if ($debug) {
            $controlHTML .= $listobject->querystring . " ; <br>";
         }
         $controlHTML .= "Inserting $name - $classname  <br>";
         $listobject->performQuery();
      }
   }
   return $controlHTML;

}

function getWHOXML($classname) {
   global $listobject;

   $classprops = array();

   $listobject->querystring = "  select * from who_xmlobjects where classname = '$classname'";
   $listobject->performQuery();
   $whorecs = $listobject->queryrecords;
   if (count($whorecs) > 0) {
      $cp = $whorecs[0];
      $classprops['xml'] = $cp['classxml'];
      $classprops['name'] = $cp['classname'];
      $classprops['parent'] = unserialize($cp['parent']);
      $classprops['parentprops'] = unserialize($cp['parentprops']);
      $classprops['description'] = $cp['description'];
      $classprops['localprops'] = unserialize($cp['localprops']);
      $classprops['type'] = $cp['type'];
   }

   return $classprops;

}

function showOperatorEditForm($formValues) {
   global $libpath, $listobject, $debug, $adminsetuparray;
   include_once("adminsetup.php");
   $controlHTML = '';
   #include_once("who_xmlobjects.php");
   if (!isset($formValues['elementid'])) {
      # nothing loade, cannot have a component without a parent, return message
      $controlHTML .= "<b>Error:</b> No element selected.<br>";
      $objResponse->assign("elementops","innerHTML",$controlHTML);

   } else {
      $elementid = $formValues['elementid'];
      $elemtype = $formValues['elemtype'];
      if (isset($formValues['parenttype'])) {
         $parenttype = split(",",$formValues['parenttype']);
         //$controlHTML .= "Getting Parent type from form field data: " . $parenttype . "<br>";
      } else {
         //$controlHTML .= "Getting Parent type from elemtype field: " . print_r($elemtype,1) . "<br>";
         if (is_array($elemtype)) {
            $parenttype = $elemtype;
         } else {
            $parenttype = array($elemtype);
         }
      }
      //$controlHTML .= "Parent type form data: " . print_r($parenttype,1) . "<br>";
      if (isset($formValues['operatorid'])) {
         # -1 will show all values (default value if nothing is passed to the routine)
         # 0 will create a blank operator (default to equation)
         # greater than 0 will show a specific operator with the given index value
         $operatorid = $formValues['operatorid'];
      } else {
         $operatorid = 0;
      }
      $objResponse = new xajaxResponse();
      #$debug = 1;
      $opresult = operatorEditForm($formValues,$who_xmlobjects, $elementid, $parenttype, $operatorid, 0);
      $i = $opresult['lastindex'];
      #$controlHTML = "Operator $operatorid requested: ";
      $controlHTML .= $opresult['innerHTML'];
      # if only one is edited, then just assign that one to be refreshed,referenced by 'op$i' where $i is the ID
      # if all are requested, or if this is an addition, we have to add all back in, so we reference
      # the container div, 'elementops'
      switch ($operatorid) {
         case 0:
         # have to get all the other ones (indicated by -1):
         $controlHTML .= 'New Operator Added<br>';
         $formValues['toggleStatus'] = 'block';
         $opresult = operatorEditForm($formValues,$who_xmlobjects, $elementid, $parenttype, -1, 0);
         $oplist = $opresult['innerHTML'];
         $objResponse->assign("elementops","innerHTML", $oplist . $controlHTML);
         break;

         case -1:
         $objResponse->assign("elementops","innerHTML",$controlHTML);
         break;

         default:
         $objResponse->assign("operatorcontrol$operatorid","innerHTML",$controlHTML);
         break;
      }
   }
   # adding call to fileNice
   $objResponse->call('parent.setFNFunctions','');
   #$objResponse->call('this.setFNFunctions','');
   return $objResponse;
}

function showOperatorEditResult($formValues) {
   global $libpath, $userid, $listobject, $usergroupids, $adminsetuparray, $timer;
   include_once("adminsetup.php");
   #include_once("who_xmlobjects.php");
   $objResponse = new xajaxResponse();
   #$debug = 1;
   //$localdebug = 1;

   $timer->startSplit();
   if ($debug or $localdebug) {
      $controlHTML .= "getting form variables: ";
   }
   if (!isset($formValues['elementid'])) {
      # nothing loade, cannot have a component without a parent, return message
      $controlHTML = "<b>Error:</b> No element selected.<br>";
      $objResponse->assign("op$i","innerHTML",$controlHTML);
      # adding call to fileNice
      $objResponse->call('parent.setFNFunctions','');
      #$objResponse->call('this.setFNFunctions','');
      return $objResponse;
   }
   if (!isset($formValues['operatorid'])) {
      # nothing loade, cannot have a component without a parent, return message
      $controlHTML = "<b>Error:</b> No Ooperator selected.<br>";
      $objResponse->assign("op$i","innerHTML",$controlHTML);
      # adding call to fileNice
      $objResponse->call('parent.setFNFunctions','');
      #$objResponse->call('this.setFNFunctions','');
      return $objResponse;
   }
   $split = $timer->startSplit();
   if ($debug or $localdebug) {
      $controlHTML .= $split . "<br>";
   }
   $elemperms = getScenElementPerms($listobject, $formValues['elementid'], $userid, $usergroupids, $debug);
   if ( !($elemperms & 2) ) {
      $disabled = 1;
      $controlHTML = "<b>Error:</b> You do not have edit permissions on this element.<br>";
   } else {
      $disabled = 0;
   }
   $split = $timer->startSplit();
   if ($debug or $localdebug) {
      $controlHTML .= $split . "<br>";
   }
   $elementid = $formValues['elementid'];
   $operatorid = $formValues['operatorid'];
   $opname = $formValues['name'];
   $elemtype = $formValues['elemtype'];
   if (isset($formValues['parenttype'])) {
      $parenttype = split(",",$formValues['parenttype']);
   } else {
      $parenttype = array($elemtype);
   }
   # otherwise, all required info has been supplied, so lets go ahead and save this thing
   # refresh the form so that we can print out informational messages about save
   if (!$disabled) {
      if ($formValues['actiontype'] == 'delete') {
         $opresult = deleteElementOperator($formValues);
         $controlHTML .= $opresult;
      } else {
         $controlHTML .= "Saving $opname ($operatorid) ... ";
         $opresult = saveElementOperator($formValues);
         $split = $timer->startSplit();
         if ($debug or $localdebug) {
            $controlHTML .= $split . "<br>";
         }
         $controlHTML .= "Generating operator form ... ";
         $i = $opresult['lastindex'];
         # now load the form
         $formResult = operatorEditForm($formValues,$who_xmlobjects, $elementid, $parenttype, $operatorid, 1);
         $controlHTML .= $opresult['innerHTML'] . $formResult['innerHTML'];
         $split = $timer->startSplit();
         if ($debug or $localdebug) {
            $controlHTML .= $split . "<br>";
         }
      }
   }

   $objResponse->assign("operatorcontrol$operatorid","innerHTML",$controlHTML);
   # adding call to fileNice
   $objResponse->call('parent.setFNFunctions','');
   #$objResponse->call('this.setFNFunctions','');

   return $objResponse;
}

function showElementBrowser($formValues) {
   global $listobject, $projectid, $scenarioid, $debug, $adminsetuparray, $userid, $defscenarioid, $usergroupids;
   $innerHTML = '';

   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }
   if (strlen($seglist) > 0) {
      $sscond = " b.subshedid in ( $seglist )";
   } else {
      $sscond = "(1 = 1)";
   }
   if (isset($formValues['showoutside'])) {
      $showoutside = $formValues['showoutside'];
   } else {
      $showoutside = 0;
   }
   $showoutside = 1;
   if (isset($formValues['activecontainerid'])) {
      $activecontainerid = $formValues['activecontainerid'];
   } else {
      $activecontainerid = '';
   }
   if (isset($formValues['vis_allobjects'])) {
      $vis_allobjects = $formValues['vis_allobjects'];
   } else {
      $vis_allobjects = 'block';
   }


   # element name, array containing element values (each of these values should have a name, and optionally a value list)

   # print the opening line for the object menu
   $innerHTML .= "<form name='elementbrowser' id='elementbrowser'>";
   $innerHTML .= showHiddenField('actiontype', 'editelement', 1);
   $innerHTML .= showHiddenField('projectid', $projectid, 1);
   $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);
   $innerHTML .= showHiddenField('elementid', '', 1);
   $innerHTML .= showHiddenField('vis_allobjects', $vis_allobjects, 1);


   $innerHTML .= "<b>Active Model:</b>" . showActiveList($listobject, 'activecontainerid', 'scen_model_element', 'elemname', 'elementid', "scenarioid = $scenarioid and component_type = 3", $activecontainerid, " last_tab[\"model_element\"]=\"model_element_data0\"; last_button[\"model_element\"]=\"model_element_0\"; xajax_showAddElementForm(xajax.getFormValues(\"elementbrowser\"))", '', $debug, 1, $disabled);
   if ($activecontainerid > 0) {
      # add a new element button
      $innerHTML .= "<br><a class=\"mH\" onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; ";
      $innerHTML .= "xajax_showAddElementForm(xajax.getFormValues('elementbrowser')); ";
      $innerHTML .= "\">  Create New Object in Active Model </a>";
   } else {
      # add a new element button - disabled
      $innerHTML .= "<br> Create New Object in Active Model ";
   }
   # add a new Mondel Container button
   $innerHTML .= " | <a class=\"mH\" onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; document.forms['elementbrowser'].actiontype.value = 'newcontainer'; ";
   $innerHTML .= "xajax_showAddElementForm(xajax.getFormValues('elementbrowser')); ";
   $innerHTML .= "\">  Create New Model </a>";

   $innerHTML .= "<br><a class=\"mT\" ";
   $innerHTML .= "onclick=\"toggleMenu('allobjects')\"><b>+ Existing Objects: </b>(click to hide/expand)</a>";
   #$innerHTML .= showCheckBox('showoutside', 1, $showoutside, "document.forms['addelement'].elements.showoutside.value=document.forms['elementbrowser'].elements.showoutside.value;xajax_showAddElementForm(xajax.getFormValues('elementbrowser'))", 1);
   #$innerHTML .= 'Show Objects Outside Current Boundaries';
   $innerHTML .= "<div id=\"allobjects\"  style=\"display: $vis_allobjects;\" class=\"mC\" ><ul>";


   #############################################
   ### BEGIN - hierarchical containment list ###
   #############################################

   # alternative query which will show all elements grouped under a model container
   # get all objects that contain other objects (as indicated by a containing link in the link table
   # get all objects that are contained by one of the containing objects, grouped unnder the object that contains them
   $listobject->querystring = "  select a.elementid, a.elemname ";
   $listobject->querystring .= " from scen_model_element as a ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.component_type = 3 ";
   $listobject->querystring .= "    and a.elementid = $activecontainerid ";
   $listobject->querystring .= " group by a.elementid, a.elemname ";
   $listobject->querystring .= " order by a.elemname ";
   #$debug = 1;
   if ($debug) {
      $innerHTML .= $listobject->querystring . " ; <br>";
   }
   $listobject->performQuery();
   $elemrecs = $listobject->queryrecords;

#$debug = 1;
   foreach ($elemrecs as $thiselem) {
      $elementid = $thiselem['elementid'];
      $elemname = $thiselem['elemname'];

      # HTML for the menu
      $innerHTML .= "<li>";
      $innerHTML .= "<a class=\"mH\" ";
      # old - toggles menu to show properties of this object
      $innerHTML .= "onclick=\"toggleMenu('$elementid')\">+ $elemname</a>";
      # new - goes into edit mode, more intuitive?
      #$innerHTML .= "onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; document.forms['elementbrowser'].elements.elementid.value=$elementid; ";
      #$innerHTML .= "xajax_showAddElementForm(xajax.getFormValues('elementbrowser'))\"> $elemname</a>";
      # Edit Object
      $innerHTML .= "<a class=\"mE\" onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; document.forms['elementbrowser'].elements.elementid.value=$elementid; ";
      $innerHTML .= "xajax_showAddElementForm(xajax.getFormValues('elementbrowser')); ";
      $innerHTML .= "\">  (Edit) </a>";
      # Clone Object
      $innerHTML .= "<a class=\"mE\" onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; document.forms['elementbrowser'].elements.elementid.value=$elementid; ";
      $innerHTML .= "document.forms['elementbrowser'].elements.actiontype.value='clone'; ";
      $innerHTML .= "xajax_showAddElementForm(xajax.getFormValues('elementbrowser')); ";
      $innerHTML .= "\"> (Clone) </a>";
      $innerHTML .= "<div id=\"$elementid\" class=\"mL\" style=\"display: block;\" ><ul>";


      $listobject->querystring = "  select a.elementid, a.elemname ";
      $listobject->querystring .= " from scen_model_element as a, map_model_linkages as c  ";
      $listobject->querystring .= " where a.elementid = c.src_id ";
      $listobject->querystring .= "    and c.linktype = 1 ";
      $listobject->querystring .= "    and c.dest_id = $elementid ";
      $listobject->querystring .= " group by a.elementid, a.elemname ";
      $listobject->querystring .= " order by a.elemname ";
      #$debug = 1;
      if ($debug) {
         $innerHTML .= $listobject->querystring . " ; <br>";
      }
      $listobject->performQuery();
      $thisinputs = $listobject->queryrecords;

      # show contained components
      #$innerHTML .= "<li><div class=\"mH\"";
      #$innerHTML .= "onclick=\"toggleMenu('$elementid" . "_inputs')\">+ Inputs</div>";

      #$innerHTML .= "<div id=\"$elementid" . "_inputs\" class=\"mL\"><ul>";
      foreach ($thisinputs as $thisip) {
         $inputname = $thisip['elemname'];
         $inputid = $thisip['elementid'];
         # show edit, clone links
         #$innerHTML .= "<li> $inputname - $inputid ";

         $innerHTML .= "<li><a class=\"mE\" ";
         $innerHTML .= "onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; document.forms['elementbrowser'].elements.vis_allobjects.value='none'; document.forms['elementbrowser'].elements.elementid.value=$inputid; ";
         $innerHTML .= "xajax_showAddElementForm(xajax.getFormValues('elementbrowser')); \"> $inputname</a>";
         # Edit Object
         $innerHTML .= "<a class=\"mE\" onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; document.forms['elementbrowser'].elements.vis_allobjects.value='none'; document.forms['elementbrowser'].elements.elementid.value=$inputid; ";
         $innerHTML .= "xajax_showAddElementForm(xajax.getFormValues('elementbrowser')); ";
         $innerHTML .= "\">  (Edit) </a>";
         # Clone Object
         $innerHTML .= "<a class=\"mE\" onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; document.forms['elementbrowser'].elements.elementid.value=$inputid; ";
         $innerHTML .= "document.forms['elementbrowser'].elements.actiontype.value='clone'; ";
         $innerHTML .= "xajax_showAddElementForm(xajax.getFormValues('elementbrowser'))\"> (Clone) </a>";

      }
      #$innerHTML .= "</div>";

      $innerHTML .= "</ul></div>";
   }
   #############################################
   ###  END - hierarchical containment list  ###
   #############################################


   $innerHTML .= "</ul></div>";
   $innerHTML .= "</form>";
#$debug = 0;
   return $innerHTML;
}


function showContainedElementBrowser2($elementid) {
   global $listobject, $projectid, $scenarioid, $debug, $adminsetuparray, $userid, $defscenarioid, $usergroupids;
   $innerHTML = '';
   $formname = 'elementtree';
   $listobject->querystring = "  select a.elementid, a.elemname ";
   $listobject->querystring .= " from scen_model_element as a, map_model_linkages as c  ";
   $listobject->querystring .= " where a.elementid = c.src_id ";
   $listobject->querystring .= "    and c.linktype = 1 ";
   $listobject->querystring .= "    and c.dest_id = $elementid ";
   $listobject->querystring .= " group by a.elementid, a.elemname ";
   $listobject->querystring .= " order by a.elemname ";
   #$debug = 1;
   if ($debug) {
      $innerHTML .= $listobject->querystring . " ; <br>";
   }
   $listobject->performQuery();
   $thisinputs = $listobject->queryrecords;
   $nc = count($thisinputs);

   if ($nc > 0) {
      $innerHTML .= "<br><a ";
      $innerHTML .= "onclick=\"toggleMenu('allobjects')\"><i>+ Contains $nc Children</i></a>";
      $innerHTML .= "<div id=\"allobjects\"  style=\"display: $vis_allobjects;\" class=\"mC\" >";


      foreach ($thisinputs as $thisip) {
         $inputname = $thisip['elemname'];
         $inputid = $thisip['elementid'];
         # show edit, clone links
         #$innerHTML .= "<li> $inputname - $inputid ";

         $innerHTML .= "<a class=\"mE\" ";
         $innerHTML .= "onclick=\"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$inputid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$elementid; document.forms['$formname'].elements.scenarioid.value=$scenarioid; xajax_showModelDesktopView(xajax.getFormValues('$formname'));";
         $innerHTML .= "\"> $inputname</a><br>";

      }
   } else {
      $innerHTML .= "<i>+ Contains 0 Objects: </i>";
   }
   #$innerHTML .= "</div>";

   #############################################
   ###  END - hierarchical containment list  ###
   #############################################


   $innerHTML .= "</div>";
   $innerHTML .= "</form>";
#$debug = 0;
   return $innerHTML;
}


function showElementInputBrowser($formValues, $disabled=0) {
   global $listobject, $projectid, $scenarioid, $debug, $adminsetuparray, $timer;
   $innerHTML = '';

   if (isset($formValues['elementid'])) {
      $elementid = $formValues['elementid'];
   } else {
      $innerHTML .= '<b>Notice:</b> Object must be saved before inputs are added.<br>';
      return $innerHTML;
   }

   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }
   if (strlen($seglist) > 0) {
      $sscond = " b.subshedid in ( $seglist )";
   } else {
      $sscond = "(1 = 1)";
   }
   if (isset($formValues['showoutside'])) {
      $showoutside = $formValues['showoutside'];
   } else {
      $showoutside = 0;
   }
   # only show objects that are contained by this model container (or this elements parent container)
   if (isset($formValues['localsonly'])) {
      $localsonly = $formValues['localsonly'];
   } else {
      $localsonly = 1;
   }

   # select list of possible elements to add inputs from, stash them in an array to be used later in select list
   if ($localsonly) {
      // show any elements that ARE CONTAINED by ANY ELEMENT THAT CONTAINS this element
      $listobject->querystring = "( select a.elementid, a.elemname, a.elemprops ";
      $listobject->querystring .= " from scen_model_element as a, map_model_linkages as b ";
      $listobject->querystring .= " where ";
      $listobject->querystring .= " (a.elementid = b.src_id ";
      $listobject->querystring .= "    and b.dest_id in ";
      $listobject->querystring .= "       (select dest_id from map_model_linkages ";
      $listobject->querystring .= "        where src_id = $elementid";
      $listobject->querystring .= "           and linktype = 1 ";
      $listobject->querystring .= "        group by dest_id) ";
      $listobject->querystring .= "    and b.linktype = 1 ";
      $listobject->querystring .= "    and b.src_id <> $elementid ";
      $listobject->querystring .= " ) ";
      $listobject->querystring .= " group by a.elementid, a.elemname, a.elemprops ";
      $listobject->querystring .= " order by a.elemname ";
      // show any elements that CONTAIN this element
      $listobject->querystring .= " ) UNION (";
      $listobject->querystring .= " select a.elementid, a.elemname, a.elemprops ";
      $listobject->querystring .= " from scen_model_element as a ";
      $listobject->querystring .= " where ( ";
      $listobject->querystring .= "  a.elementid in ";
      $listobject->querystring .= "       (select dest_id from map_model_linkages ";
      $listobject->querystring .= "        where src_id = $elementid";
      $listobject->querystring .= "           and linktype = 1 ";
      $listobject->querystring .= "        group by dest_id) ";
      $listobject->querystring .= " ) ";
      $listobject->querystring .= " group by a.elementid, a.elemname, a.elemprops ";
      $listobject->querystring .= " order by a.elemname ";
      // show any elements that ARE CONTAINED BY this element
      $listobject->querystring .= " ) UNION (";
      $listobject->querystring .= " select a.elementid, a.elemname, a.elemprops ";
      $listobject->querystring .= " from scen_model_element as a ";
      $listobject->querystring .= " where ( ";
      $listobject->querystring .= "  a.elementid in ";
      $listobject->querystring .= "       (select src_id from map_model_linkages ";
      $listobject->querystring .= "        where dest_id = $elementid";
      $listobject->querystring .= "           and linktype = 1 ";
      $listobject->querystring .= "        group by src_id) ";
      $listobject->querystring .= " ) ";
      $listobject->querystring .= " group by a.elementid, a.elemname, a.elemprops ";
      $listobject->querystring .= " order by a.elemname ";
      $listobject->querystring .= " ) ";

   } else {
      $listobject->querystring = "  select elementid, elemname, elemprops ";
      $listobject->querystring .= " from ( ";
      $listobject->querystring .= " ( select elementid, elemname, elemprops ";
      $listobject->querystring .= "   from scen_model_element as a, proj_subsheds as b ";
      $listobject->querystring .= "   where b.projectid = $projectid ";
      # check to see if we want to restrict to current group boundaries
      if ($showoutside) {
         # do not restrict
      } else {
         $listobject->querystring .= "    and ( $sscond ) ";
      }
      $listobject->querystring .= "      and a.elementid <> $elementid ";
      $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
      $listobject->querystring .= "      and within(a.the_geom, b.the_geom) ";
      $listobject->querystring .= " ) UNION ( ";
      $listobject->querystring .= "   select elementid, ";
      $listobject->querystring .= "      CASE ";
      $listobject->querystring .= "         WHEN scenarioid <> $scenarioid THEN 'X-ternal: ' || elemname ";
      $listobject->querystring .= "         ELSE elemname ";
      $listobject->querystring .= "      END as elemname, ";
      $listobject->querystring .= "      elemprops ";
      $listobject->querystring .= "   from scen_model_element as a ";
      $listobject->querystring .= "   where a.elementid in ";
      $listobject->querystring .= "       (select src_id from map_model_linkages where dest_id = $elementid) ";
      $listobject->querystring .= "    ) ";
      $listobject->querystring .= " ) as foo ";
      $listobject->querystring .= "   order by elemname ";
   }
   if ($debug) {
      $innerHTML .= $listobject->querystring . " ; <br>";
   }
   $listobject->performQuery();
   $elemlist = $listobject->queryrecords;

   # create the unserializer, no options, so defaults to returning an array
   $options = array();
   $unserializer = new XML_Unserializer($options);

   # element name, array containing element values (each of these values should have a name, and optionally a value list)
   $listobject->querystring = "  select a.src_id, b.elemname, a.dest_prop, a.src_prop, b.elemprops ";
   $listobject->querystring .= " from map_model_linkages as a, scen_model_element as b ";
   $listobject->querystring .= " where a.dest_id = $elementid ";
   $listobject->querystring .= "    and b.elementid = a.src_id ";
   # specify non-containment linkage
   $listobject->querystring .= "    and a.linktype = 2 ";
   $listobject->querystring .= " order by a.dest_prop ";
   if ($debug) {
      $innerHTML .= $listobject->querystring . " ; <br>";
   }
   $listobject->performQuery();
   $linkrecs = $listobject->queryrecords;

   # print the opening line for the object menu
   $i = 0;
   $innerHTML .= "<table><tr><td>Delete Link</td><td>Local Variable Name</td><td>Source Object</td><td>Object Property Name</td></tr>";
   foreach ($linkrecs as $thiselem) {
      if ($i == 0) {
         #error_log("Link variables " . print_r(array_keys($thiselem),1));
      }
      $src_id = $thiselem['src_id'];
      $elemname = $thiselem['elemname'];
      $src_prop = $thiselem['src_prop'];
      $dest_prop = $thiselem['dest_prop'];
      $prop_xml = $thiselem['elemprops'];
      #$innerHTML .= 'Props:' .  $prop_xml;
      $innerHTML .= '<tr><td valign=top>' . showCheckBox("deleteinput[$i]", 1, '', '', 1, $disabled) . '</td>';
      $innerHTML .= '<td valign=top>' . showWidthTextField("inputname[$i]", $dest_prop, 16, '', 1, $disabled) . '</td>';
      $innerHTML .= '<td valign=top>' . showActiveList($elemlist, "inputid[$i]", $elemlist, 'elemname','elementid', '',$src_id, "document.forms[\"addelement\"].elements.activetab.value=\"inputs\"; xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", 'elemname', $debug, 1, $disabled) . '</td>';
      # unserialize the property list
      $result = $unserializer->unserialize($prop_xml, false);
      $proplist = $unserializer->getUnserializedData();
      $prop_form = array();
      $k = 0;
      foreach ($proplist as $thisprop) {
         if (is_array($thisprop)) {
            foreach (array_values($thisprop) as $propval) {
               $prop_form[$k]['propkey'] = $propval;
               $prop_form[$k]['propval'] = $propval;
               #$innerHTML .= 'Prop ' . $k .  ' = ' . $thisprop . ',';
               $k++;
            }
         } else {
            $prop_form[$k]['propkey'] = $thisprop;
            $prop_form[$k]['propval'] = $thisprop;
            #$innerHTML .= 'Prop ' . $k .  ' = ' . $thisprop . ',';
            $k++;
         }
      }
      sort($prop_form);
      #$innerHTML .='Props:' .  print_r($prop_form, 1);
      #$timer->startSplit();
      # this is replaced by the next line, which is about 1,000 times faster
      #$innerHTML .= '<td valign=top>' . showElementPropertyList($src_id, $i, $src_prop, $disabled);
      $innerHTML .= '<td valign=top>' . showActiveList($prop_form, "input[$i]", $prop_form, 'propval','propkey', '',$src_prop, '', 'propval', $debug, 1, $disabled);
      #$innerHTML .= $timer->startSplit();
      $innerHTML .= '</td></tr>';

      $i++;
   }
   # show one blank entry
   $innerHTML .= '<tr><td valign=top>' . showCheckBox("deleteinput[$i]", 1, '', '', 1, $disabled) . '</td>';
   $innerHTML .= '<td valign=top>' . showWidthTextField("inputname[$i]", '', 16, '', 1, $disabled) . '</td>';
   $innerHTML .= '<td valign=top>' . showActiveList($elemlist, "inputid[$i]", $elemlist, 'elemname','elementid', '','', "document.forms[\"addelement\"].elements.activetab.value=\"inputs\"; xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", 'elemname', $debug, 1, $disabled) . '</td>';
   $innerHTML .= '<td valign=top>' . showElementPropertyList('', $i, '', $disabled) . '</td></tr>';
   $innerHTML .= "</table>";

   return $innerHTML;
}

function showRemoteObjectBrowserSelect($formValues) {
   global $listobject;
   // this is the first half of a paired set of xajax functions 
   // this shows the hierarchical browser to select an object to link
   // the second half of the pair 'showRemoteObjectBrowserProperties' 
   // removes the browser, putting the selected object in there, and 
   // then shows the property browser for that object
   // we pass in the currently selected element ID, as well as the currently selected prop, in case the 
   // methiod needs to be cancelled, in which case the old values should be returned
   $objResponse = new xajaxResponse();
   $thisdiv = $formValues['divname'];
   $controlHTML = remoteLinkEditForm($formValues, 1);
   $objResponse->assign($thisdiv,"innerHTML",$controlHTML);
   return $objResponse;
}

function addRemoteObjectSelect($formValues) {
   global $listobject;
   // create a blank link template for the javascript
   error_log("Calling addRemoteObjectSelect()");
   $objResponse = new xajaxResponse();
   $innerHTML = '';
   $j = $formValues['rlinkcount'];
   $scenarioid = $formValues['scenarioid'];
   $elementid = $formValues['elementid'];

   // this is an insert request
   $listobject->querystring = "  insert into map_model_linkages (src_prop, dest_prop, src_id, dest_id, linktype ) ";
   $listobject->querystring .= " values ('', '', -1, $elementid, 3 ) ";
   if ($debug) {
      $controlHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();
   $listobject->querystring = "  select currval('map_model_linkages_linkid_seq') as linkid ";
   if ($debug) {
      $controlHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();
   $linkid = $listobject->getRecordValue(1, 'linkid');
   if (!($linkid > 0)) {
      $innerHTML .= "Error: Could not create new link to object # $elementid <br>";
   } else {
   
      $linkvalues = array();
      # show one blank entry
      $linkValues['formname'] = "remotelink$j";
      $linkValues['divname'] = "remote_entry$j"; // this holds the entire link form - this does NOT come from the remoteLinkEditForm routine, but instead is assigned outside to allow for flexibility
      $linkValues['srcdivname'] = "remote_src$j";
      $linkValues['propdivname'] = "remote_propentry$j";
      $linkValues['linkid'] = $linkid;
      $linkValues['src_id'] = -1;
      $linkValues['scenarioid'] = $scenarioid;
      $linkValues['dest_prop'] = '';
      $linkValues['src_prop'] = '';
      $linkValues['dest_id'] = $elementid;
      // the div tag which encases this is added by the javascript call to this function, so no need to add the div
      //
      //error_log("Calling remoteLinkEditForm() with " . print_r($linkValues,1) );
      $innerHTML .= remoteLinkEditForm($linkValues);
      //error_log("Returned from remoteLinkEditForm() ");
   }
   $objResponse->assign("remote_entry$j","innerHTML",$innerHTML);
   return $objResponse;
}

function saveRemoteObjectBrowserSelect($formValues) {
   global $listobject;
   $objResponse = new xajaxResponse();
   $controlHTML = '';
   // get inputs
   $formname = $formValues['formname'];
   $thisdiv = $formValues['divname'];
   $thispropdiv = $formValues['propdivname'];
   $src_prop = $formValues['src_prop'];
   $dest_prop = $formValues['dest_prop'];
   $dest_id = $formValues['dest_id'];
   $src_id = $formValues['src_id'];
   $scenarioid = $formValues['scenarioid'];
   $linkid = $formValues['linkid'];
   
   $controlHTML .= print_r($formValues, 1) . "<br>";
   
   if ($linkid > 0 ) {
      $listobject->querystring = "  update map_model_linkages set src_prop = '$src_prop', dest_prop = '$dest_prop', ";
      $listobject->querystring .= "    src_id = $src_id, dest_id = $dest_id, linktype = 3 ";
      $listobject->querystring .= " where linkid = $linkid";
      if ($debug) {
         $controlHTML .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
   } else {
      $controlHTML .= "No valid linkid submitted ($linkid)<br>";
   }
   
   $objResponse->assign('status_bar',"innerHTML",$controlHTML);
   // now show the object browser
   $formValues['linkid'] = $linkid;
   $controlHTML = remoteLinkEditForm($formValues, 0);
   $objResponse->assign($thisdiv,"innerHTML",$controlHTML);
   return $objResponse;
}

function showRemoteInputBrowser($formValues, $disabled=0) {
   global $listobject, $projectid, $debug, $adminsetuparray, $timer, $icons;
   $innerHTML = '';

   if (isset($formValues['elementid'])) {
      $elementid = $formValues['elementid'];
   } else {
      $innerHTML .= '<b>Notice:</b> Object must be saved before inputs are added.<br>';
      return $innerHTML;
   }

   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }
   if (strlen($seglist) > 0) {
      $sscond = " b.subshedid in ( $seglist )";
   } else {
      $sscond = "(1 = 1)";
   }
   if (isset($formValues['showoutside'])) {
      $showoutside = $formValues['showoutside'];
   } else {
      $showoutside = 0;
   }
   $scenarioid = $formValues['scenarioid'];
   
   # create the unserializer, no options, so defaults to returning an array
   $options = array();
   $unserializer = new XML_Unserializer($options);

   # element name, array containing element values (each of these values should have a name, and optionally a value list)
   $listobject->querystring = "  select a.linkid, a.src_id, b.elemname, a.dest_prop, a.src_prop, b.elemprops ";
   $listobject->querystring .= " from map_model_linkages as a, scen_model_element as b ";
   $listobject->querystring .= " where a.dest_id = $elementid ";
   $listobject->querystring .= "    and b.elementid = a.src_id ";
   # specify non-containment linkage
   $listobject->querystring .= "    and a.linktype = 3 ";
   $listobject->querystring .= " order by a.dest_prop ";
   if ($debug) {
      $innerHTML .= $listobject->querystring . " ; <br>";
   }
   $listobject->performQuery();
   $linkrecs = $listobject->queryrecords;
   
   # print the opening line for the object menu
   $i = 0;
   
   $rlinkcount = count($linkrecs);
   $addClick = "rowplus = document.forms['remotelinkinfo'].elements.rlinkcount.value; rowHTML = '<div id=remote_entry' + rowplus + '></div>'; addRow('remotelinkinfo', 'remotelinktable',rowHTML); xajax_addRemoteObjectSelect(xajax.getFormValues('remotelinkinfo')); incrementFormField('remotelinkinfo', 'rlinkcount', 1)";
   $innerHTML .= "<a onClick=\"$addClick\" class='mH'>Add a New Remote Linkage</a>";
   $innerHTML .= "<form id='remotelinkinfo' id='remotelinkinfo'>";
   $innerHTML .= showHiddenField("scenarioid", $scenarioid, 1);
   $innerHTML .= showHiddenField("rlinkcount", $rlinkcount, 1);
   $innerHTML .= showHiddenField("elementid", $elementid, 1);
   $innerHTML .= "</form>";
   $innerHTML .= "<table id='remotelinktable'>";
   $innerHTML .= "<tr><td>";
   $innerHTML .= "<table widt=100%><tr><td width=5%>&nbsp;</td><td width=20%><b>Local Variable Name</b></td><td width=50%><b>Source Object</b></td><td width=25%><b>Object Property Name</b></td></tr></table>";
   $innerHTML .= "</td></tr>";
   
   $linkValues = array();
   
   foreach ($linkrecs as $thiselem) {
      if ($i == 0) {
         #error_log("Link variables " . print_r(array_keys($thiselem),1));
      }
      $linkValues['formname'] = "remotelink$i";
   
      $linkValues['linkid'] = $thiselem['linkid'];
      $linkValues['src_id'] = $thiselem['src_id'];
      $linkValues['linkname'] = $thiselem['elemname'];
      $linkValues['dest_id'] = $elementid;
      $linkValues['src_prop'] = $thiselem['src_prop'];
      $linkValues['dest_prop'] = $thiselem['dest_prop'];
      $linkValues['scenarioid'] = $scenarioid;
      $linkValues['divname'] = "remote_entry$i";
      $linkValues['srcdivname'] = "remote_src$i";
      $linkValues['propdivname'] = "remote_propentry$i";
      
      $innerHTML .= "<tr><td><div id='remote_entry$i'>";
      $innerHTML .= remoteLinkEditForm($linkValues);
      $innerHTML .= '</div></td></tr>';

      $i++;
   }
   $innerHTML .= "</table>";

   return $innerHTML;
}

function remoteLinkEditForm($formValues, $open=0) {
   global $icons, $listobject;
   $innerHTML = '';
   // get inputs
   $formname = $formValues['formname'];
   $thisdiv = $formValues['divname'];
   $thispropdiv = $formValues['propdivname'];
   $scenarioid = $formValues['scenarioid'];
   // columns for map_model_linkages
   $linkid = $formValues['linkid'];
   $src_id = $formValues['src_id'];
   $src_prop = $formValues['src_prop'];
   $dest_prop = $formValues['dest_prop'];
   $dest_id = $formValues['dest_id'];
   
   if ($src_id > 0) {
      $listobject->querystring = "  select elemname from scen_model_element where elementid = $src_id ";
      $listobject->performQuery();
      $elname = $listobject->getRecordValue(1,'elemname');
   } else {
      $elname = "Click here to add a remote object link";
      $thisid = -1;
      $thisprop = '';
   }
   
   # show one blank entry
   $onClick = "xajax_showRemoteObjectBrowserSelect(xajax.getFormValues('$formname'))";
   $saveClick = "xajax_saveRemoteObjectBrowserSelect(xajax.getFormValues('$formname'))";
   $innerHTML .= "<form name='$formname' id='$formname'><table><tr><td width=10% valign=center>" . "<a onClick=\"confirmDeleteRemoteLink('$elemname');\"><img src='" . $icons['trash'] . "'></a>";
   $innerHTML .= showHiddenField("formname", $formname, 1);
   $innerHTML .= showHiddenField("linkid", $linkid, 1);
   $innerHTML .= showHiddenField("divname", $thisdiv, 1);
   $innerHTML .= showHiddenField("propdivname", $thispropdiv, 1);
   $innerHTML .= showHiddenField("scenarioid", $scenarioid, 1);
   $innerHTML .= showHiddenField("dest_id", $dest_id, 1);
   $innerHTML .= '</td>';
   $innerHTML .= '<td valign=center width=20%>' . showWidthTextField("dest_prop", $dest_prop, 16, '', 1, $disabled, -1, $saveClick) . '</td>';
   $innerHTML .= "<td valign=center width=50%><div id=\"$thisdiv\" style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; \" >";
   if (!$open) {
      $innerHTML .= "<a onClick=\"$onClick\">$elname</a>";
      $innerHTML .= showHiddenField("src_id", $src_id, 1);
   } else {
      // show the hierarchical object browser
      $chooseButton = showGenericButton('choose', 'Choose', "xajax_saveRemoteObjectBrowserSelect(xajax.getFormValues(\"$formname\")); ", 1);
      $cancelButton = showGenericButton('cancel', 'c', "document.forms[\"$formname\"].elements.src_id = $src_id;  xajax_showRemoteObjectBrowserSelect(xajax.getFormValues(\"$formname\")) ;", 1);
      $menu = getScenarioHierarchy($listobject, $scenarioid);
      $menuHTML = showHierarchicalSelect($menu, 'src_id', $src_id, 0, $disabled);
      $innerHTML .= "$chooseButton - Cancel Button<br>";
      $innerHTML .= "<div id='src_domain' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 120px; width: 480px; display: block;  background: #eee9e9;\">";
      $innerHTML .= $menuHTML . "</div>";
   }

   $innerHTML .= "</div></td>";
   $innerHTML .= "<td valign=center width=20%><div id=\"$thispropdiv\">" . showElementPropertyList($src_id, -1, $src_prop, $disabled, 'src_prop', $saveClick) . "</td></tr></table></form>";

   return $innerHTML;
}
   

function getScenarioHierarchy($listobject, $scenid) {
   global $icons;
   $thismenu = array();
   
   $listobject->querystring = "  select elementid, elemname, objectclass ";
   $listobject->querystring .= " from (select a.elementid, a.elemname, a.objectclass, b.dest_id  ";
   $listobject->querystring .= "       from scen_model_element as a  ";
   $listobject->querystring .= "       left outer join map_model_linkages as b  ";
   $listobject->querystring .= "          on (b.src_id = a.elementid and b.linktype = 1)  ";
   $listobject->querystring .= "       where a.scenarioid = $scenid ";
   $listobject->querystring .= " ) as foo ";
   $listobject->querystring .= " where dest_id is null ";
   $listobject->performQuery();
   $roots = $listobject->queryrecords;
   foreach ($roots as $branch) {
      $elementid = $branch['elementid'];
      $name = $branch['elemname'];
      $icon = $icons['default']; # unless we have one set below
      if (isset($branch['objectclass'])) {
         if (in_array($branch['objectclass'], array_keys($icons)) ) {
            $icon = $icons[$branch['objectclass']];
         }
      }
      $thisbranch = array(
         'name'=>$name,
         'id'=>$elementid,
         'icon'=>$icon,
         'children'=>array()
      );
      $thisbranch['children'] = getElementChildInfo($elementid, $listobject);
      array_push($thismenu, $thisbranch);
   }
   return $thismenu;
}

function getElementPropertyValue($listobject, $elementid, $properties, $debug = 0) {
   $propvals = array();
   if (count($properties) == 0) {
      return array('errorMSG'=>'No properties requested');
   } else {
      $objresult = unSerializeSingleModelObject($elementid, array('debug'=>$debug));
      $thisobj = $objresult['object'];
      if (is_object($thisobj)) {
         $propvals['name'] = $thisobj->name;
         foreach ($properties as $thisprop) {
            $propvals[$thisprop] = $thisobj->getProp($thisprop);
         }
         unset($objresult);
      } else {
         return array('errorMSG'=>"Object $elementid not found");
      }
   }
   return $propvals;
}

function getElementChildInfo($elementid, $listobject) {
   global $icons;
   
   $listobject->querystring = "  select a.elementid, a.elemname, a.objectclass ";
   $listobject->querystring .= " from scen_model_element as a, map_model_linkages as b ";
   $listobject->querystring .= " where a.elementid = b.src_id ";
   $listobject->querystring .= "    and b.linktype = 1 ";
   $listobject->querystring .= "    and b.dest_id = $elementid ";
   $listobject->querystring .= " group by a.elementid, a.scenarioid, a.elemname, a.objectclass ";
   $listobject->querystring .= " order by a.elemname ";
   $listobject->performQuery();
   $obrecs = $listobject->queryrecords;
   $thisbranch = array();
   $levelobjects = array();
   $qs = "$listobject->querystring ; <br>";
   #error_log($qs);
   
   foreach ($obrecs as $thisrec) {
      $branchid = $thisrec['elementid'];
      $name = $thisrec['elemname'];
      $icon = $icons['default']; # unless we have one set below
      if (isset($thisrec['objectclass'])) {
         if (in_array($thisrec['objectclass'], array_keys($icons)) ) {
            $icon = $icons[$thisrec['objectclass']];
         }
      }
      $thisbranch = array(
         'name'=>$name,
         'id'=>$branchid,
         'icon'=>$icon,
         'children'=>array()
      );
      $thisbranch['children'] = getElementChildInfo($branchid, $listobject);
      
      array_push($levelobjects, $thisbranch);
   }
   return $levelobjects;
}


function showContainedElementBrowser($formValues, $disabled=0) {
   global $listobject, $projectid, $scenarioid, $debug, $adminsetuparray;
   $innerHTML = '';

   if (isset($formValues['elementid'])) {
      $elementid = $formValues['elementid'];
   } else {
      $innerHTML .= '<b>Notice:</b> Object must be saved before inputs are added.<br>';
      return $innerHTML;
   }

   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }
   if (strlen($seglist) > 0) {
      $sscond = " b.subshedid in ( $seglist )";
   } else {
      $sscond = "(1 = 1)";
   }
   if (isset($formValues['showoutside'])) {
      $showoutside = $formValues['showoutside'];
   } else {
      $showoutside = 0;
   }

   # select list of possible elements to add inputs from, stash them in an array to be used later in select list
   $listobject->querystring = "  select elementid, elemname ";
   $listobject->querystring .= " from scen_model_element as a";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   # Do not show objects that are contained by other containers
   $listobject->querystring .= "    AND  ";
   $listobject->querystring .= "    ( a.elementid not in ";
   $listobject->querystring .= "       (select src_id from map_model_linkages where dest_id <> $elementid and linktype = 1)  ";
   $listobject->querystring .= "   )";
   $listobject->querystring .= "    or (a.elementid in ";
   $listobject->querystring .= "       (select src_id from map_model_linkages where dest_id = $elementid) ";
   $listobject->querystring .= "    )";
   # also show anything that is not contained by any object except the Model Domain
   $listobject->querystring .= "    or (a.scenarioid = $scenarioid and a.elementid not in ";
   $listobject->querystring .= "       (select src_id from map_model_linkages where linktype = 1) ";
   $listobject->querystring .= "    )";
   $listobject->querystring .= "    and a.elementid <> $elementid ";
   $listobject->querystring .= " order by elemname ";
   if ($debug) {
      $innerHTML .= $listobject->querystring . " ; <br>";
   }
   $listobject->performQuery();
   $elemlist = $listobject->queryrecords;

   # element name, array containing element values (each of these values should have a name, and optionally a value list)
   $listobject->querystring = "  select a.src_id, b.elemname, a.dest_prop, a.src_prop ";
   $listobject->querystring .= " from map_model_linkages as a, scen_model_element as b ";
   $listobject->querystring .= " where a.dest_id = $elementid ";
   $listobject->querystring .= "    and b.elementid = a.src_id ";
   //$listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   # specify containment linkage
   $listobject->querystring .= "    and a.linktype = 1 ";
   if ($debug) {
      $innerHTML .= $listobject->querystring . " ; <br>";
   }
   $listobject->performQuery();
   $linkrecs = $listobject->queryrecords;

   # print the opening line for the object menu
   $i = 0;
   $innerHTML .= "<table><tr><td>Delete Link</td><td>Child Object</td></tr>";
   foreach ($linkrecs as $thiselem) {
      $src_id = $thiselem['src_id'];
      $elemname = $thiselem['elemname'];
      $src_prop = $thiselem['src_prop'];
      $dest_prop = $thiselem['dest_prop'];
      $innerHTML .= '<tr><td valign=top>' . showCheckBox("deletechild[$i]", 1, '', '', 1, $disabled) . '</td>';
      $innerHTML .= '<td valign=top>' . showActiveList($elemlist, "childid[$i]", $elemlist, 'elemname','elementid', '',$src_id, "document.forms[\"addelement\"].elements.activetab.value=\"contained\"; xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", 'elemname', $debug, 1, $disabled) . '</td></tr>';
      $i++;
   }
   # show one blank entry
   $innerHTML .= '<tr><td valign=top>' . showCheckBox("deletechild[$i]", 1, '', '', 1, $disabled) . '</td>';
   $innerHTML .= '<td valign=top>' . showActiveList($elemlist, "childid[$i]", $elemlist, 'elemname','elementid', '','', "document.forms[\"addelement\"].elements.activetab.value=\"contained\"; xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", 'elemname', $debug, 1, $disabled) . '</td></tr>';
   $innerHTML .= "</table>";

   return $innerHTML;
}

function showElementPropertyList($elementid, $index, $selectedcol, $disabled=0, $fieldname='input', $onChange) {
   global $listobject, $projectid, $scenarioid, $debug, $adminsetuparray, $obj_cache;
   if (!isset($obj_cache)) {
      $obj_cache = array();
   }
   $innerHTML = '';

   # print the opening line for the object menu
   $listobject->querystring = "select elemname from scen_model_element where elementid = $elementid ";
   $listobject->performQuery();
   $en = $listobject->getRecordValue(1,'elemname');
   if (in_array($en, array_keys($obj_cache))) {
      $thisobj = $obj_cache[$en];
      if ($debug) {
         $innerHTML .= "$en found in cache<br>";
      }
   } else {
      $objresult = unSerializeSingleModelObject($elementid);
      //$objresult = unSerializeModelObject($elementid);
      $thisobj = $objresult['object'];
      if ($debug) {
         $innerHTML .= "$en not found in cache<br>";
      }
      $obj_cache[$en] = $thisobj;
   }

   $proparr = array();

   #$debug = 1;
   if (is_object($thisobj)) {
      # only shows State vars
      $statenames = $thisobj->getPublicVars();
      sort($statenames);
      #$innerHTML .= $thisobj->errorstring . "<br>";
      #$innerHTML .= "Public Vars Returned: " . print_r($statenames,1) . "<br>";
      foreach($statenames as $propname) {
         array_push($proparr, array('elemprop'=>$propname) );
         if ($debug) {
            $innerHTML .= " $elementid : $propname ";
         }
         #$innerHTML .= "<li><a class=\"mO\">$propname</a>";
      }
      if ($index == -1) {
         $varname = $fieldname;
      } else {
         $varname = "$fieldname" . "[$index]";
      }
      //$innerHTML .= showList($listobject, $varname, $proparr,'elemprop', 'elemprop', '',$selectedcol,$debug, 1, $disabled);
      $innerHTML .= showActiveList($listobject, $varname, $proparr,'elemprop', 'elemprop', '',$selectedcol, $onChange, 'elemprop', $debug, 1, $disabled);
   } else {
      //$innerHTML .= showList($listobject, $varname, $proparr, 'elemprop', 'elemprop', '',$selectedcol,$debug, 1, $disabled);
      $innerHTML .= showActiveList($listobject, $varname, $proparr,'elemprop', 'elemprop', '',$selectedcol, $onChange, 'elemprop', $debug, 1, $disabled);
   }
   
   #$debug = 0;
   return $innerHTML;
}

function getElementPropertyList($elementid) {
   global $listobject, $projectid, $scenarioid, $debug, $adminsetuparray, $obj_cache;
   if (!isset($obj_cache)) {
      $obj_cache = array();
   }
   $innerHTML = '';
   $proparr = array();
   # print the opening line for the object menu
   $listobject->querystring = "select elemname from scen_model_element where elementid = $elementid ";
   $listobject->performQuery();
   $en = $listobject->getRecordValue(1,'elemname');
   if (in_array($elementid, array_keys($obj_cache))) {
      $thisobj = $obj_cache[$elementid];
      if ($debug) {
         $innerHTML .= "$en found in cache<br>";
      }
   } else {
      $objresult = unSerializeSingleModelObject($elementid);
      $thisobj = $objresult['object'];
      if ($debug) {
         $innerHTML .= "$en not found in cache - un-serializing Result:<br>";
         #$innerHTML .= $objresult['debug'];
      }
      $obj_cache[$elementid] = $thisobj;
   }


   #$debug = 1;
   if (is_object($thisobj)) {
      # only shows State vars
      $proparr = $thisobj->getPublicVars();
   }
   $proparr['elemname'] = $en;
   #$debug = 0;
   if ($debug) {
      error_log($innerHTML);
   }
   return $proparr;
}


function addElementForm($formValues, $who_xmlobjects) {
   global $listobject, $ucitables, $projectid, $scenarioid, $debug, $userid, $usergroupids, $adminsetuparray, $timer;
   $innerHTML = '';

   if ($debug) {
      $timer->startSplit();
   }

   #$debug = 1;
   #print_r($formValues);
   if (isset($formValues['elemtype'])) {
      $elemtype = $formValues['elemtype'];
   }

   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }

   if (isset($formValues['objectname'])) {
      $objectname = $formValues['objectname'];
   }

   if (isset($formValues['activecontainerid'])) {
      $activecontainerid = $formValues['activecontainerid'];
   }

   if (isset($formValues['actiontype'])) {
      $actiontype = $formValues['actiontype'];
      $projectid = $formValues['projectid'];
      $showoutside = $formValues['showoutside'];
   } else {
      $actiontype = 'insertelement';
   }

   if (strlen($seglist) > 0) {
      $sscond = " subshedid in ( $seglist )";
   } else {
      $sscond = "(1 = 1)";
   }
/*
   if (!isset($formValues['geomx']) or ($formValues['geomx'] == '') ) {
      if (strlen($seglist) > 0) {
         $listobject->querystring = "  select x(centroid(extent(the_geom))) as geomx, y(centroid(extent(the_geom))) as geomy ";
         $listobject->querystring .= " from proj_subsheds ";
         $listobject->querystring .= " where $sscond ";
         $listobject->querystring .= " and projectid = $projectid ";
      }
      if ($debug) {
         $innerHTML .= "$listobject->querystring<br>";
      }
      $listobject->performQuery();
      $geomx = $listobject->getRecordValue(1,'geomx');
      $geomy = $listobject->getRecordValue(1,'geomy');
   } else {
      $geomx = $formValues['geomx'];
      $geomy = $formValues['geomy'];
   }
   #if ($debug) {
      $innerHTML .= "Geometry Set to: $geomx - $geomy <br>";
   #}
*/
   if ($actiontype == 'delete') {
      #$debug = 1;
      $elid = $formValues['elementid'];
      $innerHTML .= " Attempting to Delete Model Element $elid";
      $deleteresult = deleteModelElement($formValues['elementid']);
      $deleteid = $deleteresult['elementid'];
      $elementid = -1;
      if ($deleteid > 0) {
         # successful, now, we want to load the element
         $formValues['elementid'] = -1;
         $innerHTML .= "Deleting Successful.<br>";
         $innerHTML .= $deleteresult['innerHTML'];
      } else {
         $innerHTML .= $deleteresult['innerHTML'];
      }
      #$debug = 0;
      $elem_xml = '';
   }

   if ($actiontype == 'clone') {
      #$debug = 1;
      $innerHTML .= " Attempting to Clone Model Element $elementid in $activecontainerid";
      $cloneresult = cloneModelElement($scenarioid, $formValues['elementid'], $activecontainerid);
      $cloneid = $cloneresult['elementid'];
      if ($cloneid > 0) {
         # successful, now, we want to load the element
         $formValues['elementid'] = $cloneid;
         $innerHTML .= "Cloning Successful.<br>";
         $innerHTML .= $cloneresult['innerHTML'];
      } else {
         $innerHTML .= $cloneresult['innerHTML'];
      }
      #$debug = 0;
   }
   $innerHTML .= showElementBrowser($formValues);

   $innerHTML .= "<form name='addelement' id='addelement'>";
   $innerHTML .= "<table>";
   $innerHTML .= "<tr>";
   $innerHTML .= "<td valign=top>";
   $innerHTML .= "<font class='heading1'>Modeling Element Form</font><br>";
   # show the menu of elements to choose from
   # construct SQL for this:
   #$innerHTML .= implode_md(',',$who_xmlobjects);

   $elem_xml = '';
   // dump the result
   $whoprops = getWHOXML($elemtype);
   if ( count($whoprops) > 0 ) {
      // create object
      $elem_xml = $whoprops['xml'];
   }

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }

   if (isset($formValues['elementid']) and ($actiontype <> 'delete') ) {
      $elementid = $formValues['elementid'];
      # this is an edit request for an existing object, get the data from the db
      $listobject->querystring = "  select elemname, elementid, elem_xml, elemcomponents, eleminputs, elemoperators, ";
      $listobject->querystring .= "    x(the_geom) as gx, y(the_geom) as gy ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      if ($debug) {
         $innerHTML .= "$listobject->querystring<br>";
      }
      $listobject->performQuery();
      $elemname = $listobject->getRecordValue(1,'elemname');
      $elem_xml = $listobject->getRecordValue(1,'elem_xml');
      $elemcomponents = $listobject->getRecordValue(1,'elemcomponents');
      $eleminputs = $listobject->getRecordValue(1,'eleminputs');
      $elemoperators = $listobject->getRecordValue(1,'elemoperators');
      $geomx = $listobject->getRecordValue(1,'gx');
      $geomy = $listobject->getRecordValue(1,'gy');
   }
   # stash the new element type in case this is a change
   $newelemtype = $elemtype;
   $thisobject = -1;
   if ($elementid > 0) {
      # wew are looking at an already created object, check the perms
      $elemperms = getScenElementPerms($listobject, $elementid, $userid, $usergroupids, $debug);
      if ( !($elemperms & 2) ) {
         $disabled = 1;
      } else {
         $disabled = 0;
      }
      #$innerHTML .= "Perm: $elemperms <br>";
      # pass the listobject to these object for their use
      $unser = unserializeModelObject($elementid);
      $thisobject = $unser['object'];
      $thisobject->listobject = $listobject;
      $elemtype = $unser['elemtype'];
      if ($elemtype == 'HSPFContainer') {
         $thisobject->ucitables = $ucitables;
         $pgs = $thisobject->getPropertyClass(array('plotgen'));
         #$innerHTML .= "Plotgens: " . print_r($pgs,1) . "<br>";
      }
   }
   # propagate the type change without disturbing any other connections
   if ($formValues['changetype'] == 1) {
      $newprops = getWHOXML($newelemtype);
      $elem_xml = $newprops['xml'];
      $innerHTML .= "Type Change requested to $newelemtype .<br>";
   }

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }

   if (strlen($elem_xml) > 0) {
      $options = array("complexType" => "object");
      $unserializer = new XML_Unserializer($options);
      if ($debug) {
         $innerHTML .= "Unserializing<br>";
      }
      // unserialize the object. Use "false" since this is not a document, "true" if it is a document
      $result = $unserializer->unserialize($elem_xml, false);
      if ($actiontype == 'clone') {
         $formValues['name'] = $elemname . '(copy)';
      }
   }

   # load object properties and display in a form
   if ( strlen($elem_xml) > 0) {
      $modelFormArray = showModelEditForm($formValues, $elem_xml, 1, $disabled, $thisobject, 'addelement');
      $elemtype = $modelFormArray['elemtype'];
      $elemform = $modelFormArray['innerHTML'];
      $innerHTML .= $modelFormArray['debug'];
      $thisobject = $modelFormArray['object'];

   } else {
      $elemform = '';
   }

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }

   # show object type browser, to reset object type, or load the blank form for the requested type
   $whosql = "(select classname as elemname, classname as elemtype from who_xmlobjects where type <> 2 order by upper(classname), classname) as whofoo ";
   $innerHTML .= "<b>Element Type:</b>";

   # this will be set by the type list if it is changed
   $innerHTML .= showHiddenField('changetype', 0, 1);
   $innerHTML .= showActiveList($listobject, 'elemtype', $whosql, 'elemname', 'elemtype', '', $elemtype, "document.forms[\"addelement\"].elements.changetype.value=1;  xajax_showAddElementForm(xajax.getFormValues(\"addelement\"))", '', $debug, 1, $disabled);
   $innerHTML .= "<br>";

   $innerHTML .= $elemform;

   $innerHTML .= "</td>";

   $innerHTML .= "</tr>";
   $innerHTML .= "<tr>";
   $innerHTML .= "<td valign=top>&nbsp;";
   if ($elemtype == '') {
      $innerHTML .= '<b>X Coord(lon):</b>' .showWidthTextField('geomx', $geomx, 8, '', 1, $disabled);
      $innerHTML .= ' <b>Y Coord(lat):</b>' .showWidthTextField('geomy', $geomy, 8, '', 1, $disabled);
   }
   $innerHTML .= "</td>";
   $innerHTML .= "</tr>";
   # show contained objects, object inputs, and processors
   $innerHTML .= "<tr>";
   $innerHTML .= "<td valign=top><font class='heading2'>Object Information/Properties</font><br>";
   #$debug = 1;
   if ($elementid > 0) {
      $innerHTML .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 160px; \"><font class='objectInfo'>" . $thisobject->showHTMLInfo() . "</font></div>";
   }
   if ($debug) {
      #$thisobject->ucitables = $ucitables;
      #$pgs = $thisobject->getPropertyClass(array('publicvars','plotgen'));
      #$innerHTML .= "Plotgens: " . print_r($pgs,1) . "<br>";
   }

   $innerHTML .= "</td>";
   $innerHTML .= "</tr>";
   $innerHTML .= "<tr>";
   $innerHTML .= "<td valign=top><font class='heading2'>Property Linkages</font><br>";
   $innerHTML .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 160px; \">";
   #$debug = 1;

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }
   $innerHTML .= showElementInputBrowser($formValues, $disabled);

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }
#   $innerHTML .= showElementPropertyList($elementid, $index, $selectedcol);
   $innerHTML .= "</div></td>";
   $innerHTML .= "</tr>";
   # show contained objects, object inputs, and processors
   $innerHTML .= "<tr>";
   $innerHTML .= "<td valign=top><font class='heading2'>Contained Objects</font><br>";
   $innerHTML .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 160px; \">";
   #$debug = 1;

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }
   $innerHTML .= showContainedElementBrowser($formValues, $disabled);

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }
#   $innerHTML .= showElementPropertyList($elementid, $index, $selectedcol);
   $innerHTML .= "</div></td>";
   $innerHTML .= "</tr>";
   $innerHTML .= "<tr>";
   $innerHTML .= "<td valign=top>";
   $innerHTML .= showHiddenField('actiontype', 'addelement', 1);
   $innerHTML .= showHiddenField('projectid', $projectid, 1);
   $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);
   $innerHTML .= showHiddenField('elementid', $elementid, 1);
   $innerHTML .= showHiddenField('showoutside', $showoutside, 1);
   
   $innerHTML .= showGenericButton('saveelement', 'Save Element', "last_tab[\"model_element\"]=\"model_element_data0\"; last_button[\"model_element\"]=\"model_element_0\"; document.forms['elementtree'].elements.elementid.value=$elementid; xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", 1);
   #$innerHTML .= showGenericButton('delete', 'Delete This Element', "xajax.js.confirmCommands(\"Delete $elemname (this cannot be un-done)??\",2); document.forms[\"addelement\"].elements.actiontype.value=\"delete\"; xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", 1);
   $innerHTML .= "</td>";

   $innerHTML .= "</tr>";
   $innerHTML .= "</table>";
   # show model elements contained components
   #$innerHTML .= "<font class='heading2'>Internal Operators:</font>";
   # this default to -1, since we will set it via javascript if it needs to change
   $innerHTML .= showHiddenField('operatorid', -1, 1);
   # here, we do this, by setting it to 0 (zero), which indicates that we wish to add a new processor/operator
   $innerHTML .= "<a class='mH' onclick=\"document.forms['addelement'].elements.operatorid.value=0; ";
   $innerHTML .= "xajax_showOperatorEditForm(xajax.getFormValues('addelement'))\"> Click Here to Add New Sub-Component</a><br>";
   #$index = 1;

   # insert the end of the form, sicne this will contain many of the same properties
   $innerHTML .= "</form>";

   $innerHTML .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; \">";
   $innerHTML .= "<div id='elementops'>";
   #$debug = 1;

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }
   $subcomparray = operatorEditForm($formValues, $who_xmlobjects, $elementid, $elemtype, -1, 0, $thisobject);

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> Operators: split time = $split <br>";
   }
   $innerHTML .= $subcomparray['innerHTML'];
   #$debug = 0;
   $innerHTML .= "</div></div>";
   return $innerHTML;

}

function addElementFormPanel($formValues, $who_xmlobjects) {
   global $listobject, $session_db, $outdir, $ucitables, $projectid, $defscenarioid, $scenarioid, $debug, $userid, $usergroupids, $adminsetuparray, $timer;
   $innerHTML = '';

   $showtime = 0;

   #$debug = 1;
//error_log("AddElementFormPanel Called ");

   # format output into tabbed display object
   $taboutput = new tabbedListObject;
   $taboutput->name = 'model_element';
   $taboutput->height = '520px';
   #$taboutput->width = '100%';
   $taboutput->width = '800px';
   $taboutput->tab_names = array('generalprops','inputs','contained','remoteinputs','processors', 'analysis','errorlog', 'debug');
   $taboutput->tab_buttontext = array(
      'generalprops'=>'General Properties',
      'inputs'=>'Local Links',
      'remoteinputs'=>'Remote Links',
      'contained'=>'Child Objects',
      'processors'=>'Sub-components',
      'analysis'=>'Data Analysis',
      'errorlog'=>'Errors',
      'debug'=>'Debug'
   );
   $taboutput->init();
   $taboutput->tab_HTML['generalprops'] .= "<b>General Properties:</b><br>";
   $taboutput->tab_HTML['inputs'] .= "<b>Local Linkages:</b><br>";
   $taboutput->tab_HTML['remoteinputs'] .= "<b>Remote Linkages</b><br>";
   $taboutput->tab_HTML['contained'] .= "<b>Child Objects.</b><br>";
   $taboutput->tab_HTML['processors'] .= ''; # this is added later for formatting reasons
   $taboutput->tab_HTML['analysis'] .= "<b>Data Analysis:</b><br>";
   $taboutput->tab_HTML['errorlog'] .= "<b>Error Log:</b><br>";
   $taboutput->tab_HTML['debug'] .= "<b>Debugging Information:</b><br>";

   if ($debug) {
      $timer->startSplit();
   }

   #$debug = 1;
   #print_r($formValues);
   if (isset($formValues['elemtype'])) {
      $elemtype = $formValues['elemtype'];
   }
   if (isset($formValues['activescenarioid'])) {
      $activescenarioid = $formValues['activescenarioid'];
   }

   if ( isset($formValues['activetab']) ) {
      $activetab = $formValues['activetab'];
      if (!in_array($activetab, array_keys($taboutput->tab_HTML))) {
         $activetab = 'generalprops';
      }
   } else {
      $activetab = 'generalprops';
   }

   if (isset($formValues['callcreate'])) {
      $callcreate = $formValues['callcreate'];
   } else {
      $callcreate = 0;
   }

   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }

   if (isset($formValues['activecontainerid'])) {
      $activecontainerid = $formValues['activecontainerid'];
   } else {
      if (isset($formValues['activecontainerid'])) {
         $activecontainerid = $formValues['activecontainerid'];
      }
   }

   if (isset($formValues['objectname'])) {
      $objectname = $formValues['objectname'];
   }

   if (isset($formValues['actiontype'])) {
      $actiontype = $formValues['actiontype'];
      $projectid = $formValues['projectid'];
      $showoutside = $formValues['showoutside'];
   } else {
      $actiontype = 'insertelement';
   }

   if (strlen($seglist) > 0) {
      $sscond = " subshedid in ( $seglist )";
   } else {
      $sscond = "(1 = 1)";
   }

   if (!isset($formValues['geomx']) or ($formValues['geomx'] == '') ) {
      $listobject->querystring = "  select x(centroid(extent(the_geom))) as geomx, y(centroid(extent(the_geom))) as geomy ";
      $listobject->querystring .= " from proj_subsheds ";
      $listobject->querystring .= " where $sscond ";
      if ($debug) {
         $taboutput->tab_HTML['debug'] .= "$listobject->querystring<br>";
      }
      $listobject->performQuery();
      $geomx = $listobject->getRecordValue(1,'geomx');
      $geomy = $listobject->getRecordValue(1,'geomy');
   } else {
      $geomx = $formValues['geomx'];
      $geomy = $formValues['geomy'];
   }
   #if ($debug) {
      $taboutput->tab_HTML['debug'] .= "Geometry Set to: $geomx - $geomy <br>";
   #}
$elid = $formValues['elementid'];
//error_log("Action type: $actiontype ");
   switch ($actiontype) {
      case 'delete':
         #$debug = 1;
         $elid = $formValues['elementid'];
         #error_log(" Attempting to Delete Model Element $elid");
         $innerHTML .= " Attempting to Delete Model Element $elid";
         $deleteresult = deleteModelElement($formValues['elementid']);
         $deleteid = $deleteresult['elementid'];
         if ($deleteid > 0) {
            # successful, now, we want to load the element
            $formValues['elementid'] = -1;
            $innerHTML .= "Deleting Successful.<br>";
            $innerHTML .= $deleteresult['innerHTML'];
            # set this to -1 so that we do not load any new characteristics
            $elementid = -1;
         } else {
            $innerHTML .= $deleteresult['innerHTML'];
         }
         #$debug = 0;
         $elem_xml = '';
      break;

      case 'clone':
         #$debug = 1;
         //$innerHTML .= " Attempting to Clone Model Element $elementid with parent $activecontainerid <br>";
         $taboutput->tab_HTML['debug'] .= " Attempting to Clone Model Element $elementid with parent $activecontainerid <br>";
         $cloneresult = cloneModelElement($scenarioid, $formValues['elementid'],$activecontainerid);
         $cloneid = $cloneresult['elementid'];
         if ($cloneid > 0) {
            # successful, now, we want to load the element
            $formValues['elementid'] = $cloneid;
            $taboutput->tab_HTML['debug'] .= "Cloning Successful.<br>";
            $taboutput->tab_HTML['debug'] .= $cloneresult['innerHTML'];
         } else {
            $taboutput->tab_HTML['debug'] .= $cloneresult['innerHTML'];
         }
         #$debug = 0;
      break;

      case 'newcontainer':
         #$debug = 0;
      break;
   }

   if ($showtime) {
      $innerHTML .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }
   # show the hierarchcial element browsing tree
   //$innerHTML .= showElementBrowser($formValues);

   if ($showtime) {
      $innerHTML .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }

   //$innerHTML .= "<table>";
   //$innerHTML .= "<tr>";
   //$innerHTML .= "<td valign=top>";
   $innerHTML .= "<form name='addelement' id='addelement'>";
   # this default to -1, since we will set it via javascript if it needs to change
   $innerHTML .= showHiddenField('addsubtype', -1, 1);
   $innerHTML .= showHiddenField('changetype', 0, 1);
   $innerHTML .= showHiddenField('operatorid', -1, 1);
   $innerHTML .= showHiddenField('activecontainerid', $activecontainerid, 1);

   # now add these components to separate panels

   #################################################################################
   ###                       Panel 1 - General Properties                        ###
   #################################################################################
   //error_log(" Showing General Properties");
   $taboutput->tab_HTML['generalprops'] .= "<font class='heading1'>Modeling Element Form - $elid</font><br>";
   # show the menu of elements to choose from
   # construct SQL for this:

   if ($debug or $showtime) {
      $taboutput->tab_HTML['generalprops'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }

   $elem_xml = '';
   // dump the result
   $taboutput->tab_HTML['debug'] .= "Getting WHO props for: $elemtype <br>";
   $whoprops = getWHOXML($elemtype);
   if ( count($whoprops) > 0 ) {
      // create object
      $elem_xml = $whoprops['xml'];
      $parenttype = $whoprops['parent'];
      $taboutput->tab_HTML['debug'] .= "Parent type: $parenttype <br>";
   }
      $taboutput->tab_HTML['debug'] .= "Parent type: $parenttype <br>";

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['debug'] .= "<b>debug:</b> split time = $split <br>";
   }

   if (isset($formValues['elementid']) and ($actiontype <> 'delete') ) {
      $elementid = $formValues['elementid'];
      # this is an edit request for an existing object, get the data from the db
      $listobject->querystring = "  select elemname, elementid, elem_xml, elemcomponents, eleminputs, elemoperators, ";
      $listobject->querystring .= "    x(the_geom) as gx, y(the_geom) as gy ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      if ($debug) {
         $taboutput->tab_HTML['debug'] .= "$listobject->querystring<br>";
      }
      $listobject->performQuery();
      $elemname = $listobject->getRecordValue(1,'elemname');
      $elem_xml = $listobject->getRecordValue(1,'elem_xml');
      $elemcomponents = $listobject->getRecordValue(1,'elemcomponents');
      $eleminputs = $listobject->getRecordValue(1,'eleminputs');
      $elemoperators = $listobject->getRecordValue(1,'elemoperators');
      $geomx = $listobject->getRecordValue(1,'gx');
      $geomy = $listobject->getRecordValue(1,'gy');
   }
   # stash the new element type in case this is a change
   $newelemtype = $elemtype;
   $thisobject = -1;
   if ($elementid > 0) {
      # wew are looking at an already created object, check the perms
      $elemperms = getScenElementPerms($listobject, $elementid, $userid, $usergroupids, $debug);
      if ( !($elemperms & 2) ) {
         $disabled = 1;
      } else {
         $disabled = 0;
      }
      #$innerHTML .= "Perm: $elemperms <br>";
      # pass the listobject to these object for their use
      #$unser = unserializeModelObject($elementid);
      $unser = unserializeSingleModelObject($elementid);
      $thisobject = $unser['object'];
      $thisobject->listobject = $listobject;
      $elemtype = $unser['elemtype'];
      if ($debug) {
         $taboutput->tab_HTML['debug'] .= "Debug info from unserializeSingleModelObject($elementid): <br>\n" . $unser['debug'] . "<br>\n";
      }
      $taboutput->tab_HTML['errorlog'] .= $unser['error'];
      $elemtype = $unser['elemtype'];
      if ($elemtype == 'HSPFContainer') {
         $thisobject->ucitables = $ucitables;
         $pgs = $thisobject->getPropertyClass(array('plotgen'));
         #$innerHTML .= "Plotgens: " . print_r($pgs,1) . "<br>";
      }
      $taboutput->tab_HTML['debug'] .= "<br>Initial Object Sub-components:<br>" . print_r(array_keys($thisobject->processors),1) . "<br>";
   }
   # now, we have our object instantiated, and populated with its changed data, we will call the create() method
   # if requested in the form
   if ($callcreate) {
      $taboutput->tab_HTML['debug'] .= "Calling create() method on this object.<br>";
      if (method_exists($thisobject, 'reCreate')) {
         error_log("reCreate() method exists");
         $thisobject->reCreate();
         //error_log("Saving Sub-components");
         saveObjectSubComponents($listobject, $thisobject, $elementid);
         //error_log("Sub-components Saved");
      }
   }
   # include debugging information
   if (isset($thisobject->debugstring)) {
      $taboutput->tab_HTML['debug'] .= "Object Wake Debug Info: <br> $thisobject->debugstring <br>";
   }
   # propagate the type change without disturbing any other connections
   if ($formValues['changetype'] == 1) {
      $newprops = getWHOXML($newelemtype);
      $elem_xml = $newprops['xml'];
      $innerHTML .= "Type Change requested to $newelemtype .<br>";
   }

   # if we are creating a new container, set the appropriate type
   if ($actiontype == 'newcontainer') {
      $newprops = getWHOXML('modelContainer');
      $elem_xml = $newprops['xml'];
      $innerHTML .= "Creating New Model Container .<br>";
   }

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['debug'] .= "<b>debug:</b> split time = $split <br>";
   }

   if (strlen($elem_xml) > 0) {
      $options = array("complexType" => "object");
      $unserializer = new XML_Unserializer($options);
      if ($debug) {
         $taboutput->tab_HTML['debug'] .= "Unserializing<br>";
      }
      // unserialize the object. Use "false" since this is not a document, "true" if it is a document
      $result = $unserializer->unserialize($elem_xml, false);
      if ($actiontype == 'clone') {
         $formValues['name'] = $elemname . '(copy)';
      }
   }

   # load object properties and display in a form
   if ( strlen($elem_xml) > 0) {

      $modelFormArray = showModelEditForm($formValues, $elem_xml, 1, $disabled, $thisobject, 'addelement');
      $elemtype = $modelFormArray['elemtype'];
      $elemform = $modelFormArray['innerHTML'];
      $parenttype = $modelFormArray['parenttype'];
      $taboutput->tab_HTML['debug'] .= $modelFormArray['debug'];
      $thisobject = $modelFormArray['object'];
      $taboutput->tab_HTML['debug'] .= "<br>Object Sub-components after showModelElementForm():<br>" . print_r(array_keys($thisobject->processors),1) . "<br>";


   } else {
      $elemform = '';
   }

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['debug'] .= "<b>debug:</b> split time = $split <br>";
   }

   # show object type browser, to reset object type, or load the blank form for the requested type
   $whosql = "(select classname as elemname, classname as elemtype from who_xmlobjects where type <> 2 order by upper(classname), classname) as whofoo ";
   $whonames = $listobject->queryrecords;
   $taboutput->tab_HTML['generalprops'] .= "<b>Element Type:</b>";
   $taboutput->tab_HTML['generalprops'] .= showActiveList($listobject, 'elemtype', $whosql, 'elemname', 'elemtype', '', $elemtype, "document.forms[\"addelement\"].elements.changetype.value=1;  xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", '', $debug, 1, $disabled);
   $taboutput->tab_HTML['generalprops'] .= "<br>";
   
   if (is_array($parenttype)) {
      array_push($parenttype, $elemtype);
   } else {
      $parenttype = array($elemtype,$parenttype);
   }
   //$innerHTML .= showHiddenField('parenttype', $parenttype, 1);
   $taboutput->tab_HTML['generalprops'] .= showHiddenField('parenttype', join(",",$parenttype), 1);
   $taboutput->tab_HTML['generalprops'] .= $elemform;
   $taboutput->tab_HTML['generalprops'] .= "<br>;";
   if ($elemtype == '') {
      $innerHTML .= '<b>X Coord(lon):</b>' .showWidthTextField('geomx', $geomx, 8, '', 1, $disabled);
      $innerHTML .= ' <b>Y Coord(lat):</b>' .showWidthTextField('geomy', $geomy, 8, '', 1, $disabled);
   }
   
   $taboutput->tab_HTML['generalprops'] .= "<br>";
   $taboutput->tab_HTML['generalprops'] .= "<font class='heading2'>Object Information/Properties</font><br>";
   #$debug = 1;
   if ($elementid > 0) {
      $taboutput->tab_HTML['generalprops'] .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 160px; \"><font class='objectInfo'>" ;
      if (method_exists($thisobject, 'showHTMLInfo')) {
         $taboutput->tab_HTML['generalprops'] .= $thisobject->showHTMLInfo();
      } else {
         $taboutput->tab_HTML['generalprops'] .= "Method showHTMLInfo() does not exist.";
      }
      $taboutput->tab_HTML['generalprops'] .= "</font></div>";
   }
   if ($debug) {
      #$thisobject->ucitables = $ucitables;
      #$pgs = $thisobject->getPropertyClass(array('publicvars','plotgen'));
      #$innerHTML .= "Plotgens: " . print_r($pgs,1) . "<br>";
   }

   $taboutput->tab_HTML['generalprops'] .= "Geometry Extent: $thisobject->extent <br>";
 //  $taboutput->tab_HTML['generalprops'] .= "<script type='text/javascript'>";
#   $taboutput->tab_HTML['generalprops'] .= "<!-- \n";
#   $jsfunc .= file_get_contents("./xajax_object_js.php");
#   $taboutput->tab_HTML['generalprops'] .= "// -->\n";
//   $taboutput->tab_HTML['generalprops'] .= "</script>";
   # add test button for getting shape info from OpenLayers,
   # only calls a static non-ajax function to alert and show the geometry for now.
   # for now, we just grab the zero'th geometry.  Later we can support selecting geometries, and importing multiple geometries
   $taboutput->tab_HTML['generalprops'] .=  showGenericButton('copygeom', 'Get Geometry', "document.forms[\"addelement\"].elements.the_geom.value=getScratchGmapGeom(); alert(\"Geometry Copied, you must save for this to take effect.\")", 1);
   $taboutput->tab_HTML['generalprops'] .=  showGenericButton('selectgeom', 'Show Geometry', "var wktshapes = new Array(); wktshapes[0] = [document.forms[\"addelement\"].elements.the_geom.value, document.forms[\"addelement\"].elements.name.value];  putWKTGoogleShape(wktshapes); selectModelShapes($elementid); alert(\"Geometry Loaded.\")", 1);
   $taboutput->tab_HTML['generalprops'] .=  showGenericButton('docreate', 'Run Create Functions', "document.forms[\"addelement\"].elements.callcreate.value = 1; xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", 1);
   $taboutput->tab_HTML['generalprops'] .=  "<hr>";

   if ($debug or $showtime) {
      $taboutput->tab_HTML['generalprops'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }

   #################################################################################
   ###                     END Panel 1 - General Properties                      ###
   #################################################################################

   #################################################################################
   ###                        Panel 2 - Linked Properties                        ###
   #################################################################################
   //error_log(" Showing Linked Properties");
   $taboutput->tab_HTML['inputs'] .= "<font class='heading2'>Local Property Linkages</font><br>";
   
   if ($showtime) {
      $taboutput->tab_HTML['inputs'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['debug'] .= "<b>debug:</b> split time = $split <br>";
   }
   $taboutput->tab_HTML['inputs'] .= showElementInputBrowser($formValues, $disabled);

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['debug'] .= "<b>debug:</b> split time = $split <br>";
   }

   if ($showtime) {
      $taboutput->tab_HTML['inputs'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }

   #################################################################################
   ###                      END Panel 2 - Linked Properties                      ###
   #################################################################################

   #################################################################################
   ###                        Panel 3 - Contained Objects                        ###
   #################################################################################
   //error_log(" Showing Contained Objects");
   # show contained objects, object inputs, and processors
   $taboutput->tab_HTML['contained'] .= "<font class='heading2'>Contained Objects</font><br>";

   if ($showtime) {
      $taboutput->tab_HTML['contained'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['debug'] .= "<b>debug:</b> split time = $split <br>";
   }
   $taboutput->tab_HTML['contained'] .= showContainedElementBrowser($formValues, $disabled);

   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }
   $taboutput->tab_HTML['contained'] .= "<br>";
   # insert the end of the form here, since the rest of the tabs are either text only, or have their own forms, which would
   # create a conflict
   if ($actiontype <> 'newcontainer') {
      $taboutput->tab_HTML['contained'] .= showHiddenField('actiontype', 'addelement', 1);
   } else {
      $taboutput->tab_HTML['contained'] .= showHiddenField('actiontype', 'newcontainer', 1);
   }
   $taboutput->tab_HTML['contained'] .= showHiddenField('activetab', 'activetab', $activetab);
   $taboutput->tab_HTML['contained'] .= showHiddenField('projectid', $projectid, 1);
   $taboutput->tab_HTML['contained'] .= showHiddenField('scenarioid', $scenarioid, 1);
   $taboutput->tab_HTML['contained'] .= showHiddenField('elementid', $elementid, 1);
   $taboutput->tab_HTML['contained'] .= showHiddenField('showoutside', $showoutside, 1);
   # the variable to see if we wish to call the create() method on this object
   $taboutput->tab_HTML['contained'] .= showHiddenField('callcreate', 0, 1);

   if ($showtime) {
      $taboutput->tab_HTML['contained'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }
   $taboutput->tab_postfix['contained'] .= "</form>";
   #################################################################################
   ###                       END Panel 3 - Contained Objects                     ###
   #################################################################################

   #################################################################################
   ###                        Panel 3.2 - Remote Linked Properties               ###
   #################################################################################

   // this is placed outside of the form body, so that it will not conflict with the other form elements
   // this adds a great deal of flexibility since you don't need to use arrays in the form variables to store multiple
   // linkages, but instead, can dedicate a unique form for each one.  this makes things a bit more ajaxy as well as 
   // easier to achieve in javascript
   //error_log(" Showing Linked Properties");
   #$taboutput->tab_HTML['inputs'] .= "<table><tr><td valign=top>";
   $taboutput->tab_HTML['remoteinputs'] .= "<font class='heading2'>Remote Property Linkages</font><br>";
   #$taboutput->tab_HTML['remoteinputs'] .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 160px; \">";
   #$debug = 1;

   if ($showtime) {
      $taboutput->tab_HTML['remoteinputs'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['debug'] .= "<b>debug:</b> split time = $split <br>";
   }
   $taboutput->tab_HTML['remoteinputs'] .= showRemoteInputBrowser($formValues, $disabled);

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['debug'] .= "<b>debug:</b> split time = $split <br>";
   }

   if ($showtime) {
      $taboutput->tab_HTML['remoteinputs'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }
   

   #################################################################################
   ###                END Panel 3.2 - Remote Linked Properties                   ###
   #################################################################################


   # model operators/sub-components
   #################################################################################
   ###                      Panel 4 - Sub-components (processors)                ###
   #################################################################################
   //error_log(" Showing Sub-components");
   # show model elements contained components
   $taboutput->tab_HTML['processors'] .= "<table><tr><td valign=top width=50%><b>Sub-components:</b>";
   #$taboutput->tab_HTML['processors'] .= "<font class='heading2'>Sub-Components:</font>";

   if ($showtime) {
      $taboutput->tab_HTML['processors'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }
   # must indicate that a new processor is requested
   # here, we do this, by setting it to 0 (zero), which indicates that we wish to add a new processor/operator
   # the operatorid variable is hidden, and displayed above at the top of the form so that it remains inside of the form bounds
   # new way, show a select list of addable sub-components
   //$taboutput->tab_HTML['processors'] .= "Passing types to sub-components: " .print_r($parenttype,1) . "<br>";
   $whosubtypes = getWHOSubCompList($parenttype);
   $taboutput->tab_HTML['processors'] .= "</td><td align=right><form id='addsubcomponents'>New: ";
   $taboutput->tab_HTML['processors'] .= showList($listobject, 'addsubtype',$whosubtypes,'elemname','elemtype','','',$debug, 1, $disabled);
   $taboutput->tab_HTML['processors'] .= "<a class='mH' onclick=\"document.forms['addelement'].elements.operatorid.value=0; document.forms['addelement'].elements.addsubtype.value = document.forms['addsubcomponents'].elements.addsubtype.value; ";
   $taboutput->tab_HTML['processors'] .= "xajax_showOperatorEditForm(xajax.getFormValues('addelement'))\"> Add </a>";
   $taboutput->tab_HTML['processors'] .= "</form></td></tr>";
   #$index = 1;
   
   $taboutput->tab_HTML['processors'] .= "<tr><td colspan=2><div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; \"> ";
   $taboutput->tab_HTML['processors'] .= "<div id='elementops'>";
   #$debug = 1;

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['processors'] .= "<b>debug:</b> split time = $split <br>";
   }
   //$subcomparray = operatorEditForm($formValues, $who_xmlobjects, $elementid, $elemtype, -1, 0, $thisobject);
   $subcomparray = operatorEditForm($formValues, $who_xmlobjects, $elementid, $parenttype, -1, 0, $thisobject);

   if ($debug) {
      $split = $timer->startSplit();
      $taboutput->tab_HTML['processors'] .= "<b>debug:</b> Operators: split time = $split <br>";
   }
   $taboutput->tab_HTML['processors'] .= $subcomparray['innerHTML'];
   #$debug = 0;
   //$taboutput->tab_HTML['processors'] .= "</div></div>";
   $taboutput->tab_HTML['processors'] .= "</div>";

   if ($showtime) {
      $taboutput->tab_HTML['processors'] .= "<b>debug:</b> split time = " . $timer->startSplit() . "<br>";
   }
   $taboutput->tab_HTML['processors'] .= "</td></tr></table>";
   #################################################################################
   ###                  END Panel 4 - Sub-components (processors)                ###
   #################################################################################


   #################################################################################
   ###                START Panel 5 - Data Analysis and Editing View             ###
   #################################################################################
   //error_log(" Showing Sub-components");
   # show model elements contained components
   $taboutput->tab_HTML['analysis'] .= "<table><tr><td valign=top width=50%><b>Analysis:</b>";
   $awin = showAnalysisWindow($formValues, $thisobject);
   $taboutput->tab_HTML['analysis'] .= "<div id='agrid_$elementid' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 480px; width: 720px; display: block;  background: #eee9e9;\">";
   $taboutput->tab_HTML['analysis'] .= $awin['innerHTML'];
   $taboutput->tab_HTML['analysis'] .= "</div>";
   
   $taboutput->tab_HTML['analysis'] .= "</td></tr></table>";
   
   
   #################################################################################
   ###                  END Panel 5 - Data Analysis and Editing View             ###
   #################################################################################

   # now, render the tabbed browser
//   $taboutput->activetab = $activetab;
//   $taboutput->render();
   $taboutput->createTabListView($activetab);
   # add the tabbed view the this object
   $innerHTML .= $taboutput->innerHTML;
   
   $elemparent = getElementContainer($listobject, $elementid);
   if ($elemparent == -1) {
      $elemparent = $elementid;
   } 
   # footer information - save button
   # these buttons lay outrside of the form boundaries for interface usability
   $innerHTML .= showGenericButton('saveelement', 'Save Element', "last_tab[\"model_element\"]=\"model_element_data0\"; last_button[\"model_element\"]=\"model_element_0\"; document.forms[\"elementtree\"].elements.elementid.value=$elementid; document.forms[\"elementtree\"].elements.activecontainerid.value=$elemparent; document.forms[\"addelement\"].elements.activecontainerid.value=$elemparent; document.forms[\"addelement\"].elements.activecontainerid.value=$elemparent; xajax_showAddElementResult(xajax.getFormValues(\"addelement\"))", 1);
   
   //$innerHTML .= "</td>";

   //$innerHTML .= "</tr>";
   //$innerHTML .= "</table>";

   return $innerHTML;

}

function getElementContainer($listobject, $elementid) {
   
   $listobject->querystring = "  select dest_id as parentid ";
   $listobject->querystring .= " from map_model_linkages ";
   $listobject->querystring .= " where linktype = 1 ";
   $listobject->querystring .= "    and src_id = $elementid ";
   //error_log($listobject->querystring);
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $parentid = $listobject->getRecordValue(1,'parentid');
   } else {
      $parentid = -1;
   }
   return $parentid;
   
}

function getDefaultScenario($listobject, $userid) {
   
   $listobject->querystring = "  select defscenario ";
   $listobject->querystring .= " from users ";
   $listobject->querystring .= " where userid = $userid ";
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $defscenario = $listobject->getRecordValue(1,'defscenario');
   } else {
      $defscenario = -1;
   }
   return $defscenario;
   
}

function getWHOSubCompList($parenttype) {
   global $listobject;
   if (!is_array($parenttype)) {
      $ptypes = array($parenttype);
   } else {
      $ptypes = $parenttype;
   }
   $listobject->querystring = "  select classname as elemtype, classname as elemname from who_xmlobjects ";
   $listobject->querystring .= " where type = 2 AND ( ( parent = '' ) ";
   if (count($ptypes) > 0) {
      foreach ($ptypes as $ptype) {
         if (trim($ptype) <> '') {
            $listobject->querystring .= "OR (parent ilike '%$ptype%') ";
         }
      }
   }
   $listobject->querystring .= " ) ";
   $listobject->querystring .= " order by upper(classname) ";
   //error_log($listobject->querystring);
   $listobject->performQuery();
   return $listobject->queryrecords;
}

function operatorEditForm($formValues, $who_xmlobjects, $elementid, $parenttype, $compid = -1, $applyvalues = 1, $parentobject=-1, $thisobject=-1) {
   global $listobject, $debug, $userid, $usergroupids, $adminsetuparray, $timer;

   $innerHTML = '';
//$debug = 1;
   if ($debug) {
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }
   $subCompArray = array();

   # need to get a select list for all of the sub-component types that either
   # 1) have no specific parent objects set (can apply to ANY component)
   # 2) or, correspond to this object type
   $whosubtypes = getWHOSubCompList($parenttype);
   //$innerHTML .= "Parent: " . print_r($parenttype,1) . ", sub comps: " . print_r($whosubtypes,1) . "<br>";
   
   # get the parent object, if it is not already created
   # the parent object will already be created if this is a call from the loading of the parent object form,
   # however, if this call is the result of a component edit call or result, the parent will not be in memory,
   # and therefore will need to be instantiated
   if (!is_object($parentobject)) {
      if ($parentobject == -1) {
         #$debug = 0;
         //$unser = unSerializeModelObject($elementid);
         $unser = unSerializeSingleModelObject($elementid);

         #$debug = 0;
         $parentobject = $unser['object'];
         if ($debug) {
            $innerHTML .= "Instantiating Parent Object <br>";
         }
      }
   }
   // end - handle passing of parent properties

   //$debug = 1;
   // create object
   $options = array("complexType" => "object");
   $unserializer = new XML_Unserializer($options);
   if ($debug) {
      $innerHTML .= "<br>retrieving saved operator<br>";
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }
   $listobject->querystring = "select array_dims(elemoperators) as adims from scen_model_element where elementid = $elementid";
   if ($debug) {
      $innerHTML .= "$listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   # get array dimensions
   $dimstr = str_replace(']','',str_replace('[','',$listobject->getRecordValue(1,'adims')));
   if ($debug) {
      $innerHTML .= "<br>Dim: $dimstr<br>";
      $innerHTML .= "<br>Parent Type: " . print_r($parenttype,1) . "<br>";
      $split = $timer->startSplit();
      $innerHTML .= "<b>debug:</b> split time = $split <br>";
   }


   if ($elementid > 0) {
      # wew are looking at an already created object, check the perms
      $elemperms = getScenElementPerms($listobject, $elementid, $userid, $usergroupids, $debug);
      if ( !($elemperms & 2) ) {
         $disabled = 1;
      } else {
         $disabled = 0;
      }
      #$innerHTML .= "Perm: $elemperms <br>";
   }

   switch ($compid) {
      case -1:
      # no specific component, so we show them all
      list($astart, $aend) = split(':', $dimstr);
      break;

      case 0:
      # insert a new value, so just add one to the existing max value
      list($astart, $aend) = split(':', $dimstr);
      $aend++;
      $astart = $aend;
      if ($debug) {
         $innerHTML .= "Appending index ($aend) onto the end of existing model operators.<br>";
      }
      break;

      default:
      # a value greater than zero has been specified, indicating a specific component to show, go get it and display it
      $astart = $compid;
      $aend = $compid;
      break;

   }

   for ($i = $astart; $i <= $aend; $i++) {
      if (($compid == 0) or ($i == '')) {
         # creating new object, add the default template unless we are passed a value
         if (isset($formValues['addsubtype'])) {
            $newtype = $formValues['addsubtype'];
         } else {
            $newtype = 'Equation';
         }
         if ($debug) {
            $innerHTML .= "Requested: " . $formValues['addsubtype'] . " Added: " . $newtype . "<br>";
         }
         $oprec = getWHOXML($newtype);
         $opxml = $oprec['xml'];
         $toggleStatus = 'block';
      } else {
         $listobject->querystring = "  select elemoperators[$i] from scen_model_element ";
         $listobject->querystring .= " where elementid = $elementid ";
         if ($debug) {
            $innerHTML .= "retrieving op $i<br>";
            $innerHTML .= "$listobject->querystring ; <br>";
            $split = $timer->startSplit();
            $innerHTML .= "<b>debug:</b> split time = $split <br>";
         }
         $listobject->performQuery();
         $toggleStatus = 'none';
         $opxml = $listobject->getRecordValue(1,'elemoperators');
      }

      if ($formValues['changetype'] == 1) {
         # we are changing type, so pull the fresh copy
         $oprec = getWHOXML($formValues['elemtype']);
         $opxml = $oprec['xml'];
         $innerHTML .= 'Type Change Requested.<br>';
         $toggleStatus = 'block';
         #$debug = 1;
      }

      # unserialize the object to get the elemtype, so we can retrieve parent props if specified
      # need to unserialize this component object
      $thisload = loadElement($opxml);
      $thisobject = $thisload['object'];
      #if (property_exists($thisobject, 'inflows')) {
      #   $innerHTML .= "Inputs: " .  print_r($thisobject->inflows,1) . "<br>";
      #   $innerHTML .= "Outputs: " . print_r($thisobject->outflows,1) . "<br>";
      #}
      if ($debug) {
         $innerHTML .= "load element result: " . $thisload['debug'];
         $split = $timer->startSplit();
         $innerHTML .= "<b>debug:</b> split time = $split <br>";
      }
      $elemtype = get_class($thisobject);
      # get the shell properties for this WHO object type
      $whotemplate = getWHOXML($elemtype);
      $pproptypes = $whotemplate['parentprops'];
      //$innerHTML .= print_r($pproptypes,1);
      foreach ($pproptypes as $thispname => $thisptype) {
         if ($debug) {
            $innerHTML .= "Getting property, $thispname, type $thisptype from parent.<br>";
         }
         if (is_object($parentobject)) {
            $parent_props = $parentobject->getPropertyClass(array($thisptype));
         } else {
            $parent_props = array();
         }
         if ($debug) {
            $innerHTML .= "Parent object returned. " . print_r($parent_props,1) . "<br>";
         }
         $asrec = split(':',$adminsetuparray[$elemtype]['column info'][$thispname]['params']);
         $asep = '';
         $aslist = '';
         foreach ($parent_props as $thisprop) {
            if ($thisprop <> '') {
               $aslist .= $asep . $thisprop . '|' . $thisprop;
               $asep = ',';
            }
         }
         $asrec[0] = $aslist;
         $asparams = join(':', $asrec);;
         if ($debug) {
            $innerHTML .= "Modified Params for $thispname: $asparams<br>";
         }
         if ($debug) {
            $innerHTML .= "Original Admin Info for $elemtype:<br>" . print_r($adminsetuparray[$elemtype],1) . "<br>";
         }
         # now, look for formats to copy, include any linked child formats (for meta descriptors)
         if (isset($adminsetuparray[$elemtype]['column info'][$thispname])) {
            $adminsetuparray[$elemtype]['column info'][$thispname]['params'] = $asparams;
         }
         if (isset($adminsetuparray[$elemtype]['table info']['child_formats'])) {
            foreach ($adminsetuparray[$elemtype]['table info']['child_formats'] as $thisformat) {
               if (isset($adminsetuparray[$thisformat]['column info'][$thispname])) {
                  $adminsetuparray[$thisformat]['column info'][$thispname]['params'] = $asparams;
               }
            }
         }
         # DONE - updating formats
         if ($debug) {
            $innerHTML .= "$thispname -&lt; $thisptype = " . print_r($parent_props, 1) . "asrec = ($aslist) " . $asparams . "<br>";
         }
      }

      # stash these modifications to the list object if they exist
      if (property_exists($thisobject, 'listobject')) {
         if (is_object($thisobject->listobject)) {
            $thisobject->listobject->adminsetuparray = $adminsetuparray;
            if ($debug) {
               $innerHTML .= "Admin Info Updated.<br>";
               $innerHTML .= "Source Admin Info:<br>" . print_r($adminsetuparray[$elemtype],1) . "<br>";
               $innerHTML .= "Copied Admin Info:<br>" . print_r($thisobject->listobject->adminsetuparray[$elemtype],1) . "<br>";
            }
         }
      }

      if ( (strlen(ltrim(rtrim($opxml))) > 0) and !($opxml == "''") ) {
         if ( $compid <= 0 ) {
            # printing all out for the first time, create division headers
            $innerHTML .= "<div id='operatorcontrol$i'>";
         }
         # get form values for this element type

         $olddebug = $debug;
         $debug = 0;
         //$innerHTML .= "Form Vals: " . print_r($formValues,1) . "<br>";
         $mfr = showModelEditForm($formValues, $opxml, $applyvalues, $disabled, $thisobject,"operator$i");
         $debug = $olddebug;

         $elemtype = $mfr['elemtype'];
         $name = $mfr['name'];
         $description = $mfr['description'];
         $toggleText = " style=\"display: $toggleStatus\"";
         $innerHTML .= "<a class='mH' id='op$i' ";
         $innerHTML .= "onclick=\"toggleMenu('opform$i')\" title='Click to Expand/Hide'>$name ($elemtype) </a>";
         $innerHTML .= "<div id='opform$i' class='mProp' $toggleText>";
         $innerHTML .= "<form id='operator$i' name='operator$i' action=''>";
         $innerHTML .= showHiddenField('actiontype', 'addelement', 1);
         $innerHTML .= showHiddenField('projectid', $projectid, 1);
         $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);
         $innerHTML .= showHiddenField('elementid', $elementid, 1);
         //$innerHTML .= "parenttype set to: " . join(",",$parenttype) . "<br>";
         $innerHTML .= showHiddenField('parenttype', join(",",$parenttype), 1);
         $innerHTML .= showHiddenField('operatorid', $i, 1);
         # this will be set by the type list if it is changed
         $innerHTML .= showHiddenField('changetype', 0, 1);
         $innerHTML .= "<b>Type:</b>";
         #$innerHTML .= print_r($whosubtypes, 1) . "<br>";
         $innerHTML .= showActiveList($whosubtypes, 'elemtype', '', 'elemname', 'elemtype', '', $elemtype, "document.forms[\"operator$i\"].elements.changetype.value=1; xajax_showOperatorEditForm(xajax.getFormValues(\"operator$i\"))", 'elemname', $debug, 1, $disabled);
         $innerHTML .= "<br>";
         $innerHTML .= $mfr['innerHTML'];
         if ($debug) {
            $innerHTML .= " $listobject->querystring ; <br>";
            $innerHTML .= "Op $i XML: $opxml<br>";
         }
         $innerHTML .= "</form>";
         $innerHTML .= showGenericButton('savenew', 'Save Operator', "xajax_showOperatorEditResult(xajax.getFormValues(\"operator$i\"))", 1, $disabled);
         $innerHTML .= showGenericButton('deleteop', 'Delete Operator', "xajax.js.confirmCommands(\"Delete $name (this cannot be un-done)??\",2); ; document.forms[\"operator$i\"].elements.actiontype.value=\"delete\"; xajax_showOperatorEditResult(xajax.getFormValues(\"operator$i\"))", 1, $disabled);
         $innerHTML .= "</div>";
         if ($compid == -1) {
            # printing all out for the first time, create division end
            $innerHTML .= "</div>";
         }
      }

      if ($debug) {
         $split = $timer->startSplit();
         $innerHTML .= "<b>debug:</b> split time = $split <br>";
      }

   }
   $subCompArray ['innerHTML'] = $innerHTML;
   $subCompArray ['lastindex'] = $aend;

   return $subCompArray;
}

function compactSerializeObject($thisobject, $debug = 0) {
    
   $innerHTML = '';
   $debugHTML = '';
   $retarr = array();
   
   //$debug = 1;
   
   if (is_object($thisobject)) {
      # compact the object before saving it to XML
      # zero out things that shold not be saved
      $debugHTML .= "Stripping large generic data from object for compact storage.<br>";
      $thisobject->initialized = 0; # set this to zero so that the model will wake and be refreshed
      $name = 'Un-Named Object';
      if (property_exists($thisobject, 'name')) {
         $name = $thisobject->name;
      }
      
      if ($debug) {
         error_log("Checking for presence of large data types.");
      }
      if (property_exists($thisobject, 'listobject')) {
         $thisobject->listobject = NULL;
      }
      if (property_exists($thisobject, 'ucitables')) {
         $debugHTML .= "Setting a ucitables object on $name.<br>";
         if ($debug) {
            error_log("Setting a ucitables object on $name.<br>");
         }
         $thisobject->ucitables = NULL;
      }
      if (property_exists($thisobject, 'the_geom')) {
         $debugHTML .= "Nullifying the_geom on $name.<br>";
         if ($debug) {
            error_log("Nullifying the_geom on $name.");
         }
         $thisobject->the_geom = '';
      }
      if (property_exists($thisobject, 'fno')) {
         $debugHTML .= "Clearing listobject object on $name.<br>";
         if ($debug) {         
            error_log("Clearing listobject object on $name.");
         }
         $thisobject->fno = '';
      }
      if (property_exists($thisobject, 'dbcolumntypes')) {
         $debugHTML .= "Clearing dbcolumntypes object on $name.<br>";
         if ($debug) {         
            error_log("Clearing dbcolumntypes object on $name.");
         }
         $thisobject->dbcolumntypes = '';
      }
      $debugHTML .= "Putting object to sleep.<br>";
      if ($debug) {
         error_log("Putting object to sleep.");
      }
      # do any other cleanup you can
      if (method_exists($thisobject, 'sleep')) {
         $thisobject->sleep();
      }

      # now, put the object back into XML form to be stored in the database
      $debugHTML .= "Converting $name to XML.<br>";
      if ($debug) {
         error_log("Converting $name to XML.");
      }
      /*
      $options = array(
                          XML_SERIALIZER_OPTION_INDENT      => '    ',
                          XML_SERIALIZER_OPTION_LINEBREAKS  => "\n",
                          XML_SERIALIZER_OPTION_DEFAULT_TAG => 'unnamedItem',
                          XML_SERIALIZER_OPTION_TYPEHINTS   => true
                );
      */
      $options = array("complexType" => "object", XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML);
      $serializer = new XML_Serializer($options);
      // perform serialization

      $debugHTML .= "Serializing<br>";
      $oc = get_class($thisobject);
      if ($debug) {
         error_log("Serializing object of $oc class <br>");
      }
      $result = $serializer->serialize($thisobject);
      if ($debug) {
         error_log("Object serialized");
      }
      $debugHTML .= "Printing Result<br>";
      $sn = $formValues['savenew'];
      $ow = $formValues['overwrite'];
      $debugHTML .= "$sn<br>";
      $debugHTML .= "$ow<br>";
      // check result code and display XML if success
      $debugHTML .= "Retrieving XML data for storage.<br>";
      if ($debug) {
         error_log("Retrieving XML data for storage.");
      }
      if ($result === true)
      {
         $debugHTML .= "Storing XML in database<br>";
         $object_xml = $serializer->getSerializedData();
      }
      $inputs_xml = '';
      $debugHTML .= "Retrieving XML for object inputs.<br>";
      if ($debug) {
         error_log("Retrieving XML for object inputs.");
      }
      $inputs = array();
      foreach ($thisobject->inputs as $thisin) {
         array_push($inputs, $thisin['objectname']);
         $instr .= ',' . $thisin['objectname'];
      }
      unset($serializer);
      $serializer = new XML_Serializer($options);
      $result = $serializer->serialize($inputs);
      if($result === true) {
         $inputs_xml = $serializer->getSerializedData();
      }
      $props_xml = '';
      # need to get the names of the operators on $name to add to public variables,
      $publicprops = $thisobject->getPublicVars();
      # since we have not-reconstituted the operators yet
      $debugHTML .= "Generating XML for model property list.<br>";
      if ($debug) {
         error_log("Generating XML for model property list: " . print_r($publicprops,1));
      }
      
      unset($serializer);
      $serializer = new XML_Serializer();
      $result = $serializer->serialize($publicprops);
      if ($debug) {
         error_log("Properties serialized.");
      }
      if ($result === true) {
         $props_xml = $serializer->getSerializedData();
      }
      if ($debug) {
         error_log(" XML for model property list complete.");
      }
      

      $retarr['innerHTML'] = $innerHTML;
      $retarr['debugHTML'] = $debugHTML;
      $retarr['object_xml'] = $object_xml;
      $retarr['props_xml'] = $props_xml;
      $retarr['inputs_xml'] = $inputs_xml;
      $retarr['error'] = 0;
   } else {
      $retarr['error'] = 1;
      $retarr['errorHTML'] .= "Passed item is not a valid object.<br>";
   }
   if ($debug) {
      error_log("Returning serialized object.");
   }
   
   return $retarr;
}

function loadElement($elem_xml) {
   global $debug, $listobject, $adminsetuparray, $fno, $timer;
   $options = array("complexType" => "object");
   #$options = array("complexType" => "array");
   $unserializer = new XML_Unserializer($options);
   $thisdebug = '';
   if ($debug) {
      $thisdebug .= "Unserializing<br>";
   }
   // unserialize the object. Use "false" since this is not a document, "true" if it is a document
   # base this on the submitted XML, otherwise, retrieve the existing object
   $result = $unserializer->unserialize($elem_xml, false);
   $thisobject = $unserializer->getUnserializedData();
   #$elemtype = $unserializer->getRootName();
   if ($result) {
      if (property_exists($thisobject, 'listobject')) {
         if ($debug) {
            $thisdebug .= "Setting a listobject object on this object.<br>";
         }
         $thisobject->listobject = $listobject;
      }
      if (property_exists($thisobject, 'ucitables')) {
         if ($debug) {
            $thisdebug .= "Setting a ucitables object on this object.<br>";
         }
         $thisobject->ucitables = $ucitables;
      }
      if (property_exists($thisobject, 'fno')) {
         if ($debug) {
            $thisdebug .= "Setting a fno object on this object.<br>";
         }
         $thisobject->fno = $fno;
      }
      if (method_exists($thisobject, 'wake')) {
         $thisobject->wake();
      }

      /*
      # check to see if any array props have been mangled
      $props = (array)$thisobject;
      foreach (array_keys($props) as $thisprop) {
         #error_log($thisprop);
         if (property_exists($thisobject, $thisprop)) {

            $propval = $thisobject->$thisprop;
            $proparr = (array)$propval;
            if (isset($proparr['XML_Serializer_Tag'])) {
               $thisobject->thisprop = $proparr['XML_Serializer_Tag'];
               $thisdebug .= "Setting $thisprop to array<br>";
               error_log("Setting $thisprop to array<br>");
            }
         }
      }
      */
   }

   $retarr = array();
   $retarr['object'] = $thisobject;
   $retarr['debug'] = $thisdebug;
   return $retarr;
}

function createObjectLink($projectid, $scenarioid, $src_id, $dest_id, $linktype, $src_prop='', $dest_prop='', $testonly = 0) {
   global $listobject;
   $innerHTML = '';
   $debugHTML = '';
   
   $retarr = array();
   
   // do not allow an object to link to itself
   if ($src_id == $dest_id) {
      $innerHTML .= "<b>Error:</b> Can not link an object to itself.<br>";
      $debugHTML .= "<b>Error:</b> Self-referencing linkage: createObjectLink($projectid, $scenarioid, $src_id, $dest_id, $linktype, $src_prop, $dest_prop).<br>";
   } else {
      // first check if this is a parent containment link, do not let there be duplicates
      if ($linktype == 1) {
         $listobject->querystring = " delete from map_model_linkages ";
         $listobject->querystring .= " where scenarioid = $scenarioid ";
         $listobject->querystring .= "    and src_id = $src_id ";
         $listobject->querystring .= "    and linktype = 1 ";
         $debugHTML .= "$listobject->querystring ; <br>";
         if (!$testonly) {
            $listobject->performQuery();
         }
      }
         
      // next check not to duplicate a link
      $listobject->querystring = "  select count(*) as numrecs from map_model_linkages ";
      $listobject->querystring .= " where projectid = $projectid ";
      $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= "    and src_id = $src_id ";
      $listobject->querystring .= "    and dest_id = $dest_id ";
      if ($linktype <> 1) {
         $listobject->querystring .= "    and src_prop = '$src_prop' ";
         $listobject->querystring .= "    and dest_prop = '$dest_prop' ";
      }
      $listobject->querystring .= "    and linktype = $linktype ";
      $debugHTML .= "$listobject->querystring ; <br>";
      $listobject->performQuery();
      $numrecs = $listobject->getRecordValue(1,'numrecs');
      if (!($numrecs >= 1)) {

         $listobject->querystring = "  insert into map_model_linkages (projectid, scenarioid, src_id, ";
         $listobject->querystring .= "    dest_id, src_prop, dest_prop, linktype) ";
         $listobject->querystring .= " values ($projectid, $scenarioid, $src_id, ";
         $listobject->querystring .= " $dest_id, '$src_prop', '$dest_prop', $linktype) ";
         $debugHTML .= "$listobject->querystring ; <br>";
         if (!$testonly) {
            $listobject->performQuery();
         }
         // verify creation
         $listobject->querystring = "  select count(*) as numrecs from map_model_linkages ";
         $listobject->querystring .= " where projectid = $projectid ";
         $listobject->querystring .= "    and scenarioid = $scenarioid ";
         $listobject->querystring .= "    and src_id = $src_id ";
         $listobject->querystring .= "    and dest_id = $dest_id ";
         if ($linktype <> 1) {
            $listobject->querystring .= "    and src_prop = '$src_prop' ";
            $listobject->querystring .= "    and dest_prop = '$dest_prop' ";
         }
         $listobject->querystring .= "    and linktype = $linktype ";
         $debugHTML .= "$listobject->querystring ; <br>";
         $listobject->performQuery();
         $numrecs = $listobject->getRecordValue(1,'numrecs');
         if ($numrecs == 0) {
            // creation failed for some reason, notify
            $innerHTML .= "Error: Link creation failed for $src_id ($src_prop) to $dest_id ($dest_prop) <br>\n";
         } else {
            $innerHTML .= "Link creation succeeded for $src_id ($src_prop) to $dest_id ($dest_prop) <br>\n";
         }
      } else {
         $innerHTML .= "Link from $src_id ($src_prop) to $dest_id ($dest_prop) already exists <br>\n";
      }
      if ($debug) {
         error_log($debugHTML);
      }

      # update model properties list
   }
   
   $retarr['innerHTML'] = $innerHTML;
   $retarr['debugHTML'] = $debugHTML;
   return $retarr;
}

function addObjectLink($projectid, $scenarioid, $src_id, $dest_id, $link_type, $src_prop='', $dest_prop='') {
   global $listobject;
   $innerHTML = '';
   $debugHTML = '';
   
   $retarr = array();
   
   $lout = createObjectLink($projectid, $scenarioid, $src_id, $dest_id, $link_type, $src_prop, $dest_prop);
   $innerHTML .= $lout['debugHTML'];
   # update the objects property list
   updateObjectPropList($dest_id);
   
   $retarr['innerHTML'] = $innerHTML;
   $retarr['debugHTML'] = $debugHTML;
   return $retarr;
}

function updateObjectPropList($elementid) {     
   global $listobject;
   if ($debug) {
      error_log("Updating properties on object $elementid");
      error_log("Unserializing $elementid");
   }
   $loadres = unSerializeSingleModelObject($elementid);
   $thisobject = $loadres['object'];
   if ($debug) {
      error_log("Compacting and updating inputs $elementid");
   }
   if (is_object($thisobject)) {
      $compres = compactSerializeObject($thisobject);
      if (!$compres['error']) {
         if ($debug) {
            error_log("Storing inputs and properties on $elementid");
         }
         $object_xml = $compres['object_xml'];
         $props_xml = $compres['props_xml'];
         $inputs_xml = $compres['inputs_xml'];

         # get the object back
         $listobject->querystring = " update scen_model_element set elem_xml = '$object_xml', elemprops = '$props_xml', eleminputs = '$inputs_xml' ";
         $listobject->querystring .= " where elementid = $elementid ";
         if ($debug) {
         //if (in_array($elementid, array(658,657,656))) {
            $innerHTML .= "$listobject->querystring ;<br>";
            error_log($listobject->querystring);
         }
         $listobject->performQuery();
         if ($debug) {
            error_log("Database update for $elementid");
         }
      } else {
         $innerHTML .= $compres['errorHTML'];
      }
   } else {
      $innerHTML .= "There was a problem loading object $elementid - " . $loadres['error'];
   }
   
   return $innerHTML;
}

function updateObjectProps($projectid, $elementid, $prop_array, $debug = 0) {
   global $listobject;
   $innerHTML = '';
   $debugHTML = '';
   
   $retarr = array();
      
   $loadres = unSerializeSingleModelObject($elementid);
   $thisobject = $loadres['object'];
   $debugHTML .= "Object unserialized <br>\n";

   $props = (array)$thisobject;
   # now set the object properties with the information passed in
   foreach (array_keys($props) as $thisprop) {
      $propstr .= ',' . $thisprop;
      if (in_array($thisprop, array_keys($prop_array))) {
         if ($thisprop <> '') {
            $thisobject->setProp($thisprop, $prop_array[$thisprop]);
            #$thisobject->$thisprop = $formValues[$thisprop];
            if ($debug) {
               $debugHTML .= "Property $thisprop set <br>\n";
            }
         }
      }
   }

   if (is_object($thisobject)) {
      $compres = compactSerializeObject($thisobject);
      if (!$compres['error']) {
         if ($debug) {
            $debugHTML .= "Storing inputs and properties on $elementid <br>\n";
         }
         $object_xml = $compres['object_xml'];
         $props_xml = $compres['props_xml'];
         $inputs_xml = $compres['inputs_xml'];

         # get the object back
         $listobject->querystring = " update scen_model_element set elem_xml = '$object_xml', ";
         if (isset($prop_array['cacheable'])) {
            $ca = $prop_array['cacheable'];
            $listobject->querystring .= " cacheable = " . intval($ca) . ", ";
         }
         if (isset($prop_array['custom1'])) {
            $c1 = $prop_array['custom1'];
            $listobject->querystring .= " custom1 = '" . $listobject->escapeString($c1) . "', ";
         }
         if (isset($prop_array['custom2'])) {
            $c2 = $prop_array['custom2'];
            $listobject->querystring .= " custom2 = '" . $listobject->escapeString($c2) . "', ";
         }
         $listobject->querystring .= " elemprops = '$props_xml', ";
         $listobject->querystring .= " eleminputs = '$inputs_xml' ";
         if (in_array('name', array_keys($prop_array))) {
            $listobject->querystring .= ", elemname = '" . $listobject->escapeString($prop_array['name']) . "'";
         }
         $listobject->querystring .= " where elementid = $elementid ";
         if ($debug) {
            $debugHTML .= "$listobject->querystring ;<br>";
            #error_log($listobject->querystring);
         }
         $listobject->performQuery();
         if ($debug) {
            $debugHTML .= "Database update for $elementid <br>\n";
         }
      } else {
         $debugHTML .= $compres['errorHTML'];
      }
   } else {
      $innerHTML .= "There was a problem loading object $elementid - " . $loadres['error'];
   }
      
   $retarr['innerHTML'] = $innerHTML;
   $retarr['debugHTML'] = $debugHTML;
   return $retarr;
}

function updateObjectSubComponents($projectid, $elementid, $prop_array) {
   global $listobject;
   $innerHTML = '';
   $debugHTML = '';
   
   $retarr = array();
      
   $loadres = unSerializeSingleModelObject($elementid);
   $thisobject = $loadres['object'];

   if (is_object($thisobject)) {
      # now set the object properties with the information passed in
      foreach ($prop_array as $propkey => $thisprop) {
         if (isset($thisobject->processors[$propkey])) {
            if (is_object($thisobject->processors[$propkey])) {
               if (method_exists($thisobject, 'setProp')) {
                  $thisobject->processors[$propkey]->setProp($thisprop, $prop_array[$thisprop]);
               }
            }
         }
      }
   }

   $innerHTML .= saveObjectSubComponents($listobject, $thisobject, $elementid, 1);
   return $innerHTML;
}

function runObjectCreate($projectid, $elementid) {
   global $listobject;
   $innerHTML = '';
   $debugHTML = '';
   
   $retarr = array();
      
   $loadres = unSerializeSingleModelObject($elementid);
   $thisobject = $loadres['object'];
   if (is_object($thisobject)) {
      //error_log("Checking for reCreate() method");
      $innerHTML .= "Calling reCreate() method on this object.<br>";
      if (method_exists($thisobject, 'reCreate')) {
         //error_log("reCreate() method exists");
         $thisobject->reCreate();
         $innerHTML .= saveObjectSubComponents($listobject, $thisobject, $elementid );
         //error_log("Finished reCreate() ");
      }
   }
   return $innerHTML;
}

function showModelEditForm($formValues, $elem_xml, $applyvalues = 1, $disabled=0, $thisobject=-1, $formname='') {
   #include("who_xmlobjects.php");
   global $debug, $listobject, $adminsetuparray, $fno, $timer, $userid;

   #if (
   $modelFormArray = array();
   $modelFormArray['innerHTML'] = '';
   #$debug = 1;
   if (is_object($thisobject) and !($formValues['changetype'] == 1)) {
      if ($debug) {
         $modelFormArray['debug'] .= "Object already awake, no need to reinstantiate.<br>";
      }
   } else {
      if ( (strlen($elem_xml) > 0) ) {
         # need to unserialize this object
         $thisload = loadElement($elem_xml);
         $thisobject = $thisload['object'];
         $modelFormArray['debug'] .= $thisload['debug'];
      }
   }



   $elemtype = get_class($thisobject);

   $props = (array)$thisobject;
   $listobject->tablename = $elemtype;
   if ($debug) {
      $modelFormArray['debug'] .= "Element properties: <br>" . print_r(array_keys($props),1) ."<br>";
   }
   #$innerHTML .= implode_md(',', array_keys($props)) ."<br>";
   # set any already submitted values
   foreach (array_keys($formValues) as $thisvar) {
      $thisval = $formValues[$thisvar];
      if ($debug) {
         $modelFormArray['innerHTML'] .= "$thisvar - $thisval <br> ";
      }
   }
   if ($debug) {
      $modelFormArray['innerHTML'] .= "elemtype - $elemtype <br> ";
   }
   if ($applyvalues) {
      # if we are asked to apply the  form submission values (if this is a submit request result)
      # set object properties to the form values
      $invars = array_keys($formValues);
      foreach (array_keys($props) as $thisprop) {
         if (in_array($thisprop, $invars)) {
            if ($thisprop <> '') {
               $props[$thisprop] = $formValues[$thisprop];
               if ($debug) {
                  $modelFormArray['innerHTML'] .= "Setting $thisprop to " . print_r($formValues[$thisprop],1) . "<br>";
               }
            }
         }
      }
      # now, re-awaken object to make sure any new form values are available to the wake() method
      if (is_object($thisobject) and method_exists($thisobject, 'wake')) {
         $thisobject->wake();
      }
   } else {
      if ($debug) {
         $modelFormArray['innerHTML'] .= "Form Values not applied to this objects properties<br> ";
      }
   }

   ##########################################################################
   # now, check to display any dynamic array elements from local properties #
   ##########################################################################
   $whoprops = getWHOXML($elemtype);
   $localprops = $whoprops['localprops'];
   $parenttype = $whoprops['parent'];
   #$localprops = $who_xmlobjects[$elemtype]['localprops'];
   foreach ($localprops as $thispname => $thisptype) {
      $thisprop = $thisobject->getPropertyClass(array($thisptype));
      if (is_array($thisprop)) {
         $asrec = split(':',$adminsetuparray[$elemtype]['column info'][$thispname]['params']);
         $asep = '';
         $aslist = '';
         foreach ($thisprop as $propval) {
            $aslist .= $asep . $propval . '|' . $propval;
            $asep = ',';
         }
         $asrec[0] = $aslist;
         $asparams = join(':', $asrec);
         if ($debug) {
            $innerHTML .= "Original Admin Info for $elemtype:<br>" . print_r($adminsetuparray[$elemtype],1) . "<br>";
         }
         # now, look for formats to copy, include any linked child formats (for meta descriptors)
         if (isset($adminsetuparray[$elemtype]['column info'][$thispname])) {
            $adminsetuparray[$elemtype]['column info'][$thispname]['params'] = $asparams;
         }
      }
   }
   ####################################################
   # finished, display any dynamic array elements     #
   ####################################################
   # if there are groupid select list on this component, screen it such that only the users own groups can be included
   if (isset($adminsetuparray[$elemtype]['column info']['groupid'])) {
      $adminsetuparray[$elemtype]['column info']['groupid']['params'] = "groups:groupid:groupname:groupname:0:groupid in (select groupid from mapusergroups where userid = $userid) ";
   }
   if (method_exists($thisobject, 'showEditForm')) {
      if ($applyvalues) {
         foreach ($props as $thispname => $thispvalue) {
            $thisobject->setProp($thispname, $thispvalue);
         }
      }
      $efinfo = $thisobject->showEditForm($formname,$disabled);
      $modelFormArray['elemtype'] = $efinfo['elemtype'];
      $modelFormArray['object'] = $efinfo['object'];
      $modelFormArray['parenttype'] = $parenttype;
      #$debug = 0;
      $modelFormArray['innerHTML'] .= $efinfo['innerHTML'];
      $modelFormArray['name'] = $efinfo['name'];
      $modelFormArray['description'] = $efinfo['description'];
   } else {
      $modelFormArray['elemtype'] = $elemtype;
      $modelFormArray['parenttype'] = $parenttype;
      #$debug = 0;
      $modelFormArray['innerHTML'] .= showFormVars($listobject,$props,$adminsetuparray[$elemtype],1, 0, $debug, 0, 1, $disabled, $fno);
      $modelFormArray['name'] = $thisobject->name;
      $modelFormArray['description'] = $thisobject->description;
   }

   $modelFormArray['object'] = $thisobject;

   return $modelFormArray;
}

function saveElementOperator($formValues) {
   #include("who_xmlobjects.php");
   global $listobject, $debug, $adminsetuparray;

   #$debug = 1;
   $innerHTML = '';
   if ($debug) {
      $innerHTML .= "Saving Model Element Operator.<br>";
   }

   $actiontype = $formValues['actiontype'];
   $elemtype = $formValues['elemtype'];
   $objectname = $formValues['name'];
   $projectid = $formValues['projectid'];
   $scenarioid = $formValues['scenarioid'];
   $elementid = $formValues['elementid'];
   $deleteinput = $formValues['deleteinput'];
   $operatorid = $formValues['operatorid'];
   $geomx = $formValues['geomx'];
   $geomy = $formValues['geomy'];

   $whoprops = getWHOXML($elemtype);
   if ( isset($whoprops['xml']) ) {
      // create object
      $elem_xml = $whoprops['xml'];
      #$options = array("complexType" => "object");
      $options = array("complexType" => "object", XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML);

      $unserializer = new XML_Unserializer($options);
      if ($debug) {
         $innerHTML .= "Unserializing<br>";
      }
      // unserialize the object. Use "false" since this is not a document, "true" if it is a document
      /*
      $result = $unserializer->unserialize($elem_xml, false);
      $thisobject = $unserializer->getUnserializedData();
      if (property_exists($thisobject, 'listobject')) {
         $thisobject->listobject = $listobject;
      }
      if (property_exists($thisobject, 'ucitables')) {
         if ($debug) {
            $innerHTML .= "Setting a ucitables object on this object.<br>";
         }
         $thisobject->ucitables = $ucitables;
      }
      if (method_exists($thisobject, 'wake')) {
         $thisobject->wake();
      }
      */
      $thisload = loadElement($elem_xml);
      $thisobject = $thisload['object'];
      $modelFormArray['debug'] .= $thisload['debug'];
      $props = (array)$thisobject;
      $propstr = '';
      $inputs = array();
      $instr = '';
      foreach ($thisobject->inputs as $thisin) {
         array_push($inputs, $thisin['objectname']);
         $instr .= ',' . $thisin['objectname'];
      }
      # now set the object properties with the information passed in
      #$innerHTML .= "Props:" . print_r(array_keys($props),1) . "<br>";
      //$innerHTML .= "Invars:" . print_r($formValues,1) . "<br>";
      foreach (array_keys($props) as $thisprop) {
         #$innerHTML .= "Looking for $thisprop.<br>";
         $propstr .= ',' . $thisprop;
         if (in_array($thisprop, array_keys($formValues))) {
            # scalar prop
            if ($thisprop <> '') {
               $propval = $formValues[$thisprop];
               if (is_array($propval[0])) {
                  $innerHTML .= " $thisprop is nested array.<br>";
                  $propval = $formValues[$thisprop][0];
                  $innerHTML .= " $thisprop " . print_r($propval,1) . "<br>";
               }
               $thisobject->setProp($thisprop, $propval);
               #$thisobject->$thisprop = $formValues[$thisprop];
               #$innerHTML .= "Saving $thisprop.<br>";
            }
         }
      }

      # clear large objects that will be set at runtime in order to speed up db performance
      if (property_exists($thisobject, 'ucitables')) {
         if ($debug) {
            $innerHTML .= "Clearing ucitables object on this object.<br>";
         }
         $thisobject->ucitables = array();
      }
      if (property_exists($thisobject, 'listobject')) {
         if ($debug) {
            $innerHTML .= "Clearing listobject object on this object.<br>";
         }
         $thisobject->listobject = -1;
      }
      if (property_exists($thisobject, 'fno')) {
         if ($debug) {
            $innerHTML .= "Clearing listobject object on this object.<br>";
         }
         $thisobject->fno = '';
      }

      # now,call the objects sleep routine
      if (method_exists($thisobject, 'sleep')) {
         $thisobject->sleep();
      }

      # now, put the object back into XML form to be stored in the database
      $options = array("complexType" => "object", XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML);
      $serializer = new XML_Serializer($options);
      // perform serialization

      if ($debug) {
         $innerHTML .= "Serializing<br>";
      }
      $result = $serializer->serialize($thisobject);


      if ($debug) {
         $innerHTML .= "Saving Result<br>";
      }
      $sn = $formValues['savenew'];
      $ow = $formValues['overwrite'];
      // check result code and display XML if success
      if($result === true)
      {
         if ($debug) {
            $innerHTML .= "Storing XML in database<br>";
         }
         $xml = $serializer->getSerializedData();
      }

      # now, all properties from the form have been set, so, go ahead and stow this in the database
      $listobject->querystring = "  update scen_model_element set elemoperators[$operatorid] = '$xml' ";
      $listobject->querystring .= " where elementid = $elementid ";
      if ($debug) { 
         $innerHTML .= "$listobject->querystring<br>"; 
      }
      $listobject->performQuery();
      # delete old linkages
   } else {
      $innerHTML .= "<b>Error:</b> Cannot find setup in formation for this component type. Bailing.<br>";
   }

   $innerHTML .= "Finished<br>";

   $opresult['innerHTML'] = $innerHTML;
   $opresult['object'] = $thisobject;
   return $opresult;
   #return $innerHTML;

}

function deleteElementOperator($formValues) {
   global $listobject, $debug, $adminsetuparray;

   #$debug = 1;
   $innerHTML = '';
   $innerHTML .= "Deleting Model Element Operator.<br>";

   $elementid = $formValues['elementid'];
   $operatorid = $formValues['operatorid'];

   # now, all properties from the form have been set, so, go ahead and stow this in the database
   $listobject->querystring = "  update scen_model_element set elemoperators[$operatorid] = '' ";
   $listobject->querystring .= " where elementid = $elementid ";
   if ($debug) { $innerHTML .= "$listobject->querystring<br>"; }
   $listobject->performQuery();

   return $innerHTML;

}

function cloneElementOperator($formValues) {
   global $listobject, $debug, $adminsetuparray;

   #$debug = 1;
   $innerHTML = '';
   $innerHTML .= "Deleting Model Element Operator.<br>";

   $elementid = $formValues['elementid'];
   $operatorid = $formValues['operatorid'];
   $opname = $formValues['name'];

   $innerHTML .= copySubComponent($elementid, $opname);

   return $innerHTML;

}

function copySubComponent($src_elementid, $src_opname, $dest_elementid = -1, $dest_opname = '') {
   global $listobject, $debug, $adminsetuparray;
   $innerHTML = '';
   
   if ($dest_opname == '') {
      $dest_opname = $src_opname;
   }
   if ($dest_elementid == -1) {
      $dest_elementid = $src_elementid;
   }
   $src_obres = unserializeSingleModelObject($src_elementid);
   $src_object = $src_obres['object'];
   if (isset($src_object->processors[$src_opname])) {
      $src_op = $src_object->processors[$src_opname];
      $dest_obres = unserializeSingleModelObject($dest_elementid);
      $dest_object = $dest_obres['object'];
      $dest_object->addOperator($dest_opname, $src_op, 0);

      $innerHTML .= "Adding a copy of $src_opname to element $dest_elementid as $dest_opname <br>\n";
      saveObjectSubComponents($listobject, $dest_object, $dest_elementid, 1);
   } else {
      $innerHTML .= "Sub-component $src_opname does not exist on element $src_elementid <br>\n";
   }
   return $innerHTML;

}

function getAllHubBroadCasts($listobject, $elementid, $hubname = 'child', $classname = '', $varname = '') {
   // this gets any broadcast variables on the hub specified, so it looks at the owner of the hub
   // and any children on that owner for broadcasts directed at the hub
   $retvals = array();
   if ($hubname == 'parent') {
      $elid = getElementContainer($listobject, $elementid);
   } else {
      $elid = $elementid;
   }
   $retvals = getBroadCasts($elid, 'child', '', $classname, $varname);
   $childrecs = getChildComponentType($listobject, $elid);
   foreach ($childrecs as $thischild) {
      $cid = $thischild['elementid'];
      $stuff = getBroadCasts($cid, 'parent', '', $classname, $varname);
      if (count($stuff) > 0) {
         foreach($stuff as $thing) {
            $retvals[] = $thing;
         }
      }
   }
   
   return $retvals;
}

function getBroadCasts($elementid, $casthub = '', $castmode = '', $classname = '', $varname = '') {
   // $casttypes = 'read' - read, 'cast' - send, '' - all
   // $casthub = 'parent', 'child', or '' - all
   
   $retvals = array();
   $src_obres = unserializeSingleModelObject($elementid);
   $src_object = $src_obres['object'];
   $proc_names = array_keys($src_object->processors);
   foreach ($proc_names as $thisvar) {
   //error_log("Checking $thisvar ");
      if (is_object($src_object->processors[$thisvar])) {
         if (get_class($src_object->processors[$thisvar]) == 'broadCastObject') {
            //error_log(" $thisvar is Broadcast Object ");
            // we have found a valid broadcast object, now check for type and class name and 
            // return if we have a match
            $thismode = $src_object->processors[$thisvar]->broadcast_mode;
            $thisclass = $src_object->processors[$thisvar]->broadcast_class;
            $thishub = $src_object->processors[$thisvar]->broadcast_hub; 
            //error_log("Details = $thismode - $thisclass - $thishub ");

            $hubmatch = (($thishub == $casthub) or ($casthub ==  ''));
            $typematch = (($thismode == $castmode) or ($castmode ==  ''));
            $classmatch = (($thisclass == $classname) or ($classname ==  ''));
            //error_log("Matches = $hubmatch - $typematch - $classmatch ");

            if ($hubmatch and $typematch and $classmatch) {
               for ($i = 0; $i < count($src_object->processors[$thisvar]->local_varname); $i++) {
                  $bvname = $src_object->processors[$thisvar]->broadcast_varname[$i];
                  $lname = $src_object->processors[$thisvar]->local_varname[$i];
                  // if a variable name was submitted with the function call,
                  // we only get this one variable
                  if (($bvname == $varname) or ($varname ==  '')) {
                     $retvals[] = array(
                        'broadcast_class'=>$thisclass,
                        'broadcast_hub'=>$thishub,
                        'broadcast_mode'=>$thismode,
                        'broadcast_varname'=>$bvname,
                        'local_varname'=>$lname,
                        'elementid' => $elementid
                     );
                  }
               }
            }
         }
      }
   }
   return $retvals;   
}

function changeObjectType($elementid, $newtype) {
   
}

function addElementResult($formValues) {
   global $listobject, $timer, $debug, $goutdir, $gouturl, $outdir, $outurl, $userid, $adminsetuparray;
   #include("who_xmlobjects.php");


   #$debug = 1;
   $innerHTML = '';
   $innerHTML .= "Saving Model Element.<br>";

   $actiontype = $formValues['actiontype'];
   $elemtype = $formValues['elemtype'];
   $objectname = $formValues['name'];
   $projectid = $formValues['projectid'];
   $scenarioid = $formValues['scenarioid'];
   $elementid = $formValues['elementid'];
   $input = $formValues['input'];
   $inputid = $formValues['inputid'];
   $childid = $formValues['childid'];
   $inputname = $formValues['inputname'];
   $callcreate = $formValues['callcreate'];
   $changetype = $formValues['changetype'];
   $operms = $formValues['operms'];
   $gperms = $formValues['gperms'];
   $pperms = $formValues['pperms'];
   $groupid = $formValues['groupid'];
   if (isset($formValues['cacheable'])) {
      $cacheable = $formValues['cacheable'];
   } else {
      $cacheable = 0;
   }
   $innerHTML .= "Input Array:<br>" . print_r($input,1) .'<br>Input Names:<br>' . print_r($inputname,1) . '<br>'. print_r($formValues,1) . '<br>';
   if (isset($formValues['deleteinput'])) {
      $deleteinput = $formValues['deleteinput'];
   } else {
      $deleteinput = array();
   }
   if (isset($formValues['activecontainerid'])) {
      $activecontainerid = $formValues['activecontainerid'];
   } else {
      $activecontainerid ='';
   }
   if (isset($formValues['deletechild'])) {
      $deletechild = $formValues['deletechild'];
   } else {
      $deletechild = array();
   }


   # this stuff gets handled separately from the other parsing, since the geometry is not stored on the object.
   # this is mainly due to the overhead that would be incurred when running the XML parsing routines on an object
   # that had a large polygon, or multipolygon geometry
   $geomx = $formValues['geomx'];
   $geomy = $formValues['geomy'];
   if ($debug) {
      $innerHTML .= "Geom submitted: " . $formValues['the_geom'] . "<br>";
   }
   if ( (ltrim(rtrim($geomx)) == '') or (ltrim(rtrim($geomx)) == '')) {
      $centroid_wkt = 'NULL';
   } else {
      $centroid_wkt = "POINT($geomx $geomy)";
   }
   if (isset($formValues['the_geom'])) {
      $wkt_geom = $formValues['the_geom'];
      $geompieces = split('\(', $wkt_geom);
      $glen = count($geompieces);
      if ($debug) {
         $innerHTML .= "Geom parsed: " . print_r($geompieces,1) . "<br>";
      }
      if ( (ltrim(rtrim($geompieces[$glen - 1])) == ')') or (ltrim(rtrim($geompieces[$glen - 1])) == '') or (count($geompieces) == 1) ) {
         $wkt_geom = 'NULL';
         $innerHTML .= "Geometry set to null<br>";
      }
      switch ($geompieces[0]) {
         case 'POINT':
            $wkt_type = 1;
            $geomcol = "point_geom";
         break;

         case 'LINE':
            $wkt_type = 2;
            $geomcol = "line_geom";
         break;

         case 'POLYGON':
            $wkt_type = 3;
            $geomcol = "poly_geom";
         break;

         case 'MULTIPOLYGON':
            $wkt_type = 3;
            $geomcol = "poly_geom";
         break;

         default:
         # geometry not parsed, or typ enot understood, use the centroid
            $wkt_type = 1;
            $geomcol = "point_geom";
            $wkt_geom = $centroid_wkt;
         break;
      }
   } else {
      # geometry not parsed, or typ enot understood, use the centroid
      $wkt_type = 1;
      $geomcol = "point_geom";
      $wkt_geom = 'NULL';
   }
   $timer->startSplit();
   $split = $timer->startSplit();
   $innerHTML .= "Checking for elem type $elemtype in object array.<br>";
   $whoprops = getWHOXML($elemtype);
   //$debug = 1;
   if ( count($whoprops) > 0 ) {
      $innerHTML .= "Found $elemtype, creating blank object.<br>";
      $innerHTML .= "Form Value changetype = $changetype <br>";
      // create object
      $elem_xml = $whoprops['xml'];
      $component_type = $whoprops['type'];
      // do not retrieve the object if we are requesting a change to object type
      if ($elementid > 0 and ($changetype <> 1)) {
         # restore this object so that we can store a list of all its preoprties for use by other objects
         # during linkage
         error_log("START Un-serializing Object $elementid");
         $obres = unserializeSingleModelObject($elementid);
         $thisobject = $obres['object'];
         error_log("FINISHED un-serializing Object $elementid");
      } else {
         if ($changetype == 1) {
            $innerHTML .= "Object type change to $elemtype requested.<br>";
         }
         $options = array("complexType" => "object");
         $unserializer = new XML_Unserializer($options);
         if ($debug) {
            $innerHTML .= "Unserializing<br>";
         }
         // unserialize the object. Use "false" since this is not a document, "true" if it is a document
         $result = $unserializer->unserialize($elem_xml, false);
         $thisobject = $unserializer->getUnserializedData();
         if (isset($thisobject->listobject)) {
            $thisobject->listobject = $listobject;
         }
         if (property_exists($thisobject, 'listobject')) {
            if ($debug) {
               $innerHTML .= "Setting a ucitables object on this object.<br>";
            }
            $thisobject->listobject = $listobject;
         }
         if (property_exists($thisobject, 'ucitables')) {
            if ($debug) {
               $innerHTML .= "Setting a ucitables object on this object.<br>";
            }
            $thisobject->ucitables = $ucitables;
         }
         if (method_exists($thisobject, 'wake')) {
            $thisobject->wake();
         }
      }
      $split = $timer->startSplit();
      $innerHTML .= "Finished in $split seconds.<br>";
      $innerHTML .= "Getting property list for element $elementid.<br>";
      # properties to set
      $props = (array)$thisobject;
      # properties visible to other objects
      if ($elementid > 0) {
         $publicprops = getElementPropertyList($elementid);
         if ($debug) {
            $innerHTML .= "Properties for element $elementid obtained from db column.<br>" . print_r($publicprops,1) . "<br>";
         }
      } else {
         $publicprops = $thisobject->getPublicVars();
         if ($debug) {
            $innerHTML .= "Properties for new element obtained from object.<br>" . print_r($publicprops,1) . "<br>";
         }
      }
      $split = $timer->startSplit();
      $innerHTML .= "Finished in $split seconds.<br>";
      $innerHTML .= "Setting properties from form submission.<br>";
      $propstr = '';
      $inputs = array();
      $instr = '';
      foreach ($thisobject->inputs as $thisin) {
         array_push($inputs, $thisin['objectname']);
         $instr .= ',' . $thisin['objectname'];
      }
      # now set the object properties with the information passed in
      foreach (array_keys($props) as $thisprop) {
         $propstr .= ',' . $thisprop;
         if (in_array($thisprop, array_keys($formValues))) {
            if ($thisprop <> '') {
               $thisobject->setProp($thisprop, $formValues[$thisprop]);
               #$thisobject->$thisprop = $formValues[$thisprop];
            }
         }
      }
      $split = $timer->startSplit();
      $innerHTML .= "Finished in $split seconds.<br>";
   }
   # now, we have our object instantiated, and populated with its changed data, we will call the create() method
   # if requested in the form
   if ($callcreate) {
      error_log("Checking for reCreate() method");
      $innerHTML .= "Calling create() method on this object.<br>";
      if (method_exists($thisobject, 'reCreate')) {
         error_log("reCreate() method exists");
         $thisobject->reCreate();
         $innerHTML .= saveObjectSubComponents($listobject, $thisobject, $elementid );
         error_log("Finished reCreate() ");
      }
   }
   # zero out things that should not be saved
   $innerHTML .= "Stripping large generic data from object for compact storage.<br>";
   $thisobject->initialized = 0; # set this to zero so that the model will wake and be refreshed
   if (property_exists($thisobject, 'listobject')) {
      $thisobject->listobject = NULL;
   }
   if (property_exists($thisobject, 'ucitables')) {
      if ($debug) {
         $innerHTML .= "Setting a ucitables object on this object.<br>";
      }
      $thisobject->ucitables = NULL;
   }
   if (property_exists($thisobject, 'the_geom')) {
      if ($debug) {
         $innerHTML .= "Nullifying the_geom on this object.<br>";
      }
      $thisobject->the_geom = '';
   }
   $split = $timer->startSplit();
   $innerHTML .= "Finished in $split seconds.<br>";
   $innerHTML .= "Putting object to sleep.<br>";
   # do any other cleanup you can
   $thisobject->sleep();
   $split = $timer->startSplit();
   $innerHTML .= "Finished in $split seconds.<br>";

   # now, put the object back into XML form to be stored in the database
   $innerHTML .= "Converting to XML.<br>";
   $serializer = new XML_Serializer();
   // perform serialization

   $innerHTML .= "Serializing<br>";
   $result = $serializer->serialize($thisobject);
   $split = $timer->startSplit();
   $innerHTML .= "Finished in $split seconds.<br>";

   $innerHTML .= "Printing Result<br>";
   $sn = $formValues['savenew'];
   $ow = $formValues['overwrite'];
   $innerHTML .= "$sn<br>";
   $innerHTML .= "$ow<br>";
   // check result code and display XML if success
   $innerHTML .= "Retrieving XML data for storage.<br>";
   if($result === true)
   {
      $innerHTML .= "Storing XML in database<br>";
      $xml = $serializer->getSerializedData();
   }
   $split = $timer->startSplit();
   $innerHTML .= "Finished in $split seconds.<br>";
   $inputs_xml = '';
   $innerHTML .= "Retrieving XML for object inputs.<br>";
   $result = $serializer->serialize($inputs);
   if($result === true) {
      $inputs_xml = $serializer->getSerializedData();
   }
   $split = $timer->startSplit();
   $innerHTML .= "Finished in $split seconds.<br>";
   $props_xml = '';
   if ( (!is_array($publicprops)) or (count($publicprops) == 0)) {
      # need to get the names of the operators on this object to add to public variables,
      $publicprops = $thisobject->getPublicVars();
   }
   # since we have not-reconstituted the operators yet
   $innerHTML .= "Generating XML for model property list.<br>";
   $result = $serializer->serialize($publicprops);
   if($result === true) {
      $props_xml = $serializer->getSerializedData();
   }
   $innerHTML .= "This Objects Properties XML: $props_xml<br>";
   $split = $timer->startSplit();
   $innerHTML .= "Finished in $split seconds.<br>";
   $saveasnew = 0;
   $debug = 0;
   if ( ($elementid > 0) and !$saveasnew) {
      #if we have not returned, then go ahead and update this object
      $innerHTML .= "Updating object in database.<br>";
      if ($groupid == '') {
         $groupid = -1;
      }

      $listobject->querystring = "  update scen_model_element set elemname = '$objectname', cacheable = $cacheable, ";
      $listobject->querystring .= "    elemprops = '$props_xml', elem_xml = '$xml', objectclass = '$elemtype', ";
      $listobject->querystring .= "    groupid = $groupid, operms = $operms, gperms = $gperms, pperms = $pperms, ";
      $listobject->querystring .= "    the_geom = ";
      if (ltrim(rtrim($centroid_wkt)) == 'NULL') {
         $listobject->querystring .= "    NULL";
      } else {
         $listobject->querystring .= "    GeomFromText('$centroid_wkt', 4326)";
      }
      $listobject->querystring .= "    , component_type = $component_type, ";
      $listobject->querystring .= "    geomtype = $wkt_type, $geomcol = ";
      if (ltrim(rtrim($wkt_geom)) == 'NULL') {
         $listobject->querystring .= " NULL ";
      } else {
         if ($wkt_type == 3) {
            # may later have to add a similar transform for line strings
            $listobject->querystring .= "Multi(GeomFromText('$wkt_geom', 4326)) ";
         } else {
            $listobject->querystring .= "GeomFromText('$wkt_geom', 4326) ";
         }
      }
      $listobject->querystring .= " where elementid = $elementid ";
      if ($thisobject->debug) {
         $innerHTML .= "$listobject->querystring<br>";
      }
      $innerHTML .= "XML for $thisobject->name properties: " . $props_xml . "<br>";
      $listobject->performQuery();
      # delete old linkages
      $listobject->querystring = "  delete from map_model_linkages ";
      $listobject->querystring .= " where dest_id = $elementid ";
      // we handle remote links (type 3) separately in ajax, a better way I think than this.
      $listobject->querystring .= " and linktype in (1,2) ";
      if ($debug) { $innerHTML .= "$listobject->querystring<br>"; }
      $listobject->performQuery();
      $split = $timer->startSplit();
      $innerHTML .= "Finished in $split seconds.<br>";
      # now update this elements variable linkages, if any
      foreach (array_keys($inputid) as $thisinputid) {
         $innerHTML .= "Updating input linkage database.<br>";
         if ($inputid[$thisinputid] > 0 and !($deleteinput[$thisinputid] == 1) ) {
            $src_id = $inputid[$thisinputid];
            # now insert the selected link, if this is the first call to this, we allow a blank value
            $src_prop = $input[$thisinputid];
            if (strlen(ltrim(rtrim($src_prop))) == 0) {
               # make it NULL
               $src_prop = 'NULL';
            }
            if (is_array($inputname)) {
               # hack to avoid some kind of ajax trouble, mangling text array??
               $dest_prop = $inputname[$thisinputid];
            } else {
               $dest_prop = $inputname;
            }
            if (strlen(ltrim(rtrim($dest_prop))) == 0) {
               # make it NULL
               $dest_prop = 'NULL';
            }
            if ($debug) {
               $innerHTML .= "Input Array:<br>" . print_r($input,1) .'<br>Input Names:<br>' . print_r($inputname,1) . '<br>';
               $innerHTML .= "$listobject->querystring<br>";
            }
            createObjectLink($projectid, $scenarioid, $src_id, $elementid, 2, $src_prop, $dest_prop);
         }
         $split = $timer->startSplit();
         $innerHTML .= "Finished in $split seconds.<br>";
      }
      $innerHTML .= "Updating contained object linkage.<br>";
      # now update this elements child linkages, if any
      foreach (array_keys($childid) as $thisinputid) {
         if ($childid[$thisinputid] > 0 and !($deletechild[$thisinputid] == 1) ) {
            $src_id = $childid[$thisinputid];
            createObjectLink($projectid, $scenarioid, $src_id, $elementid, 1);
         }
      }
      $split = $timer->startSplit();
      $innerHTML .= "Finished in $split seconds.<br>";
   } else {
      $innerHTML .= "Saving object as new.<br>";
      $listobject->querystring = "  select count(*) as already from scen_model_element ";
      $listobject->querystring .= " where scenarioid = $scenarioid ";
      $listobject->querystring .= "    and elemname = '$objectname' ";
      $innerHTML .= "$listobject->querystring<br>";
      $listobject->performQuery();
      $already = $listobject->getRecordValue(1,'already');
      if ($already > 0) {
         $innerHTML .= "<b>Error:</b> An object in this scenario already exists with the name '$objectname'. ";
         $innerHTML .= " Either change the name, or select 'Overwrite Element' to replace this object ";
         return $innerHTML;
      } else {
         #if we have not returned, then go ahead and insert this object

         # if we have a activecontainerid and this is NOT explicitly created as a new contianer,
         # we set the component group to be the same as the activecontainerid group.  If it IS a new container
         # then we set the component group to be the users private group.
         if ( ($activecontainerid > 0) and ($actiontype <> 'newcontainer') ) {
            $listobject->querystring = " select groupid from scen_model_element where elementid = $activecontainerid ";
            $listobject->performQuery();
            $groupid = $listobject->getRecordValue(1,'groupid');
         } else {
            $listobject->querystring = " select groupid from users where userid = $userid ";
            $listobject->performQuery();
            $groupid = $listobject->getRecordValue(1,'groupid');
         }
         $listobject->adminsetup = $adminsetuparray['scen_model_element'];
         $listobject->querystring = "  insert into scen_model_element (scenarioid, groupid, elemname, elem_xml, objectclass, ";
         $listobject->querystring .= "    operms, gperms, pperms, ";
         if (  (ltrim(rtrim($geomx)) <> '') and (ltrim(rtrim($geomy)) <> '') ) {
            $listobject->querystring .= "the_geom, ";
         }
         $listobject->querystring .= "    eleminputs, elemprops, component_type, ownerid, geomtype, $geomcol ) ";
         $centroid_wkt = "POINT($geomx $geomy)";
         $listobject->querystring .= " values ($scenarioid, $groupid, '$objectname', '$xml', '$elemtype', ";
         $listobject->querystring .= "    $operms, $gperms, $pperms, ";
         if (  (ltrim(rtrim($geomx)) <> '') and (ltrim(rtrim($geomy)) <> '') ) {
            $centroid_wkt = "POINT($geomx $geomy)";
            $listobject->querystring .= " GeomFromText('$centroid_wkt', 4326), ";
         }
         $listobject->querystring .= "    '$inputs_xml', '$props_xml', $component_type, $userid, $wkt_type, ";

         if ($wkt_geom <> 'NULL') {
            if ( $wkt_type == 3) {
               # may later have to add a similar transform for line strings
               $listobject->querystring .= "Multi(GeomFromText('$wkt_geom', 4326)) ) ";
            } else {
               $listobject->querystring .= "GeomFromText('$wkt_geom', 4326) ) ";
            }
         } else {
            $listobject->querystring .= "NULL ) ";
         }
         $innerHTML .= "$listobject->querystring<br>";
         $listobject->performQuery();
         $listobject->querystring = "SELECT currval('scen_model_element_elementid_seq') ";
         $listobject->performQuery();
         $listobject->show = 0;
         $listobject->showList();
         #$innerHTML .= "$listobject->outstring <br>";
         $newelid = $listobject->getRecordValue(1,'currval');
         #$innerHTML .= "$activecontainerid  $newelid<br>";
         # if we have a activecontainerid value set, we go ahead and insert a linkage for this new element
         # unless of course this is a explicitly created as a new contianer, byt pressing the
         # "Create New Object in Active Model" button, (not just a contianer that
         # is contained by another active container) if this is a new container, then we do not force it to be contained
         # by the current active model.  However, if this was a container created by the
         # "Create New Object in Active Model" button, then actiontype will not
         # be 'newcontainer' so we go ahead and add the linkages.
         if ( ($activecontainerid > 0) and ($newelid > 0) and ($actiontype <> 'newcontainer') ) {
            createObjectLink($projectid, $scenarioid, $newelid, $activecontainerid, 1);
         }
      }
      $split = $timer->startSplit();
      $innerHTML .= "Finished in $split seconds.<br>";
   }

   return $innerHTML;
}

function serializeModelObject($thisobject) {
   # goes through all contained objects:
   #   processors
   #   inputs
   #   components
   # and serializes them into appropriate containers
   # returns the xml for the object and each of the contained sets of objects for database storage


}


function unSerializeSingleModelObjectDB($dbobj, $elementid, $input_props = array()) {
   global $listobject;
   $db_holder = $listobject;
   $listobject = $dbobj;
   $retarr = unSerializeSingleModelObject($elementid, $input_props);
   $listobject = $db_holder;
   return $retarr;
}


function unSerializeSingleModelObject($elementid, $input_props = array()) {
   global $listobject, $tmpdir, $shellcopy, $ucitables, $scenarioid, $debug, $outdir, $outurl, $goutdir, $gouturl, $unserobjects, $adminsetuparray, $wdm_messagefile, $basedir;
   # goes through all contained objects:
   #   processors
   #   inputs
   #   components
   # and un-serializes them into objects
   # connects objects to parent container
   # returns the object, and any error output in an associative array('debug', 'object')
   #$debug = 1;
   # create a global container to hold any objects that have already been instantiated
   $returnArray = array();
   $returnArray['error'] = '';
   $returnArray['debug'] = '';
   $returnArray['object'] = '';
   if (!is_array($unserobjects)) {
      if ($debug) {
         $returnArray['debug'] .= "Creating Blank Unser Objects array<br>";
      }
      $unserobjects = array();
   }
   if ($debug) {
      $returnArray['debug'] .= "Unser Objects<br>";
      $returnArray['debug'] .= print_r($unserobjects,1);
      $returnArray['debug'] .= "End Unser Objects<br>";
   }

   if ($elementid > 0) {
      $listobject->querystring = "  select elem_xml, elemname, x(centroid(the_geom)) as xcoord, ";
      $listobject->querystring .= "    groupid, operms, gperms, pperms, ";
      $listobject->querystring .= "    y(centroid(the_geom)) as ycoord, array_dims(elemoperators) as adims, ";
      $listobject->querystring .= "    CASE ";
      $listobject->querystring .= "       WHEN geomtype = 3 THEN asText(poly_geom)";
      $listobject->querystring .= "       WHEN geomtype = 2 THEN asText(line_geom)";
      $listobject->querystring .= "       WHEN geomtype = 1 THEN asText(point_geom)";
      $listobject->querystring .= "    END as wkt_geom, geomtype, ";
      $listobject->querystring .= "    CASE ";
      $listobject->querystring .= "       WHEN geomtype = 1 THEN box2d(point_geom) ";
      $listobject->querystring .= "       WHEN geomtype = 2 THEN box2d(line_geom) ";
      $listobject->querystring .= "       WHEN geomtype = 3 THEN box2d(poly_geom) ";
      $listobject->querystring .= "       ELSE box2d(the_geom) ";
      $listobject->querystring .= "    END as geom_extent ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      if ($debug) {
         $returnArray['debug'] .= " Retreiving object $elementid : $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $returnArray['error'] .= " Retreiving object $elementid : $listobject->querystring ; <br>";

      $elem_xml = $listobject->getRecordValue(1,'elem_xml');
      $elemname = $listobject->getRecordValue(1,'elemname');
      $xcoord = $listobject->getRecordValue(1,'xcoord');
      $ycoord = $listobject->getRecordValue(1,'ycoord');
      $wkt_geom = $listobject->getRecordValue(1,'wkt_geom');
      $extent = $listobject->getRecordValue(1,'geom_extent');
      $groupid = $listobject->getRecordValue(1,'groupid');
      $operms = $listobject->getRecordValue(1,'operms');
      $gperms = $listobject->getRecordValue(1,'gperms');
      $pperms = $listobject->getRecordValue(1,'pperms');

      $dimstr = str_replace(']','',str_replace('[','',$listobject->getRecordValue(1,'adims')));
      if ($debug) {
         $returnArray['debug'] .= "<br>Dim: $dimstr<br>";
      }
      list($astart, $aend) = split(':', $dimstr);
   } else {
      $elem_xml = '';
      $astart = 0;
      $aend = -1;
   }
   $opxmls = array();
   $returnArray['debug'] .= "Searching for Operators on $elemname <br>";
   for ($i = $astart; $i <= $aend; $i++) {
      if ($debug) {
         $returnArray['debug'] .= "retrieving op $i<br>";
      }
      $listobject->querystring = "  select elemoperators[$i] ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      $listobject->performQuery();
      $thisxml = $listobject->getRecordValue(1,'elemoperators');
      if (str_replace('"', '', $thisxml) <> '') {
         $opxmls[$i] = $thisxml;
      }
      if ($debug) {
         $returnArray['debug'] .= " $listobject->querystring ; <br>";
         $returnArray['debug'] .= "Op $i XML: $opxmls[$i]<br>";
      }
   }

   if ($debug) {
      $returnArray['debug'] .= "Creating Unserializer<br>";
   }

   // tell the unserializer to create an object
   $options = array("complexType" => "object");
   // tell the unserializer to create an array of properties
   #$options = array("complexType" => "array");

   // create object
   $unserializer = new XML_Unserializer($options);

   if ($debug) {
      $returnArray['debug'] .= "Unserializing<br>";
   }
   // unserialize the object. Use "false" since this is not a document, "true" if it is a document
   $result = $unserializer->unserialize($elem_xml, false);
   $returnArray['elemtype'] = $unserializer->getRootName();

   if ($debug) {
      $returnArray['debug'] .= "Result of Unserializing<br>";
   }
   // dump the result
   $thisobject = $unserializer->getUnserializedData();

   # make sure this is a valid object
   if (!is_object($thisobject) or !($result === true)) {
      # problem re-serializing
      $returnArray['error'] .= "Problem Un-serializing object: $elemname, ID: $elementid <br>";
      return $returnArray;
   }

   # first thing, set the component id, so that all following actions do not get goofed up
   $thisobject->setCompID($elementid);
   $thisobject->groupid = $groupid;
   $thisobject->operms = $operms;
   $thisobject->gperms = $gperms;
   $thisobject->pperms = $pperms;

   if ($debug) {
      $oprops = (array)$thisobject;
      $returnArray['debug'] .= "<br><b>Saved properties</b>:<br>";
      $returnArray['debug'] .= print_r($oprops,1);
      $returnArray['debug'] .= "<br><b>Setting Transient properrties</b><br>";
   }

   if (property_exists($thisobject, 'the_geom')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting a geometry object on this object.<br>";
      }
      if (strlen($wkt_geom) > 0) {
         $thisobject->the_geom = $wkt_geom;
         $thisobject->geomtype = $geomtype;
         $thisobject->extent = $extent;
      } else {
         $thisobject->the_geom = 'POINT( ' . $xcoord . ' ' . $ycoord . ' )';
         $thisobject->geomtype = 1;
         $thisobject->extent = 'BOX( ' . $xcoord . ' ' . $ycoord . ' , ' . $xcoord . ' ' . $ycoord . ' )';
      }
   }

   if (property_exists($thisobject, 'listobject')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting a list object on this object.<br>";
      }
      $thisobject->listobject = $listobject;
   }

   if (property_exists($thisobject, 'geomx')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting x-coord on this object.<br>";
      }
      $thisobject->geomx = $xcoord;
   }

   if (property_exists($thisobject, 'geomy')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting y-coord on this object.<br>";
      }
      $thisobject->geomy = $ycoord;
   }

   if (property_exists($thisobject, 'wdm_messagefile')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting a WDM message file on this object.<br>";
      }
      $thisobject->wdm_messagefile = $wdm_messagefile;
   }
   if (property_exists($thisobject, 'ucitables')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
      }
      $thisobject->ucitables = $ucitables;
   }
   foreach ($input_props as $this_propname => $this_propvalue) {
      $thisobject->setProp($this_propname, $this_propvalue);
   }
   // disable this since this mode does not require a debugging function
   $enable_debug = 1;
   if (isset($input_props['debug'])) {
      $enable_debug = $input_props['debug'];
   }
   if ($enable_debug == 0) {
      $thisobject->debugmode = -1;
      $thisobject->logerrors = 0;
   }
   
   if (method_exists($thisobject, 'wake')) {
      $thisobject->wake();
   }
   #$returnArray['debug'] .= "<b>Original object debug status: </b>" . $thisobject->debug . "<br>";
   #$thisobject->debug = $debug;
   $thisobject->outdir = $outdir;
   $thisobject->outurl = $outurl;
   $thisobject->goutdir = $goutdir;
   $thisobject->gouturl = $gouturl;
   $thisobject->tmpdir = $tmpdir;
   $thisobject->basedir = $basedir;
   $thisobject->shellcopy = $shellcopy;
   $unserobjects[$elementid] = $thisobject;
   $returnArray['error'] .= $thisobject->errorstring;

   if ($debug) {
      $returnArray['debug'] .= "<br><b>Unserializing operators</b><br>";
   }

   #return $returnArray;
   $j = 0;
   foreach ($opxmls as $thisop) {
      // unserialize the object. Use "false" since this is not a document, "true" if it is a document
      $result = $unserializer->unserialize($thisop, false);
      if ($debug) {
         $returnArray['debug'] .= "<br><b>Result of Unserializing</b><br>";
      }
      if ($result === true) {
         // dump the result
         $opobject = $unserializer->getUnserializedData();
         if (property_exists($opobject, 'listobject')) {
            if ($debug) {
               $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
            }
            $opobject->listobject = $listobject;
         }
         if (property_exists($opobject, 'ucitables')) {
            if ($debug) {
               $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
            }
            $opobject->ucitables = $ucitables;
         }
         // disable this since this mode does not require a debugging function
         $enable_debug = 1;
         if (isset($input_props['debug'])) {
            $enable_debug = $input_props['debug'];
         }
         if ($enable_debug == 0) {
            $opobject->debugmode = -1;
            $opobject->logerrors = 0;
         }
         if (method_exists($opobject, 'wake')) {
            $opobject->wake();
         }
         $opobject->outdir = $outdir;
         $opobject->outurl = $outurl;
         $opobject->goutdir = $goutdir;
         $opobject->gouturl = $gouturl;
         # manually set the componentid, since these do not have a db generated ID, and they only exist in
         # a scope that is local to the containing object, set them to be a decimal on the parent elementid
         $opobject->componentid = "$elementid" . "." . $j;
         if ($debug) {
            $returnArray['debug'] .= print_r($opobject,1);
            $returnArray['debug'] .= "<br><b>Unserializing operators</b><br>";
         }
         $thisobject->addOperator($opobject->name, $opobject, $opobject->defaultval);
         $j++;
      }
   }

   # retrieve input linkages
   $listobject->querystring = "  select src_id, src_prop, dest_prop ";
   $listobject->querystring .= " from map_model_linkages ";
   $listobject->querystring .= " where dest_id = $elementid ";
   //$listobject->querystring .= "    and linktype = 2 ";
   $listobject->querystring .= "    and linktype in ( 2, 3 ) ";
   # screen out non-set objects so as to avoid an error
   #$listobject->querystring .= "    and src_id <> -1 ";
   if ($debug) {
      $returnArray['debug'] .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   $linkrecs = $listobject->queryrecords;
   if ($debug) {
      $returnArray['debug'] .= " Searching for Input objects in $thisobject->name <br>";
   }
   foreach ($linkrecs as $thisrec) {
      $src_id = $thisrec['src_id'];
      $src_prop = $thisrec['src_prop'];
      $dest_prop = $thisrec['dest_prop'];
      if ($debug) {
         $returnArray['debug'] .= " Searching for $src_id in " . print_r(array_keys($unserobject)) . '<br>';
      }
      if ($src_id <> -1) {
         # need to insert a dummy object for this linkage
         if ($debug) {
            $returnArray['debug'] .= " Adding Input $linkobj->name : $src_prop -gt $dest_prop <br>";
         }
         # insert a blank object here, since we really only need a placeholder
         $linkobj = new modelObject;
         $thisobject->addInput($dest_prop, $src_prop, $linkobj);
      } else {
         $linkerror = 'NULL Linkage found';
         $linkdebug = 'NULL Linkage found';
         $returnArray['debug'] .= $linkdebug;
         $linkobj = NULL;
      }
   }


   #$thisobject->setStateVar();
   $returnArray['object'] = $thisobject;
   #$debug = 0;
   return $returnArray;

}

function getElementCacheable($listobject, $elementid) {
   // have we prohibited caching of this object?
   $listobject->querystring = " select cacheable from scen_model_element where elementid = $elementid ";
   $listobject->performQuery();
   if ($listobject->numrows > 0) {
      $cacheable = $listobject->getRecordValue(1,'cacheable');
   } else {
      $cacheable = 0;
   }
   return $cacheable;
   
}

function unSerializeModelObject($elementid, $input_props = array(), $model_listobj = '', $cache_level = -1, $cache_id = -1, $current_level = -1) {
   global $listobject, $tmpdir, $shellcopy, $ucitables, $scenarioid, $debug, $outdir, $outurl, $goutdir, $gouturl, $unserobjects, $adminsetuparray, $wdm_messagefile, $wdimex_exe, $basedir, $model_startdate, $model_enddate;
   
   //error_log("unSerializeModelObject called for $elementid <br>");
   
   # goes through all contained objects:
   #   processors
   #   inputs
   #   components
   # and un-serializes them into objects
   # connects objects to parent container
   # returns the object, and any error output in an associative array('debug', 'object')
   #$debug = 1;
   # create a global container to hold any objects that have already been instantiated
   $returnArray = array();
   $returnArray['error'] = '';
   $returnArray['debug'] = '';
   $returnArray['object'] = '';
   $returnArray['complist'] = array();
   $returnArray['cached'] = array();
   array_push($returnArray['complist'], $elementid);
   if (!isset($model_startdate)) {
      $model_startdate = '';
      $returnArray['error'] .= "Global model_startdate not defined .<br>";
   }
   if (isset($input_props['model_startdate'])) {
      $model_startdate = $input_props['model_startdate'];
      $returnArray['error'] .= "Setting model_startdate - $model_startdate .<br>";
   }
   if (!isset($model_enddate)) {
      $model_enddate = '';
      $returnArray['error'] .= "Global model_enddate not defined .<br>";
   }
   if (isset($input_props['model_enddate'])) {
      $model_enddate = $input_props['model_enddate'];
      $returnArray['error'] .= "Setting model_enddate - $model_enddate .<br>";
   }
   
   // unless we are passed one, we implicitly assume that the standard list object is valid
   if (!is_object($model_listobj)) {
      $model_listobj = $listobject;
   }
   
   if (!is_array($unserobjects)) {
      if ($debug) {
         $returnArray['debug'] .= "Creating Blank Unser Objects array<br>";
      }
      $unserobjects = array();
       // the level in the nested hierarchy
   }
   if ($debug) {
      $returnArray['debug'] .= "Unser Objects<br>";
      $returnArray['debug'] .= print_r($unserobjects,1);
      $returnArray['debug'] .= "End Unser Objects<br>";
   }
   $cache_file_exists = 0;
   // now, check if this run has been requested with model data caching on (cache_level >= 0)
   // can also perform a check on the cache based on run-date.  If cache_level is a date, then 
   // we use that logic instead
   if ( $cache_level <> -1) {
      $returnArray['error'] .= "Evaluating intval($cache_level) = " . intval($cache_level) . "<br>";
      //if (intval($cache_level) === $cache_level) {
      if ( !(strtotime($cache_level)) ) {
         // cache_level is an integer
         $cache_type = 'level';
         $returnArray['error'] .= "Cache level is an integer<br>";
      } else {
         // cache_level is a date
         $cache_type = 'date';
         $returnArray['error'] .= "Cache level is a date<br>";
      }
   } else {
      $cache_type = 'disabled';
   }
   //error_log("Checking for $elementid - cache_level = $cache_level, cache_type = $cache_type, cache_id = $cache_id, current_level = $current_level <br>");
   $order = getElementOrder($listobject, $elementid);
   $returnArray['error'] .= "Checking element order for $elementid .<br>";
   
   switch ($cache_type) {
      case 'level':
      if ($current_level >= $cache_level) {
         $cache_file = '';
         // check for cached file
         $cache_sql = "  select output_file from scen_model_run_elements ";
         $cache_sql .= " where elementid = $elementid ";
         $cache_sql .= "    and runid = $cache_id ";
      } else {
         $cache_type = 'disabled';
         error_log("Cacheable Check: $cacheable = getElementCacheable(listobject, $elementid) <br>");
         $returnArray['error'] .= "Current level $current_level is less than cache Level $cache_level - cacheing disabled.<br>";
      }
      break;
      
      case 'date':
         $cache_file = '';
         // check for cached file
         $cache_sql = "  select output_file from scen_model_run_elements ";
         $cache_sql .= " where elementid = $elementid ";
         $cache_sql .= "    and runid = $cache_id ";
         $cache_sql .= "    and run_date >= '$cache_level' ";
         $cache_sql .= "    and starttime <= '$model_startdate' ";
         $cache_sql .= "    and endtime >= '$model_enddate' ";
      break;
      
   }
   
   // check to see if we have manually defined this as uncacheable
   $cacheable = getElementCacheable($listobject, $elementid);
   // need to check to see if this is a container with children, if it is NOT we do not want to run it as cached
   if ( ($order == 0) or (! ($cacheable == 1))) {
      $cache_type = 'disabled';
      $returnArray['error'] .= "$elementid is a $order'th order element, 'cacheable' setting = $cacheable - cacheing disabled.<br>";
   }
   //error_log("Cacheable Check: $cacheable = getElementCacheable(listobject, $elementid) <br>");
   
   if ($cache_type <> 'disabled') {
      // verify that the file exists
      $returnArray['error'] .= $cache_sql . "<br>";
      $listobject->querystring = $cache_sql;
      $listobject->performQuery();
      if ($listobject->numrows > 0) {
         $cache_file = $listobject->getRecordValue(1,'output_file');
         $fe = fopen($cache_file,'r');
         if ($fe) {
            $cache_file_exists = 1;
            fclose($fe);
            $returnArray['error'] .= "Found Cache file $cache_file for $elementid.<br>";
         } else {
            $cache_type = 'disabled';
            $returnArray['error'] .= "Cache file $cache_file does not exist - cacheing disabled.<br>";
         }
      } else {
         $cache_type = 'disabled';
         $returnArray['error'] .= "Cache file $cache_file not found - cacheing disabled.<br>";
      }
   }
      
   $returnArray['error'] .= " cache_level = $cache_level, current_level = $current_level, cache_file_exists = $cache_file_exists, cache_type = $cache_type, number of components = " . count($unserobjects) . "<br>";
   // cache_level = 0 means all except the top-most parent will be run as cached time series (if possible)
   // cache_level = 1 means all except the immediate children of this object
   // if cache_level >= 0 and current_level >= cache_level then we go ahead and make time series out of these
   //   cached time series characteristics
   // current level begins as -1 since the top-most object cannot be run from cache
   // if cache_level >= 0 then cache_level will still be greater than the first level at the first object
   // otherwise, we proceed along normal lines
   
   if ( ($cache_type <> 'disabled') and (count($unserobjects) >= 1) ) {
      //error_log("Creating cached object for $elementid <br>");
      if ($debug) {
         $returnArray['debug'] .= "Looking for cached copy of element $elementid <br>";
      }
      $returnArray['error'] .= "Retrieving cached copy of element $elementid <br>";
      // get a standalone copy (no children) of the element to be run from cache
      $obres = unSerializeSingleModelObject($elementid);
      $src_object = $obres['object'];
      // create a blank timeseriesfile object
      $thisobject = new timeSeriesFile;
      // set the timeseries file to the cache file
      $thisobject->filepath = $cache_file;
      $thisobject->setCompID($elementid);
      $thisobject->name = $src_object->name;
      $thisobject->listobject = $model_listobj;
      if (method_exists($thisobject, 'wake')) {
         $thisobject->wake();
      }
      #$returnArray['debug'] .= "<b>Original object debug status: </b>" . $thisobject->debug . "<br>";
      #$thisobject->debug = $debug;
      $thisobject->outdir = $outdir;
      $thisobject->outurl = $outurl;
      $thisobject->goutdir = $goutdir;
      $thisobject->gouturl = $gouturl;
      $thisobject->tmpdir = $tmpdir;
      $thisobject->max_memory_values = 500; // sets the default maximum memory values for these objects
      $thisobject->basedir = $basedir;
      $thisobject->shellcopy = $shellcopy;
      $thisobject->debug = $src_object->debug;

      // copy any broadcast to parent objects in the original object to the timeseries
      $j = 0;
      foreach ($src_object->processors as $proc_name => $proc_object) {
         if (is_object($proc_object)) {
            if (get_class($proc_object) == 'broadCastObject') {
               if ( ($proc_object->broadcast_hub == 'parent') and ($proc_object->broadcast_mode == 'cast') ) {
                  if (property_exists($proc_object, 'listobject')) {
                     if ($debug) {
                        $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
                     }
                     $proc_object->listobject = $model_listobj;
                  }
                  if (property_exists($proc_object, 'ucitables')) {
                     if ($debug) {
                        $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
                     }
                     $proc_object->ucitables = $ucitables;
                  }
                  if (method_exists($proc_object, 'wake')) {
                     $proc_object->wake();
                  }
                  $proc_object->outdir = $outdir;
                  $proc_object->outurl = $outurl;
                  $proc_object->goutdir = $goutdir;
                  $proc_object->gouturl = $gouturl;
                  # manually set the componentid, since these do not have a db generated ID, and they only exist in
                  # a scope that is local to the containing object, set them to be a decimal on the parent elementid
                  $proc_object->componentid = "$elementid" . "." . $j;
      
                  $thisobject->addOperator($proc_name, $proc_object, 0);
                  $returnArray['error'] .= "Adding parent cast $proc_name for $elementid <br>";
                  $j++;
               }
            }
         }
      }
            
      array_push($returnArray['cached'], $elementid);
      $unserobjects[$elementid] = $thisobject;
      
      $returnArray['error'] .= "Cached object created for $elementid <br>";
      error_log("Cached object created for $elementid <br>");
         
      // return the object
      
   } else {
      // instantiate this model to run
      if ($elementid > 0) {
         $listobject->querystring = "  select elem_xml, elemname, x(centroid(the_geom)) as xcoord, ";
         $listobject->querystring .= "    y(centroid(the_geom)) as ycoord, array_dims(elemoperators) as adims, ";
         $listobject->querystring .= "    CASE ";
         $listobject->querystring .= "       WHEN geomtype = 3 THEN asText(poly_geom)";
         $listobject->querystring .= "       WHEN geomtype = 2 THEN asText(line_geom)";
         $listobject->querystring .= "       WHEN geomtype = 1 THEN asText(point_geom)";
         $listobject->querystring .= "    END as wkt_geom, geomtype, ";
         $listobject->querystring .= "    CASE ";
         $listobject->querystring .= "       WHEN geomtype = 1 THEN box2d(point_geom) ";
         $listobject->querystring .= "       WHEN geomtype = 2 THEN box2d(line_geom) ";
         $listobject->querystring .= "       WHEN geomtype = 3 THEN box2d(poly_geom) ";
         $listobject->querystring .= "       ELSE box2d(the_geom) ";
         $listobject->querystring .= "    END as geom_extent ";
         $listobject->querystring .= " from scen_model_element ";
         $listobject->querystring .= " where elementid = $elementid ";
         if ($debug) {
            $returnArray['debug'] .= " $listobject->querystring ; <br>";
         }
         $listobject->performQuery();
         //$returnArray['error'] .= "$listobject->querystring ;<br>";

         $elem_xml = $listobject->getRecordValue(1,'elem_xml');
         $elemname = $listobject->getRecordValue(1,'elemname');
         $xcoord = $listobject->getRecordValue(1,'xcoord');
         $ycoord = $listobject->getRecordValue(1,'ycoord');
         $wkt_geom = $listobject->getRecordValue(1,'wkt_geom');
         $geomtype = $listobject->getRecordValue(1,'geomtype');
         $extent = $listobject->getRecordValue(1,'geom_extent');

         $dimstr = str_replace(']','',str_replace('[','',$listobject->getRecordValue(1,'adims')));
         if ($debug) {
            $returnArray['debug'] .= "<br>Dim: $dimstr<br>";
         }
         list($astart, $aend) = split(':', $dimstr);
      } else {
         $elem_xml = '';
         $astart = 0;
         $aend = -1;
         $elemname = '';
      }
      $opxmls = array();
      $returnArray['debug'] .= "Searching for Operators on $elemname <br>";
      for ($i = $astart; $i <= $aend; $i++) {
         if ($debug) {
            $returnArray['debug'] .= "retrieving op $i<br>";
         }
         $listobject->querystring = "  select elemoperators[$i] ";
         $listobject->querystring .= " from scen_model_element ";
         $listobject->querystring .= " where elementid = $elementid ";
         $listobject->performQuery();
         $thisxml = $listobject->getRecordValue(1,'elemoperators');
         if (str_replace('"', '', $thisxml) <> '') {
            $opxmls[$i] = $thisxml;
            if ($debug) {
               $returnArray['debug'] .= " $listobject->querystring ; <br>";
               $returnArray['debug'] .= "Op $i XML: $opxmls[$i]<br>";
            }
         }
      }

      if ($debug) {
         $returnArray['debug'] .= "Creating Unserializer<br>";
      }

      // tell the unserializer to create an object
      $options = array("complexType" => "object");
      // tell the unserializer to create an array of properties
      #$options = array("complexType" => "array");

      // create object
      $unserializer = new XML_Unserializer($options);

      if ($debug) {
         $returnArray['debug'] .= "Unserializing<br>";
      }
      // unserialize the object. Use "false" since this is not a document, "true" if it is a document
      $result = $unserializer->unserialize($elem_xml, false);
      $returnArray['elemtype'] = $unserializer->getRootName();

      if ($debug) {
         $returnArray['debug'] .= "Result of Unserializing<br>";
      }
      // dump the result
      $thisobject = $unserializer->getUnserializedData();
      // if this is the parent object, we need to vcheck for the model_startdate and model_enddate props to set in the array to pass
      if (count($unserobjects) == 0) {
         if (property_exists($thisobject, 'starttime')) {
            if ( ($model_startdate == '') or ($model_enddate == '') ) {
               $model_startdate = $thisobject->starttime;
               $model_enddate = $thisobject->endtime;
            }
         }
      }

      # make sure this is a valid object
      if (!is_object($thisobject) or !($result === true)) {
         # problem re-serializing
         $returnArray['error'] .= "Problem Un-serializing object: $elemname, ID: $elementid <br>";
         return $returnArray;
      }

      # first thing, set the component id, so that all following actions do not get goofed up
      $thisobject->setCompID($elementid);

      if (property_exists($thisobject, 'the_geom')) {
         if ($debug) {
            $returnArray['debug'] .= "Setting a geometry object on this object.<br>";
         }
         if (strlen($wkt_geom) > 0) {
            $thisobject->the_geom = $wkt_geom;
            $thisobject->geomtype = $geomtype;
            $thisobject->extent = $extent;
         } else {
            $thisobject->the_geom = 'POINT( ' . $xcoord . ' ' . $ycoord . ' )';
            $thisobject->geomtype = 1;
            $thisobject->extent = 'BOX( ' . $xcoord . ' ' . $ycoord . ' , ' . $xcoord . ' ' . $ycoord . ' )';
         }
      }

      if (property_exists($thisobject, 'listobject')) {
         if ($debug) {
            $returnArray['debug'] .= "Setting a list object on this object.<br>";
         }
         $thisobject->listobject = $model_listobj;
      }

      if (property_exists($thisobject, 'geomx')) {
         if ($debug) {
            $returnArray['debug'] .= "Setting x-coord on this object.<br>";
         }
         $thisobject->geomx = $xcoord;
      }

      if (property_exists($thisobject, 'geomy')) {
         if ($debug) {
            $returnArray['debug'] .= "Setting y-coord on this object.<br>";
         }
         $thisobject->geomy = $ycoord;
      }

      if (property_exists($thisobject, 'wdm_messagefile')) {
         if ($debug) {
            $returnArray['debug'] .= "Setting a WDM message file on this object.<br>";
         }
         $thisobject->wdm_messagefile = $wdm_messagefile;
      }

      if (property_exists($thisobject, 'wdimex_exe')) {
         if ($debug) {
            $returnArray['debug'] .= "Setting a wdimex executable path on this object.<br>";
         }
         //error_log("Setting a wdimex executable path on this object to $wdimex_exe.<br>");
         $thisobject->wdimex_exe = $wdimex_exe;
      }
      if (property_exists($thisobject, 'ucitables')) {
         if ($debug) {
            $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
         }
         $thisobject->ucitables = $ucitables;
      }

      # now, check to see if an array of properties was input, if so, apply them to this object
      # ultimately, it would be nice to be able to set values other than properties, such as 
      # sub-components, however, this is more complex

      foreach ($input_props as $this_propname => $this_propvalue) {
         $thisobject->setProp($this_propname, $this_propvalue);
      }

      if (method_exists($thisobject, 'wake')) {
         $thisobject->wake();
      }
      #$returnArray['debug'] .= "<b>Original object debug status: </b>" . $thisobject->debug . "<br>";
      #$thisobject->debug = $debug;
      $thisobject->outdir = $outdir;
      $thisobject->outurl = $outurl;
      $thisobject->goutdir = $goutdir;
      $thisobject->gouturl = $gouturl;
      $thisobject->tmpdir = $tmpdir;
      $thisobject->basedir = $basedir;
      $thisobject->shellcopy = $shellcopy;
      $unserobjects[$elementid] = $thisobject;

      if ($debug) {
         $returnArray['debug'] .= print_r($thisobject,1);
         $returnArray['debug'] .= "<br><b>Unserializing operators</b><br>";
      }
      $returnArray['error'] .= "Retrieving object sub-components, dates: $model_startdate, $model_enddate <br>";

      #return $returnArray;
      $j = 0;
      foreach ($opxmls as $thisop) {
         // unserialize the object. Use "false" since this is not a document, "true" if it is a document
         $result = $unserializer->unserialize($thisop, false);
         if ($debug) {
            $returnArray['debug'] .= "<br><b>Result of Unserializing</b><br>";
         }
         if ($result === true) {
            // dump the result
            $opobject = $unserializer->getUnserializedData();
            if (property_exists($opobject, 'listobject')) {
               if ($debug) {
                  $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
               }
               $opobject->listobject = $model_listobj;
            }
            if (property_exists($opobject, 'ucitables')) {
               if ($debug) {
                  $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
               }
               $opobject->ucitables = $ucitables;
            }
            if (method_exists($opobject, 'wake')) {
               $opobject->wake();
            }
            $opobject->outdir = $outdir;
            $opobject->outurl = $outurl;
            $opobject->goutdir = $goutdir;
            $opobject->gouturl = $gouturl;
            # manually set the componentid, since these do not have a db generated ID, and they only exist in
            # a scope that is local to the containing object, set them to be a decimal on the parent elementid
            $opobject->componentid = "$elementid" . "." . $j;
            if ($debug) {
               $returnArray['debug'] .= print_r($opobject,1);
               $returnArray['debug'] .= "<br><b>Unserializing operators</b><br>";
            }
            $thisobject->addOperator($opobject->name, $opobject, $opobject->defaultval);
            $j++;
         }
      }

      # retrieve component linkages
      $listobject->querystring = "  select src_id, src_prop, dest_prop ";
      $listobject->querystring .= " from map_model_linkages ";
      $listobject->querystring .= " where dest_id = $elementid ";
      $listobject->querystring .= "    and linktype = 1 ";
      if ($debug) {
         $returnArray['debug'] .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();

      $linkrecs = $listobject->queryrecords;
      if ($debug) {
         $returnArray['debug'] .= " Searching for Contained objects in $thisobject->name <br>";
      }
      foreach ($linkrecs as $thisrec) {
         $src_id = $thisrec['src_id'];
         if ($debug) {
            $returnArray['debug'] .= " Searching for $src_id in " . print_r(array_keys($unserobjects)) . '<br>';
         }
         if (in_array($src_id, array_keys($unserobjects))) {
            # fetch from already instantiated objects
            $linkobj = $unserobjects[$src_id];
         } else {
            // increment current_level + 1 when we call contained objects
            $returnArray['error'] .= "Unserializing element $src_id with dates $model_startdate, $model_enddate <br>";
            if ($cacheable == 0) {
               $child_cache_level = 0;
            } else {
               $child_cache_level = $cache_level;
            }
            $linkobjarray = unSerializeModelObject($src_id, array(), $model_listobj, $cache_level, $cache_id, $current_level + 1);
            $linkerror = $linkobjarray['error'];
            $linkdebug = $linkobjarray['debug'];
            foreach ($linkobjarray['complist'] as $thiselement) {
               if (!in_array($thiselement, $returnArray['complist'])) {
                  array_push($returnArray['complist'], $thiselement);
               }
            }
            foreach ($linkobjarray['cached'] as $thiselement) {
               if (!in_array($thiselement, $returnArray['cached'])) {
                  array_push($returnArray['cached'], $thiselement);
               }
            }
            $returnArray['debug'] .= $linkdebug;
            $linkobj = $linkobjarray['object'];
            if (strlen($linkerror) > 0) {
               # error in sub-object, return the message and quit
               $returnArray['error'] .= " Error instantiating sub-object $src_id :<br>";
               $returnArray['error'] .= $linkerror;
            }
         }
         if ($debug) {
            $returnArray['debug'] .= " Adding Component $linkobj->name  <br>";
         }
         $thisobject->addComponent($linkobj);
      }

      # retrieve input linkages
      $listobject->querystring = "  select src_id, src_prop, dest_prop ";
      $listobject->querystring .= " from map_model_linkages ";
      $listobject->querystring .= " where dest_id = $elementid ";
      //$listobject->querystring .= "    and linktype = 2 ";
      $listobject->querystring .= "    and linktype in ( 2, 3 ) ";
      # screen out non-set objects so as to avoid an error
      #$listobject->querystring .= "    and src_id <> -1 ";
      if ($debug) {
         $returnArray['debug'] .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();

      $linkrecs = $listobject->queryrecords;
      if ($debug) {
         $returnArray['debug'] .= " Searching for Input objects in $thisobject->name <br>";
      }
      foreach ($linkrecs as $thisrec) {
         $src_id = $thisrec['src_id'];
         $src_prop = $thisrec['src_prop'];
         $dest_prop = $thisrec['dest_prop'];
         if ($debug) {
            $returnArray['debug'] .= " Searching for $src_id in " . print_r(array_keys($unserobjects)) . '<br>';
         }
         if ($src_id <> -1) {
            if (in_array($src_id, array_keys($unserobjects))) {
               # fetch from already instantiated objects
               $linkobj = $unserobjects[$src_id];
               if ($debug) {
                  $returnArray['debug'] .= " Adding Input $linkobj->name :from unser array <br>";
               }
            } else {
               if ($debug) {
                  $returnArray['debug'] .= " Creating Input $src_id  <br>";
               }
               // now, here we could put in a switch to see if we should run the input objects that are NOT contained
               // by this object.  We could opt to have those objects instantiated as a time series with cached values 
               // from a previous model run, allowing us a more economical way of running objects that rely on inputs 
               // from other model containers, external to this one.
               $linkobjarray = unSerializeModelObject($src_id, array(), $model_listobj, $cache_level, $cache_id, $current_level);
               $linkerror = $linkobjarray['error'];
               $linkdebug = $linkobjarray['debug'];
               $returnArray['debug'] .= $linkdebug;
               foreach ($linkobjarray['complist'] as $thiselement) {
                  if (!in_array($thiselement, $returnArray['complist'])) {
                     array_push($returnArray['complist'], $thiselement);
                  }
               }
               foreach ($linkobjarray['cached'] as $thiselement) {
                  if (!in_array($thiselement, $returnArray['cached'])) {
                     array_push($returnArray['cached'], $thiselement);
                  }
               }
               $linkobj = $linkobjarray['object'];

               if (strlen($linkerror) > 0) {
                  # error in sub-object, return the message and quit
                  $returnArray['error'] .= $linkerror;
                  // return $returnArray;
               }
            }
            if ($debug) {
               $returnArray['debug'] .= " Adding Input $linkobj->name : $src_prop -gt $dest_prop <br>";
            }
            $thisobject->addInput($dest_prop, $src_prop, $linkobj);
         } else {
            $linkerror = 'NULL Linkage found';
            $linkdebug = 'NULL Linkage found';
            $returnArray['debug'] .= $linkdebug;
            $linkobj = NULL;
         }
      }
   }

   #$thisobject->setStateVar();
   $returnArray['object'] = $thisobject;
   #$debug = 0;
   return $returnArray;

}

function modelRunForm($formValues) {
   global $listobject, $debug, $scenarioid, $projectid, $seglist, $adminsetuparray;

   $innerHTML = '';

   $listobject->querystring = "  select elementid, elemname ";
   $listobject->querystring .= " from scen_model_element ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and component_type = 3 ";
   $listobject->querystring .= " order by elemname ";
   if ($debug) {
      $innerHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   $options = array();
   foreach ($listobject->queryrecords as $thisrec) {
      array_push($options, array('option'=>$thisrec['elementid'], 'label'=>$thisrec['elemname']));
   }
   if (isset($formValues['elements'])) {
      $elements = $formValues['elements'];
      $showcached = $formValues['showcached'];
   } else {
      $elements = array();
      $showcached = 1; # default to showing cachd values to prevent inadvertant re-running of the model
   }
   $innerHTML .= " <font class=\"heading1\">Select Model to Run:</font><br>";
   #$innerHTML .= print_r($listobject->queryrecords, 1);
   #$innerHTML .= print_r($formValues, 1);
   $innerHTML .= "<form name='runmodel' id='runmodel'>";
   $innerHTML .= showHiddenField('actiontype', 'runmodel', 1);
   $innerHTML .= showHiddenField('projectid', $projectid, 1);
   $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);
   #$innerHTML .= showMultiCheckBox('elements', $options, $elements, '<br>', '', 1);
   foreach ($options as $thisoption) {
      $innerHTML .= showRadioButton('elements', $thisoption['option'], $elements, "last_tab[\"modelout\"]=\"modelout_data0\"; last_button[\"modelout\"]=\"modelout_0\"; document.forms[\"runmodel\"].showcached.value = 1; xajax_showModelRunResult(xajax.getFormValues(\"runmodel\")); ", 1, 0);
      $innerHTML .= " " . $thisoption['label'] . "<br>";
   }
   //$innerHTML .= '<br>';
   //$innerHTML .= showCheckBox('showcached', 1, $showcached, '', 1, 0);
   $innerHTML .= showHiddenField('showcached', $showcached, 1);
   //$innerHTML .= " <b>Show cached model output</b><br>";
   //$innerHTML .= showGenericButton('runselectedmodel', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data0\"; last_button[\"modelout\"]=\"modelout_0\"; xajax_showModelRunResult(xajax.getFormValues(\"runmodel\")); ", 1);
   $innerHTML .= "</form>";

   return $innerHTML;

}


function modelControlForm($projectid, $scenarioid, $elementid) {
   global $listobject;
   
   $controlHTML = '';
   
   $controlHTML .= "<form name='modelcontrolform' id='modelcontrolform'>";
   $controlHTML .= showHiddenField('actiontype', 'runmodel', 1);
   $controlHTML .= showHiddenField('projectid', $projectid, 1);
   $controlHTML .= showHiddenField('scenarioid', $scenarioid, 1);
   $controlHTML .= showHiddenField('elements', $elementid, 1);
   # these two: showcached and redraw, are always set to 0, since they are only 1 when called form this screen if
   # a re-draw is requested
   $controlHTML .= showHiddenField('redraw', 0, 1);
   $controlHTML .= showHiddenField('showcached', 0, 1);
   $controlHTML .= showGenericButton('run_bgmodel', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_runModelBackground(xajax.getFormValues(\"modelcontrolform\")); ", 1);
   $controlHTML .= "<br>Run ID? (-1 means do not store as run): " . showWidthTextField('runid', -1, 6, '', 1);
   $controlHTML .= "<br>Cache Level (-1 means do not use cached values for this run): " . showWidthTextField('cache_level', -1, 6, '', 1);
   $controlHTML .= "</form>";
   
   return $controlHTML;
}

function modelRunResult($formValues) {
   global $listobject, $userid, $debug, $tmpdir, $scenarioid, $outdir, $outurl, $projectid, $seglist, $adminsetuparray;

   $innerHTML = '';
   # supresses output of listobject and stores it in a string on the object
   $listobject->show = 0;

   if (isset($formValues['elements'])) {
      $elementid = $formValues['elements'];
      $showcached = $formValues['showcached'];
   } else {
      $elementid = -1;
      $showcached = 0;
   }
   # can also receive input from a form field named "elementid"
   if (isset($formValues['elementid'])) {
      $elementid = $formValues['elementid'];
      $showcached = 1;
   }


   # format output into tabbed display object
   $taboutput = new tabbedListObject;
   $taboutput->name = 'modelout';
   $taboutput->tab_names = array('modelcontrol','runlog','graphs','reports','errorlog', 'debug');
   $taboutput->tab_buttontext = array(
      'modelcontrol'=>'Model Controls',
      'status'=>'Model Status',
      'runlog'=>'Run Log',
      'graphs'=>'Graphs',
      'reports'=>'Reports',
      'errorlog'=>'Error Log',
      'debug'=>'Debug Info'
   );
   $taboutput->init();
   $taboutput->tab_HTML['modelcontrol'] .= "<b>Model Controls:</b><br>";
   $taboutput->tab_HTML['reports'] .= "<b>Model Reports:</b><br>";
   $taboutput->tab_HTML['runlog'] .= "<b>Model Run-Log:</b><br>";
   $taboutput->tab_HTML['runlog'] .= "Initiating Model Run.<br>";
   $taboutput->tab_HTML['graphs'] .= "<b>Model Graphs:</b><br>";
   $taboutput->tab_HTML['debug'] .= "<b>Debugging Information:</b><br>";

   if ($elementid > 0) {



      if (!$showcached) {
         # set initial tab here
         #$innerHTML .= "<script language='JavaScript'>last_tab = 'runlog';</script>";
         $innerHTML .= "<div id'=modeloutput' style=\"border: 1px solid rgb(0 , 0, 0); width: 100%\" ><font class='heading1'>Model Run Output </font><br>";
         # need to store the control panel separately from the cached output, as an XML block of
         # variable settings. This will allow us to load the previous output, along with the settings that drove that
         # output, but also allow any changes to the model control panel that may have occured since the last run.
         # currently, I haven't put the ability to show settable fields for model runtime inputs, so this is kinda moot,
         # but that is coming soon....
         # alternatively, I could save the model elements after a run (should do this anyhow)
         # and then generate the model control settings from the actual model object properties
         # this would hsow the most recent settings, and any changes that have already been made
         # of course, this could be time consuming as the model controls would then have to instantiate
         # a copy of each model object before running, slow, slow, slow
         $taboutput->tab_HTML['modelcontrol'] .= "<form name='runmodelcontrol' id='runmodelcontrol'>";
         $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('actiontype', 'runmodel', 1);
         $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('projectid', $projectid, 1);
         $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('scenarioid', $scenarioid, 1);
         $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('elements', $elementid, 1);
         # these two: showcached and redraw, are always set to 0, since they are only 1 when called form this screen if
         # a re-draw is requested
         $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('redraw', 0, 1);
         $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('showcached', 0, 1);
         //$taboutput->tab_HTML['modelcontrol'] .= showGenericButton('rerun_model', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_showModelRunResult(xajax.getFormValues(\"runmodelcontrol\")); ", 1);
         $taboutput->tab_HTML['modelcontrol'] .= showGenericButton('run_bgmodel', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_runModelBackground(xajax.getFormValues(\"runmodelcontrol\")); ", 1);
         //$taboutput->tab_HTML['modelcontrol'] .= showGenericButton('redraw_button', 'Re-draw Graphs', " xajax_showRedrawGraphs(xajax.getFormValues(\"runmodelcontrol\")); show_next(\"modelout_data2\", \"modelout_2\", \"modelout\")", 1);
         $taboutput->tab_HTML['modelcontrol'] .= "</form>";
         #$debug = 1;
         $taboutput->tab_HTML['runlog'] .= "Retrieving component: $elementid <br>";

         $listobject->querystring = "select * from system_status where element_key = $elementid and element_name = 'model_run'";
         $listobject->performQuery();
         if (count($listobject->queryrecords > 0)) {
            # we will get the status, but not do anything with it quite yet, since we do not want to get involved
            # with the whole 'model dies abruptly and leaves running flag set, preventing others from running it'
            # issue
            $status = $listobject->getRecordValue(1, 'status_flag'); # flags 0 - idle, 1 - running, 2 - editing
            $listobject->querystring = " delete from system_status where element_key = $elementid and element_name = 'model_run'";
            $listobject->performQuery();
            $listobject->querystring = " insert into system_status (element_key, element_name, ";
            $listobject->querystring .= "    status_flag, status_mesg, process_ownerid ) ";
            $listobject->querystring .= " values ($elementid, 'model_run', 1, ";
            $listobject->querystring .= "    'Beginning Model Execution at ' || now(), $userid )";
            $listobject->performQuery();
         } else { 
            $listobject->querystring = " insert into system_status (element_key, element_name, ";
            $listobject->querystring .= "    status_flag, status_mesg, process_ownerid ) ";
            $listobject->querystring .= " values ($elementid, 'model_run', 1, ";
            $listobject->querystring .= "    'Beginning Model Execution at ' || now(), $userid )";
            $listobject->performQuery();
         }
         $thisobresult = unSerializeModelObject($elementid);
         $thisobject = $thisobresult['object'];
         $thisname = $thisobject->name;
         $thisobject->outdir = $outdir;
         $thisobject->outurl = $outurl;
         $taboutput->tab_HTML['debug'] .= "Model Debug Status: " . $thisobject->debug . "<br>";
         $taboutput->tab_HTML['runlog'] .= "Running component group: $thisname <br>";
         $thisobject->runModel();
error_log("runModel() Returned from calling routine.");
         $debugstring = '';
         $debugstring .= $thisobresult['debug'] . " <br>";
         $debugstring .= $thisobject->debugstring . '<br>';
error_log("Assembling Panels.");
         $taboutput->tab_HTML['runlog'] .= $thisobject->outstring . " <br>";
         $taboutput->tab_HTML['errorlog'] .= '<b>Errors:</b>' . $thisobresult['error'] . " <br>";
         $taboutput->tab_HTML['reports'] .= "Component Logging Info: <br>";
         $taboutput->tab_HTML['reports'] .= $thisobject->reportstring . " <br>";
         if (strlen($graphstring) <= 1024) {
            $taboutput->tab_HTML['graphs'] .= $thisobject->graphstring . " <br>";
         } else {
error_log("Writing graph output to file.");
            # stash the debugstring in a file, give a link to download it
            $fname = 'graph' . $thisobject->componentid . ".html";
            $floc = $outdir . '/' . $fname;
            $furl = $outurl . '/' . $fname;
            $fp = fopen ($floc, 'w');
            fwrite($fp, $graphstring);
            $taboutput->tab_HTML['graph'] .= "<a href='$furl' target=_new>Click Here to Download Graphs Info</a>";
         }
         

         if (strlen($debugstring) <= 4096) {
            $taboutput->tab_HTML['debug'] .= $debugstring . '<br>';
         } else {
error_log("Writing debug output to file.");
            # stash the debugstring in a file, give a link to download it
            $fname = 'debug' . $thisobject->componentid . ".html";
            $floc = $outdir . '/' . $fname;
            $furl = $outurl . '/' . $fname;
            $fp = fopen ($floc, 'w');
            fwrite($fp, $debugstring);
            $taboutput->tab_HTML['debug'] .= "<a href='$furl' target=_new>Click Here to Download Debug Info</a>";
         }

         $taboutput->tab_HTML['runlog'] .= "Finished.<br>";
error_log("Creating output in html form.");
         $taboutput->createTabListView();
         $innerHTML .= $taboutput->innerHTML . "</div>";
error_log("Storing mode output in database");
         $listobject->querystring = "  update scen_model_element set output_cache = '" . addslashes($innerHTML) . "'";
         $listobject->querystring .= " where elementid = $elementid ";
         $listobject->performQuery();
error_log("Done");
         $listobject->querystring = " update system_status set status_flag = 0, ";
         $listobject->querystring .= "    status_mesg = 'Model Run completed at ' || now() ";
         $listobject->querystring .= " where element_key = $elementid ";
         $listobject->querystring .= "    and element_name = 'model_run' ";
         $listobject->performQuery();
      } else {
         $innerHTML .= "<hr><i>Cached Model Output:</i></h3>" . showCachedModelOutput($elementid);
      }
   } else {
      $innerHTML .= "<b>Error: </b>No Model Selected.<br>.</div>";
   }
error_log("Returning model run output to user");

   return $innerHTML;

}

function forkModelRun($formValues) {
   global $listobject, $userid, $debug, $tmpdir, $scenarioid, $outdir, $outurl, $projectid, $seglist, $adminsetuparray;

   $innerHTML = '';
   # supresses output of listobject and stores it in a string on the object
   $listobject->show = 0;

   if (isset($formValues['elements'])) {
      $elementid = $formValues['elements'];
      $showcached = $formValues['showcached'];
   } else {
      $elementid = -1;
      $showcached = 0;
   }

   $arrOutput = array();
   if ($elementid > 0) {
      $command = "/usr/bin/php -f $basepath/test_modelrun.php $elementid";
      exec( "$command > /dev/null &", $arrOutput );
   }
}

function showCachedModelOutput($elementid) {
   global $listobject, $adminsetuparray;

   $listobject->querystring = "  select output_cache from scen_model_element";
   $listobject->querystring .= " where elementid = $elementid ";
   $listobject->performQuery();
   $innerHTML = $listobject->getRecordValue(1,'output_cache');

   return $innerHTML;
}

function showModelControlButtons($elementid, $projectid, $scenarioid) {
   $innerHTML = "<form name='runmodelcontrol' id='runmodelcontrol'>";
   $innerHTML .= showHiddenField('actiontype', 'runmodel', 1);
   $innerHTML .= showHiddenField('projectid', $projectid, 1);
   $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);
   $innerHTML .= showHiddenField('elements', $elementid, 1);
   # these two: showcached and redraw, are always set to 0, since they are only 1 when called form this screen if
   # a re-draw is requested
   $innerHTML .= showHiddenField('redraw', 0, 1);
   $innerHTML .= showHiddenField('showcached', 0, 1);
   //$innerHTML .= showGenericButton('rerun_model', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_showModelRunResult(xajax.getFormValues(\"runmodelcontrol\")); ", 1);
   $innerHTML .= showGenericButton('run_bgmodel', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_runModelBackground(xajax.getFormValues(\"runmodelcontrol\")); ", 1);
   //$innerHTML .= showGenericButton('redraw_button', 'Re-draw Graphs', " xajax_showRedrawGraphs(xajax.getFormValues(\"runmodelcontrol\")); show_next(\"modelout_data2\", \"modelout_2\", \"modelout\")", 1);
   $innerHTML .= "</form>";
   return $innerHTML;
}

function redrawGraphs($formValues) {
   global $listobject, $adminsetuparray;

   $innerHTML = '';

   $elementid = $formValues['elements'];
   $thisobresult = unSerializeModelObject($elementid);
   $thismodel = $thisobresult['object'];
   # calls the reDraw method of a model, which simply passes the message down to any contained objects that have a
   # reDraw method, to load their data from cache, and reDraw.
   #$thismodel->debugmode = 1;
   #$thismodel->cascadedebug = 1;
   #$thismodel->setDebug(1);
   $thismodel->reDraw();
   $innerHTML .= $thismodel->graphstring . " <br>";
   /*
   foreach ($thismodel->components as $thiscomp) {
      # check for graph output
      if (strlen($thiscomp->imgurl) > 0) {
         $innerHTML .= "<img src=$thiscomp->imgurl><br>";
      }
   }
   */

   #$innerHTML = 'Managed to unserialize the object!!!';

   return $innerHTML;
}

function importModelElementForm($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $debug, $usergroupids, $adminsetuparray;
   $innerHTML = "";

   if (isset($formValues['src_projectid'])) {
      $src_projectid = $formValues['src_projectid'];
   } else {
      $src_projectid = $projectid;
   }

   if (isset($formValues['src_scenarioid'])) {
      $src_scenarioid = $formValues['src_scenarioid'];
   } else {
      $src_scenarioid = $scenarioid;
   }
   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }

   # check to see if the scenarioid selected is within the source project, otherwise,
   # select the lowest numbered scenario in the newly selected project
   $listobject->querystring = "  select ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN a.scenarioid is null THEN b.minscenarioid";
   $listobject->querystring .= "    ELSE a.scenarioid ";
   $listobject->querystring .= " END as src_scenarioid ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select min(scenarioid) as minscenarioid from scenario ";
   $listobject->querystring .= "    where projectid = $src_projectid ";
   $listobject->querystring .= " ) as b left outer join ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select scenarioid from scenario ";
   $listobject->querystring .= "    where projectid = $src_projectid ";
   $listobject->querystring .= "       and scenarioid = $src_scenarioid ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " on ( 1 = 1 ) ";
   if ($debug) {
      $innerHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   $src_scenarioid = $listobject->getRecordValue(1, 'src_scenarioid');

   $disabled = 0;

   # print the opening line for the object menu
   $innerHTML .= "<form name='importelement' id='importelement'>";
   $innerHTML .= showHiddenField('actiontype', 'editelement', 1);
   $innerHTML .= showHiddenField('projectid', $projectid, 1);
   $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);

   $innerHTML .= showActiveList($listobject, "src_projectid", 'project', 'projectname','projectid', '',$src_projectid, "xajax_showImportModelElementResult(xajax.getFormValues(\"importelement\"))", 'projectname', 1, 1, $disabled) . '<br>';

   $innerHTML .= showActiveList($listobject, "src_scenarioid", 'scenario', 'scenario','scenarioid', "projectid = $src_projectid",$src_scenarioid, "xajax_showImportModelElementResult(xajax.getFormValues(\"importelement\"))", 'scenario', $debug, 1, $disabled) . '<br>';

   $listobject->querystring = "  select elementid, elemname ";
   $listobject->querystring .= " from scen_model_element ";
   $listobject->querystring .= " where scenarioid = $src_scenarioid ";
   # show model components only
   $listobject->querystring .= "    and component_type in ( 1, 3, 4) ";
   if ($debug) {
      $innerHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   $options = array();
   foreach ($listobject->queryrecords as $thisrec) {
      array_push($options, array('option'=>$thisrec['elementid'], 'label'=>$thisrec['elemname']));
   }
   if (isset($formValues['elements'])) {
      $elements = $formValues['elements'];
   } else {
      $elements = array();
   }
   #$innerHTML .= print_r($listobject->queryrecords, 1);
   #$innerHTML .= print_r($formValues, 1);
   $innerHTML .= showMultiCheckBox('elements', $options, $elements, '<br>', '', 1);
   $innerHTML .= '<br>';
   $innerHTML .= showGenericButton('import', 'Import Model Element', "xajax_showImportModelElementResult(xajax.getFormValues(\"importelement\"))", 1);

   $innerHTML .= "</form>";

   return $innerHTML;

}

function importModelElementResult($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $debug, $usergroupids, $adminsetuparray;
   $innerHTML = "";
   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }

   if (strlen($seglist) > 0) {
      $sscond = " subshedid in ( $seglist )";
   } else {
      $sscond = "(1 = 1)";
   }

   # get centroid of current group to set coordinates for imported elements
   $listobject->querystring = "  select x(centroid(extent(the_geom))) as geomx, y(centroid(extent(the_geom))) as geomy ";
   $listobject->querystring .= " from proj_subsheds ";
   $listobject->querystring .= " where $sscond ";
   if ($debug) {
      $innerHTML .= "$listobject->querystring<br>";
   }
   $listobject->performQuery();
   $geomx = $listobject->getRecordValue(1,'geomx');
   $geomy = $listobject->getRecordValue(1,'geomy');
   $centroid_wkt = "POINT($geomx $geomy)";

   $elements = $formValues['elements'];
   foreach ($elements as $elementid) {

      $innerHTML .= "Copying Element $elementid and Linkages.<br>";
      $cloneresult = cloneModelElement($scenarioid, $elementid);

      # get the new element id
      $elementid = $cloneresult['elementid'];
      if ($elementid > 0) {
         $innerHTML .= "New element id = $elementid. <br>";
         $innerHTML .= "Setting Geometry on $elementid to ($geomx , $geomy).<br>";
         $listobject->querystring = "  update scen_model_element ";
         $listobject->querystring .= " set the_geom = GeomFromText('$centroid_wkt', 4326) ";
         $listobject->querystring .= " where elementid = $elementid ";
         $listobject->performQuery();
      }
   }
   $innerHTML .= "<b>Notice:</b> Imported objects retain their linkages to other objects in the source scenario/project.  You must manually update any model linkages to connect to objects in this scenario.<br>";

   $innerHTML .= "Finished.<br>";
   return $innerHTML;

}


function copyModelGroupForm($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $debug, $usergroupids, $adminsetuparray;
   $innerHTML = "";

   # copy a model container and all contained elements

   if (isset($formValues['src_projectid'])) {
      $src_projectid = $formValues['src_projectid'];
   } else {
      $src_projectid = $projectid;
   }

   if (isset($formValues['src_scenarioid'])) {
      $src_scenarioid = $formValues['src_scenarioid'];
   } else {
      $src_scenarioid = $scenarioid;
   }
   if (isset($formValues['dest_scenarioid'])) {
      $dest_scenarioid = $formValues['dest_scenarioid'];
   } else {
      $dest_scenarioid = $scenarioid;
   }
   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }
   if (isset($formValues['showmine'])) {
      $showmine = $formValues['showmine'];
   }

   # check to see if the scenarioid selected is within the source project, otherwise,
   # select the lowest numbered scenario in the newly selected project
   $listobject->querystring = "  select ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN a.scenarioid is null THEN b.minscenarioid";
   $listobject->querystring .= "    ELSE a.scenarioid ";
   $listobject->querystring .= " END as src_scenarioid ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select min(scenarioid) as minscenarioid from scenario ";
   $listobject->querystring .= "    where projectid = $src_projectid ";
   $listobject->querystring .= " ) as b left outer join ";
   $listobject->querystring .= " ( select * from ";
   $listobject->querystring .= "   ((select -1 as scenarioid) ";
   $listobject->querystring .= "    UNION ";
   $listobject->querystring .= "    ( select scenarioid from scenario ";
   $listobject->querystring .= "      where projectid = $src_projectid ";
   $listobject->querystring .= "    )) as foo";
   $listobject->querystring .= "   where scenarioid = $src_scenarioid ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " on ( 1 = 1 ) ";
   if ($debug) {
      $innerHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   $src_scenarioid = $listobject->getRecordValue(1, 'src_scenarioid');

   $disabled = 0;

   # print the opening line for the object menu
   $innerHTML .= "<form name='importelement' id='importelement'>";
   $innerHTML .= showHiddenField('actiontype', 'editelement', 1);
   $innerHTML .= showHiddenField('projectid', $projectid, 1);
   $innerHTML .= showHiddenField('src_projectid', $projectid, 1);
   $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);

   #$innerHTML .= showCheckBox('showmine', 1, $showmine, '', 1, 0) . " Include My Models (check this to make copies of your models)<br>";

   #$innerHTML .= showActiveList($listobject, "src_projectid", 'project', 'projectname','projectid', '',$src_projectid, "xajax_showCopyModelGroupForm(xajax.getFormValues(\"importelement\"))", 'projectname', $debug, 1, $disabled) . '<br>';

   # create a scenarioid query
   $scensql = " ( (select -1 as scenarioid, 'No Scenario' as scenario) ";
   $scensql .= "  UNION ";
   $scensql .= "  ( select scenarioid, scenario from scenario ";
   $scensql .= "    where projectid = $src_projectid and ( (ownerid = $userid  and operms >= 4) ";
   $scensql .= "       or ( groupid in ($usergroupids) and gperms >= 4 ) ";
   $scensql .= "       or (pperms >= 4) ) ";
   $scensql .= "    order by scenario ) ";
   $scensql .= " ) as foo ";
   $innerHTML .= $scensql;

   $innerHTML .= "<table border=1><tr>";
   $innerHTML .= "<td valign=top width=50%><div id='src_domain'>";
   $innerHTML .= " Select Domain to Copy <b>From</b>:<br>";
   $innerHTML .= showActiveList($listobject, "src_scenarioid", $scensql, 'scenario','scenarioid', '', $src_scenarioid, "xajax_showCopyModelGroupForm(xajax.getFormValues(\"importelement\"))", '', $debug, 1, $disabled) . '<br>';
   $innerHTML .= "<b>Model groups in selected Domain:</b><br>";
   $listobject->querystring = "  select elementid, elemname ";
   $listobject->querystring .= " from scen_model_element ";
   $listobject->querystring .= " where scenarioid = $src_scenarioid ";
   # show model components only
   $listobject->querystring .= "    and component_type in ( 3) ";
   $listobject->querystring .= "    and ( ( ( pperms & 4 ) = 4 ) ";
   $listobject->querystring .= "       or (  ( (gperms & 4) = 4) and (groupid in ($usergroupids)) )  ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " order by elemname ";
   if ($debug) {
      $innerHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   $options = array();
   foreach ($listobject->queryrecords as $thisrec) {
      array_push($options, array('option'=>$thisrec['elementid'], 'label'=>$thisrec['elemname']));
   }
   if (isset($formValues['elements'])) {
      $elements = $formValues['elements'];
   } else {
      $elements = array();
   }
   #$innerHTML .= print_r($listobject->queryrecords, 1);
   #$innerHTML .= print_r($formValues, 1);
   $innerHTML .= showMultiCheckBox('elements', $options, $elements, '<br>', '', 1);
   $innerHTML .= "</div></td>";

   $innerHTML .= "<td valign=top width=50%><div id='src_domain'>";
   $innerHTML .= " Select Domain to Copy <b>To</b>:<br>";
   $innerHTML .= showActiveList($listobject, "dest_scenarioid", $scensql, 'scenario','scenarioid', '', $dest_scenarioid, "xajax_showCopyModelGroupForm(xajax.getFormValues(\"importelement\"))", '', $debug, 1, $disabled) . '<br>';
   $innerHTML .= "<b>Model groups in destination Domain:</b>";
   $listobject->querystring = "  select elemname ";
   $listobject->querystring .= " from scen_model_element ";
   $listobject->querystring .= " where scenarioid = $dest_scenarioid ";
   # show model components only
   $listobject->querystring .= "    and component_type in ( 3) ";
   $listobject->querystring .= "    and ( ( ( pperms & 4 ) = 4 ) ";
   $listobject->querystring .= "       or (  ( (gperms & 4) = 4) and (groupid in ($usergroupids)) )  ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " order by elemname ";
   if ($debug) {
      $innerHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();
   $innerHTML .= "<ul>";
   foreach ($listobject->queryrecords as $thisrec) {
      $innerHTML .= "<li style='list-style-type: circle'>" . $thisrec['elemname'];
   }
   $innerHTML .= "</ul>";
   $innerHTML .= "</div></td>";
   $innerHTML .= "</tr></table>";

   $innerHTML .= '<br>';
   $innerHTML .= showGenericButton('import', 'Copy Selected Model Group(s)', "xajax_showCopyModelGroupResult(xajax.getFormValues(\"importelement\"))", 1);

   $innerHTML .= "</form>";

   return $innerHTML;

}


function copyModelGroupForm2($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $debug, $usergroupids, $adminsetuparray;
   $innerHTML = "";

   # copy a model container and all contained elements

   if (isset($formValues['src_projectid'])) {
      $src_projectid = $formValues['src_projectid'];
   } else {
      $src_projectid = $projectid;
   }

   if (isset($formValues['src_scenarioid'])) {
      $src_scenarioid = $formValues['src_scenarioid'];
   } else {
      $src_scenarioid = $scenarioid;
   }
   if (isset($formValues['dest_scenarioid'])) {
      $dest_scenarioid = $formValues['dest_scenarioid'];
   } else {
      $dest_scenarioid = $scenarioid;
   }
   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }
   if (isset($formValues['showmine'])) {
      $showmine = $formValues['showmine'];
   }

   # check to see if the scenarioid selected is within the source project, otherwise,
   # select the lowest numbered scenario in the newly selected project
   $listobject->querystring = "  select ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN a.scenarioid is null THEN b.minscenarioid";
   $listobject->querystring .= "    ELSE a.scenarioid ";
   $listobject->querystring .= " END as src_scenarioid ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select min(scenarioid) as minscenarioid from scenario ";
   $listobject->querystring .= "    where projectid = $src_projectid ";
   $listobject->querystring .= " ) as b left outer join ";
   $listobject->querystring .= " ( select * from ";
   $listobject->querystring .= "   ((select -1 as scenarioid) ";
   $listobject->querystring .= "    UNION ";
   $listobject->querystring .= "    ( select scenarioid from scenario ";
   $listobject->querystring .= "      where projectid = $src_projectid ";
   $listobject->querystring .= "    )) as foo";
   $listobject->querystring .= "   where scenarioid = $src_scenarioid ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " on ( 1 = 1 ) ";
   if ($debug) {
      $innerHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   $src_scenarioid = $listobject->getRecordValue(1, 'src_scenarioid');
   $sel_html = showHierarchicalSelectMenu($listobject, 'select', 'importelement', 'elements', $projectid, -1, $userid, $usergroupids, $debug);
   $dest_html = showHierarchicalSelectMenu($listobject, 'multiselect', 'destinationelement', 'dest_element', $projectid, -1, $userid, $usergroupids, $debug);
   $disabled = 0;

   # print the opening line for the object menu
   $innerHTML .= "<form name='importelement' id='importelement'>";
   $innerHTML .= showHiddenField('actiontype', 'editelement', 1);
   $innerHTML .= showHiddenField('projectid', $projectid, 1);
   $innerHTML .= showHiddenField('src_projectid', $projectid, 1);
   $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);

   
   $innerHTML .= "<table border=1><tr>";
   $innerHTML .= "<td valign=top> Select Items to Copy:<br><div id='src_domain' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 360px; width: 240px; display: block;  background: #eee9e9;\">";
   
   $innerHTML .= $sel_html;
   $innerHTML .= "</div></td>";
   $innerHTML .= "<td valign=top> Select Domain to Copy <b>To</b>:<br><div id='dest_domain' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 360px; width: 240px; display: block;  background: #eee9e9;\">";
   $innerHTML .= "test Dest" . $dest_html;
   $innerHTML .= "</div></td>";
   $innerHTML .= "</tr></table>";

   $innerHTML .= '<br>';
   $innerHTML .= showGenericButton('import', 'Copy Selected Model Group(s)', "xajax_showCopyModelGroupResult(xajax.getFormValues(\"importelement\"))", 1);

   $innerHTML .= "</form>";

   return $innerHTML;

}

function copyModelGroupResult($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $debug, $usergroupids, $adminsetuparray;
   $innerHTML = "";
   
   $projectid = $formValues['projectid'];
   $scenarioid = $formValues['scenarioid'];
   $dest_scenarioid = $formValues['dest_scenarioid'];
   $thislevel_values = $formValues; # creates a copy for iterative calling of this routine, replacing the dest_parent & elid
   if (!($dest_scenarioid > 0)) {
      $innerHTML .= "<b>Error:</b> No destination domain was selected.<br>";
   }
   $elements = $formValues['elements'];
   $innerHTML .= "Elements submitted for cloning: " . print_r($elements, 1) . "<br>";
   if (isset($formValues['dest_parent'])) {
      # this is supposed to go underneath another contianer in this scenario
      $dest_parent = $formValues['dest_parent'];
      $innerHTML .= "Target parent $dest_parent submitted<br>";
   } else {
      $dest_parent = -1;
      $innerHTML .= "No Target parent submitted<br>";
      //$innerHTML .= print_r($formValues,1) . "<br>";
   }

   foreach ($elements as $elementid) {
      # get a list of elements in the current group
      $listobject->querystring = "  select src_id ";
      $listobject->querystring .= " from map_model_linkages ";
      $listobject->querystring .= " where dest_id = $elementid ";
      # get only link type 1, which is contained object, other linkages will be gotten later
      $listobject->querystring .= "    and linktype = 1 ";
      if ($debug) {
         $innerHTML .= "<b>debug:</b> get a list of elements in the current group <br>";
         $innerHTML .= "$listobject->querystring<br>";
      }
      $listobject->performQuery();
      $contained = $listobject->queryrecords;

      # now, clone the container first
      $cloneresult = cloneModelElement($dest_scenarioid, $elementid);
      $innerHTML .= $cloneresult['innerHTML'] . '<br>';
      $cid = $cloneresult['elementid'];
      if ($dest_parent > 0) {
         $innerHTML .= "New object group $cid inserted underneath $dest_parent <br>";
         $innerHTML .= createObjectLink($projectid, $dest_scenarioid, $cid, $dest_parent, 1);
      }
      if ($debug) {
         $innerHTML .= "<b>debug:</b> Debug info from cloneModelElement($dest_scenarioid, $elementid); <br>";
         $innerHTML .= $cloneresult['debug'] . '<br>';
      }
      $new_elems = array();
      $new_elems[$elementid]['new_id'] = $cid;
      $new_elems[$elementid]['old_id'] = $elementid;
      $new_ids = array();
      $old_ids = array();
      array_push($new_ids, $cid);
      array_push($old_ids, $elementid);

      foreach ($contained as $thisel) {
         $elid = $thisel['src_id'];
         $innerHTML .= "Copying Element $elid and Linkages to scenario $dest_scenarioid .<br>";
         $debug = 1;
         $cloneresult = cloneModelElement($dest_scenarioid, $elid);
         if ($debug) {
            $innerHTML .= "<b>debug:</b> Debug info from cloneModelElement($dest_scenarioid, $elid); <br>";
            $innerHTML .= $cloneresult['debug'] . '<br>';
         }
         $innerHTML .= $cloneresult['innerHTML'] . '<br>';
         $debug = 0;

         # get the new element id
         $newid = $cloneresult['elementid'];
         if ($newid > 0) {
            $new_elems[$elid]['new_id'] = $newid;
            $new_elems[$elid]['old_id'] = $elid;
            array_push($new_ids, $newid);
            array_push($old_ids, $elid);
         } else {
            $innerHTML .= "<b>Error:<b> Problem copying elementid $elid .<br>";
         }
      }

      $nlist = join(',', array_values($new_ids));
      $olist = join(',', array_values($old_ids));

      # OK, now we have a list of all the new element names,
      # so we need to iterate through them and re-create all linkages
      $listobject->querystring = "  select src_id, dest_id, linktype, src_prop, dest_prop ";
      $listobject->querystring .= " from map_model_linkages ";
      $listobject->querystring .= " where dest_id in ( $elist ) ";
      if ($debug) {
         $innerHTML .= "$listobject->querystring<br>";
      }
      $listobject->performQuery();

      $old_links = $listobject->queryrecords;

      # when cloning, the destination object is the new ID, but the link is made to the old ID, therefore
      # we need to go through the newly created linkages and update the links so that the source object
      # is changed to be the newly created source object
      foreach ($old_ids as $thislink) {
         $src_id = $thislink;
         $new_src = $new_elems[$src_id]['new_id'];
         $listobject->querystring = "  update map_model_linkages ";
         $listobject->querystring .= "    set src_id = $new_src ";
         $listobject->querystring .= " where src_id = $src_id ";
         $listobject->querystring .= "    AND dest_id in ( $nlist ) ";
         if ($debug) {
            $innerHTML .= "$listobject->querystring ; <br> ";
         }
         $listobject->performQuery();
      }

   }
   $innerHTML .= "<b>Notice:</b> Copying a model container will create NEW copies of all linked entities, therefore, any updates to these objects will persist ONLY within the context of the NEW model container.<br>";

   $innerHTML .= "Finished.<br>";
   $retarr['innerHTML'] = $innerHTML;
   $retarr['element_map'] = $new_elems;
   return $retarr;

}



function copyModelGroupFull($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $debug, $usergroupids, $adminsetuparray;
   $innerHTML = "";
   
   $projectid = $formValues['projectid'];
   $scenarioid = $formValues['scenarioid'];
   $dest_scenarioid = $formValues['dest_scenarioid'];
   $retarr = array();
   
   $thislevel_values = $formValues; # creates a copy for iterative calling of this routine, replacing the dest_parent & elid
   if (!($dest_scenarioid > 0)) {
      $innerHTML .= "<b>Error:</b> No destination domain was selected.<br>";
   }
   $elements = $formValues['elements'];
   if (isset($formValues['dest_parent'])) {
      # this is supposed to go underneath another contianer in this scenario
      $dest_parent = $formValues['dest_parent'];
      $innerHTML .= "Target parent $dest_parent submitted<br>";
   } else {
      $dest_parent = -1;
      $innerHTML .= "No Target parent submitted<br>";
      //$innerHTML .= print_r($formValues,1) . "<br>";
   }

   foreach ($elements as $elementid) {
      # get a list of elements in the current group
      $listobject->querystring = "  select src_id ";
      $listobject->querystring .= " from map_model_linkages ";
      $listobject->querystring .= " where dest_id = $elementid ";
      # get only link type 1, which is contained object, other linkages will be gotten later
      $listobject->querystring .= "    and linktype = 1 ";
      if ($debug) {
         $innerHTML .= "<b>debug:</b> get a list of elements in the current group <br>";
         $innerHTML .= "$listobject->querystring<br>";
      }
      $listobject->performQuery();
      $contained = $listobject->queryrecords;

      # now, clone the container first
      $cloneresult = cloneModelElement($dest_scenarioid, $elementid);
      $cid = $cloneresult['elementid'];
      $retarr['elementid'] = $cid;
      if ($dest_parent > 0) {
         $innerHTML .= "New object group $cid inserted underneath $dest_parent <br>";
         $innerHTML .= createObjectLink($projectid, $dest_scenarioid, $cid, $dest_parent, 1);
      }
      if ($debug) {
         $innerHTML .= "<b>debug:</b> Debug info from cloneModelElement($dest_scenarioid, $elementid); <br>";
         $innerHTML .= $cloneresult['debug'] . '<br>';
      }
      $new_elems = array();
      $new_elems[$elementid]['new_id'] = $cid;
      $new_elems[$elementid]['old_id'] = $elementid;
      $new_ids = array();
      $old_ids = array();
      array_push($new_ids, $cid);
      array_push($old_ids, $elementid);

      foreach ($contained as $thisel) {
         $elid = $thisel['src_id'];
         $innerHTML .= "Copying Element $elid and Linkages to scenario $dest_scenarioid .<br>";
         $local_formvalues = $formValues;
         $local_formvalues['elements'] = array($elid);
         //$local_formvalues['dest_parent'] = $cid;
         $local_formvalues['dest_parent'] = -1; # set this to -1 because in the cloning process any children links are created
         $cloneresult = copyModelGroupFull($local_formvalues);
         
         if ($debug) {
            $innerHTML .= "<b>debug:</b> Debug info from cloneModelElement($dest_scenarioid, $elid); <br>";
            $innerHTML .= $cloneresult['debug'] . '<br>';
         }

         # get the new element id
         $newid = $cloneresult['elementid'];
         if ($newid > 0) {
            $new_elems[$elid]['new_id'] = $newid;
            $new_elems[$elid]['old_id'] = $elid;
            array_push($new_ids, $newid);
            array_push($old_ids, $elid);
         } else {
            $innerHTML .= "<b>Error:<b> Problem copying elementid $elid .<br>";
         }
      }

      $nlist = join(',', array_values($new_ids));
      $olist = join(',', array_values($old_ids));

      # when cloning, the destination object is the new ID, but the link is made to the old ID, therefore
      # we need to go through the newly created linkages and update the links so that the source object
      # is changed to be the newly created source object
      foreach ($old_ids as $thislink) {
         $src_id = $thislink;
         $new_src = $new_elems[$src_id]['new_id'];
         $listobject->querystring = "  update map_model_linkages ";
         $listobject->querystring .= "    set src_id = $new_src ";
         $listobject->querystring .= " where src_id = $src_id ";
         $listobject->querystring .= "    AND dest_id in ( $nlist ) ";
         if ($debug) {
            $innerHTML .= "$listobject->querystring ; <br> ";
         }
         $listobject->performQuery();
      }

   }
   $innerHTML .= "<b>Notice:</b> Copying a model container will create NEW copies of all linked entities, therefore, any updates to these objects will persist ONLY within the context of the NEW model container.<br>";

   $innerHTML .= "Finished.<br>";
   $retarr['innerHTML'] = $innerHTML;
   $retarr['element_map'] = $new_elems;
   return $retarr;

}


function cloneModelElement($scenarioid, $elementid, $activecontainerid = -1, $copylinks = 1, $debug = 0) {
   global $listobject, $projectid, $userid, $usergroupids, $adminsetuparray;

   $innerHTML = '';
   
   // make sure that the SERIAL is set properly
   $listobject->querystring = "SELECT max(elementid) as maxel from scen_model_element ";
   $listobject->performQuery();
   $listobject->show = 0;
   $lastelid = $listobject->getRecordValue(1,'maxel') + 1;
   $listobject->querystring = "SELECT setval('scen_model_element_elementid_seq', $lastelid) ";
   $listobject->performQuery();

   $perms = getScenElementPerms($listobject, $elementid, $userid, $usergroupids, $debug);
   $listobject->querystring = " select groupid from users where userid = $userid ";
   if ($debug) {
      $innerHTML .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();
   $groupid = $listobject->getRecordValue(1,'groupid');
   if (!($perms && 4)) {
      # check for read access
      $innerHTML .= "<b>Error: </b> You do not have read access to this element (perms = $perms).<br>";
      $outarr['elementid'] = -1;
   } else {
      $listobject->querystring = "  insert into scen_model_element(scenarioid, modelid, elemname, custom1, custom2, ";
      $listobject->querystring .= "    file_based, elem_xml, elem_path, objectclass, the_geom, elemprops, cacheable, ";
      $listobject->querystring .= "    eleminputs, groupid, operms, gperms, pperms, elemcomponents, elemoperators, ";
      $listobject->querystring .= "    component_type, ownerid, geomtype, poly_geom, point_geom, line_geom ) ";
      $listobject->querystring .= " select $scenarioid, modelid, elemname || ' (copy)', custom1, custom2, file_based, ";
      $listobject->querystring .= "    elem_xml, elem_path, objectclass, the_geom, elemprops, cacheable, ";
      $listobject->querystring .= "    eleminputs, $groupid, 7, gperms, pperms, elemcomponents, elemoperators, ";
      $listobject->querystring .= "    component_type, $userid, geomtype, poly_geom, point_geom, line_geom ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      if ($debug) {
         $innerHTML .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $listobject->querystring = "  select CURRVAL(pg_get_serial_sequence('scen_model_element', 'elementid')) as elid ";
      if ($debug) {
         $innerHTML .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $newelementid = $listobject->getRecordValue(1,'elid');;
      $outarr['elementid'] = $newelementid;
      if ($copylinks) {
         $listobject->querystring = "  insert into map_model_linkages (  projectid, scenarioid, linktype, ";
         $listobject->querystring .= "    src_id, dest_id, src_prop, dest_prop ) ";
         $listobject->querystring .= " select $projectid, $scenarioid, linktype, src_id, $newelementid, src_prop,";
         $listobject->querystring .= "    dest_prop  ";
         $listobject->querystring .= " from map_model_linkages ";
         $listobject->querystring .= " where dest_id = $elementid ";
         //$listobject->querystring .= " and linktype <> 1 "; // do NOT clone the containment linkages since this violates the one-container rule
         if ($debug) {
            $innerHTML .= " $listobject->querystring ; <br>";
         }
         $listobject->performQuery();
      }
      # add a linkage to the current model container if this is set
      //error_log("Linking new child $newelementid to parent $activecontainerid ");
      if ($activecontainerid > 0) {
         createObjectLink($projectid, $scenarioid, $newelementid, $activecontainerid, 1);
      }
   }
   $outarr['innerHTML'] = $innerHTML;
   return $outarr;
}

function deleteModelElement($elementid, $debug = 0) {
   global $listobject, $projectid, $userid, $usergroupids, $adminsetuparray;

   $innerHTML = '';

   $perms = getScenElementPerms($listobject, $elementid, $userid, $usergroupids, $debug);
   if (!($perms && 2)) {
      # check for write access
      $innerHTML .= "<b>Error: </b> You do not have delete access to this element.<br>";
      $outarr['elementid'] = -1;
   } else {
      # get list of objects that are contained ONLY by this object, delete them
      # old school rules allowed for a single object to be contained by multiple models
      # while this is not encourage, we can at least prevent unintended deletions
      $listobject->querystring = "  select src_id from map_model_linkages ";
      $listobject->querystring .= " where linktype = 1 ";
      $listobject->querystring .= "    and dest_id = $elementid ";
      $listobject->querystring .= "    and src_id not in ";
      $listobject->querystring .= "       ( select src_id ";
      $listobject->querystring .= "         from map_model_linkages ";
      $listobject->querystring .= "         where linktype = 1 ";
      $listobject->querystring .= "            and  dest_id <> $elementid ";
      $listobject->querystring .= "    ) ";
      if ($debug) {
         $innerHTML .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $drecs = $listobject->queryrecords;
      foreach ($drecs as $thisdel) {
         $subdel = deleteModelElement($thisdel['src_id']);
         $innerHTML .= $subdel['innerHTML'];
      }
      
      $listobject->querystring = "  delete from scen_model_element ";
      $listobject->querystring .= " where ( elementid = $elementid ) ";
      if ($debug) {
         $innerHTML .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $listobject->querystring = "  select CURRVAL(pg_get_serial_sequence('scen_model_element', 'elementid')) as elid ";
      if ($debug) {
         $innerHTML .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $newelementid = $listobject->getRecordValue(1,'elid');;
      $outarr['elementid'] = $newelementid;
      $listobject->querystring = "  delete from map_model_linkages  ";
      $listobject->querystring .= " where dest_id = $elementid ";
      $listobject->querystring .= "    or src_id = $elementid ";
      if ($debug) {
         $innerHTML .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
   }
   $outarr['innerHTML'] = $innerHTML;
   return $outarr;
}

function generateTabModelOutput($thisobject, $projectid, $scenarioid, $elementid) {
   # format output into tabbed display object
   $taboutput = new tabbedListObject;
   $taboutput->name = 'modelout';
   $taboutput->tab_names = array('modelcontrol','runlog','graphs','reports','errorlog', 'debug');
   $taboutput->tab_buttontext = array(
   'modelcontrol'=>'Model Controls',
   'runlog'=>'Run Log',
   'graphs'=>'Graphs',
   'reports'=>'Reports',
   'errorlog'=>'Error Log',
   'debug'=>'Debug Info'
   );
   $taboutput->init();
   $taboutput->tab_HTML['modelcontrol'] .= "<b>Model Controls:</b><br>";
   $taboutput->tab_HTML['reports'] .= "<b>Model Reports:</b><br>";
   $taboutput->tab_HTML['runlog'] .= "<b>Model Run-Log:</b><br>";
   $taboutput->tab_HTML['runlog'] .= "Initiating Model Run.<br>";
   $taboutput->tab_HTML['graphs'] .= "<b>Model Graphs:</b><br>";
   $taboutput->tab_HTML['debug'] .= "<b>Debugging Information:</b><br>";

   $taboutput->tab_HTML['modelcontrol'] .= "<form name='runmodelcontrol' id='runmodelcontrol'>";
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('actiontype', 'runmodel', 1);
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('projectid', $projectid, 1);
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('scenarioid', $scenarioid, 1);
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('elements', $elementid, 1);
   # these two: showcached and redraw, are always set to 0, since they are only 1 when called form this screen if
   # a re-draw is requested
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('redraw', 0, 1);
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('showcached', 0, 1);
   //$taboutput->tab_HTML['modelcontrol'] .= showGenericButton('rerun_model', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_showModelRunResult(xajax.getFormValues(\"runmodelcontrol\")); ", 1);
   $taboutput->tab_HTML['modelcontrol'] .= showGenericButton('run_bgmodel', 'Run In Background', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_runModelBackground(xajax.getFormValues(\"runmodelcontrol\")); ", 1);
   //$taboutput->tab_HTML['modelcontrol'] .= showGenericButton('redraw_button', 'Re-draw Graphs', " xajax_showRedrawGraphs(xajax.getFormValues(\"runmodelcontrol\")); show_next(\"modelout_data2\", \"modelout_2\", \"modelout\")", 1);
   $taboutput->tab_HTML['modelcontrol'] .= "</form>";
   #$debug = 1;
   $taboutput->tab_HTML['runlog'] .= "Retrieving component: $elementid <br>";
   
   $thisname = $thisobject->name;
   
   $taboutput->tab_HTML['debug'] .= "Model Debug Status: " . $thisobject->debug . "<br>";
   $taboutput->tab_HTML['runlog'] .= "Running component group: $thisname <br>";
   
   error_log("runModel() Returned from calling routine.");
   $debugstring = '';
   error_log("Assembling Panels.");
   $taboutput->tab_HTML['runlog'] .= $thisobject->outstring . " <br>";
   $taboutput->tab_HTML['errorlog'] .= '<b>Errors:</b>' . $thisobresult['error'] . " <br>";
   if (strlen($thisobject->errorstring) <= 4096) {
      $taboutput->tab_HTML['errorlog'] .= $thisobject->errorstring . " <br>";
   } else {
      error_log("Writing errors to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'error' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, "Component Logging Info: <br>");
      fwrite($fp, $thisobject->errorstring . " <br>");
      $taboutput->tab_HTML['errors'] .= "<a href='$furl' target=_new>Click Here to Download Model Error Info</a>";
   }
   if (strlen($thisobject->reportstring) <= 4096) {
      $taboutput->tab_HTML['reports'] .= "Component Logging Info: <br>";
      $taboutput->tab_HTML['reports'] .= $thisobject->reportstring . " <br>";
   } else {
      error_log("Writing reports to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'report' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, "Component Logging Info: <br>");
      fwrite($fp, $thisobject->reportstring . " <br>");
      $taboutput->tab_HTML['reports'] .= "<a href='$furl' target=_new>Click Here to Download Model Reporting Info</a>";
   }
   $taboutput->tab_HTML['graphs'] .= $thisobject->graphstring . " <br>";

   $outdir = $thisobject->outdir;
   $outurl = $thisobject->outurl;
   if (strlen($thisobject->debugstring) <= 4096) {
      $taboutput->tab_HTML['debug'] .= $thisobresult['debug'] . " <br>";
      $taboutput->tab_HTML['debug'] .= $thisobject->debugstring . '<br>';
   } else {
      error_log("Writing debug output to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'debug' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, $thisobresult['debug'] . " <br>");
      fwrite($fp, $thisobject->debugstring . '<br>');
      $taboutput->tab_HTML['debug'] .= "<a href='$furl' target=_new>Click Here to Download Debug Info</a>";
   }

   $taboutput->tab_HTML['runlog'] .= "Finished.<br>";
   error_log("Creating output in html form.");
   $taboutput->createTabListView();
   $innerHTML .= $taboutput->innerHTML . "</div>";
   return $innerHTML;
}


function showAnalysisWindow($formValues, $thisobject=-1) {
   global $session_db, $outdir, $listobject;
   $robj = array();
   $robj['innerHTML'] = '';
   
   $elementid = $formValues['elementid'];
   if (isset($formValues['load_table'])) {
      $load_table = $formValues['load_table'];
   } else {
      $load_table = 0;
   }
   if (isset($formValues['pan_graph'])) {
      $pan_graph = $formValues['pan_graph'];
   } else {
      $pan_graph = 0;
   }
   if (isset($formValues['runid'])) {
      $runid = $formValues['runid'];
   } else {
      $runid = -1;
   }
   // uncomment this to force all files to be loaded automatically
   //$load_table = 1;
   //$robj['innerHTML'] = print_r($_POST,1) . "<br>";
   
   if (!(is_object($thisobject))) {
      $unser = unserializeSingleModelObject($elementid);
      $thisobject = $unser['object'];
      $thisobject->listobject = $listobject;
   }
   if (!(get_class($thisobject) == 'PEAR_Error')) {
      $thisobject->setDataColumnTypes();
      
      $form_name = "agridform_$elementid";
      $robj['innerHTML'] .= "<form name='$form_name' id='$form_name'>";
      $robj['innerHTML'] .= "<input type='hidden' name='divname' value='agrid_$elementid'>";
      $robj['innerHTML'] .= "<input type='hidden' name='elementid' value='$elementid'>";
      // show selector for stored model runs
      // eventuially we will want them to be able to specify a runid, for some multiple scenario action
      // then , we will look and see if the data has recently been updated.
      //$runid = -1; // this should hold the run scenario where data is stored such as the file name and so forth
      $robj['innerHTML'] .= showActiveList($listobject, 'runid', 'scen_model_run_elements', 'runid', 'runid', " elementid = $elementid ", $runid, "", 'runid', $debug, 1, 0);

      $sinfo = checkSessionTable($thisobject, $elementid, $runid);
      $table_exists = $sinfo['table_exists'];
      $file_exists = $sinfo['file_exists'];

      if (!$table_exists and !$load_table) {
         if ($file_exists) {
            $fsize = $sinfo['file_size'];
            if ($fsize > 1048576) {
               $fsize_fmt = round($fsize / 1048576.0,2) . ' MB';
            } else {
               $fsize_fmt = round($fsize / 1024.0,1) . ' kB';
            }
            $robj['innerHTML'] .= showCheckBox('load_table', 1, $load_table, '', 1, 0);
            $robj['innerHTML'] .= "A log file exists for this most recent model run.  Check this box and hit the 'Load Table' button to load this file into database memory - this will keep this data available until you log out of your modeling session. <font size=-1>(note:  The file size is $fsize_fmt - it takes about 5 seconds per MegaByte to load the file into a temporary table)</font>";
            $robj['innerHTML'] .= "<br><center>" . showGenericButton('load_file','Load Table', "xajax_refreshAnalysisWindow(xajax.getFormValues(\"$form_name\")) ; ", 1, 0) . "</center>";
         } else {
            $robj['innerHTML'] .= "There is no recent model run log file for this object, thus no data can be analyzed.  Make sure that 'Store Run Data in Text File?' is set to 'True' in this object's 'General Properties' panel, and run your model again.";
         }
      } else {   
     //    $robj['innerHTML'] .= "loading Table - variable set to $load_table <br>";
         $tableinfo = loadSessionTable($thisobject, $elementid, $runid);
         $session_table = $tableinfo['session_table'];
         $robj['innerHTML'] .= $tableinfo['innerHTML'];
         $qwiz = showAnalysisQueryWizard($formValues, $session_table, $form_name);
         $robj['innerHTML'] .= $qwiz['innerHTML'];
         $qqq = $qwiz['sql'];
         $robj['innerHTML'] .= showCheckBox('pan_graph', 1, $pan_graph, '', 1, 0);
         $robj['innerHTML'] .= " <b>Check This Box to Force Graph to Pan with Results Page<br>";
         //$res = showAnalysisGrid($formValues, $session_table, $form_name);
         $res = showAnalysisGrid($formValues, "($qqq) as foo", $form_name);
         $robj['innerHTML'] .= $res['innerHTML'];
         $subquery = $res['subquery'];
         $robj['innerHTML'] .= "$subquery <br>";
         // if there are graph components on this object, set the log to be the results of the query 
         // and regenerate the graphs, showing them below this query grid
         $obclass = get_class($thisobject);
         $graphclasses = array('graphObject', 'giniGraph', 'flowDurationGraph');
         if (in_array($obclass, $graphclasses)) {
            if ($pan_graph) {
               // we want to pan with the visible result list
               $qqq = $subquery;
            }
            //$robj['innerHTML'] .= "<b>This is a graph class.  Can show graphs to reflect the sub-query results</b><br>";
            $session_db->querystring = "create temp table tmp_graph_$elementid as " . $qqq;
            $thisobject->dbtblname = "tmp_graph_$elementid";
            $session_db->tablename = $thisobject->dbtblname;
            $session_db->performQuery();
            if ($debug) {
               $robj['innerHTML'] .= "$session_db->querystring <br>";
            }
            $thisobject->log2db = 1;
            $thisobject->logLoaded = 1;
            $thisobject->logRetrieved = 0;
            $thisobject->listobject = $session_db;
            //$thisobject->debug = 1;
            $robj['innerHTML'] .= "Redrawing <br>";
            $robj['innerHTML'] .= "Graph Scale: " . $thisobject->scale . " <br>";
            $thisobject->reDraw();
            //$thisobject->debug = 0;
            //$robj['innerHTML'] .= $thisobject->graphstring;
            if ($obclass = 'giniGraph') {
               foreach ($thisobject->processors as $thisproc) {
                  if (get_class($thisproc) == 'graphComponent') {
                     $robj['innerHTML'] .= $thisproc->name . ", Gini Coeff = " . $thisproc->value . "<br>";
                  }
                  
               }
            }
            $robj['innerHTML'] .= "<img src='$thisobject->imgurl'>";
            //$robj['innerHTML'] .= "Finished <br>";
            if ($debug) {
               $robj['innerHTML'] .= "<hr>" . $thisobject->debugstring;
               $robj['innerHTML'] .= "<hr>" . $session_db->error;
            }
         }

      }
   } else {
      $robj['innerHTML'] .= "<b>Notice: </b>Blank object, can not show analysis window until object is saved.<br>";
      $robj['innerHTML'] .= $thisobject->getMessage() . "<br>";
   }
   

   $robj['innerHTML'] .= "</form>";
      
      
   return $robj;
   
}

function checkSession() {
   global $session_db;
   $session_db->querystring = " select count(*) from sessions where session_id = '" . session_id() . "'";
   error_log("Session Query: " . $session_db->querystring);
   $session_db->performQuery();
   $num = $session_db->getRecordValue(1,'count');
   if ($num == 0) {
      $session_db->querystring = " insert into sessions ( session_id ) values ( '" . session_id() . "' )";
      error_log($session_db->querystring);
      $session_db->performQuery();
   }
}

function getSessionTableNames($thisobject, $elementid, $runid = -1, $data_element = '') {
   global $session_db, $outdir, $listobject;
   
   // check for session existence
   $innerHTML = '';
   checkSession();
   $rm = 0;
   // make table name
   if (strlen($data_element) == 0) {
      //$robj['innerHTML'] .= "Session Table Name: $session_table <br>";
      /*
      if ($runid > 0) {
         // this is the master log table for this element
         $session_table = session_id() . "_$runid" . "_$elementid";
         $listobject->querystring = "  select output_file, run_date from scen_model_run_elements where runid = $runid and elementid = $elementid";
   error_log("Session Query: " . $listobject->querystring);
         $listobject->performQuery();
         if (count($listobject->queryrecords) > 0) {
            $filename = $listobject->getRecordValue(1,'output_file');
            $run_date = $listobject->getRecordValue(1,'run_date');
         } else {
            $filename = $outdir . "/runlog" . $runid . "." . $elementid . ".log";
            $innerHTML .= "Failed to locate run record.<br>";
            $rm = 1;
         }
      } else {
         // this is the master log table for this element
         $session_table = session_id() . "_$elementid";
         $filename = $outdir . "/lastlog" . $elementid . ".log";
      }
      */
      
      // this is the master log table for this element
      if ($runid >= 0) {
         $session_table = session_id() . "_$runid" . "_$elementid";
      } else {
         $session_table = session_id() . "_$elementid";
      }
      $listobject->querystring = "  select output_file, run_date from scen_model_run_elements where runid = $runid and elementid = $elementid";
      //error_log("Session Query: " . $listobject->querystring);
      $listobject->performQuery();
      if (count($listobject->queryrecords) > 0) {
         $filename = $listobject->getRecordValue(1,'output_file');
         $run_date = $listobject->getRecordValue(1,'run_date');
      } else {
         $filename = $outdir . "/runlog" . $runid . "." . $elementid . ".log";
         $innerHTML .= "Failed to locate run record.<br>";
         $rm = 1;
      }
   }
   
   return array('tablename'=>$session_table, 'filename'=>$filename, 'innerHTML'=>$innerHTML, 'run_date' => $run_date, 'record_missing'=>$rm);
}

function checkSessionTable($thisobject, $elementid, $runid = -1, $data_element = '') {
   global $session_db, $outdir;
   
   $tinfo = array('table_exists'=>0, 'file_exists'=>0);
   // make table name
   $sinfo = getSessionTableNames($thisobject, $elementid, $runid, $data_element);
   $session_table = $sinfo['tablename'];
   $filename = $sinfo['filename'];
   // if table exists, don't do anything, just get the last version
   // only would drop the table if the user made a call to the routine to change the model run/scenario
   // which doesn't happen through this display routine
   
   if ($session_db->tableExists($session_table) == 1) {
      $tinfo['table_exists'] = 1;
   }
   //if (file_exists($filename)) {
   if (!$tinfo['record_missing']) {
      $fe = fopen($filename,'r');
      if ($fe) {
         $tinfo['file_exists'] = 1;
         if (substr($filename,0,4) == 'http') {
            $tinfo['file_size'] = "Remote File - size unkown";
            $tinfo['remote'] = 1;
         } else {
            $tinfo['file_size'] = filesize($filename);
            $tinfo['remote'] = 0;
         }
      }
      fclose($fe);
   } else {
      $tinfo['file_exists'] = 0;
   }
   
   return $tinfo;
}

function loadSessionTable($thisobject, $elementid, $runid = -1, $data_element = '') {
   global $session_db, $outdir;
   
   //$lobj['innerHTML'] .= "Checking $filename  <br>";
   $tableinfo = checkSessionTable($thisobject, $elementid, $runid, $data_element);
   $file_exists = $tableinfo['file_exists'];
   $remote_file = $tableinfo['remote'];
   
   $lobj = array('innerHTML'=>'', 'session_table'=>'', 'error'=>0);
   // make table name
   $sinfo = getSessionTableNames($thisobject, $elementid, $runid, $data_element);
   $session_table = $sinfo['tablename'];
   $filename = $sinfo['filename'];
   $run_date = $sinfo['run_date'];
   
   // if table exists, don't do anything, just get the last version
   // only would drop the table if the user made a call to the routine to change the model run/scenario
   // which doesn't happen through this display routine
   
   if (!($session_db->tableExists($session_table) == 1)) {
      $loadtable = 1;
   } else {
      // a table exists, but lets check to make sure that it is updated
      if ($file_exists) {

         if ($remote_file) {
            $file_epoch = filemtime_remote($filename);
         } else {
            $file_epoch = filemtime($filename);
         }
         $lobj['innerHTML'] .= "Checking $filename  <br>";
         $session_db->querystring = " select creation_date from session_tbl_log where tablename = '$session_table'";
         error_log("Comparing $file_epoch to $tabledate - $table_epoch ");
         $session_db->performQuery();
         if (count($session_db->queryrecords) > 0) {
            $tabledate = $session_db->getRecordValue(1,'creation_date');
            $table_epoch = date('U',strtotime($tabledate));
         } else {
            $tabledate = '';
            $table_epoch = -1;
         }
         if ($file_epoch > $table_epoch) {
            //refresh table
            $session_db->querystring = " delete from session_tbl_log where tablename = '$session_table'";
            $session_db->performQuery();
            $session_db->querystring = " drop table \"$session_table\" ";
            $session_db->performQuery();
            $loadtable = 1;
         }
      } else {
         $lobj['innerHTML'] .= "Can not locate $filename exiting. <br>";
         $lobj['error'] = 1;
         $loadtable = 0;
      }
   }
      $dbcoltypes = $thisobject->dbcolumntypes;
   //$lobj['innerHTML'] .= print_r($dbcoltypes, 1) . "<br>";
   $lobj['innerHTML'] .= "Selected $filename (run on $run_date) <br>";
   if ($loadtable) {
      $dbcoltypes = $thisobject->dbcolumntypes;
      $lobj['innerHTML'] .= "Loading $filename  <br>";
      $darr = delimitedFileToTable($session_db, $filename, ',', $session_table, 1, -1, array(), $dbcoltypes);
      $lobj['innerHTML'] .= "Loaded " . count($darr) . " data lines.<br>";
      $session_db->querystring = " insert into session_tbl_log (tablename) values ('$session_table') ";
      $session_db->performQuery();
      if (count($darr) == 0) {
         $lobj['error'] = 1;
      }      
   }
   $lobj['session_table'] = $session_table;
   
   return $lobj;
}


function showAnalysisQueryWizard ($formValues, $session_table, $form_name) {
   global $adminsetuparray, $session_db;
   $query_wiz = array();
   $controlHTML = '';
   
   ############################################################
   ###                 CUSTOM OUTPUT FORM                   ###
   ############################################################
   $controlHTML .= "<a class=\"mH\"";
   $controlHTML .= "onclick=\"toggleMenu('$form_name" . "_format')\">+ Show/Hide Custom Query</a>";
   $controlHTML .= "<div id=\"$form_name" . "_format\" class=\"mL\">";
   # show a set of custom queryWizard objects
   $queryparent = new blankShell;
   # setting this to the query assembled by the search object
   $queryparent->dbtblname = $session_table;
   $querywizard = new queryWizardComponent;
   $querywizard->parentobject = $queryparent;
   $querywizard->listobject = $session_db;
   # create a list for use in the form drop-downs of the various columns that we can select
   $aslist = '';
   $asep = '';
   $table_cols = $session_db->getColumns($session_table);
   foreach ($table_cols as $thiscol) {
      $aslist .= $asep . $thiscol . '|' . $thiscol;
      $asep = ',';
   }
   //$controlHTML .= " Column List: $aslist <br>";
   $qset = array();
   $qset['queryWizardComponent'] = $adminsetuparray['queryWizardComponent'];
   # blank this out, since we do not want any of the informational fields
   
   $qset['queryWizardComponent']['column info'] = array("custom_to_file"=>array("type"=>3,"params"=>"0|False,1|True:ctfid:ctfname::0","label"=>"Output Results to File?","visible"=>1, "readonly"=>0, "width"=>6)); 
   foreach (array('queryWizard_selectcolumns'=>'qcols', 'queryWizard_wherecolumns'=>'wcols', 'queryWizard_ordercolumns'=>'ocols') as $colname => $lname) {
      $qset[$colname] = $adminsetuparray[$colname];
      $asrec = split(':',$qset[$colname]['column info'][$lname]['params']);
      $asrec[0] = $aslist;
      $asparams = join(':', $asrec);
      $qset[$colname]['column info'][$lname]['params'] = $asparams;
      //$controlHTML .= " Column Array for <b>$colname</b>: " . print_r($asrec,1). " <br>";
      //$controlHTML .= " Column Select Record: " . $asparams . " <br>";
   }
   $qset['queryWizard_selectcolumns']['column info']['qcols_txt']['visible'] = 0; 
   $qset['queryWizard_selectcolumns']['table info']['showlabels'] = 1; 
   $querywizard->force_cols = 1;
   $querywizard->quote_tablename = 1;
   $querywizard->force_names = array('custom_to_file'=>$custom_to_file);
   $querywizard->qcols = $formValues['qcols'];
   $querywizard->qcols_func = $formValues['qcols_func'];
   $querywizard->qcols_alias = $formValues['qcols_alias'];
   $querywizard->wcols = $formValues['wcols'];
   $querywizard->wcols_op = $formValues['wcols_op'];
   $querywizard->wcols_value = $formValues['wcols_value'];
   $querywizard->wcols_refcols = $formValues['wcols_refcols'];
   $querywizard->ocols = $formValues['ocols'];
   
   $querywizard->listobject->adminsetuparray = $qset;
   $formatinfo = $querywizard->showEditForm('custom');
   $controlHTML .= $formatinfo['innerHTML'];
   $querywizard->assembleQuery();
   $controlHTML .= $querywizard->sqlstring . "<br>";
   $controlHTML .= "<center>" . showGenericButton('search','Search', "xajax_refreshAnalysisWindow(xajax.getFormValues(\"$form_name\")) ; ", 1, 0) . "</center>";
   $controlHTML .= "</div><hr>";
   
   ############################################################
   ###                  END CUSTOM OUTPUT FORM              ###
   ############################################################
   $query_wiz['innerHTML'] = $controlHTML;
   $query_wiz['sql'] = $querywizard->sqlstring;
   return $query_wiz;
}

function compareRunData($elementid, $runid, $variables, $startdate='', $enddate='', $doquery = 1) {
   global $session_db;
   $runs = split(',', $runid);
   $vars = split(',', $variables);
   $unser = unserializeSingleModelObject($elementid);
   $thisobject = $unser['object'];
   $tbls = array();
   $result = array('query'=>'', 'records'=>array());

   $ri = 0;
   foreach ($runs as $thisrun) {
      $l = loadSessionTable($thisobject, $elementid, $thisrun);
      if (!$l['error']) {
         $tbls[] = $l['session_table'];
         $ros[$ri] = $thisrun;
         $ri++;
      }
      //print("Session table loading: " . print_r($l, 1) . " <br>");
   }

   $slist = "tbl_0.thisdate";
   $sdel = ',';
   $ti = 0;

   foreach ($tbls as $thistable) {
      //print("Table $thistable - ti = $ti <br>\n");
      $vlist = '';
      $vdel = ',';
      $thisrun = $ros[$ti];
      foreach ($vars as $thisvar) {
         $vlist .= "$vdel avg(\"$thisvar\") as \"$thisvar\"";
         $vdel = ',';
         $slist .= "$sdel tbl_$ti" . ".\"$thisvar\" as \"$thisvar" . "_$thisrun\"";
         $sdel = ',';
      }
      $tlist .= "$tdel (select thisdate::date $vlist from \"$thistable\" group by thisdate::date ) as tbl_$ti ";
      $tdel .= " left outer join ";
      if ($ti > 0) {
         $tp = $ti - 1;
         $tjoin = " on ( tbl_$ti" . ".thisdate = tbl_$tp" . ".thisdate ) ";
      }  else {
         $tjoin = '';
      }
      $tlist .= $tjoin;
      $ti++;
   }
   $wlist = 'WHERE (1 = 1)';
   if ($startdate <> '') {
      $wlist .= " AND thisdate >= '$startdate'";
   }
   if ($enddate <> '') {
      $wlist .= " AND thisdate <= '$enddate'";
   }
   $query = "select $slist from $tlist $wlist order by tbl_0.thisdate ";
   $result['query'] = $query;
   if ($doquery) {
      $session_db->querystring = $query;
      $session_db->performQuery();
      $result['records'] = $session_db->queryrecords;
   }
   
   return $result;
}

function showAnalysisGrid($formValues, $tablename, $form_name) {
   global $session_db;
   // now that the table is set up, show the dataGrid
   $result = array('innerHTML'=>'', 'subquery'=>'');
   if (isset($formValues['offset'])) {
      $offset = $formValues['offset'];
   } else {
      $offset = 0;
   }
   if (isset($formValues['first'])) {
      $offset = 0;
   }
   if (isset($formValues['limit'])) {
      $limit = $formValues['limit'];
   } else {
      $limit = 10;
   }
   if (isset($formValues['order'])) {
      $order = $formValues['order'];
   } else {
      $order = '';
   }
   // is this unique enough for yah??
   //$divname = $tablename;
   $divname = 'div' . $form_name;
   $ag = createxAjaxGrid($tablename, $divname, $offset, $limit, null, null, $order, $form_name);
   $result['innerHTML'] = $ag['innerHTML'];
   $result['subquery'] = $ag['query'];
   /*
   $session_db->tablename = $tablename;
   $session_db->getAllRecords($offset,$limit,$order);
   $session_db->show = 0;
   $session_db->showList();
   $html = $session_db->outstring;
   */ 
   return $result;
}

function createxAjaxGrid($tablename, $divname, $start = 0, $limit = 1,$filter = null, $content = null, $order = null, $form_name){
   global $session_db;
   $result = array('innerHTML'=>'', 'query'=>'');
   $html = '';
   
   // for now, we keep this format, but make sure that filter and content (the two sides of the where x = y clause) are null
   // later, we will integrate this with the query wizard component to give advanced column control as well as conditions
   // and ordering
   $session_db->tablename = $tablename;
   if($content == null){
      $numRows = $session_db->getNumRows();
      $session_db->getAllRecords($start,$limit,"\"$order\"");
      $arreglo = $session_db->queryrecords;
      //$html .= "axaxGrid query: " . $session_db->querystring . "<br>";
   }else{
      $numRows = $session_db->getNumRows($filter, $content);
      $arreglo = $session_db->getAllRecords($start,$limit,"\"$order\"");
      // this is disabled until we get integration with the query wizard
      //$arreglo =& Person::getRecordsFiltered($start, $limit, $filter, $content, $order);  
   }
   $result['query'] = $session_db->querystring;
   if($filter != null)
      $_SESSION['filter'] = $filter;
   
   // Editable zone
   // get any formatting info for the variables from our object
   $columns = $session_db->getColumns($tablename);
   
   $headers = array();
   $attribsHeader = array();
   $attribsCols = array();
   $eventHeader = array();
   $fieldsFromSearch = array();
   $fieldsFromSearchShowAs = array();
   
   $numcols = count($columns);
   
   /*
   $attribsHeader[] = 'width="20%"';
   $attribsHeader[] = 'width="20%"';
   $attribsHeader[] = 'width="20%"';
   $attribsHeader[] = 'width="30%"';
   
   
   $attribsCols[] = 'style="text-align: left"';
   $attribsCols[] = 'style="text-align: left"';
   $attribsCols[] = 'style="text-align: left"';
   $attribsCols[] = 'style="text-align: left"';
   
   $eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","lastname");return false;\'';
   $eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","firstname");return false;\'';
   $eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","email");return false;\'';
   $eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","origin");return false;\'';
   
   $fieldsFromSearch[] = 'lastname';
   $fieldsFromSearch[] = 'firstname';
   $fieldsFromSearch[] = 'email';
   $fieldsFromSearch[] = 'origin';
   
   $fieldsFromSearchShowAs[] = "Last Name";
   $fieldsFromSearchShowAs[] = "First Name";
   $fieldsFromSearchShowAs[] = "E-Mail";
   $fieldsFromSearchShowAs[] = "Origin";
   */
   if (class_exists('ScrollTable')) {
      $table = new ScrollTable($numcols,$start,$limit,$filter,$numRows,$content,$order);
      
      $table->formname = $form_name;
      $table->use_post = 1;
      $table->read_only = 1;
      $table->edtext = 'Edit';
      $table->deltext = 'Delete';
      $table->img_url = '/images';
      $table->show_funcjs = 'xajax_refreshAnalysisWindow';
      $table->setFooter();
      foreach (array_keys($arreglo[0]) as $thiscol) {
         $headers[] = $thiscol;
         $eventHeader[] = $table->generateHeaderAction($thiscol);
      }
      $table->setHeader('title',$headers,$attribsHeader,$eventHeader);
      /*
      $table->setAttribsCols($attribsCols);
      $table->addRowSearch("alumno",$fieldsFromSearch,$fieldsFromSearchShowAs);
      */
      $j = 0;
      foreach ( $arreglo as $row) {
         // Change here by the name of fields of its database table
         $rowc = array();
         foreach (array_keys($row) as $thiskey) {
            $rowc[] = $row[$thiskey];
         }

         //$rowc[] = '<a href="?" onClick="xajax_show('.$row['id'].');return false">'.$row['lastname'].'</a>';
         $table->addRow($divname,$rowc);
         $j++;

      }

      // End Editable Zone

      $html .= $table->render();
   } else {
      $html .= "Class: ScrollTable - does not exist.<br>";
   }
   
   $result['innerHTML'] = $html;
   return $result;
}

function getRunDetail($listobject, $elid, $runid, $host = '') {
   $quick_num = 1000;
   // get information for the parent container
   $output = getStatusSingle($listobject, $elid, $runid, $host);   
   $run_rec = getRunFile($listobject, $elid, $runid);
   if (is_array($run_rec)) {
      if ($output['run_status'] == '') {
         // we have no system_status entry, but we DO have a file, so we consider it to be a 
         // valid completed model run
         $output['run_status'] = 0;
      }
      // now, we can look at the following types of components and make inferences on their status:
      // USGSChannelGeomObject
      // CBPLandDataConnection
      $output['exists'] = 1;
      $run_file = $run_rec['output_file'];
      $output['runfile'] = $run_file;
      $output['rundate'] = $run_rec['run_date'];
      $output['starttime'] = $run_rec['starttime'];
      $output['endtime'] = $run_rec['endtime'];
      $info = verifyRunVars($run_file,array('demand_mgd','discharge_mgd','Qout'),$quick_num);
      $output['Qout'] = $info['Qout']['mean'];
      $imp_off = $info['impoundment_inactive']['max'];
      // get this from the river since the mapping is wrong inthe broadcast object
      //$output['demand_mgd'] = $info['demand_mgd']['mean'];
      $output['discharge_mgd'] = $info['discharge_mgd']['mean'];
      $child_rec = getChildComponentType($listobject, $elid, 'USGSChannelGeomObject', 1);
      // get information for the Mainstem Container pertaining to drainage area
      $thischild = $child_rec[0];
      $cid = $thischild['elementid'];
      $prop_array = array('drainage_area', 'area', 'length');
      $props = getElementPropertyValue($listobject, $cid, $prop_array);
      // get Runoff data from the Mainstem Container peraining to drainage area
      $runoff_rec = getRunFile($listobject, $cid, $runid);
      $runoff_file = $runoff_rec['output_file'];
      $info = verifyRunVars($runoff_file,array('demand_broadcast_mgd','Rin'),$quick_num);
      $output['demand_cfs'] = $info['demand_broadcast_mgd']['mean'] * 1.54;
      $output['Rin'] = $info['Rin']['mean'];
      return array_merge($output, $props);
   } else {
      $output['exists'] = 0;
      return $output;
   }
}


function verifyRunVars($runfile, $varnames, $numlines=10) {
   // returns the min, max, and first and last values from the requested vars
   $contents = readDelimitedFile($runfile,',', 1, $numlines);
   if (count($varnames) == 0) {
      $varnames = array_keys($contents[0]);
   }
   foreach ($contents as $thisline) {
      foreach($varnames as $thisvar) {
         if (isset($thisline[$thisvar])) {
            $varout[$thisvar]['values'][] = $thisline[$thisvar];
         }
      }
   }
   $varnames = array_keys($varout);
   foreach($varnames as $thisvar) {
      $varout[$thisvar]['min'] = min($varout[$thisvar]['values']);
      $varout[$thisvar]['max'] = max($varout[$thisvar]['values']);
      $varout[$thisvar]['sum'] = array_sum($varout[$thisvar]['values']);
      $varout[$thisvar]['mean'] = $varout[$thisvar]['sum'] / count($varout[$thisvar]['values']);
      $varout[$thisvar]['first'] = $varout[$thisvar]['values'][0];
      $varout[$thisvar]['last'] = $varout[$thisvar]['values'][count($varout[$thisvar]['values']) - 1];
   }
   return $varout;
}

?>