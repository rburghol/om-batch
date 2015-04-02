<?php

$noajax = 1;
$projectid = 3;
$target_scenarioid = 28;
$cbp_scenario = 3;
include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);

if (count($argv) < 3) {
   print("Usage: php cbp_icprb_landuse.php lutable matrixname [startyear=1980] [endyear=2050] [scenarioid=28]");
   die;
}
$lutable = $argv[1];
$matrixname = $argv[2];
if (isset($argv[3])) {
   $startyear = $argv[3];
} else {
   $startyear = 1980;
}
if (isset($argv[4])) {
   $endyear = $argv[4];
} else {
   $endyear = 2050;
}

include_once("./lib_batchmodel.php");
// START - set up database connections
$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

}

// get a list of land uses
$cbp_listobject->querystring = "  select b.id3 as luname  ";
$cbp_listobject->querystring .= " from cbp_model_location as b ";
$cbp_listobject->querystring .= " where b.scenarioid = $cbp_scenario ";
$cbp_listobject->querystring .= " and b.id1 = 'land' ";
$cbp_listobject->querystring .= " group by b.id3 ";
$cbp_listobject->querystring .= " order by b.id3 ";
print("$cbp_listobject->querystring ; <br>");
$cbp_listobject->performQuery();
$lunames = $cbp_listobject->queryrecords;
//just select every object that has an entry in the desired land use table
// NOW - BEGIN to create the model components
// just 
$cbp_listobject->querystring .= " UNION ";
$cbp_listobject->querystring .= " (select 'PR0_0001_0000' as newriverseg, '0001' as f_node, '0000' as t_node, 0.0 as area_sqmi) ";
print("$cbp_listobject->querystring ; <br>");
$cbp_listobject->performQuery();
//$cbp_listobject->showList();
$recs = $cbp_listobject->queryrecords;
$rec_nos = array();

