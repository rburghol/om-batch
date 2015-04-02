<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
include_once('./xajax_modeling.element.php');
error_reporting (E_ERROR);

if ( isset($argv[1]) and isset($argv[2]) ) {
   $scenarioid = $argv[1];
   $propvals = split('=', $argv[2]);
   if (count($propvals) > 1) {
      $prop = $propvals[0];
      $value = join ('=', array_slice($propvals,1));
      $cmd = 'setprop';
   } else {
      $cmd = $propvals[0];
   }
   //list($prop,$value) = split('=', $argv[2]);
} else {
   print("Usage: batch_setprop.php scenarioid \"prop=value\" [elementid] [elemname] [custom1] [custom2] [objectclass] \n");
   print("[] may be \"\" to use other criteria \n");
   print(" Instead of prop=value, other commands:\n");
   print("    resave - just open and save each element specified \n");
   print("Use '-1' as value for scenarioid to update all scenarios (use with caution) \n");
   die;
}
if (isset($argv[3])) {
   $elementid = $argv[3];
} else {
   $elementid = '';
}
if (isset($argv[4])) {
   $elemname = $argv[4];
} else {
   $elemname = '';
}
if (isset($argv[5])) {
   $custom1 = $argv[5];
} else {
   $custom1 = '';
}
if (isset($argv[6])) {
   $custom2 = $argv[6];
} else {
   $custom2 = '';
}
if (isset($argv[7])) {
   $objectclass = $argv[7];
} else {
   $objectclass = '';
}

$segs = array();
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
if ($objectclass <> '') {
   $listobject->querystring .= " AND objectclass = '$objectclass' ";
}
print("Looking for match <br>\n");
print("$listobject->querystring ; <br>\n");
$listobject->performQuery();
$recs = $listobject->queryrecords;

$serializer = new XML_Serializer();
foreach ($recs as $thisrec) {
   $elid = $thisrec['elementid'];
   $elemname = $thisrec['elemname'];
   print("Setting $prop = $value on $elemname ($elid) \n");
   switch ($cmd) {
      case 'setprop':
         $prop_array = array($prop => $value );
      break;
      
      case 'resave':
         $prop_array = array();
      break;
      
      default:
         $prop_array = array($prop => $value );
      break;
   }
   print("Cmd = $cmd - setting " . print_r($prop_array,1) . "\n");
   updateObjectProps($projectid, $elid, $prop_array, 0);
   $i++;
   //break;
}

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
