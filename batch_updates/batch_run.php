<?php
// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
include("../xajax_modeling.element.php");
$starttime = '1984-01-01 00:00:00';
$endtime = '2005-12-31 00:00:00';

$projectid = 3;

$elems = array();
/*
$rundate = '2010-08-13 03:30:00';
$run_mode = 1; // 0 - historic, 1 - baseline, 2 - current, 3 - future
$runid = 1; // 0 - historical, 1 - baseline, 2 - current
$listobject->querystring = "  select elementid from scen_model_element ";
$listobject->querystring .= " where scenarioid = 28 ";
$listobject->querystring .= "    and objectclass = 'modelContainer' ";
$listobject->querystring .= "    and length(elemname) = 16 ";
$listobject->querystring .= "    and substring(elemname,14,3) in (";
$listobject->querystring .= "       select substring(elemname,14,3) as abbrev ";
$listobject->querystring .= "       from scen_model_element ";
$listobject->querystring .= "       where objectclass = 'modelContainer' ";
$listobject->querystring .= "          and scenarioid = 28 ";
$listobject->querystring .= "          and substring(elemname,4,1) = '_' ";
$listobject->querystring .= "          and length(elemname) = 16 ";
$listobject->querystring .= "       group by abbrev";
$listobject->querystring .= "    )";
$listobject->querystring .= "    and elementid NOT in (";
$listobject->querystring .= "       select element_key ";
$listobject->querystring .= "       from system_status ";
$listobject->querystring .= "       where (last_updated >=  '$rundate' ";
$listobject->querystring .= "          and status_flag = 0) ";
$listobject->querystring .= "          OR (last_updated >=  now() - interval '1 hour' ";
$listobject->querystring .= "          and status_flag = 1) ";
$listobject->querystring .= "    )";
*/

$exceptionlist = "'382033307855570','383739007846589','383807707846436','383827007913390','384025007912113','384315007839190','384533907908595','384750907905508','384751107906339','385059507922287','385303907915134','385411007828530','385657707823209','385926307912574','385939807910252','390006907904562','390042107857265','390045507740592','390049007741590','390120507900312','390203907857450','390322607858133','390340607820455','390427407730435','390433007819300','390446007720240','390848107855286','390916007731180','391346407851103','391757007800420','391912407744320','392011507847006','392021007846327','392503607824324','392619407840270','392701007743550','392714007825319','392757207743340','392808007823348','392912807742511','392917507742475','393001507821443','393025907722379','393029807836075','393118907821153','393127107832488','393215007742410','393219007827170','393302907820004','393455407818317','393611007749190','393618707754385','393749107833490','393822907833439','393838007833446','393840107833193','393913707833590','393932007833490','393942807750319','393955107833500','394017807801307','394032207802359','394257007749310','394719407745091','393746107754131'";