foreach ($recs as $thisrec) {
   $cbp_segmentid = $thisrec['newriverseg'];
   $area_sqmi = $thisrec['area_sqmi'];
   print("Creating $cbp_segmentid <br>");
   $copy = copyModelGroupFull($cbp_copy_params);
   $map = $copy['element_map'];
   $parent = $map[$cbp_container_el]['new_id'];
   $rec_nos[$cbp_segmentid] = $parent;
   updateObjectProps($projectid, $parent, array('name'=>$cbp_segmentid, 'description'=>"Main CBP Segment Container for $cbp_segmentid", 'debug'=>0));
   
   // do updates to the Graphs
   updateObjectProps($projectid, $map[$cbp_graph_el]['new_id'], array('name'=>"Graph: " . $shed_merge, 'title'=>"Flows - $shed_merge", 'debug'=>0));
   updateObjectProps($projectid, $map[$cbp_gini_el]['new_id'], array('name'=>"Graph: Gini " . $shed_merge, 'title'=>"Gini - $shed_merge", 'debug'=>0));
   updateObjectProps($projectid, $map[$cbp_impoundment_el]['new_id'], array('name'=>"Impoundment on " . $shed_merge, 'debug'=>0));   
   // do updates to the river segment Main Stem
   $riverid = $map[$cbp_river_el]['new_id'];
   updateObjectProps($projectid, $riverid, array('name'=>"Main Stem (adjustable) " . $shed_merge, 'area'=>$area_sqmi, 'debug'=>0));   
   
   //**********************************************************
   // START - get land use from cbp for this segment
   //**********************************************************
   
   // get land segments matching this unique_id
   $cbp_listobject->querystring = " select \"FIPSAB\" as landseg from tmp_icprb_landuse_baseline where uniqid is null and riverseg = '$cbp_segmentid'";
   print("$cbp_listobject->querystring ; <br>");
   $cbp_listobject->performQuery();
   $lus = $cbp_listobject->queryrecords;
   $nolu = 0;
   // do updates to the land use child
   foreach ($lus as $thislu) {
      $landseg = $thislu['landseg'];
      $cid = $map[$cbp_landuse_el]['new_id'];
      if ($nolu == 0) {
         print("Updating $cid $landseg ; <br>");
         // updating the first copy, so no need to make another one
      } else {
         // this segment needs additional landsegs, so we must copy them
         print("Cloning $landseg ; <br>");
         $cloneresult = cloneModelElement($target_scenarioid, $cbp_landuse_el);
         $cid = $cloneresult['elementid'];
         print("Updating $cid $landseg ; <br>");

         //print("reCreate: $msg <br>\n");
      }
      $msg = runObjectCreate($projectid, $cid);
      updateObjectProps($projectid, $cid, array('name'=>"Land Segment $landseg", 'id2'=>$landseg, 'debug'=>0));
      $linkhtml = createObjectLink($projectid, $target_scenarioid, $cid, $parent, 1);
      print(print_r($linkhtml,1) . " <br>");

      $lu_tables = array('baseline', 'current');
      foreach ($lu_tables as $thislu_tab) {

         $cbp_listobject->querystring = " select * from tmp_icprb_landuse_$thislu_tab where uniqid is null and riverseg = '$cbp_segmentid' and \"FIPSAB\" = '$landseg' ";
         print("$cbp_listobject->querystring ; <br>");
         $cbp_listobject->performQuery();
         $lutabs = $cbp_listobject->queryrecords;
         foreach ($lutabs as $thistab) {
            // now apply land use
            $landuse = array();
            $k = 0;
            foreach ($lunames as $thislunamerec) {
               $thisluname = $thislunamerec['luname'];
               $landuse[$k]['luname'] = $thisluname;
               $landuse[$k]['1980'] = number_format(floatval($thistab[$thisluname]),2, '.', '');
               $landuse[$k]['2010'] = number_format(floatval($thistab[$thisluname]),2, '.', '');
               $k++;
            }
            print("Trying to apply land use matrix: " . print_r($landuse,1) . "<br>\n");
            $loadres = unSerializeSingleModelObject($cid);
            $thisobject = $loadres['object'];
            if (is_object($thisobject)) {
               //print("$cid object retrieved<br>\n");
               if (is_object($thisobject->processors["landuse_$thislu_tab"])) {
                  print("$cid object landuse found landuse_$thislu_tab<br>\n");
                  if (method_exists($thisobject->processors["landuse_$thislu_tab"], 'assocArrayToMatrix')) {
                     //print("$cid object assocArrayToMatrix() exists<br>\n");
                     $thisobject->processors["landuse_$thislu_tab"]->assocArrayToMatrix($landuse);
                     saveObjectSubComponents($listobject, $thisobject, $cid, 1);
                  }
               }
            }
         }
      }

      $nolu++;
   }
   //**********************************************************
   // END - get land use from cbp for this segment
   //**********************************************************
}

// NOW create linkages for CBP segment containers
reset($recs);
foreach ($recs as $thisrec) {
   $cbp_segmentid = $thisrec['newriverseg'];
   $t_node = $thisrec['t_node'];
   $listobject->querystring = "  select elementid from scen_model_element where substring(elemname,5,4) = '$t_node' and scenarioid = $target_scenarioid ";
   print("$listobject->querystring ; <br>");
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $parent = $listobject->getRecordValue(1,'elementid');
      $listobject->querystring = "  select elementid from scen_model_element where elemname = '$cbp_segmentid' and scenarioid = $target_scenarioid ";
      $listobject->performQuery();
      $child = $listobject->getRecordValue(1,'elementid');
      createObjectLink($projectid, $target_scenarioid, $child, $parent, 1);
      //print(print_r($linkhtml,1) . " <br>");
      print("Linked child ($child) to parent ($parent)<br>\n");
   } else {
      print("Element $cbp_segmentid / $unique_id does not have a parent container ('$t_node')<br>\n");
   }
}
//**************************************************************************************************************
// END - CBP River Segment Containers
//**************************************************************************************************************


