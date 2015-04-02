<?php

$noajax = 1;
$projectid = 3;
$target_scenarioid = 37;
$cbp_scenario = 4;
$userid = 1;
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
include_once("./lib_batchmodel.php");

// functions

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
   $listobject->querystring = " delete from scen_model_run_elements where scenarioid = $target_scenarioid ";
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
$cbp_container_el = 176615;
$cbp_river_el = 79091;
$cbp_impoundment_el = 103438;
$cbp_landuse_el = 52272;
$cbp_gage_el = 52278;
$cbp_graph_el = 52274;
$cbp_gini_el = 52276;

$listobject->querystring = " ( select riverseg, substring(riverseg, 5,4) as f_node, ";
$listobject->querystring .= "    substring(riverseg,10,4) as t_node, ";
$listobject->querystring .= "    sum(area2d(the_geom) * 3.861021e-07) as area_sqmi ";
$listobject->querystring .= " from sc_cbp53 ";
$listobject->querystring .= " where ( ";
$listobject->querystring .= "    ( riverseg like 'P%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'J%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'Y%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'R%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'W%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'E%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'BS%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'TU%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'MN%' ) ";
$listobject->querystring .= "    OR ";
$listobject->querystring .= "    ( riverseg like 'NR%' ) ";
$listobject->querystring .= " ) ";
//$listobject->querystring .= " and catcode2 = 'PL0_4510_0001' ";
//$listobject->querystring .= " and catcode2 like 'PL0%' ";
$listobject->querystring .= " group by riverseg, substring(riverseg, 5,4), substring(riverseg,10,4) ";
$listobject->querystring .= " order by riverseg ";
$listobject->querystring .= " ) ";
$listobject->querystring .= " UNION ";
$listobject->querystring .= " (select 'CBW_0000_0000' as riverseg, '0000' as f_node, ";
$listobject->querystring .= "    '0000' as t_node, 0.0 as area_sqmi ";
$listobject->querystring .= " ) ";
$listobject->querystring .= " UNION ";
$listobject->querystring .= " (select 'CBW_0001_0000' as riverseg, '0001' as f_node, '0000' as t_node, ";
$listobject->querystring .= "    0.0 as area_sqmi";
$listobject->querystring .= " ) ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();
//$listobject->showList();
//die;
$recs = $listobject->queryrecords;
$rec_nos = array();

foreach ($recs as $thisrec) {
   $cbp_segmentid = $thisrec['riverseg'];
   $newbasin = createCOVABasin($listobject, $projectid, $target_scenarioid, $cbp_container_el, $cbp_segmentid);
   $elid = $newbasin['elementid'];
   //$listobject->querystring = "  update scen_model_element set custom1 = 'cbp_node' where elementid = $elid ";
   print("Created container with for $cbp_segmentid with elementid = $elid ; <br> \n");
   $listobject->performQuery();
   //print($newbasin['innerHTML'] . " \n");
   //die;
   
}

// NOW create linkages for CBP segment containers
reset($recs);
foreach ($recs as $thisrec) {
   $cbp_segmentid = $thisrec['riverseg'];
   $t_node = $thisrec['t_node'];
   $listobject->querystring = "  select elementid from scen_model_element where substring(elemname,5,4) = '$t_node' and scenarioid = $target_scenarioid ";
   print("$listobject->querystring ; <br>");
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $grandparent = $listobject->getRecordValue(1,'elementid');
      $parent = getCOVAUpstream($listobject, $grandparent);
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

/*

// update geometries for both CBP and ICPRB segments to baseline - will override below if it is sub-segmented in ICRPB
$listobject->querystring = " update scen_model_element set geomtype = 3, poly_geom = force_2d(setsrid(a.the_geom,4326))  ";
$listobject->querystring .= " from sc_cbp5 as a  ";
$listobject->querystring .= " where a.catcode2 = scen_model_element.elemname ";
$listobject->querystring .= " and scenarioid = 28 ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();

// First, clear both CBP and ICPRB segment geometries
// match on shed_merge for ICPRB segments, and match on riverseg = elemname for CHBP segments
$listobject->querystring = "  update scen_model_element set geomtype = 3, ";
$listobject->querystring .= "    poly_geom = NULL ";
$listobject->querystring .= " where objectclass = 'modelContainer' ";
$listobject->querystring .= "  and scenarioid = 28 ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();
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

*/

?>
