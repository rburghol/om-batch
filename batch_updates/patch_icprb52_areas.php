<?php

$noajax = 1;
$projectid = 3;
$scid = 28;
include("./xajax_modeling.element.php");
include("./lib_verify.php");

// patch ICPRB watershed linkages and names
// two modes: 
   // 1) check for dropped linkages (upstream), missing to_node (downstream) - check for unintended links to new records
   // 2) process changes

// types of changes that may take place:
// watershed object name
// downstream linkages
// upstream linkages
// land use update/insert


if (isset($argv[1])) {
   $mode = $argv[1];
} else {
   print("Missing Operation. Usage: patch_sheds.php filename operation <br>\n");
   die;
}

   
switch ($mode) {
   
   case 1:
      $listobject->querystring = "  select a.riverseg, a.contrib_area_sqmi,  ";
      $listobject->querystring .= "    (b.base_area - b.trib_area) as local_area ";
      $listobject->querystring .= " from (select catcode2 as riverseg, contrib_area_sqmi ";
      $listobject->querystring .= "       from sc_p52icprb ";
      $listobject->querystring .= "       group by catcode2, contrib_area_sqmi ";
      $listobject->querystring .= " )as a,  ";
      $listobject->querystring .= "    (select a.catcode2 as riverseg, ";
      $listobject->querystring .= "     round( sum(area2d(a.the_geom)* 3.86102159E-7)::numeric,2) as base_area, ";
      $listobject->querystring .= "        sum(b.trib_area) as trib_area ";
      $listobject->querystring .= "     from sc_p52icprb as a, ";
      $listobject->querystring .= "        ( select catcode2, ";
      $listobject->querystring .= "          round( sum(area2d(the_geom)* 3.86102159E-7)::numeric,2) as trib_area ";
      $listobject->querystring .= "          from tmp_icprb_localshapes ";
      $listobject->querystring .= "          group by catcode2 ";
      $listobject->querystring .= "     ) as b ";
      $listobject->querystring .= "     where a.catcode2 = b.catcode2 ";
      $listobject->querystring .= "     group by a.catcode2 ";
      $listobject->querystring .= " ) as b ";
      $listobject->querystring .= " where a.riverseg = b.riverseg ";
      $listobject->querystring .= " order by a.riverseg ";
      print("$listobject->querystring \n");
      $listobject->performQuery();
      $recs = $listobject->queryrecords;
      
      foreach ($recs as $thisrec) {
         $elname = $thisrec['riverseg'];
         $elid = getElementID($listobject, $scid, $elname);
         if ($elid) {
            $total_area = trim($thisrec['contrib_area_sqmi']);
            $local_area = trim($thisrec['local_area']);
            // synch the new name on the object properties
            $props = array('drainage_area'=>$total_area, 'area'=>$local_area);
            // now, update this objects children who share its name
            $child_rec = getChildComponentType($listobject, $elid, 'USGSChannelGeomObject', 1);
            $mainstem = $child_rec[0];
            $cid = $mainstem['elementid'];
            print("Updating $elname ($cid) - setting 'drainage_area'=>$total_area 'area'=>$local_area\n");
            updateObjectProps($projectid, $cid, $props);
         }
      }
   break;   
}
//print_r($verification);

print("Naming Problems:\n");
print_r($verification['name_problems']);

?>