//**************************************************************************************************************
// START - ICPRB Custom sub-segments
//**************************************************************************************************************
// custom ICPRB Sub-segments template
$container_el = 73527;
$impoundment_el = 103453;
$river_el = 76103;
$landuse_el = 73535;
$gage_el = 79092;
$graph_el = 73533;
$gini_el = 73531;
$copy_params = array(
   'projectid'=>$projectid,
   'dest_scenarioid'=>$target_scenarioid,
   'elements'=>array($container_el)
);
$cbp_listobject->querystring = " select * ";
//$cbp_listobject->querystring .= " from tmp_icprb_sheds ";
$cbp_listobject->querystring .= " from icprb_watersheds ";
//$cbp_listobject->querystring .= "    where cbp_segmentid = 'PL0_4510_0001' ";
//$cbp_listobject->querystring .= "    where cbp_segmentid like 'PL0%' ";
$cbp_listobject->querystring .= " where unique_id is not null ";
$cbp_listobject->querystring .= " order by t_node ";
print("$cbp_listobject->querystring ; <br>");
$cbp_listobject->performQuery();
//$cbp_listobject->showList();
$recs = $cbp_listobject->queryrecords;

foreach ($recs as $thisrec) {
   $unique_id = $thisrec['unique_id'];
   $shed_merge = $thisrec['shed_merge'];
   $area_sqmi = $thisrec['local_area'];
   // BEGIN select mainsteam stream channel properties
   $length_ft = $thisrec['local_length'];
   $slope = $thisrec['local_slope'];
   $phys_prov = $thisrec['phys_prov'];
   $contrib_area_sqmi = $thisrec['total_area'];
   // END select mainstem stream channel properties
   print("Creating $unique_id - $shed_merge <br>");
   $copy = copyModelGroupFull($copy_params);
   $map = $copy['element_map'];
   $parent = $map[$container_el]['new_id'];
   $rec_nos[$shed_merge] = $parent;
   $cbp_segmentid = $thisrec['cbp_segmentid'];
   updateObjectProps($projectid, $parent, array('name'=>$shed_merge, 'description'=>$unique_id, 'debug'=>0));
   
   // do updates to the Graphs
   updateObjectProps($projectid, $map[$graph_el]['new_id'], array('name'=>"Graph: " . $shed_merge, 'title'=>"Flows - $shed_merge", 'debug'=>0));
   updateObjectProps($projectid, $map[$gini_el]['new_id'], array('name'=>"Graph: Gini " . $shed_merge, 'title'=>"Gini - $shed_merge", 'debug'=>0));
   updateObjectProps($projectid, $map[$impoundment_el]['new_id'], array('name'=>"Impoundment on " . $shed_merge, 'debug'=>0));
   
   // do updates to the river segment Main Stem
   $riverid = $map[$river_el]['new_id'];
   updateObjectProps($projectid, $riverid, array('name'=>"Main Stem " . $shed_merge, 'area'=>$area_sqmi, 'drainage_area'=>$contrib_area_sqmi, 'slope' => $slope, 'length' => $length_ft, 'province' => $phys_prov, 'debug'=>0));
   
   // update/create land use runoff objects
   // get land segments matching this unique_id
   $cbp_listobject->querystring = " select \"FIPSAB\" as landseg from tmp_icprb_landuse_baseline where uniqid = '$unique_id' and riverseg = '$cbp_segmentid'";
   print("$cbp_listobject->querystring ; <br>");
   $cbp_listobject->performQuery();
   $lus = $cbp_listobject->queryrecords;
   $nolu = 0;
   // do updates to the land use child
   foreach ($lus as $thislu) {
      $landseg = $thislu['landseg'];
      $cid = $map[$landuse_el]['new_id'];
      if ($nolu == 0) {
         print("Updating $cid $landseg ; <br>");
         // these updates are not yet working...
      } else {
         print("Cloning $landseg ; <br>");
         $cloneresult = cloneModelElement($target_scenarioid, $landuse_el);
         $cid = $cloneresult['elementid'];
         print("Updating $cid $landseg ; <br>");

         //print("reCreate: $msg <br>\n");
      }
      $msg = runObjectCreate($projectid, $cid);
      updateObjectProps($projectid, $cid, array('name'=>"Land Segment $landseg", 'id2'=>$landseg, 'debug'=>0));
      $linkhtml = createObjectLink($projectid, $target_scenarioid, $cid, $parent, 1);
      print(print_r($linkhtml,1) . " <br>");

      $lu_tables = array('baseline', 'current');
      foreach ($lu_tables as $thislu_tab) {

         $cbp_listobject->querystring = " select * from tmp_icprb_landuse_$thislu_tab where uniqid = '$unique_id' and riverseg = '$cbp_segmentid' and \"FIPSAB\" = '$landseg' ";
         print("$cbp_listobject->querystring ; <br>");
         $cbp_listobject->performQuery();
         $lutabs = $cbp_listobject->queryrecords;
         foreach ($lutabs as $thistab) {
            // now apply land use
            $landuse = array();
            $k = 0;
            foreach ($lunames as $thislunamerec) {
               $thisluname = $thislunamerec['luname'];
               $landuse[$k]['luname'] = $thisluname;
               $landuse[$k]['1980'] = number_format(floatval($thistab[$thisluname]),2, '.', '');
               $landuse[$k]['2010'] = number_format(floatval($thistab[$thisluname]),2, '.', '');
               $k++;
            }
            print("Trying to apply land use matrix: " . print_r($landuse,1) . "<br>\n");
            $loadres = unSerializeSingleModelObject($cid);
            $thisobject = $loadres['object'];
            if (is_object($thisobject)) {
               //print("$cid object retrieved<br>\n");
               if (is_object($thisobject->processors["landuse_$thislu_tab"])) {
                  print("$cid object landuse found landuse_$thislu_tab<br>\n");
                  if (method_exists($thisobject->processors["landuse_$thislu_tab"], 'assocArrayToMatrix')) {
                     //print("$cid object assocArrayToMatrix() exists<br>\n");
                     $thisobject->processors["landuse_$thislu_tab"]->assocArrayToMatrix($landuse);
                     saveObjectSubComponents($listobject, $thisobject, $cid, 1);
                  }
               }
            }
         }
      }

      $nolu++;
   }
   //break;
}