/*
// this one looks at the test cases for karst/non-karst
$rundate = '2010-08-30 16:10:00';
$karstlist = "'394257007749310','391757007800420','393022707740106','392755107743300','392852007740142','392622907740124','393332007737077','393929207734188','393215007742410','392917507742475','392912807742511','392757207743340','380802707905181','380926007858190','381437007902090','380325007854280','384032307838259','384519007840140','384315007839190','391037107753294','390508907759140','391406207754009','390356007800150','391105207751150','391242107750048','391520307749130','391550807751259','391716807751544','383520407911551','383025007915504','383527207914493','383612507913411','383010407919306','383303007916296','383657107913331','382756507920208','383307407917312','383256107916157','385202007902276','384316007907114','390037807856174','383847407911541','384012707911509','384954407903348','385310807901458','385325107902085','385420107900201','390008407856485','384744507906421','384025007912113','384533907908595','384750907905508','390042107857265','390203907857450','390322607858133','383827007913390','384751107906339','390845407854141','391152907851288','391945407844073','390933307851181','390938207856006','392103307842386','390957707856331','391314407851093','391400307856489','391508407855026','391641207853119','391855107847255','392550107842492','391716007847073','390355807855167','390835107854270','390929307856211','391938407847480','392853507822560','393223107820461','392755207825033','393113407820490','393150707821175','392714007825319','393001507821443','393118907821153','393302907820004','393455407818317','392808007823348','392503607824324','394007007731329','393957607732553','393930007732430','382925907842100','390905007750524','391007507750423','391010307750262','391208507749137','391525307747040'";

$karstlist = "'391406207754009','391550807751259','391242107750048','391520307749130','391105207751150','391716807751544','380802707905181','390356007800150','390508907759140','391037107753294','393022707740106','392755107743300','380926007858190','392852007740142','384032307838259','384519007840140','392622907740124','393332007737077','393929207734188','390929307856211','391945407844073','390938207856006','393930007732430','391938407847480','392103307842386','391855107847255','391152907851288','390835107854270','392550107842492','391314407851093','394007007731329','384954407903348','385325107902085','390008407856485','390957707856331','391400307856489','391508407855026','391641207853119','392755207825033'";


$karst_sheds_noexcep = "'PS2_5560_5100A02', 'PS2_5560_5100A03','PS3_6460_6230A02', 'PS3_6460_6230A03', 'PS5_4380_4370A01','PS5_4380_4370A02', 'PS5_4380_4370A03', 'PS5_4380_4370A04', 'PS5_4380_4370A05', 'PU2_3090_4050A01B01C01D01E01F01', 'PU2_3090_4050A01B01C01D01E01F02', 'PU2_3090_4050A01B01C01D01E02', 'PU2_3090_4050A01B01C01D04',  'PU2_3090_4050A01B03', 'PU2_3090_4050A01B04', 'PU2_3090_4050A01B05', 'PU2_3090_4050A01B06', 'PU2_5190_4310A01B01C01D02', 'PU2_5190_4310A01B01C01D03', 'PU2_5190_4310A01B01C01D06', 'PU3_3860_3610A01B01C01D01E01F02', 'PU4_4310_4210A01B01C01D01E01F01', 'PU4_4310_4210A01B01C01D01E01F03','PU4_4310_4210A01B01C01D01E02','PU4_4310_4210A01B01C01D01E03', 'PU4_4310_4210A01B01C01D01E05', 'PU4_4310_4210A01B01C01D02', 'PU4_4310_4210A01B01C01D04', 'PU4_4310_4210A01B01C01D05', 'PU4_4310_4210A01B02', 'PU4_4310_4210A01B03'";

$karst_rerun_baseline = "'391314407851093','391152907851288','390938207856006', '390929307856211', '390835107854270', '392550107842492', '392103307842386', '391945407844073', '391938407847480', '391855107847255'";
$run_mode = 1;
$rerun = 1;
$runid = 1; // 0 - historical, 1 - baseline, 2 - current
$listobject->querystring = "  select b.uniq_id, a.elementid, a.elemname  ";
$listobject->querystring .= " from tmp_icprb_localshapes as b, scen_model_element as a  ";
$listobject->querystring .= " where b.catcode2 || b.nested_she = a.elemname  ";
$listobject->querystring .= " and a.scenarioid = 28  ";
if (!$rerun) {
   $listobject->querystring .= " and a.elementid not in ( ";
   $listobject->querystring .= "    select elementid  ";
   $listobject->querystring .= "    from scen_model_run_elements  ";
   $listobject->querystring .= "    where runid = $run_mode ";
   $listobject->querystring .= "    and starttime = '$starttime' ";
   $listobject->querystring .= "    and endtime = '$endtime' ";
   $listobject->querystring .= "    and run_date < '$rundate' ";
   $listobject->querystring .= " ) ";
}
$listobject->querystring .= " and elementid NOT in (";
$listobject->querystring .= "    select element_key ";
$listobject->querystring .= "    from system_status ";
$listobject->querystring .= "    where (last_updated >=  '$rundate' ";
$listobject->querystring .= "       and status_flag = 0) ";
$listobject->querystring .= "       OR (last_updated >=  now() - interval '1 hour' ";
$listobject->querystring .= "       and status_flag = 1) ";
$listobject->querystring .= " )";
// do not use this since we have the manually defined $karst_sheds_noexcep
//$listobject->querystring .= " and length(elemname) = 16 ";
//$listobject->querystring .= " and b.uniq_id in ($karstlist) ";
//$listobject->querystring .= " and b.uniq_id not in ($exceptionlist) ";
// karst with no exceptions
$listobject->querystring .= " and b.shed_merge in ($karst_sheds_noexcep) ";
// re-run small group of missing baseline sheds
//$listobject->querystring .= " and b.uniq_id in ($karst_rerun_baseline) ";
$listobject->querystring .= " order by elemname ";

*/

