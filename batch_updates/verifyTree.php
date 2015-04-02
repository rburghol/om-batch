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
if (isset($_GET['refreshparent'])) {
   $refreshparent = $_GET['refreshparent'];
} else {
   // defaults to only showing failed records
   $refreshparent = 0;
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
}
print("$listobject->querystring \n");
//print("Checking " .print_r($outlets,1) . " \n");
if (isset($elementid)) {
   print("<a href='./get_resultTree.php?elementid=$elementid&runid=$runid'>Click here to see the result tree for this object</a> <br>\n");
}

$msg_bad = '';
$msg_ok = '';
$msg_null = '';
$badno = 0;
$okno = 0;
$nullno = 0;

foreach ($outlets as $thischild) {
   $childinfo = retrieveRunSummary($listobject, $thischild['elementid'], $runid);
   //print_r($childinfo);
   switch ($childinfo['run_verified']) {
      case '-1':
      // no record
      $list_null .= $childinfo['elementid'] . "<br>";
      $msg_null .= $childinfo['run_summary'] . "<br>";
      $nullno++;
      break;
      
      case '0':
      $list_bad .= $childinfo['elementid'] . "<br>";
      $msg_bad .= $childinfo['run_summary'] . "<br>";
      $badno++;
      break;
      
      case '1':
      $list_ok .= $childinfo['elementid'] . "<br>";
      $msg_ok .= $childinfo['run_summary'] . "<br>";
      $okno++;
      break;
      
      default:
      // no record
      $list_null .= $childinfo['elementid'] . "<br>";
      $msg_null .= $childinfo['run_summary'] . "<br>";
      $nullno++;
      break;
      
   }
   
}

print("Done.\n");
print("<hr>");
print("<table><tr>");
print("<td valign=top><b>Failed Verification:</b> $badno elements:<br>$list_bad<br>$msg_bad</td>");
if ($showpass) {
   print("<td valign=top><b>Passed Verification</b> $okno elements<br>$msg_ok</td>");
} else {
   print("<td valign=top><b>Passed Verification</b> $okno elements<br>$list_ok</td>");
}
print("<td valign=top><b>No Run File Exits</b> $nullno elements<br>$list_null</td>");
print("</tr></table>");
   
?>
