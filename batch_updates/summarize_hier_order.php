<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");

// default values
$startdate = '1984-01-01';
$enddate = '2005-12-31';
$rundate = '2010-10-01';
$runid = 1;
// END - default values

$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

if (isset($argv[1])) {
   $scenarioid = $argv[1];
} else {
   $scenarioid = -1;
}
if (isset($argv[2])) {
   $obclass = $argv[2];
} else {
   $obclass = -1;
}
if (isset($argv[3])) {
   $namelen = $argv[3];
} else {
   $namelen = -1;
}
if (isset($argv[4])) {
   $runid = $argv[4];
}
if (isset($argv[5])) {
   $rundate = $argv[5];
}
if (isset($argv[6])) {
   $target_order = $argv[6];
} else {
   $target_order = 'all';
}

$run_names = array(
   1=>'baseline',
   2=>'current'
);

$listobject->querystring = "  select elementid, elemname from scen_model_element ";
$listobject->querystring .= " where ( (scenarioid = $scenarioid) or ($scenarioid = -1) ) ";
$listobject->querystring .= "    and ( (objectclass = '$obclass') or ('$obclass' = '-1') )  ";
$listobject->querystring .= "    and ( (length(elemname) >= $namelen) or ('$namelen' = '-1') )  ";
print("$listobject->querystring \n");
$listobject->performQuery();
$elrecs = $listobject->queryrecords;
$not_run = array();
$failed = array();

foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];
   $elemname = $thisrec['elemname'];
   $order = getElementOrder($listobject, $elid);
   if (!isset($orders[$order])) {
      $orders[$order] = 0;
   }
   $orders[$order] += 1;
   
   if ( ($target_order <> 'all') and ($target_order == $order)) {
      // now get run info
      $been_run = checkRunDate($listobject, $elid, $runid, $rundate, $startdate, $enddate, 0);
      if (!$been_run) {
         $not_run['ids'][] = $elid;
         $not_run['names'][] = $elemname;
      }
      $run_name = $run_names[$runid];
      $uid = getUID($listobject, $elid);
      $run_info = getRunAnalysisData($cbp_listobject, $uid, $run_name, 0);
      $rmv = $run_info['data']['verified_runmode'];
      if ( ($rmv == 0) or ($rmv == '') ) {
         $failed['ids'][] = $elid;
         $failed['names'][] = $elemname;
      }
   }
}

if ($obclass == -1) {
   $obclass = 'all';
}
print("Elements of class '$obclass'\n");
$keys = array_keys($orders);
rsort($keys);
foreach ($keys as $thisorder) {
   $thiscount = $orders[$thisorder];
   print("   Order #$thisorder, count: $thiscount \n");
}
print("Objects of order '$target_order', scenario $runid, that have not been run since $rundate.\n");
print("Names: " . print_r($not_run['names'],1) . "\n");
print_r("ID's: " . print_r($not_run['ids'],1) . "\n");
print("\nObjects that failed run verification.\n");
print("Names: " . print_r($failed['names'],1) . "\n");
print_r("ID's: " . print_r($failed['ids'],1) . "\n");
print("\nDone.\n");

?>