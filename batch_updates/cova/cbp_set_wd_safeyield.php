<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('./xajax_modeling.element.php');
#include_once('lib_batchmodel.php');

error_reporting(E_ERROR);
global $unserobjects;

if (count($argv) < 3) {
   error_log("Usage: php cbp_init_wds.php scenarioid riverseg [elementid = -1] [mpid = -1] [overwrite=0] [reinitialize=0] \n");
   die;
}

function getVwudsWDMax($vwudsdb, $userid, $mpid, $debug = 0) {
   $mplist = "'" . join("','", split(',',$mpid)) . "'";
   $vwudsdb->querystring = "  select c.\"USERID\" as userid, c.\"MPID\" as mpid, ";
   $vwudsdb->querystring .= "    c.\"GWLIMIT_YR\" as permit_maxyear, a.max_annual, b.permit_exemption, ";
   $vwudsdb->querystring .= "    CASE ";
   $vwudsdb->querystring .= "       WHEN b.max_val is not null THEN b.max_val ";
   $vwudsdb->querystring .= "       ELSE 0 ";
   $vwudsdb->querystring .= "    END AS max_val, c.abandoned ";
   $vwudsdb->querystring .= " from vwuds_measuring_point as c left outer join vwuds_max_action as a ";
   $vwudsdb->querystring .= "    on (a.userid = c.\"USERID\" and a.mpid = c.\"MPID\" and c.\"ACTION\" = a.action) ";
   $vwudsdb->querystring .= " left outer join view_vwp_exemption as b ";
   $vwudsdb->querystring .= "    on (a.userid = b.userid and a.mpid = b.mpid) ";
   $vwudsdb->querystring .= " where c.\"ACTION\" = 'WL' ";
   $vwudsdb->querystring .= "    and c.\"USERID\" = '$userid' ";
   $vwudsdb->querystring .= "    and c.\"MPID\" in ($mplist) ";
   if ($debug) {
      error_log("$vwudsdb->querystring ; \n");
   }
   $vwudsdb->performQuery();
   if ($vwudsdb->numrows == 0) {
      return false;
   } else {
      return $vwudsdb->queryrecords[0];
   }
}

