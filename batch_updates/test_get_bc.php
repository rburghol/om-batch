<html>
<body>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 20;

error_reporting(E_ALL);
include_once('xajax_modeling.element.php');
include_once('lib_verify.php');

if (isset($_GET['elementid'])) {
   $elid = $_GET['elementid'];
} else {
   $elid = $argv[1];
}
$mode = '';
if (isset($_GET['mode'])) {
   $mode = $_GET['mode'];
}
$bclass = '';
if (isset($_GET['class'])) {
   $bclass = $_GET['class'];
}

$phtml = '';
$chtml = '';

print("Getting broadcast objects for $elementid .<br>\n");
// this gets all broadcasts, so that we can include links to see the details for them
$c_casts = getBroadCasts($elid, 'child', '', '');
$p_casts = getBroadCasts($elid, 'parent', '', '');

if ( (count($c_casts) == 0) and (count($p_casts) == 0)) {
   $info = 'No Broadcast Classes Found';
} else {

   $pclasses = array_keys($p_casts);
   $phtml .= "<table><tr><td>";
   $phtml .= print_r($pclasses,1);
   $phtml .= "</td></tr></table>";

   $cclasses = array_keys($c_casts);
   $chtml .= "<table><tr><td>";
   $chtml .= print_r($cclasses,1);
   $childrecs = getChildComponentType($listobject, $elid);
   $chtml .= "<table><tr>";
   foreach ($childrecs as $thisrec) {
      $cid = $thisrec['elementid'];
      $cname = $thisrec['elemname'];
      //$child_casts = getBroadCasts($cid, 'parent', '', '');
      $chtml .= "<td> Child $cname ($cid) </td>";
   }
   $chtml .= "</tr></table>";
   $chtml .= "</tr></table>";
   $chtml .= "</td></tr></table>";
   
   $info = $phtml .= "<br>";
   $info .= "Object $elid " . getElementName($listobject, $elid) . "<br>";
   $info .= $chtml;
} 

print("$info");
print("Finished.  Saved $i items.<br>");

?>

</body>
</html>