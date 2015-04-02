<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

// get outlet for terminal CBP segment
// OR, set them up manually as in here


error_reporting(E_NONE);

if (count($argv) < 2) {
   print("Usage: php move_element.php elementid [params in format name=value options are parentid,scenarioid] \n");
   die;
}

$elementid = $argv[1];
$params = array();
for ($i = 2; $i < count($argv); $i++) {
   if (isset($argv[$i])) {
      $pv = explode('=', $argv[$i]);
      if (isset($pv[1])) {
         $params[$pv[0]] = $pv[1];
      } else {
         error_log("Malformed parameter " . $argv[$i] . "\n");
      }
   }
}
error_log("Params " . print_r($params,1));
$debug = 1;
if (isset($params['parentid'])) {
   error_log("Changing object parent for $elementid to  $parentid ");
   $info = getElementInfo($listobject, $params['parentid'], $debug);
   $scenid = $info['scenarioid'];
   $res = createObjectLink($projectid, $scenid, $elementid, $params['parentid'], 1);
   print("Result " . print_r($res,1) . "\n");
}
if (isset($params['scenarioid'])) {
   // a scenarioid change has been requested
   changeObjectDomain($elementid,$params['scenarioid'], $debug);
}
?>
