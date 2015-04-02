<?php

$noajax = 1;
$projectid = 3;
$target_scenarioid = 28;
$cbp_scenario = 3;
# branch outlets
# North Fork New River 9170
# New River 8051
# North Branch Potomac 3930
# South Branch Potomac 4210
# Monocacy 4040
# Occoquan 5250
# Shenandoah 4370
# main stem potomac 4820
$outlet = '4820';
$overwrite = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
if (!($userid == 1)) {
   # user must be logged in, and must be administrator account
   print("Unauthorized access to this routine - user must be logged in, and must be administrator account<br>");
   die;
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

if ($overwrite) {
   $listobject->querystring = " delete from scen_model_element where scenarioid = $target_scenarioid ";
   $listobject->performQuery();
   $listobject->querystring = " delete from map_model_linkages where scenarioid = $target_scenarioid ";
   $listobject->performQuery();
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

// NOW - BEGIN to create the model components


//**************************************************************************************************************
// START - RIVER Segment Containers - these will be eseentially blank to begin with, since we do not know how we 
// are going to handle runoff for the non-ICPRB indicator portions of the segments
// we may use the land output and scale it, or we may use the river segment inflow and scale it
// this is undecided as of yet
//**************************************************************************************************************
// CBP Segments template
$cbp_container_el = 52270;
$cbp_river_el = 79091;
$cbp_impoundment_el = 103438;
$cbp_landuse_el = 52272;
$cbp_gage_el = 52278;
$cbp_graph_el = 52274;
$cbp_gini_el = 52276;
$cbp_copy_params = array(
   'projectid'=>$projectid,
   'dest_scenarioid'=>$target_scenarioid,
   'elements'=>array($cbp_container_el)
);
/*
$cbp_listobject->querystring = "  (select catcode2 as newriverseg, substring(catcode2, 5,4) as f_node, ";
$cbp_listobject->querystring .= "    substring(catcode2,10,4) as t_node, ";
$cbp_listobject->querystring .= "    sum(area2d(the_geom) * 3.861021e-07) as area_sqmi ";
$cbp_listobject->querystring .= " from sc_p52icprb ";
$cbp_listobject->querystring .= " where ( ";
$cbp_listobject->querystring .= "    catcode2 in (select cbp_segmentid from tmp_icprb_sheds group by cbp_segmentid) ";
$cbp_listobject->querystring .= "    ) OR ( ";
$cbp_listobject->querystring .= "    substring(catcode2,5,4) in (select substring(cbp_segmentid,10,5) from tmp_icprb_sheds group by cbp_segmentid) ";
$cbp_listobject->querystring .= " )";
$cbp_listobject->querystring .= " group by catcode2, substring(catcode2, 5,4), substring(catcode2,10,4) ";
$cbp_listobject->querystring .= " order by catcode2 )";
$cbp_listobject->querystring .= " UNION ";
$cbp_listobject->querystring .= " (select 'PR0_0000_0000' as newriverseg, '0000' as f_node, '0000' as t_node, 0.0 as area_sqmi )";
$cbp_listobject->querystring .= " UNION ";
$cbp_listobject->querystring .= " (select 'PR0_0001_0000' as newriverseg, '0001' as f_node, '0000' as t_node, 0.0 as area_sqmi) ";
print("$cbp_listobject->querystring ; <br>");
*/

$cbp_listobject->querystring = " ( select catcode2 as newriverseg, substring(catcode2, 5,4) as f_node, ";
$cbp_listobject->querystring .= "    substring(catcode2,10,4) as t_node, ";
$cbp_listobject->querystring .= "    sum(area2d(the_geom) * 3.861021e-07) as area_sqmi ";
$cbp_listobject->querystring .= " from sc_p52icprb ";
$cbp_listobject->querystring .= " where catcode2 like 'P%' ";
//$cbp_listobject->querystring .= " where catcode2 = 'PL0_4510_0001' ";
//$cbp_listobject->querystring .= " where catcode2 like 'PL0%' ";
$cbp_listobject->querystring .= " group by catcode2, substring(catcode2, 5,4), substring(catcode2,10,4) ";
$cbp_listobject->querystring .= " order by catcode2 )";
$cbp_listobject->querystring .= " UNION ";
$cbp_listobject->querystring .= " (select 'PR0_0000_0000' as newriverseg, '0000' as f_node, '0000' as t_node, 0.0 as area_sqmi )";
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
   
   //**********************************************************
   // START - get land use from cbp for this segment
   //**********************************************************
   $cbp_listobject->querystring = "  select id3 from cbp_model_location ";
   $cbp_listobject->querystring .= " where scenarioid = $cbp_scenario ";
   $cbp_listobject->querystring .= "    and id1 = 'lrseg' ";
   $cbp_listobject->querystring .= "    and id2 = '$outlet' ";
   $cbp_listobject->querystring .= " group by id3 ";
   $cbp_listobject->querystring .= " order by id3 ";
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $cbp_listobject->performQuery();
   $lseginfo = getCBPLandSegments($cbp_listobject, $cbp_scenario, $cbp_segmentid, $debug);
   $lsegs = $lseginfo['local_landsegs'];
   $nolu = 0;
   
   // do updates to the Graphs
   updateObjectProps($projectid, $map[$cbp_graph_el]['new_id'], array('name'=>"Graph: " . $shed_merge, 'title'=>"Flows - $shed_merge", 'debug'=>0));
   updateObjectProps($projectid, $map[$cbp_gini_el]['new_id'], array('name'=>"Graph: Gini " . $shed_merge, 'title'=>"Gini - $shed_merge", 'debug'=>0));
   updateObjectProps($projectid, $map[$cbp_impoundment_el]['new_id'], array('name'=>"Impoundment on " . $shed_merge, 'debug'=>0));
   
   // do updates to the river segment Main Stem
   $riverid = $map[$cbp_river_el]['new_id'];
   updateObjectProps($projectid, $riverid, array('name'=>"Main Stem (adjustable) " . $shed_merge, 'area'=>$area_sqmi, 'debug'=>0));
   foreach ($lsegs as $landseg) {
      $cid = $map[$cbp_landuse_el]['new_id'];
      if ($nolu > 0) {
         print("Cloning $landseg ; <br>");
         $cloneresult = cloneModelElement($target_scenarioid, $cbp_landuse_el);
         $cid = $cloneresult['elementid'];
         print("Updating $cid $landseg ; <br>");
         
         //print("reCreate: $msg <br>\n");
      }
      $msg = runObjectCreate($projectid, $cid);
      updateObjectProps($projectid, $cid, array('name'=>"Land Segment $landseg", 'id2'=>$landseg, 'debug'=>0));
      $linkhtml = createObjectLink($projectid, $target_scenarioid, $cid, $parent, 1);
      //print(print_r($linkhtml,1) . " <br>");
      // now, get the local land use for this river segment and land segment
      $landinfo = getCBPLandSegmentLanduse($cbp_listobject, $cbp_scenario, $landseg, 1, $cbp_segmentid);
      $landuse = $landinfo['local_annual'];
      //print($landinfo['debug']);
      
      //print("Trying to apply land use matrix for river segment $cbp_segmentid ($landseg): " . print_r($landuse,1) . "<br>\n");
      
      $loadres = unSerializeSingleModelObject($cid);
      $thisobject = $loadres['object'];
      // set historic land use
      if (is_object($thisobject)) {
         //print("$cid object retrieved<br>\n");
         if (is_object($thisobject->processors["landuse_historic"])) {
            //print("$cid object landuse found<br>\n");
            if (method_exists($thisobject->processors["landuse_historic"], 'assocArrayToMatrix')) {
               //print("$cid object assocArrayToMatrix() exists<br>\n");
               $thisobject->processors["landuse_historic"]->assocArrayToMatrix($landuse);
               saveObjectSubComponents($listobject, $thisobject, $cid, 1);
            }
         }
      }
      
      // set current, baseline and future land uses (note, the future is just a copy of the current for now)
      $lu_tables = array('baseline', 'current', 'future');
      foreach ($lu_tables as $thislu_tab) {

         $cbp_listobject->querystring = " select * from tmp_icprbcbp_landuse_$thislu_tab where riverseg = '$cbp_segmentid' and landseg = '$landseg' ";
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
$cbp_listobject->querystring .= " from tmp_icprb_sheds ";
//$cbp_listobject->querystring .= "    where cbp_segmentid = 'PL0_4510_0001' ";
//$cbp_listobject->querystring .= "    where cbp_segmentid like 'PL0%' ";
$cbp_listobject->querystring .= " order by t_node ";
print("$cbp_listobject->querystring ; <br>");
$cbp_listobject->performQuery();
//$cbp_listobject->showList();
$recs = $cbp_listobject->queryrecords;

foreach ($recs as $thisrec) {
   $unique_id = $thisrec['unique_id'];
   $shed_merge = $thisrec['shed_merge'];
   $area_sqmi = $thisrec['local_area'];
   $contrib_area_sqmi = $thisrec['shed_size'];
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
   
   // select mainsteam stream channel properties
   $cbp_listobject->querystring = " select CASE ";
   $cbp_listobject->querystring .= "       WHEN a.phys_prov = 'Appalachian' THEN 1 ";
   $cbp_listobject->querystring .= "       WHEN a.phys_prov = 'Valley and Ridge' THEN 2 ";
   $cbp_listobject->querystring .= "       WHEN a.phys_prov = 'Piedmont' THEN 3 ";
   $cbp_listobject->querystring .= "       WHEN a.phys_prov = 'Coastal Plain' THEN 4 ";
   $cbp_listobject->querystring .= "       ELSE 1 ";
   $cbp_listobject->querystring .= "    END as phys_prov, ";
   $cbp_listobject->querystring .= "  round((b.slope/100.0)::numeric,4) as slope, round(c.length_mi * 5364.0) as length_ft, ";
   $cbp_listobject->querystring .= " a.drainage_area, a.local_area ";
   $cbp_listobject->querystring .= " from tmp_icprb_phys as a, tmp_icprb_slope as b, tmp_icprb_streamlength as c ";
   $cbp_listobject->querystring .= " where a.unique_id = $unique_id ";
   $cbp_listobject->querystring .= " and b.unique_id = $unique_id ";
   $cbp_listobject->querystring .= " and c.unique_id = $unique_id ";
   print("$cbp_listobject->querystring ; <br>");
   $cbp_listobject->performQuery();
   $slope = $cbp_listobject->getRecordValue(1,'slope');
   $phys_prov = $cbp_listobject->getRecordValue(1,'phys_prov');
   $length_ft = $cbp_listobject->getRecordValue(1,'length_ft');
   // do updates to the river segment Main Stem
   $riverid = $map[$river_el]['new_id'];
   updateObjectProps($projectid, $riverid, array('name'=>"Main Stem " . $shed_merge, 'area'=>$area_sqmi, 'drainage_area'=>$contrib_area_sqmi, 'slope' => $slope, 'length' => $length_ft, 'province' => $phys_prov, 'debug'=>0));
   
   // update/create land use runoff objects
   $cbp_listobject->querystring = " select * from tmp_icprb_landuse_baseline where uniqueid = '$unique_id' and riverseg = '$cbp_segmentid'";
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

      $lu_tables = array('baseline', 'current', 'historic', 'future');
      foreach ($lu_tables as $thislu_tab) {

         $cbp_listobject->querystring = " select * from tmp_icprb_landuse_$thislu_tab where uniqueid = '$unique_id' and riverseg = '$cbp_segmentid' and landseg = '$landseg' ";
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
$listobject->querystring = "  update scen_model_element set geomtype = 3, poly_geom = force_2d(transform(a.the_geom,4326)) ";
$listobject->querystring .= " from tmp_icprb_local_lr as a ";
$listobject->querystring .= " where a.catcode2 = scen_model_element.elemname";
$listobject->querystring .= "  and scenarioid = 28 ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();
$listobject->querystring = "  update scen_model_element set geomtype = 3, poly_geom = force_2d(transform(a.the_geom,4326)) ";
$listobject->querystring .= " from sc_p52icprb as a ";
$listobject->querystring .= " where a.catcode2 = scen_model_element.elemname ";
$listobject->querystring .= " and a.catcode2 not in (select catcode2 from tmp_icprb_local_lr ) ";
$listobject->querystring .= "  and scenarioid = 28 ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();


// update geometries for both CBP and ICPRB segments
$listobject->querystring = " update scen_model_element set geomtype = 3, poly_geom = force_2d(transform(a.the_geom,4326))  ";
$listobject->querystring .= " from tmp_icprb_localshapes as a  ";
$listobject->querystring .= " where a.shed_merge = scen_model_element.elemname ";
$listobject->querystring .= " and scenarioid = 28 ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();

?>
