<?php
$noajax = 1;
$userid = 1;
$scenarioid = 82;
include("./xajax_modeling.element.php");
global  $cbp_listobject, $listobject;
//error_reporting(E_ALL);
$segment = 'JU3_6900_6950';
$endsegment = 'JL7_7070_0001';
$update = 0;
if (isset($argv[1])) {
   // just do one requested stream
   $res = array($argv[1]);
} else {
   $res = getStreamSection($listobject, $segment, $endsegment);
}
// putting in scenarioid = 9 - sample projects
$cbp_copy_params = array(
   'projectid'=>3,
   'dest_scenarioid'=>$scenarioid,
   'elements'=>array(207667),
   'dest_parent'=>327456
);
$lastseg = -1;
$k = 1;

foreach ($res as $thisone) {
   // copy the segments
   $prop_array = array('name' => "R." . str_pad($k, 2, '0', STR_PAD_LEFT) . " $thisone", 'province'=>2, 'n'=>0.036);
   if ($update) {
      // just reset the reaxch properties, otherwise insert new ones
      print("Searching for " . $thisone . "\n");
      //$cid = getElementID($listobject, $scenarioid, $prop_array['name']);
      $elprops = getComponentCustom($listobject, $scenarioid, 'usgs_physchannel', $thisone, 1, array(), 1);
      $cid = $elprops[0]['elementid'];
   } else {
      $output = copyModelGroupFull($cbp_copy_params, 1);
      print_r($output);
      $cid = $output['element_map'][207667]['new_id'];
      deleteSubComponent($cid, 'Qin'); // have to remove this since the template is based on the VAHydro setup which overrides the Qin var
   }
   print("Found elementid $cid \n");
   //die;
   // link it to its upstream segment (the last segment added $lastseg)
   // set its parameters from sc_cbp5
   setCOVACBPReachProps($projectid, $cbp_listobject, $listobject, $thisone, $cid, 1, 2);
   if ( ($lastseg <> -1) and (!$update) ) { 
      // link the inflow of this segment to the outflow of the last segment
       createObjectLink(3, 9, $lastseg, $cid, 2, 'Qout', 'Qin');
   } else {
      echo "Updating - no linking required\n";
   }
   updateObjectProps(3, $cid, $prop_array);
   $lastseg = $cid;
   $k++;
}

?>