$scenid = $argv[1];
$riverseg = $argv[2];
if (isset($argv[3])) {
   $elementid = $argv[3];
} else {
   $elementid = -1;
}
if (isset($argv[4])) {
   $onempid = $argv[4];
} else {
   $onempid = -1;
}
if (isset($argv[5])) {
   $overwrite = $argv[5];
} else {
   $overwrite = 0;
}
if (isset($argv[6])) {
   $reinit = $argv[6];
} else {
   $reinit = 0;
}
if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' ";
   error_log("Looking for river abbreviation match <br>\n");
   error_log("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $thisrec) {
      $segs[] = $thisrec['riverseg'];
   }
   error_log("Found " . join(",", $segs) . "\n");
} else {
   $segs = split(',',$riverseg);
}
if ($elementid <> -1) {
   $segs = array($elementid);
}
if ($onempid <> -1) {
   $segs = array($elementid);
   $elementid = -1;
   error_log("Requested MPID $onempid, setting elementid = -1 \n");
}
//print_r($segs);
$debug = 0;
foreach ($segs as $riverseg) {
   if ($elementid == -1) {
      if ($onempid == -1) {
         error_log("Looking for CBP parent of $riverseg <br>\n");
         $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
         $wds = getCOVAWithdrawals($listobject, $elid);
      } else {
         error_log("Searching for CBP Parent by MPID ");
         $wds = getComponentCustom($listobject, $scenid, 'cova_withdrawal', $onempid, -1, array(), $debug);
      }
   } else {
      $wds = array(getElementInfo($listobject, $elementid, $debug));
   }
   error_log("Recs: " . print_r($wds,1) . "\n");
   
   foreach ($wds as $thiswd) {
      $recid = $thiswd['elementid'];
      $wdname = $thiswd['elemname'];
		error_log("Handling $wdname - ID: $recid");
      $obres = unSerializeSingleModelObject($recid);
      $thisobject = $obres['object'];
      //error_log("Error Info:" . print_r($obres['error'],1) . "\n");
      //error_log("Debug Info:" . print_r($obres['debug'],1) . "\n");
      if ($reinit) {
         if (method_exists($thisobject, 'getWithdrawalLocation')) {
            error_log("Checking db class " . get_class($thisobject->vwuds_db) . "\n");
            error_log("calling object getWithdrawalLocation() method ");
            $wkt_geom = $thisobject->getWithdrawalLocation();
            // will set this later in the script
         } else {
            error_log(" Method getWithdrawalLocation is not defined on $wdname ($elementid) \n");
         }
         if (method_exists($thisobject, 'reCreate')) {
		      $thisobject->vwuds_db = $vwudsdb;
            error_log("calling object reCreate() method ");
            error_log("Checking db class " . get_class($thisobject->vwuds_db) . "\n");
            $thisobject->reCreate();
            //error_log("$innerHTML \n");
         } else {
            error_log("Recreation failed.  $wdname ($elementid)  Object has no reCreate() method \n");
         }
			$innerHTML = saveObjectSubComponents($listobject, $thisobject, $recid );
			//updateObjectPropList($recid, $thisobject, $debug );
         error_log("Setting Geometry \n");
         $prop_array = array('the_geom'=>$wkt_geom);
         //$res = updateObjectProps($projectid, $recid, $prop_array, 1);
         // now reload the object
         $obres = unSerializeSingleModelObject($recid);
         $thisobject = $obres['object'];
      }
      error_log("Estimating Max Withdrawal Info For $wdname ($recid) \n");
      $userid = $thisobject->id1;
      $sy_desc = '';
      $mpids = split(",",$thisobject->id2);
      $safeyield_annual = 0.0;
      foreach ($mpids as $mpid) {
         $maxinfo = getVwudsWDMax($vwudsdb, $userid, $mpid, $debug);
         $mp_sy = 0.0;
         if (!$maxinfo) {
            // returned nothing, need to throw a flag and default to the current_mgd value
            error_log("getVwudsWDMax returned no records for $userid, $mpid \n");
            $sy_desc .= 'Could not obtain annual records for this MP.';
            if (isset($thisobject->processors['current_mgy'])) {
               $mp_sy = $thisobject->processors['current_mgy']->getProp('equation');
               $sy_desc .= ' Using Current WD estimate.';
            } else {
               error_log("Current_mgy is not set on this object. \n");
               $mp_sy = 0;
               $sy_desc .= ' Current MGY Not defined, defaulting to 0.0 MG/yr.';
            }
         } else {
            if ($maxinfo['abandoned']) {
               $sy_desc .= ' This measuring point marked as abandoned';
               $mp_sy = 0.0;
               if (isset($thisobject->processors['current_mgy'])) {
                  $thisobject->processors['current_mgy']->setProp('equation', '0.0');
                  $thisobject->processors['current_mgy']->setProp('description', $sy_desc);
               } else {
                  error_log("current_mgy is not a valid sub-comp on this object.");
                  $cobj = new Equation;
                  $cobj->equation = "0.0";
                  $cobj->description = 'Current demand in MGD';
                  if ( $overwrite or (!isset($thisobject->processors['safe_yield_mgy'])) ) {
                     error_log("Adding current_mgy to $elid \n");
                     $thisobject->addOperator('current_mgy', $cobj);
                  }
               }
            } else {
               // report on values obtained
               error_log("getVwudsWDMax returned a max historical value  of " . $maxinfo['max_annual'] . " MGY and Max Capacity of " . $maxinfo['max_val'] . " MGY\n");
               $sy_desc .= ' VWUDS Records Located.';
               if (!$maxinfo['permit_maxyear']) {
                  if (!$maxinfo['max_val']) {
                     $sy_desc .= ' VWUDS max reported used.';
                     $mp_sy = $maxinfo['max_annual'];
                  } else {
                     $sy_desc .= ' VWP exemption value used.';
                     $mp_sy = $maxinfo['max_val'];
                  }
               } else {
                  $sy_desc .= ' VWP Permit Max used.';
                  $mp_sy = $maxinfo['permit_maxyear'];
               }
            }
         }
         error_log("MP $mpid estiamted as $mp_sy \n");
         $safeyield_annual += floatval($mp_sy);
      }
      
      // set the safe-yield sub-object description to detail the source of this data, VWP exemp table, VWUDS MP table, current annual value
      if (strlen(trim($safeyield_annual)) == 0) {
         $safeyield_annual = 0.0;
         $sy_desc .= ' NULL set to 0.0.';
      }
      error_log("Safe Yield estimated as $safeyield_annual MGY\n");
      $syobj = new Equation;
      $syobj->equation = $safeyield_annual;
      $syobj->description = $sy_desc;
      $symgd = new Equation;
      $symgd->equation = 'safe_yield_mgy * historic_monthly_pct / modays';
      $symgd->description = 'Safe Yield in MGD';
      if ( $overwrite or (!isset($thisobject->processors['safe_yield_mgy'])) ) {
         $thisobject->addOperator('safe_yield_mgy', $syobj);
         $thisobject->addOperator('safe_yield', $symgd);
         $res = saveObjectSubComponents($listobject, $thisobject, $recid, 1, 0);
      } else {
         error_log("Overwrite is 0, safe_yield object not saved. \n");
      }
   }
}

?>
