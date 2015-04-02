<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('../xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/

$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");

$listobject->querystring = "  select elementid, elemname, objectclass from scen_model_element ";
$listobject->querystring .= " where scenarioid = $scid and objectclass in ";
$listobject->querystring .= " ('dataConnectionObject', 'USGSChannelGeomObject', 'modelContainer') ";
//$listobject->querystring .= " ('dataConnectionObject') ";
//$listobject->querystring .= " ('USGSChannelGeomObject') ";
//$listobject->querystring .= " and elementid in (76103, 103437, 73527) ";
//$listobject->querystring .= " limit 2";
print("$listobject->querystring ; <br>\n");
$listobject->performQuery();

$elrecs = $listobject->queryrecords;
$debug = 0;
$i = 0;
$serializer = new XML_Serializer();
foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];
   $objectclass = $thisrec['objectclass'];
   print("Updating: " .  $thisrec['elemname'] . " ($elid)\n");
   //$prop_array = array('drainage_area'=>$drainage_area, 'area_sqmi'=>$local_area);
   //updateObjectProps($projectid, $elid, $prop_array);

   $loadres = unSerializeSingleModelObject($elid);
   $thisobject = $loadres['object'];
   
   if (is_object($thisobject)) {
      switch ($objectclass) {
         case 'dataConnectionObject':
            print("$elid object retrieved<br>\n");
            if (is_object($thisobject->processors["broadcast_withdrawals"])) {
               print("$elid object found broadcast_withdrawals<br>\n");
               print("Local Vars: " . print_r($thisobject->processors["broadcast_withdrawals"]->local_varname,2) . "<br>\n");
               print("Broadcast Vars: " . print_r($thisobject->processors["broadcast_withdrawals"]->broadcast_varname,2) . "<br>\n");
               $bv = $thisobject->processors["broadcast_withdrawals"]->broadcast_varname;
               foreach($bv as $key=>$val) {
                  if ($val == 'withdrawal_cfs') {
                     $bv[$key] = 'withdrawal_mgd';
                  }
               }
               $thisobject->processors["broadcast_withdrawals"]->broadcast_varname = $bv;
            }
         break;
         
         case 'USGSChannelGeomObject':
            print("$elid object retrieved<br>\n");
            if (is_object($thisobject->processors["Land and Tributary Inflows"])) {
               print("$elid object found Land and Tributary Inflows<br>\n");
               print("Local Vars: " . print_r($thisobject->processors["Land and Tributary Inflows"]->local_varname,2) . "<br>\n");
               print("Broadcast Vars: " . print_r($thisobject->processors["Land and Tributary Inflows"]->broadcast_varname,2) . "<br>\n");
               $lv = $thisobject->processors["Land and Tributary Inflows"]->broadcast_varname;
               foreach($lv as $key=>$val) {
                  if ($val == 'withdrawal_cfs') {
                     $lv[$key] = 'withdrawal_mgd';
                  }
               }
               $thisobject->processors["Land and Tributary Inflows"]->broadcast_varname = $lv;
            }
         break;
         
         case 'modelContainer':
            print("$elid object retrieved<br>\n");
            if (is_object($thisobject->processors["Stream Outflows"])) {
               print("$elid object found Stream Outflows<br>\n");
               print("Local Vars: " . print_r($thisobject->processors["Stream Outflows"]->local_varname,2) . "<br>\n");
               print("Broadcast Vars: " . print_r($thisobject->processors["Stream Outflows"]->broadcast_varname,2) . "<br>\n");
               array_push($thisobject->processors["Stream Outflows"]->broadcast_varname, 'cumulative_wd_mgd');
               array_push($thisobject->processors["Stream Outflows"]->local_varname, 'demand_mgd');
               array_push($thisobject->processors["Stream Outflows"]->broadcast_varname, 'cumulative_ps_mgd');
               array_push($thisobject->processors["Stream Outflows"]->local_varname, 'discharge_mgd');
            }
         break;
      }
      saveObjectSubComponents($listobject, $thisobject, $elid );
      
      
   }
   
   $i++;
   //break;
}

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
