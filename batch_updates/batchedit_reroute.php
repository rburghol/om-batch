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

error_reporting(E_ERROR);
$elem_list = array();
$elem_list[] = array('PL1_4540_0001A01','PL1_4540_0001','PR0_0001_0000');
$elem_list[] = array('PS2_6730_6660A01','PS2_6730_6660','PS2_6660_6490');
$elem_list[] = array('PU0_9914_3602A01','PU0_9914_3602','PU6_3602_3730');
$elem_list[] = array('PU1_3100_3690A01','PU1_3100_3690','PU6_3690_3610');
$elem_list[] = array('PU1_5520_5210A01','PU1_5520_5210','PU3_5210_5050A01B01');
$elem_list[] = array('PU2_3180_3370A01', 'PU2_3180_3370', 'PU2_3370_4020A01B01C01D01E01F01G01H01I01');
$elem_list[] = array('PM1_4500_4580A01', 'PM1_4500_4580','PM7_4580_4820');
$elem_list[] = array('PU1_5820_5380A01','PU1_5820_5380','PU1_5380_5050A01');
$elem_list[] = array('PU2_3370_4020A01','PU2_3370_4020','PU6_4020_3870A01');
$elem_list[] = array('PU6_4020_3870A01','PU6_4020_3870','PU6_3870_3690');


// get all children
$i = 0;

foreach ($elem_list as $this_set) {
   $thiselem = $this_set[0];
   $thisparent = $this_set[1];
   $thisgrandparent = $this_set[2];
   $listobject->querystring = " select elementid, elemname from scen_model_element where scenarioid = $scid and objectclass = 'modelContainer' and elemname = '$thiselem' ";
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   if (count($listobject->queryrecords) == 1) {
       $thisrec = $listobject->queryrecords[0];
	   $childid = $thisrec['elementid'];
	   $change = 1;
	   // get parent
	   $parentid = getElementContainer($listobject, $childid);
	   $pname = getElementName($listobject, $parentid);
	   if ($pname <> $thisparent) {
	      print("Unexpected parent name - $pname <> $thisparent - will not relink $thiselem\n");
	      $change = 0;
	   }
	   // get grandparent
	   $grandparentid = getElementContainer($listobject, $parentid);
	   $gpname = getElementName($listobject, $grandparentid);
	   if ($gpname <> $thisgrandparent) {
	      print("Unexpected grandparent name - $gpname <> $thisgrandparent - will not relink $thiselem\n");
	      $change = 0;
	   }
	   if ($change) {
	      print("Moving link for $childid from $parentid to $grandparentid \n");
    	   // remove link from parent
	      // add link to grandparent
	      // this routine automatically removes the old when adding the new
	      addObjectLink($projectid, $scid, $childid, $grandparentid, 1);
	      // delete parent
	      deleteModelElement($elementid);
	      // erase run records from grandparent
	      removeRunCache($listobject, $grandparentid, 1);
	      removeRunCache($listobject, $grandparentid, 2);
	      $i++;
	   }
	} else {
	   print("$thiselem not found.\n");
	}
}

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