// this one also looks at the completed model set, as well as run start time
// this runs the whole enchilada
$rundate = '2010-08-27 12:30:00';
$runid = 2; // 0 - historical, 1 - baseline, 2 - current
$run_mode = 2; // 0 - historic, 1 - baseline, 2 - current, 3 - future
$rerun = 0;
$listobject->querystring = "  select b.uniq_id, a.elementid, a.elemname  ";
$listobject->querystring .= " from tmp_icprb_localshapes as b, scen_model_element as a  ";
$listobject->querystring .= " where b.catcode2 || b.nested_she = a.elemname  ";
$listobject->querystring .= " and a.scenarioid = 28  ";
if (!$rerun) {
   $listobject->querystring .= " and a.elementid not in ( ";
   $listobject->querystring .= "    select elementid  ";
   $listobject->querystring .= "    from scen_model_run_elements  ";
   $listobject->querystring .= "    where runid = $run_mode ";
   $listobject->querystring .= "    and starttime = '$starttime' ";
   $listobject->querystring .= "    and endtime = '$endtime' ";
   $listobject->querystring .= "    and run_date < '$rundate' ";
   $listobject->querystring .= " ) ";
}
// don't get ones that are currently running
$listobject->querystring .= " and elementid NOT in (";
$listobject->querystring .= "    select element_key ";
$listobject->querystring .= "    from system_status ";
$listobject->querystring .= "    where (last_updated >=  '$rundate' ";
$listobject->querystring .= "       and status_flag = 0) ";
$listobject->querystring .= "       OR (last_updated >=  now() - interval '1 hour' ";
$listobject->querystring .= "       and status_flag = 1) ";
$listobject->querystring .= " )";
// don't do ones we have suspicions about
$listobject->querystring .= " and b.uniq_id not in ($exceptionlist) ";
$listobject->querystring .= " and length(elemname) = 16 ";
$listobject->querystring .= " order by elemname ";

print("$listobject->querystring \n");
//die;

$listobject->performQuery();
foreach ($listobject->queryrecords as $thisrec) {
   $elems[] = $thisrec['elementid'];
}
print("Found " . count($elems) . " models to run \n");

// specify max models to run at a time
$max_simultaneous = 6;
$startdate = '1984-01-01';
$enddate = '2005-12-31';
$years = 20;
$sleep_factor = $years * 10; // give it some time to accumulate  a cache

// use returnPids('php') to get all php pid processes
// store the pid of a model run in the system_status table using getpid()
// retrieve list of pids of system_status entries with status_flag = 1
// if intersection of (number of pids in status_flag = 1) & (php pids) < $max_simultaneous spawn a new model
// else wait

while ( count($elems) > 0 )  {
   $listobject->querystring = " select pid from system_status where status_flag = 1 group by pid ";
   $listobject->performQuery();
   $model_pids = array();
   foreach ($listobject->queryrecords as $thisrec) {
      $model_pids[] = $thisrec['pid'];
   }
   $active_models = returnPids('php');
   $num_active = count($active_models);
   //print("Only $num_active \n");
   if ($num_active < $max_simultaneous) {
      // spawn a new one
      $elementid = array_pop($elems);
      $prop_array = array('run_mode' => $run_mode, 'debug' => 0);
      updateObjectProps($projectid, $elementid, $prop_array);
      $arrOutput = array();
      print("Only $num_active out of $max_simultaneous models running.  Spawning model run for element $elementid.<br>\n");
      $command = "$php_exe -f $basedir/test_modelrun.php $elementid $startdate $enddate $runid";
      $controlHTML .= "Spawning process for $elementid <br>";
      error_log("$command > /dev/null &");
      $forkout = exec( "$command > /dev/null &", $arrOutput );
   }
   sleep($sleep_factor);   
}

?>
