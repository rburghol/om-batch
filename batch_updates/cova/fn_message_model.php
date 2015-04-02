<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 4) {
   print("Usage: php fn_message_model.php elementid host message [runid = NULL] \n");
   die;
}
$elid = $argv[1];
$sip = $argv[2];
$msg_type = $argv[3];
if (isset($argv[4])) {
   $runid = $argv[4];
} else {
   $runid = NULL;
}
addMessage($listobject, $elid, $sip, $msg_type, $runid);

?>
