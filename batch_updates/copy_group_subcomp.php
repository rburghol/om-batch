<?php

$noajax = 1;
$projectid = 3;
$target_scenarioid = 37;
$cbp_scenario = 4;
$userid = 1;
$noajax = 1;
include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
//include_once("./lib_batchmodel.php");

if (count($argv) < 4) {
   print("Usage: php copy_group_subcomp.php scenarioid src_elementid [subcomp1[|newname1],subcomp2...] [elementid] [elemname] [custom1] [custom2] [function (append,overwrite,delete)]\n");
   print("Use '-1' as value for scenarioid to update all scenarios (use with caution) \n");
   die;
}

$scenarioid = $argv[1];
$src_elementid = $argv[2];
if (isset($argv[3])) {
   $subcomps = split(',', $argv[3]); 
} else {
   $subcomps = array();
}
if (isset($argv[4])) {
   $elementid = $argv[4];
} else {
   $elementid = '';
}
if (isset($argv[5])) {
   $elemname = $argv[5];
} else {
   $elemname = '';
}
if (isset($argv[6])) {
   $custom1 = $argv[6];
} else {
   $custom1 = '';
}
if (isset($argv[7])) {
   $custom2 = $argv[7];
} else {
   $custom2 = '';
}
if (isset($argv[8])) {
   $function = $argv[8];
} else {
   $function = 'append';
}

$obres = unserializeSingleModelObject($src_elementid);
$srcob = $obres['object'];
$name = $srcob->name;
if (count($subcomps) == 0) {
   $subcomps = array_keys($srcob->processors);
}

print("Copying components: " . print_r($subcomps,1) . "\n");

$listobject->querystring = "  select elementid, elemname from scen_model_element ";
$listobject->querystring .= " where ( (scenarioid = $scenarioid) or ($scenarioid = -1) ) ";
if ($elementid <> '') {
   $listobject->querystring .= " AND elementid = $elementid ";
}
if ($elemname <> '') {
   $listobject->querystring .= " AND elemname = '$elemname' ";
}
if ($custom1 <> '') {
   $listobject->querystring .= " AND custom1 = '$custom1' ";
}
if ($custom2 <> '') {
   $listobject->querystring .= " AND custom2 = '$custom2' ";
}
print("$listobject->querystring ; <br>");
$listobject->performQuery();
//$listobject->showList();

$recs = $listobject->queryrecords;
//error_reporting(E_ALL);
foreach ($recs as $thisrec) {
   $destid = $thisrec['elementid'];
   if ( ($src_elementid <> $destid) or ($src_elementid == -1) ) {
      foreach ($subcomps as $thiscomp) {
         $scs = split("\|", $thiscomp);
         if (count($scs) == 1) {
            $scs[1] = $scs[0];
         }
         print("Trying to add Sub-comp $scs[0] to Element $destid <br>\n" . print_r($scs,1) . "\n");
         print("copySubComponent($src_elementid, $scs[0], $destid, $scs[1])<br>\n");
         print("Handling $function request \n");
         switch ($function) {
            case 'overwrite':
            // just copy it and overright
            print("Overwriting\n");
            $cr = copySubComponent($src_elementid, $scs[0], $destid, $scs[1], 1);
            $msg = "Sub-comp $scs[0] added to Element $destid as $scs[1] <br>\n";
            break;
            case 'delete':
            // just copy it and overright
            print("Deletin\n");
            $cr = "This function is not yet enabled \n";
            $msg = "\n";
            break;
            case 'append':
            // do not over-write
            print("Appending\n");
            $cr = copySubComponent($src_elementid, $scs[0], $destid, $scs[1], 0);
            $msg = "Sub-comp $scs[0] added to Element $destid as $scs[1] <br>\n $cr \n";
            break;
         }
            
         print("$cr<br>\n");
         print("$msg<br>\n");
         $i++;
      }
   }
}

?>
