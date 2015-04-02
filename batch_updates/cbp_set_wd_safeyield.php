<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

if (count($argv) < 3) {
   print("Usage: php cbp_init_wds.php scenarioid riverseg [overwrite=0] \n");
   die;
}

function getVwudsWDMax($vwudsdb, $userid, $mpid, $debug = 0) {
   $mplist = "'" . join("','", split(',',$mpid)) . "'";
   $vwudsdb->querystring = "  select c.\"USERID\" as userid, c.\"MPID\" as mpid, ";
   $vwudsdb->querystring .= "    c.\"GWLIMIT_YR\" as permit_maxyear, a.max_annual, b.permit_exemption, ";
   $vwudsdb->querystring .= "    CASE ";
   $vwudsdb->querystring .= "       WHEN b.max_val is not null THEN b.max_val ";
   $vwudsdb->querystring .= "       ELSE 0 ";
   $vwudsdb->querystring .= "    END AS max_val ";
   $vwudsdb->querystring .= " from vwuds_measuring_point as c left outer join vwuds_max_action as a ";
   $vwudsdb->querystring .= "    on (a.userid = c.\"USERID\" and a.mpid = c.\"MPID\" and c.\"ACTION\" = a.action) ";
   $vwudsdb->querystring .= " left outer join view_vwp_exemption as b ";
   $vwudsdb->querystring .= "    on (a.userid = b.userid and a.mpid = b.mpid) ";
   $vwudsdb->querystring .= " where c.\"ACTION\" = 'WL' ";
   $vwudsdb->querystring .= "    and c.\"USERID\" = '$userid' ";
   $vwudsdb->querystring .= "    and c.\"MPID\" in ($mplist) ";
   if ($debug) {
      print("$vwudsdb->querystring ; \n");
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
   $overwrite = $argv[3];
} else {
   $overwrite = 0;
}
if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' ";
   print("Looking for river abbreviation match <br>\n");
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $thisrec) {
      $segs[] = $thisrec['riverseg'];
   }
} else {
   $segs = split(',',$riverseg);
}
//print_r($segs);

foreach ($segs as $riverseg) {
   print("Looking for CBP parent of $riverseg <br>\n");
   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   
   $wds = getCOVAWithdrawals($listobject, $elid);
   foreach ($wds as $thiswd) {
      $recid = $thiswd['elementid'];
      $wdname = $thiswd['elemname'];
      $obres = unSerializeSingleModelObject($recid);
      $thisobject = $obres['object'];
      print("Estimating Max Withdrawal Info For $wdname ($recid) \n");
      $userid = $thisobject->id1;
      $sy_desc = '';
      $mpids = split(",",$thisobject->id2);
      $safeyield_annual = 0.0;
      foreach ($mpids as $mpid) {
         $maxinfo = getVwudsWDMax($vwudsdb, $userid, $mpid, 1);
         $mp_sy = 0.0;
         if (!$maxinfo) {
            // returned nothing, need to throw a flag and default to the current_mgd value
            print("getVwudsWDMax returned no records for $userid, $mpid \n");
            $sy_desc .= 'Could not obtain annual records for this MP.';
            if (isset($thisobject->processors['current_mgy'])) {
               $mp_sy = $thisobject->processors['current_mgy']->getProp('equation');
               $sy_desc .= ' Using Current WD estimate.';
            } else {
               print("Current_mgy is not set on this object. \n");
               $mp_sy = 0;
               $sy_desc .= ' Current MGY Not defined, defaulting to 0.0 MG/yr.';
            }
         } else {
            // report on values obtained
            print("getVwudsWDMax returned a max historical value  of " . $maxinfo['max_annual'] . " MGY and Max Capacity of " . $maxinfo['max_val'] . " MGY\n");
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
         print("MP $mpid estiamted as $mp_sy \n");
         $safeyield_annual += floatval($mp_sy);
      }
      
      // set the safe-yield sub-object description to detail the source of this data, VWP exemp table, VWUDS MP table, current annual value
      if (strlen(trim($safeyield_annual)) == 0) {
         $safeyield_annual = 0.0;
         $sy_desc .= ' NULL set to 0.0.';
      }
      print("Safe Yield estimated as $safeyield_annual MGY\n");
      $syobj = new Equation;
      $syobj->equation = $safeyield_annual;
      $syobj->description = $sy_desc;
      if ( $overwrite or (!isset($thisobject->processors['safe_yield'])) ) {
         $thisobject->addOperator('safe_yield', $syobj);
         $res = saveObjectSubComponents($listobject, $thisobject, $recid, 1, 0);
      } else {
         print("Overwrite is 0, safe_yield object not saved. \n");
      }
   }
}

?>
