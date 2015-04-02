<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

if (count($argv) < 2) {
   print("Usage: php fn_getVwudsMax.php userid [mpid] \n");
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
   if ($mplist <> "''") {
      $vwudsdb->querystring .= "    and c.\"MPID\" in ($mplist) ";
   }
   //if ($debug) {
      print("$vwudsdb->querystring ; \n");
   //}
   $vwudsdb->performQuery();
   if ($vwudsdb->numrows == 0) {
      if ($debug) {
         print("Nothing found for Uid: $userid and MPID(s): $mpid\n");
      }
      return false;
   } else {
      if ($debug) {
         print("Found for Uid: $userid and MPID(s): $mpid\n" . print_r($vwudsdb->queryrecords[0],1) . "\n");
      }
      return $vwudsdb->queryrecords[0];
   }
}

$userid = $argv[1];

if (isset($argv[2])) {
   $mpid = $argv[2];
} else {
   $mpid = '';
}

getVwudsWDMax($vwudsdb, $userid, $mpid, 1);

?>