<html>
<body>
<h3>Test Model Run</h3>

<?php


# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
error_log("Remote Run Parameter: $remote_run ");

#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");
$debug = 0;
$startdate = '';
$enddate = '';
$run_date = date('r');

if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($_GET['startdate'])) {
   $startdate = $_GET['startdate'];
}
if (isset($_GET['enddate'])) {
   $enddate = $_GET['enddate'];
}
if (isset($argv[1])) {
   $elementid = $argv[1];
}
if (isset($argv[2])) {
   $startdate = $argv[2];
}
if (isset($argv[3])) {
   $enddate = $argv[3];
}
$runid = -1; // if this is not set, we just store last run (-1)
if (isset($argv[4])) {
   $runid = $argv[4];
}
$cache_level = -1; // if this is not set, we assume no cached runs
if (isset($argv[5])) {
   $cache_level = $argv[5];
}
   
if ($elementid > 0) {
   # format output into tabbed display object
   $taboutput = new tabbedListObject;
   $taboutput->name = 'modelout';
   $taboutput->tab_names = array('modelcontrol','runlog','graphs','reports','errorlog', 'debug');
   $taboutput->tab_buttontext = array(
   'modelcontrol'=>'Model Controls',
   'runlog'=>'Run Log',
   'graphs'=>'Graphs',
   'reports'=>'Reports',
   'errorlog'=>'Error Log',
   'debug'=>'Debug Info'
   );
   $taboutput->init();
   $taboutput->tab_HTML['modelcontrol'] .= "<b>Model Controls:</b><br>";
   $taboutput->tab_HTML['reports'] .= "<b>Model Reports:</b><br>";
   $taboutput->tab_HTML['runlog'] .= "<b>Model Run-Log:</b><br>";
   $taboutput->tab_HTML['runlog'] .= "Initiating Model Run.<br>";
   $taboutput->tab_HTML['graphs'] .= "<b>Model Graphs:</b><br>";
   $taboutput->tab_HTML['debug'] .= "<b>Debugging Information:</b><br>";

   $taboutput->tab_HTML['modelcontrol'] .= "<form name='runmodelcontrol' id='runmodelcontrol'>";
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('actiontype', 'runmodel', 1);
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('projectid', $projectid, 1);
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('scenarioid', $scenarioid, 1);
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('elements', $elementid, 1);
   # these two: showcached and redraw, are always set to 0, since they are only 1 when called form this screen if
   # a re-draw is requested
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('redraw', 0, 1);
   $taboutput->tab_HTML['modelcontrol'] .= showHiddenField('showcached', 0, 1);
   //$taboutput->tab_HTML['modelcontrol'] .= showGenericButton('rerun_model', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_showModelRunResult(xajax.getFormValues(\"runmodelcontrol\")); ", 1);
   $taboutput->tab_HTML['modelcontrol'] .= showGenericButton('run_bgmodel', 'Run Model', "last_tab[\"modelout\"]=\"modelout_data1\"; last_button[\"modelout\"]=\"modelout_1\"; xajax_runModelBackground(xajax.getFormValues(\"runmodelcontrol\")); ", 1);
   $taboutput->tab_HTML['modelcontrol'] .= "Run ID? (-1 means do not store as run): " . showWidthTextField('runid', -1, 30, '', 1);
   //$taboutput->tab_HTML['modelcontrol'] .= showGenericButton('redraw_button', 'Re-draw Graphs', " xajax_showRedrawGraphs(xajax.getFormValues(\"runmodelcontrol\")); show_next(\"modelout_data2\", \"modelout_2\", \"modelout\")", 1);
   $taboutput->tab_HTML['modelcontrol'] .= "</form>";
   #$debug = 1;
   $taboutput->tab_HTML['runlog'] .= "Retrieving component: $elementid <br>";
   $input_props = array();
   if ( (strlen($startdate) > 0) and (strlen($enddate) > 0)) {
      $input_props['model_startdate'] = $startdate;
      $input_props['model_enddate'] = $enddate;
   }
   $thisobresult = unSerializeModelObject($elementid, $input_props, $modeldb, $cache_level, $runid);
   $thisobject = $thisobresult['object'];
   $components = $thisobresult['complist'];
   $cachedlist = $thisobresult['cached'];
   $taboutput->tab_HTML['errorlog'] .= "<b>Model Unserialization Errors</b><br>" . $thisobresult['error'] . "<hr>";
   $thisname = $thisobject->name;
   $thisobject->outdir = $outdir;
   $thisobject->outurl = $outurl;
   if ( (strlen($startdate) > 0) and (strlen($enddate) > 0)) {
      $thisobject->starttime = $startdate;
      $thisobject->endtime = $enddate;
      error_log("Setting Start and End Date for model to $startdate - $enddate \n");
   } else {
      $startdate = $thisobject->starttime;
      $enddate = $thisobject->endtime;
   }
   // set the model system log to be the parent model run database 
   // this will insure that all of the run status messages go to a central place
   $thisobject->modelhost = $serverip;
   $thisobject->runid = $runid;
   $thisobject->systemlog_obj = $listobject;
   $taboutput->tab_HTML['debug'] .= "Model Debug Status: " . $thisobject->debug . "<br>";
   $taboutput->tab_HTML['runlog'] .= "Running component group: $thisname <br>";
   #$thisobject->cascadedebug = 1;
   #$thisobject->setDebug(1,2);
   $thisobject->runModel();
   error_log("runModel() Returned from calling routine.");
   $meanexectime = $thisobject->meanexectime;
   $debugstring = '';
   error_log("Assembling Panels.");
   $taboutput->tab_HTML['runlog'] .= $thisobject->outstring . " <br>";
   $taboutput->tab_HTML['errorlog'] .= '<b>Model Execution Errors:</b>' . $thisobresult['error'] . " <br>";
   if (strlen($thisobject->errorstring) <= 4096) {
      $taboutput->tab_HTML['errorlog'] .= $thisobject->errorstring . " <br>";
   } else {
      error_log("Writing errors to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'error' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, "Component Logging Info: <br>");
      fwrite($fp, $thisobject->errorstring . " <br>");
      $taboutput->tab_HTML['errors'] .= "<a href='$furl' target=_new>Click Here to Download Model Error Info</a>";
   }
   if (strlen($thisobject->reportstring) <= 4096) {
      $taboutput->tab_HTML['reports'] .= "Component Logging Info: <br>";
      $taboutput->tab_HTML['reports'] .= $thisobject->reportstring . " <br>";
   } else {
      error_log("Writing reports to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'report' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, "Component Logging Info: <br>");
      fwrite($fp, $thisobject->reportstring . " <br>");
      $taboutput->tab_HTML['reports'] .= "<a href='$furl' target=_new>Click Here to Download Model Reporting Info</a>";
   }
   if (strlen($graphstring) <= 1024) {
      $taboutput->tab_HTML['graphs'] .= "<img src='' id='image_screen' height=400 width=600>";
      $taboutput->tab_HTML['graphs'] .= "<div id='view_box' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 180; width: 624; display: block;  background: #eee9e9;\">";
      $taboutput->tab_HTML['graphs'] .= $thisobject->graphstring . " <br>";
      $taboutput->tab_HTML['graphs'] .= "</div>";
   } else {
   error_log("Writing graph output to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'graph' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, $graphstring);
      $taboutput->tab_HTML['graph'] .= "<a href='$furl' target=_new>Click Here to Download Graphs Info</a>";
   }

   if (strlen($thisobject->debugstring) <= 4096) {
      $taboutput->tab_HTML['debug'] .= $thisobresult['debug'] . " <br>";
      $taboutput->tab_HTML['debug'] .= $thisobject->debugstring . '<br>';
   } else {
      error_log("Writing debug output to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'debug' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, $thisobresult['debug'] . " <br>");
      fwrite($fp, $thisobject->debugstring . '<br>');
      $taboutput->tab_HTML['debug'] .= "<a href='$furl' target=_new>Click Here to Download Debug Info</a>";
   }

   $taboutput->tab_HTML['runlog'] .= "Finished.<br>";
   error_log("Creating output in html form.");
   $taboutput->createTabListView();
   $innerHTML .= $taboutput->innerHTML . "</div>";
   error_log("Storing $elementid model output in database");
   $listobject->querystring = "  update scen_model_element set output_cache = '" . addslashes($innerHTML) . "'";
   $listobject->querystring .= " where elementid = $elementid ";
   $listobject->performQuery();
   //error_log("$listobject->querystring");
   error_log("Storing model run data in scen_model_run_elements");
   // and a unique runid specifier 
   // by storing the xml of the object we will be able to check to see if the object has changed and should be 
   // re-run, or if objects that this object gets linkages from have changed in order to use cached model output
   // if nothing has changed then we can use cached

   // insert copy of this parent element as "last run" (runid = -1)
   $cfilename = $outdir . "/objectlog." . $elementid . "." . $elementid .  ".log";
   $cfileurl = "http://$serverip" . $outurl . "/objectlog." . $elementid . "." . $elementid .  ".log";
   $listobject->querystring = "  delete from scen_model_run_elements ";
   $listobject->querystring .= " where elementid = $elementid ";
   $listobject->querystring .= " and runid = -1 ";
   $listobject->performQuery();
   $listobject->querystring = "  insert into scen_model_run_elements (runid,starttime, endtime, elem_xml, elementid, output_file, remote_url, run_date, host, exec_time_mean)";
   $listobject->querystring .= " select -1, '$startdate', '$enddate', a.elem_xml, a.elementid, '$cfilename', '$cfileurl', '$run_date', '$serverip', $meanexectime ";
   $listobject->querystring .= " from scen_model_element as a ";
   $listobject->querystring .= " where elementid = $elementid ";
   $listobject->performQuery();
   if ($runid <> -1) {
      $rfilename = $outdir . "/runlog$runid" . "." . $elementid . ".log";
      copy($cfilename, $rfilename);
      // we want to store this output as a specific run, in addition to the default "last run" code 
      $listobject->querystring = "  delete from scen_model_run_elements ";
      $listobject->querystring .= " where elementid = $elementid ";
      $listobject->querystring .= " and runid = $runid ";
      $listobject->performQuery();
      // custom to be run on this install - 
      $rfileurl = "http://$serverip" . $outurl . "/runlog$runid" . "." . $elementid . ".log";
      $listobject->querystring = "  insert into scen_model_run_elements ";
      $listobject->querystring .= " (runid,starttime, endtime, elem_xml,";
      $listobject->querystring .= "  elementid, output_file, remote_url, ";
      $listobject->querystring .= "  run_date, host, exec_time_mean)";
      $listobject->querystring .= " select $runid, '$startdate', ";
      $listobject->querystring .= " '$enddate', a.elem_xml, ";
      $listobject->querystring .= " a.elementid, '$rfilename', '$rfileurl', ";
      $listobject->querystring .= " '$run_date', '$serverip', $meanexectime ";
      $listobject->querystring .= " from scen_model_element as a ";
      $listobject->querystring .= " where elementid = $elementid ";
      $listobject->performQuery();
   }

   foreach ($components as $thiscomp) {
      // insert copy of this as "last run" (runid = -1)
      $cfilename = $outdir . "/objectlog." . $elementid . "." . $thiscomp . ".log";
      $listobject->querystring = "  delete from scen_model_run_elements ";
      $listobject->querystring .= " where elementid = $thiscomp ";
      $listobject->querystring .= " and runid = -1 ";
      $listobject->performQuery();
      $listobject->querystring = "  insert into scen_model_run_elements (runid,starttime, endtime, elem_xml, elementid, output_file, run_date, host) ";
      $listobject->querystring .= " select -1, '$startdate', '$enddate', a.elem_xml, a.elementid, '$cfilename', '$run_date', '$serverip' ";
      $listobject->querystring .= " from scen_model_element as a ";
      $listobject->querystring .= " where elementid = $thiscomp ";
      $listobject->performQuery();
      if ( ($runid <> -1) and !in_array($thiscomp, $cachedlist)) {
         // we want to store this output as a specific run, in addition to the default "last run" code 
         $rfilename = $outdir . "/runlog$runid" . "." . $thiscomp . ".log";
         copy($cfilename, $rfilename);
         $listobject->querystring = "  delete from scen_model_run_elements ";
         $listobject->querystring .= " where elementid = $thiscomp ";
         $listobject->querystring .= " and runid = $runid ";
         $listobject->performQuery();
         // custom to be run on this install - 
         $rfileurl = 'http://$serverip' . $outurl . "/runlog$runid" . "." . $thiscomp . ".log";
         $listobject->querystring = "  insert into scen_model_run_elements ";
         $listobject->querystring .= "( runid,starttime, endtime, ";
         $listobject->querystring .= " elem_xml, elementid, output_file, remote_url, ";
         $listobject->querystring .= " run_date, host )";
         $listobject->querystring .= " select $runid, '$startdate', ";
         $listobject->querystring .= "'$enddate', a.elem_xml, ";
         $listobject->querystring .= " a.elementid, '$rfilename', '$rfileurl', ";
         $listobject->querystring .= " '$run_date', '$serverip' ";
         $listobject->querystring .= " from scen_model_element as a ";
         $listobject->querystring .= " where elementid = $thiscomp ";
         $listobject->performQuery();
      }
   }
   //error_log("$listobject->querystring");
   error_log("Done");

   //print($innerHTML);
}
?>
</body>

</html>
