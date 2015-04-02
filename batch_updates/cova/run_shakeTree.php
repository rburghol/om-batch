<?php

//include('./xajax_modeling.element.php');
if (isset($argv[9])) {
   $root = $argv[9];
} else {
   $root = 0;
}
switch ($root) {
   case 0:
   include('/var/www/html/wooomm/xajax_modeling.element.php');
   print("Using stable version of model - library = $libpath \n");
   break;

   case 1:
   include('/var/www/html/wooommdev/xajax_modeling.element.php');
   print("Using development version of model\n");
   break;

   default:
   include('/var/www/html/wooomm/xajax_modeling.element.php');
   break;
}
include_once("./lib_verify.php");
include_once("./cova/lib_batchmodel.php");
error_reporting(E_ERROR);

$startdate = '1984-01-01';
//$enddate = '1984-12-31';
$enddate = '2005-12-31';
$cache_date = '2010-10-18 12:00:00';
// specify max models to run at a time
$max_simultaneous = 5;
$scid = 37;

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
   $run_id = $argv[3];
}
if (isset($argv[4])) {
   $startdate = $argv[4];
}
if (isset($argv[5])) {
   $enddate = $argv[5];
}
if (isset($argv[6])) {
   $cache_date = $argv[6];
}
if (isset($argv[7])) {
   $force_overwrite = $argv[7];
}
if (isset($argv[8])) {
   $scid = $argv[8];
}

// set up the sleep factor
$start_year = intval(date('Y', strtotime($startdate)));
$end_year = intval(date('Y', strtotime($enddate)));
$years = $end_year - $start_year;
// specify max models to run at a time
$max_simultaneous = 6;
if ($years <= 0) {
   $years = 1;
}
$sleep_factor = $years * 10; // give it some time to accumulate  a cache
if ($sleep_factor < 20) {
   $sleep_factor = 20;
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

print("Run Order Submitted: \n");
print("   Element List - $elemname \n");
print("   Run ID - $run_id \n");
print("   Start date - $startdate \n");
print("   End date - $enddate \n");
print("   Sleep Factor - $sleep_factor \n");
print("   Overwrite? - $force_overwrite \n");

$run_name = $run_names[$run_id];
$debug = 1;

$listobject->querystring = "  select elementid from scen_model_element ";
// this gets us only ICPRB segments, we can do the CBP ones later when we need to
switch ($operation) {
   case 1:
   $listobject->querystring .= " where objectclass = 'modelContainer' ";
   $listobject->querystring .= "    and elementid  = $elemname ";
   print("$listobject->querystring \n");
   $listobject->performQuery();
   $outlets = $listobject->queryrecords;
   break;

   case 2:
   $listobject->querystring .= " where objectclass = 'modelContainer' ";
   $listobject->querystring .= "    and scenarioid = $scid ";
   $listobject->querystring .= "    and elemname = '$elemname' ";
   print("$listobject->querystring \n");
   $listobject->performQuery();
   $outlets = $listobject->queryrecords;
   break;

   case 3:
   // gets all possible outlet locations, then sorts them by order (highest to lowest)
   // MUST set the overwrite variable to 0, otherwise, the lower orders will be repeatedly run
   $force_overwrite = 0;
   $elems = getICPRBElements($listobject, $scid, 1, 0);
   $ordered = groupByOrder($listobject, $elems);
   $outlets = array();
   $orders = array_keys($ordered);
   rsort($orders);
   print_r($ordered);
   foreach ($orders as $thisorder) {
      foreach ($ordered[$thisorder]['elements'] as $thisel) {
         $outlets[] = array('elementid'=>$thisel, 'elemname' => getElementName($listobject, $thisel));
      }
   }
   break;

   case 4:
   // new custom object notation
   $listobject->querystring .= " where scenarioid = $scid ";
   $listobject->querystring .= "    and custom1 = 'cova_ws_container' ";
   $listobject->querystring .= "    and custom2 = '$elemname' ";
   print("$listobject->querystring \n");
   $listobject->performQuery();
   $outlets = $listobject->queryrecords;
   break;
}
print("$listobject->querystring \n");

foreach ($outlets as $thisrec) {

   $now = date('r');
   $recid = $thisrec['elementid'];
   $recname = $thisrec['elemname'];
   print("$now : Run $thisid / $recname, running $recid \n");
   
   if ($force_overwrite) {
      if ($force_overwrite == 2) {
         // just remove the trunk of this tree, leave all branches alone
         print("Removing trunk of tree $recid / run id $run_id \n");
         removeRunCache($listobject, $recid, $run_id);
      } else {
         print("Removing all in tree $recid / run id $run_id \n");
         removeTreeCache($listobject, $recid, $run_id);
      }
   }
   
   $waiting = 1;
   while ( $waiting )  {
      print("Shaking the tree on $recid (IP: $serverip)\n");
      $run_finished = shakeTree($listobject, $serverip, $max_simultaneous, $recid, $run_id, $startdate, $enddate, $cache_date, $debug);
      if ($run_finished) {
         $waiting = 0;
      } else {
         print("Sleeping for $sleep_factor \n");
         sleep($sleep_factor);
      }
   }
}

print("Done.\n");
   
?>
