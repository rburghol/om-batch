<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('./xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
$userid = 1;

error_reporting(E_ERROR);
$elem_list = array(159298, 160534, 161016, 161054, 161210, 159866, 161228, 161434, 162438);


// get all children
$i = 0;

foreach ($elem_list as $thiselem) {

   $out = deleteModelElement($thiselem);
   print($out['innerHTML'] . "\n");
   // erase run records from grandparent
   removeRunCache($listobject, $thiselem, 1);
   removeRunCache($listobject, $thiselem, 2);
   removeRunCache($listobject, $thiselem, -1);
   $i++;
}

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
