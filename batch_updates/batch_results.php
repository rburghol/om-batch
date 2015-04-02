<?php
// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include("../xajax_modeling.element.php");
//$rundate = '2010-08-13 03:30:00';
$rundate = '2010-08-30 15:30:00';
//$endrun = '2010-08-05 12:00:00';
$runid = 1;

$karst_list = "'PS2_5560_5100A02', 'PS2_5560_5100A03','PS3_6460_6230A02', 'PS3_6460_6230A03', 'PS5_4380_4370A01','PS5_4380_4370A02', 'PS5_4380_4370A03', 'PS5_4380_4370A04', 'PS5_4380_4370A05', 'PU2_3090_4050A01B01C01D01E01F01', 'PU2_3090_4050A01B01C01D01E01F02', 'PU2_3090_4050A01B01C01D01E02', 'PU2_3090_4050A01B01C01D04',  'PU2_3090_4050A01B03', 'PU2_3090_4050A01B04', 'PU2_3090_4050A01B05', 'PU2_3090_4050A01B06', 'PU2_5190_4310A01B01C01D02', 'PU2_5190_4310A01B01C01D03', 'PU2_5190_4310A01B01C01D06', 'PU3_3860_3610A01B01C01D01E01F02', 'PU4_4310_4210A01B01C01D01E01F01', 'PU4_4310_4210A01B01C01D01E01F03','PU4_4310_4210A01B01C01D01E02','PU4_4310_4210A01B01C01D01E03', 'PU4_4310_4210A01B01C01D01E05', 'PU4_4310_4210A01B01C01D02', 'PU4_4310_4210A01B01C01D04', 'PU4_4310_4210A01B01C01D05', 'PU4_4310_4210A01B02', 'PU4_4310_4210A01B03'";

$test_list = "'PU1_9908_9910A01'";

$elems = array();
$listobject->querystring = "  select a.elementid, a.elemname, b.output_file ";
$listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b ";
$listobject->querystring .= " where scenarioid = 28 ";
// use this to only get a list of completed runs since rundate
$listobject->querystring .= "    and a.elementid in (";
$listobject->querystring .= "       select element_key from system_status ";
$listobject->querystring .= "       where status_flag = 0 and last_updated >= '$rundate'";
$listobject->querystring .= "    ) ";
$listobject->querystring .= "    and a.elementid = b.elementid ";
$listobject->querystring .= "    and b.runid = $runid ";
$listobject->querystring .= "    and a.elemname in ($karst_list) ";
//$listobject->querystring .= "    and a.elemname in ($test_list) ";
$listobject->querystring .= " order by elemname ";
$listobject->performQuery();
print("$listobject->querystring \n");
$records = $listobject->queryrecords;
//die;

// get the object name
// get all children (same base name, > 16 chars long, same scenario, objectclass = 'modelContainer'
// get lastlog files for each
$j = 0;
foreach ( $records as $thisrec )  {
   $parentname = $thisrec['elemname'];
   $parentid = $thisrec['elementid'];
   $objres = unSerializeSingleModelObject($parentid);
   $j++;
   $obj = $objres['object'];
   $uniqueid = $obj->description;
   $pfilename = $thisrec['output_file'];
   $poutput = "$basedir/test/output_runid$runid/$uniqueid" . ".csv";
   print("Handling Parent container $parentname ($parentid) \n");
   print("Executing copy($pfilename, $poutput) \n");
   copy($pfilename, $poutput);
   $listobject->querystring = "  select a.elementid, a.elemname, b.output_file ";
   $listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b ";
   $listobject->querystring .= " where scenarioid = 28 ";
   // use this to only get a list of extras
   $listobject->querystring .= "    and a.elementid = b.elementid ";
   $listobject->querystring .= "    and b.runid = $runid ";
   $listobject->querystring .= "    and a.objectclass = 'modelContainer' ";
   $listobject->querystring .= "    and length(a.elemname) > length('$parentname') ";
   $listobject->querystring .= "    and substring(a.elemname,1,length('$parentname')) = '$parentname' ";
   //print("$listobject->querystring \n");
   $listobject->performQuery();
   $model_children = $listobject->queryrecords;
   foreach ($model_children as $thischild) {
      $childid = $thischild['elementid'];
      $objres = unSerializeSingleModelObject($childid);
      $obj = $objres['object'];
      $uniqueid = $obj->description;
      $cname = $obj->name;
      $cfilename = $thischild['output_file'];
      $coutput = "$basedir/test/output_runid$runid/$uniqueid" . ".csv";
      print("Getting output of child $cname - $childid for $parentid .<br>\n");
      print("Executing copy($cfilename, $coutput) \n");
      copy($cfilename, $coutput);
      $j++;
      
   }
}
print("Handled $j object \n");
?>
