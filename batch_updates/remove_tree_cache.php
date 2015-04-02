<?php

//include('./xajax_modeling.element.php');
$root = 1;

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
   include('/var/www/html/wooommdev/xajax_modeling.element.php');
   break;
}
include_once("./lib_verify.php");
//include_once("./cova/lib_batchmodel.php");
error_reporting(E_ERROR);

$startdate = '1984-01-01';
//$enddate = '1984-12-31';
$enddate = '2005-12-31';
$cache_date = '2010-10-18 12:00:00';
// specify max models to run at a time
$max_simultaneous = 5; // set to 4 while dumping WDMs cause it caues all kinds of slowness
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
   $debug = $argv[4];
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
print("Using DBConn to $dbip \n");

$run_name = $run_names[$run_id];

$listobject->querystring = "  select elementid from scen_model_element ";
// this gets us only ICPRB segments, we can do the CBP ones later when we need to
switch ($operation) {
   case 1:
   $listobject->querystring .= " where elementid  = $elemname ";
   $listobject->performQuery();
   $outlets = $listobject->queryrecords;
   break;

   case 2:
   $listobject->querystring .= " where objectclass = 'modelContainer' ";
   $listobject->querystring .= "    and scenarioid = $scid ";
   $listobject->querystring .= "    and elemname = '$elemname' ";
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
   $listobject->performQuery();
   $outlets = $listobject->queryrecords;
   break;

   case 5:
   // load a file of custom2's
   // if in load from file mode, the second paramter "elemname" is actually the file name
   $batchlist = file_get_contents($elemname);
   $c2list = "'" . join("','", split("\n",$batchlist) ) . "'";
   $listobject->querystring .= " where scenarioid = $scid and custom2 in ($c2list)  ";
   $listobject->querystring .= " and custom2 is not null  ";
   $listobject->querystring .= " and custom2 <> '' ";
   $listobject->performQuery();
   $outlets = $listobject->queryrecords;
   break;

   case 6:
   // load a file of elementid's
   $batchlist = file_get_contents($elemname);
   $ellist = join(",", split("\n",$batchlist) );
   $listobject->querystring .= " where scenarioid = $scid and elementid in ($ellist) ";
   $listobject->performQuery();
   $outlets = $listobject->queryrecords;
   break;
}
print("$listobject->querystring \n");

// first, clear run data if need be:
foreach ($outlets as $thisrec) {

   $now = date('r');
   $recid = $thisrec['elementid'];
   $recname = $thisrec['elemname'];
   print("$now : Removing $recid / Run $run_id (Debug = $debug)\n");
   
   removeTreeCache($listobject, $recid, $run_id, $debug);
}

print("Done.\n");
   
?>
