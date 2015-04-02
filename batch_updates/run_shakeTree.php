<?php

//include('./xajax_modeling.element.php');
$root = 1;

switch ($root) {
   case 0:
   include('/var/www/html/om/xajax_modeling.element.php');
   print("Using stable version of model - library = $libpath \n");
   break;

   case 1:
   include('/var/www/html/om/xajax_modeling.element.php');
   print("Using development version of model\n");
   break;

   default:
   include('/var/www/html/om/xajax_modeling.element.php');
   break;
}
error_reporting(E_ERROR);
//error_reporting(E_ALL);
include_once("/var/www/html/om/lib_verify.php");
include_once("/var/www/html/lib/lib_batchmodel.php");

$startdate = '1984-01-01';
//$enddate = '1984-12-31';
$enddate = '2005-12-31';
$cache_date = '2010-10-18 12:00:00';
// specify max models to run at a time
$max_simultaneous = 5; // set to 4 while dumping WDMs cause it caues all kinds of slowness
$scid = 37;
print("Using DBConn to $dbip \n");
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
// $strict setting - whether or not to evaluate the unit area on a relatively strict limit 
// strict = 0 - do the run verification routine, but use broad criteria to evaluate it
// strict = 1 - verify with narrow valid criteria
// strict = -1 - do not verify, assume it is OK
if (isset($argv[9])) {
   $strict = $argv[9];
} else {
   $strict = 1;
}
if (isset($argv[10])) {
   $run_mode = $argv[10];
} else {
   $run_mode = NULL;
}

if (isset($argv[11])) {
   $run_method = $argv[11];
} else {
   $run_method = 'normal';
}
$url_params = array();
if (isset($argv[12])) {
   $url_pieces = explode("&", $argv[12]);
   foreach ($url_pieces as $thispiece) {
      list($key, $val) = explode('=', $thispiece);
      $url_params[$key] = $val;
   }
}
print("URL Params submitted: " . print_r($url_params,1) . "\n");
if (isset($argv[13])) {
   $debug = $argv[13];
} else {
   $debug = 0;
}

// set up the sleep factor
$start_year = intval(date('Y', strtotime($startdate)));
$end_year = intval(date('Y', strtotime($enddate)));
$years = $end_year - $start_year;
if ($years <= 0) {
   $years = 1;
}
$sleep_factor = $years * 10; // give it some time to accumulate  a cache
if ($sleep_factor < 40) {
   $sleep_factor = 40;
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
   $listobject->querystring .= "    and custom1 in ( 'cova_ws_container', 'cova_ws_subnodal' ) ";
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
   $batchlist = trim(file_get_contents($elemname));
   $ellist = join(",", split("\n",$batchlist) );
   $listobject->querystring .= " where scenarioid = $scid and elementid in ($ellist) ";
   $listobject->performQuery();
   $outlets = $listobject->queryrecords;
   break;
}
print("$listobject->querystring \n");

// first, clear run dat if need be:
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
}


while (count($outlets) > 0) {

   $now = date('r');
   $recid = $thisrec['elementid'];
   $recname = $thisrec['elemname'];
   print("$now : Run $thisid / $recname, running $recid \n");

   $thisrec = array_shift($outlets);
   // shaketree
   // if it is finished, do nothing,
   // otherwise, push it back on the array stack

   
   print("Shaking the tree on $recid (IP: $serverip)\n");
   $run_finished = shakeTree($listobject, $serverip, $max_simultaneous, $recid, $run_id, $startdate, $enddate, $cache_date, $debug, $strict, $run_mode, $url_params);
   if (!$run_finished) {
      print("Model Element $recid not completed ... sleeping \n");
      // put it back in the array for further waiting
      array_push($outlets, $thisrec);
      // we only need to sleep if the last model that we checked actually was run during the check, otherwise, 
      print("Sleeping for $sleep_factor \n");
      sleep($sleep_factor);
   } else {
      print("Model Element $recid completed run - removing from queue.\n");
   }
}

print("Done.\n");
   
?>
