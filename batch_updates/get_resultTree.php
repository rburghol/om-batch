<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");

$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

if (isset($_GET['elementid'])) {
   $elid = $_GET['elementid'];
   $format = 'table';
   $runid = 1;
} else {
   $elid = $argv[1];
   $format = $argv[2];
   $runid = $argv[3];
}
if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}
if (isset($_GET['format'])) {
   $format = $_GET['format'];
}
if (isset($_GET['host'])) {
   $host = $_GET['host'];
} else {
   $host = $serverip;
}

$quick_num = 1000;

$rundata = retrieveRunSummary($listobject, $elid, $runid);

if (strlen(trim($rundata['run_summary'])) == 0) {
   $elname = getElementName($listobject, $elid);
   $order = $rundata['order'];
   $status = $rundata['run_status'];
   $rundata['message'] = "No run info stored for $elname ($elid) <br>\n";
   $rundata['message'] .= "Run Status: $status <br>\n";
   //$rundata['message'] .= "Query: " . $rundata['query'] . "<br>\n";
} else {
   $rundata['message'] = $rundata['run_summary'];
}

if (isset($elid)) {
   print("<a href='./verifyTree.php?elementid=$elid&runid=$runid'>Click here to verify this tree</a> <br>\n");
}


$children = getNextContainers($listobject, $elid);
foreach ($children as $thischild) {
   $cid = $thischild['elementid'];
   $childinfo = retrieveRunSummary($listobject, $cid, $runid);
   if (strlen(trim($childinfo['run_summary'])) == 0) {
      $elname = getElementName($listobject, $cid);
      $childinfo['message'] = "No run info stored for $elname ($cid) <br>\n";
   } else {
      $childinfo['message'] = $childinfo['run_summary'];
   }
   $childinfo['link'] = "./get_resultTree.php?elementid=$cid&runid=$runid";
   $rundata['children'][] = $childinfo;
}
$parentid = getElementContainer($listobject, $elid);
$parentname = getElementName($listobject, $parentid);
print("<a href='./get_resultTree.php?elementid=$parentid&runid=$runid'>Click here to see element parent - $parentname ($parentid) </a><hr>");
$formatted = formatPrintMessages($rundata);
print($formatted);

?>