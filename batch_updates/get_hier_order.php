<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");

$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;


$elid = $argv[1];

$order = getElementOrder($listobject, $elid);

print("Order of element $elid = $order \n");


$container_tree = getNestedContainers($listobject, $elid);
echo "Number of elements in tree = " . count($container_tree) . "\n";
echo "Container Tree " . print_r($container_tree, 1) . "\n";

$listobject->querystring = "  select unique_id, shed_merge from icprb_watersheds, scen_model_element as a ";
$listobject->querystring .= " where shed_merge = a.elemname ";
$listobject->querystring .= "    and a.elementid = $elid ";
print("$listobject->querystring \n");
$listobject->performQuery();
$unique_id = $listobject->getRecordValue(1,'unique_id');
$lu_area = getLandUseArea($cbp_listobject, $unique_id, $lutab = 'baseline', 1);
echo "Land USe " . print_r($lu_area, 1) . "\n";
?>