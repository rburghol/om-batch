<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

$scenid = $argv[1];
$riverseg = $argv[2];

if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' ";
   print("Looking for river abbreviation match <br>\n");
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $thisrec) {
      $segs[] = $thisrec['riverseg'];
   }
} else {
   $segs = array($riverseg);
}
//print_r($segs);

function inventoryVPDES($vpdes_permit_no, $debug = 0) {
   global $vpdesdb;
   
   $vpdesdb->querystring = "  select vpdes_permit_no, constit, count(*) as numrecs, ";
   $vpdesdb->querystring .= "    min(mon_startdate) as first_date, ";
   $vpdesdb->querystring .= "    max(mon_enddate) as last_date, avg(mean_value) as mean_monthly ";
   $vpdesdb->querystring .= " from vpdes_discharge_no_ms4 ";
   $vpdesdb->querystring .= " where vpdes_permit_no = '$vpdes_permit_no' ";
   $vpdesdb->querystring .= " group by vpdes_permit_no, constit ";
   if ($debug) {
      print("$vpdesdb->querystring ;<br>\n");
   }
   $vpdesdb->performQuery();
   return $vpdesdb->queryrecords;

}

foreach ($segs as $riverseg) {

   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   print("CBP parent of $riverseg is $elid<br>\n");
   $ps = getVPDESDischarges($vpdesdb, $listobject, $elid, 1);
   print("\n VPDES records found \n");
   $vpdes_ids = array();
   foreach ($ps as $thisps) {
      $vpdesno = trim($thisps['vpdes_permit_no']);
      if (in_array($vpdesno, $vpdes_ids)) {
         print("VPDES Permit $vpdesno already reported\n");
      } else {
         $vpdes_ids[] = $vpdesno;
         //print_r($thisps);
         $recs = inventoryVPDES($vpdesno, 0);
         //print_r($recs);
         $vpd = $thisps['vpdes_permit_no'];
         $nm = $thisps['facility_name'];
         $ty = $thisps['vpdes_type'];
         print("Permit: $vpd, Name: $nm ($ty)<br>\n");
      }
   }
   print("\n");
}

?>