// START - Establish linkages for sub-segment types
reset($recs);
foreach ($recs as $thisrec) {
   $unique_id = $thisrec['unique_id'];
   $shed_merge = $thisrec['shed_merge'];
   $t_node = $thisrec['t_node'];
   if (isset($rec_nos[$t_node])) {
      $listobject->querystring = "  select elementid from scen_model_element where elemname = '$shed_merge' and scenarioid = $target_scenarioid ";
      $listobject->performQuery();
      $parent = $rec_nos[$t_node];
      $child = $listobject->getRecordValue(1,'elementid');
      createObjectLink($projectid, $target_scenarioid, $child, $parent, 1);
      //print(print_r($linkhtml,1) . " <br>");
      print("Linked child ($child) to parent ($parent)<br>\n");
   } else {
      print("Element $shed_merge / $unique_id does not have a parent container ('$t_node')<br>\n");
   }
}
//**************************************************************************************************************
// END - ICPRB Custom sub-segments
//**************************************************************************************************************

// update geometries for both CBP and ICPRB segments
// match on shed_merge for ICPRB segments, and match on riverseg = elemname for CHBP segments
$listobject->querystring = "  update scen_model_element set geomtype = 3, ";
$listobject->querystring .= "    poly_geom = force_2d(transform(setsrid(a.the_geom,26918),4326)) ";
$listobject->querystring .= " from icprb_local_shapes as a ";
$listobject->querystring .= " where a.riverseg = scen_model_element.elemname";
$listobject->querystring .= "  and scenarioid = 28 ";
$listobject->querystring .= "  and a.uniq_id is null ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();
$listobject->querystring = "  update scen_model_element set geomtype = 3, ";
$listobject->querystring .= "    poly_geom = force_2d(transform(setsrid(a.the_geom,26918),4326)) ";
$listobject->querystring .= " from icprb_local_shapes as a, icprb_watersheds as b ";
$listobject->querystring .= " where b.shed_merge = scen_model_element.elemname ";
$listobject->querystring .= "    and b.unique_id = a.uniq_id ";
$listobject->querystring .= "  and scenarioid = 28 ";
$listobject->querystring .= "  and a.uniq_id is not null ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();


// update geometries for both CBP and ICPRB segments
$listobject->querystring = " update scen_model_element set geomtype = 3, poly_geom = force_2d(transform(a.the_geom,4326))  ";
$listobject->querystring .= " from sc_cbp5 as a  ";
$listobject->querystring .= " where a.catcode2 = scen_model_element.elemname ";
$listobject->querystring .= " and scenarioid = 28 ";
$listobject->querystring .= " and poly_geom is null ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();

?>
