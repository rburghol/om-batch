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
   $scenid = $argv[1];
   $prop = $argv[2];
} else {
   print("Usage: batch_setprop.php scenarioid prop_name [elementid] [elemname] [custom1] [custom2] [objectclass] \n");
   print("[] may be \"\" to use other criteria \n");
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
$listobject->querystring = "  select elementid, elemname from scen_model_element where scenarioid = $scenid ";
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
   $unser = unserializeSingleModelObject($elid);
   $thisobject = $unser['object'];
   if (property_exists($thisobject, "$prop")) {
      $value = $thisobject->$prop;
   } else {
      $value = " property $prop does not exist ";
   }
   print("$elemname ($elid) -> $prop = $value \n");
   //break;
}

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
