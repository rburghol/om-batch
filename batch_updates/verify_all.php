<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");

$startdate = '1984-01-01';
//$enddate = '1984-12-31';
$enddate = '2005-12-31';
$cache_date = '2010-10-18 12:00:00';
// specify max models to run at a time
$max_simultaneous = 7;
$scid = 28;

if (isset($argv[1])) {
   $operation = $argv[1];
} else {
   $operation = 2;
}
if (isset($argv[2])) {
   $elemname = $argv[2];
} else {
   $elemname = '';
}
if (isset($argv[3])) {
   $runid = $argv[3];
} else {
   $runid = 1;
}
if (isset($argv[4])) {
   $force_overwrite = $argv[4];
}
if (isset($argv[5])) {
   $startdate = $argv[5];
}
if (isset($argv[6])) {
   $enddate = $argv[6];
}
if (isset($argv[7])) {
   $cache_date = $argv[7];
}


if (isset($_GET['elemname'])) {
   $elemname = $_GET['elemname'];
}
if (isset($_GET['operation'])) {
   $operation = $_GET['operation'];
}
if (isset($_GET['elementid'])) {
   $elemname = $_GET['elementid'];
   $operation = 2;
}
if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}
if (isset($_GET['showpass'])) {
   $showpass = $_GET['showpass'];
} else {
   // defaults to only showing failed records
   $showpass = 0;
}

$run_names = array(
   1=>'baseline',
   2=>'current'
);


// this gets us only ICPRB segments, we can do the CBP ones later when we need to
switch ($operation) {
   case 1:
   $elementid = getElementID($listobject, $scid, $elemname);
   $outlets = getNestedContainers($listobject, $elementid);
   break;

   case 2:
   $outlets = getNestedContainers($listobject, $elemname);
   $elementid = $elemname;
   $elemname = getElementName($listobject, $elementid);
   break;

   case 3:
   // gets all possible outlet locations, then sorts them by order (highest to lowest)
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
}

switch ($force_overwrite) {
   case 1:
   // replace all records
   $force = 1;
   break;
   
   case 2:
   // just replace the trunk of the tree
   $force = 0;
   postSummary($listobject, $recid, $runid, '', 0);
   break;
}

print("$listobject->querystring \n");
//print("Checking " .print_r($outlets,1) . " \n");
if (isset($elementid)) {
   print("<a href='./get_resultTree.php?elementid=$elementid&runid=$runid'>Click here to see the result tree for this object</a> <br>\n");
}


foreach ($outlets as $thisrec) {
   $recid = $thisrec['elementid'];
   summarizeRun($listobject, $recid, $runid, $startdate, $enddate, $force);
}
print("Done.\n");
   
?>
