<?php

$noajax = 1;
$projectid = 3;
$scid = 28;
include("./xajax_modeling.element.php");
include("./lib_verify.php");

// patch ICPRB watershed linkages and names
// two modes: 
   // 1) check for dropped linkages (upstream), missing to_node (downstream) - check for unintended links to new records
   // 2) process changes

// types of changes that may take place:
// watershed object name
// downstream linkages
// upstream linkages
// land use update/insert

if (isset($argv[1])) {
   $filename = $argv[1];
} else {
   print("Missing File name. Usage: patch_sheds.php filename operation <br>\n");
   die;
}

if (isset($argv[2])) {
   $mode = $argv[2];
} else {
   print("Missing Operation. Usage: patch_sheds.php filename operation <br>\n");
   die;
}

if (isset($argv[3])) {
   $testonly = $argv[3];
} else {
   $testonly = 0;
}

$records = readDelimitedFile($filename,",",1);

//print_r($records);
//print("\n");


   
switch ($mode) {
   
   case 1:
      // Mode 1 - verify/report on proposed changes
      // open csv
      // iterate through records:
         // - Does to_node exist?
         //   - Is there another change record that will fix this broken linkage? If so, OK
         // - Does this name change break any upstream linkages?
         //   - Is there another change record that will fix these broken upstream linkages? If so, OK
         // - Does new name (if different) already exist?
         //   - Is there another change record for the duplicate? If so, OK
         
      $verification = verifyPatches($scid, $listobject, $cbp_listobject, $records);
   break;
   
   case 2:
      // Mode 2 - process changes - use uniqueid and elementid as the authoritative record links to update records
      // open CSV
      // query records from icprb_watersheds (both), scen_model_element, and tmp_icprb_fullsheds 
         // to get uniqueid/elementid -> shed_merge mapping
      // loop through records
         // remove old upstream linkages to these objects
         // change names in icprb_watersheds (model), tmp_icprb_fullsheds, icprb_watersheds (cbp)
      // loop through records (2nd time)
         // add new downstream linkages
         // add new upstream linkages
         
      $verification = verifyPatches($scid, $listobject, $cbp_listobject, $records);
      if ( !(count($verification['orphans']) > 0) ) {
         // change names first
         foreach ($verification['results'] as $thisresult) {
            $elid = $thisresult['elementid'];
            $newname = $thisresult['newname'];
            $oldname = $thisresult['oldname'];
            $unique_id = $thisresult['unique_id'];
            $mainstem = $thisresult['mainstem'];
            if (strlen($newname) == 0) {
               $newname = $oldname;
            }
            $to_node = $thisresult['to_node'];
            if (strlen($newname) > 13) {
               $nest = substr($newname,13);
            } else {
               $nest = '';
            }
            if ($elid <> -1) {
               if ( ($newname <> $oldname) and ($newname <> '') ) {
                  $listobject->querystring = " update scen_model_element set elemname = '$newname' where elementid = $elid ";
                  print("$listobject->querystring ; \n");
                  if (!$testonly) {
                     $listobject->performQuery();
                  }
                  if ($unique_id <> -1) {
                     $listobject->querystring = " update icprb_watersheds set shed_merge = '$newname', t_node = '$to_node', nested_shedcode = '$nest', mainstem_segment = '$mainstem' where unique_id = '$unique_id' ";
                     print("$listobject->querystring ; \n");
                     if (!$testonly) {
                        $listobject->performQuery();
                     }
                     $cbp_listobject->querystring = " update icprb_watersheds set shed_merge = '$newname', t_node = '$to_node', nested_shedcode = '$nest', mainstem_segment = '$mainstem' where unique_id = '$unique_id' ";
                     print("$cbp_listobject->querystring ; \n");
                     if (!$testonly) {
                        $cbp_listobject->performQuery();
                     }
                  } else {
                     print("Warning: $oldname / $newname - $newname does not exist in icprb_watersheds database. \n");
                  }
               }
            } else {
               print("Error: In $oldname / $newname conversion.  $newname does not exist in modeling element database. \n");
            }
         }
         // 2 - change links for these entities
         print("Changing links for target entities\n");
         foreach ($verification['results'] as $thisresult) {
            $elid = $thisresult['elementid'];
            $to_node = $thisresult['to_node'];
            if ($elid <> -1) {
               $listobject->querystring = " select elementid from scen_model_element where elemname = '$to_node' and scenarioid = $scid ";
               print("$listobject->querystring ; \n");
               $listobject->performQuery();
               if (count($listobject->queryrecords) > 0) {
                  $to_node_id = $listobject->getRecordValue(1,'elementid');
                  $output = createObjectLink($projectid, $scid, $elid, $to_node_id, 1, '', '', $testonly);
                  print_r($output);
                  print("\n");
               }
            } else {
               print("Error: In $oldname / $newname conversion.  $newname does not exist in modeling element database. \n");
            }
         }
         // 3 - refresh lijnkages for the children of any objects whose names changed
         print("Changing links for the children of entities whose names have been changed.\n");
         foreach ($verification['refresh'] as $thisrefresh) {
            $elid = $thisrefresh['elementid'];
            $cname = $thisrefresh['elemname'];
            $to_node = $thisresult['to_node'];
            if ($elid <> -1) {
               $listobject->querystring = " select a.elementid from scen_model_element as a, icprb_watersheds as b where b.shed_merge = '$cname' and a.scenarioid = $scid and a.elemname = b.t_node ";
               print("$listobject->querystring ; \n");
               $listobject->performQuery();
               $to_node_id = $listobject->getRecordValue(1,'elementid');
               $output = createObjectLink($projectid, $scid, $elid, $to_node_id, 1, '', '', $testonly);
               print_r($output);
               print("\n");
            } else {
               print("Error: In refreshing links for $cname .  t_node does not exist in modeling element database. \n");
            }
         }
      } else  {
         print("\nOrphans:\n");
         print_r($verification['orphans']);
      }
   break;
   
   case 3:
      // Mode 3 - delete old model run records for these
      // open csv
      // iterate through records:
         // - delete elements in scen_model_run_elements for any matching oldname or newnames (since both should be rerun)
         
      foreach ($records as $thisrec) {
         $oldname = trim($thisrec['shed_merge']);
         $newname = trim($thisrec['new_name']);
         $to_node = trim($thisrec['to_node']);
         $listobject->querystring = "  delete from scen_model_run_elements where elementid in ( select elementid from scen_model_element where scenarioid = $scid and elemname in ('$oldname', '$newname', '$to_node') )";
         print("$listobject->querystring ; \n");
         if (!$testonly) {
            $listobject->performQuery();
         }
      }
   break;
   
   case 4:
   // re-link
      // 2 - change links for these entities
      print("Changing links for target entities\n");
      foreach ($records as $thisrec) {
         $newname = trim($thisrec['new_name']);
         $oldname = trim($thisrec['shed_merge']);
         if ($newname <> '') {
            $ename = $newname;
         } else {
            $ename = $oldname;
         }
         $to_node = trim($thisrec['to_node']);
         $elid = getElementID($listobject, $scid, $ename);
         if ($elid) {
            $to_node_id = getElementID($listobject, $scid, $to_node);
            $listobject->performQuery();
            if ($to_node_id) {
               $output = createObjectLink($projectid, $scid, $elid, $to_node_id, 1, '', '', $testonly);
               print_r($output);
               print("\n");
            }
         } else {
            print("Error: In $ename re-link.  $ename does not exist in modeling element database. \n");
         }
      }
      // 3 - refresh lijnkages for the children of any objects whose names changed
      print("Changing links for the children of entities whose names have been changed.\n"); 
      foreach ($records as $thisrec) {
         $oldname = trim($thisrec['shed_merge']);
         $elid = getElementID($listobject, $scid, $oldname);
         $listobject->querystring = "  select shed_merge from icprb_watersheds where t_node = '$oldname' ";
         $listobject->performQuery();
         $crecs = $listobject->queryrecords;
         foreach ($crecs as $thisrec) {
            $cname = $thisrec['shed_merge'];
            $cid = getElementID($listobject, $scid, $cname);
            if ($cid) {
               $output = createObjectLink($projectid, $scid, $cid, $elid, 1, '', '', $testonly);
               print_r($output);
               print("\n");
            } else {
               print("Error: In refreshing links for $cname .  t_node does not exist in modeling element database. \n");
            }
         }
      }
   break;
   
   case 5:
   // synch names
      print("Changing names for target entities and their named children \n");
      foreach ($records as $thisrec) {
         $newname = trim($thisrec['new_name']);
         if ($newname <> '') {
            $to_node = trim($thisrec['to_node']);
            $elid = getElementID($listobject, $scid, $newname);
            // synch the new name on the object properties
            $props = array('name'=>$newname);
            updateObjectProps($projectid, $elid, $props);
            // now, update this objects children who share its name
            $children = getChildComponentType($listobject, $elid, '', -1);
            foreach ($children as $thischild) {
               $ctype = $thischild['objectclass'];
               $cid = $thischild['elementid'];
               print("Found child - $cid, of type $ctype \n");
               $props = array();
               switch ($ctype) {
                  case 'giniGraph':
                  $props = array('name'=>"Graph: Gini " . $newname, 'title'=>"Gini - $newname");
                     
                  break;
                  
                  case 'graphObject':
                     $props = array('name'=>"Graph: " . $newname, 'title'=>"Flows - $newname");
                  break;
                  
                  case 'hydroImpoundment':
                     $props = array('name'=>"Impoundment on " . $newname);
                  break;
                  
                  case 'USGSChannelGeomObject':
                     $props = array('name'=>"Main Stem " . $newname);
                  break;
               }
               if (count($props) > 0) {
                  print("Updating to: " . print_r($props,1) . "\n");
                  updateObjectProps($projectid, $cid, $props);
               }
            }
         }
      }
   break;   
   
   case 6:
   // update mainstem areas
      print("Changing mainstem areas for target entities and their named children \n");
      foreach ($records as $thisrec) {
         $oldname = trim($thisrec['shed_merge']);
         $newname = trim($thisrec['new_name']);
         $local_area = trim($thisrec['local_area']);
         $total_area = trim($thisrec['total_area']);
         if (strlen($newname) > 0) {
            $elname = $newname;
         } else {
            $elname = $oldname;
         }
         $elid = getElementID($listobject, $scid, $elname);
         
         // synch the new name on the object properties
         $props = array('area'=>$local_area, 'drainage_area'=>$total_area);
         // now, update this objects children who share its name
         $child_rec = getChildComponentType($listobject, $elid, 'USGSChannelGeomObject', 1);
         $mainstem = $child_rec[0];
         $cid = $mainstem['elementid'];
         print("Updating $elname ($cid) - setting 'area'=>$local_area, 'drainage_area'=>$total_area \n");
         updateObjectProps($projectid, $cid, $props);
      }
   break;
   
   case 7:
   // update mainstem areas - total area only, with key of cbp_segmentid
      print("Changing mainstem areas for target entities and their named children \n");
      foreach ($records as $thisrec) {
         $elname = trim($thisrec['cbp_segmentid']);
         $total_area = trim($thisrec['total_area']);
         $elid = getElementID($listobject, $scid, $elname);
         
         // synch the new name on the object properties
         $props = array('drainage_area'=>$total_area);
         // now, update this objects children who share its name
         $child_rec = getChildComponentType($listobject, $elid, 'USGSChannelGeomObject', 1);
         $mainstem = $child_rec[0];
         $cid = $mainstem['elementid'];
         print("Updating $elname ($cid) - setting 'drainage_area'=>$total_area \n");
         updateObjectProps($projectid, $cid, $props);
      }
   break;
   
   case 8:
   // update mainstem areas - local area only, with key of cbp_segmentid
      print("Changing mainstem areas for target entities and their named children \n");
      foreach ($records as $thisrec) {
         $elname = trim($thisrec['cbp_segmentid']);
         $local_area = trim($thisrec['local_area']);
         $elid = getElementID($listobject, $scid, $elname);
         
         // synch the new name on the object properties
         $props = array('area'=>$local_area);
         // now, update this objects children who share its name
         $child_rec = getChildComponentType($listobject, $elid, 'USGSChannelGeomObject', 1);
         $mainstem = $child_rec[0];
         $cid = $mainstem['elementid'];
         print("Updating $elname ($cid) - setting 'drainage_area'=>$local_area \n");
         updateObjectProps($projectid, $cid, $props);
      }
   break;   
}
//print_r($verification);

print("Naming Problems:\n");
print_r($verification['name_problems']);

?>