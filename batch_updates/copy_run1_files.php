<?php
// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
$scid = 28;
include("../xajax_modeling.element.php");
//$rundate = '2010-08-13 03:30:00';
$rundate = '2010-08-30 15:30:00';
$startrun = '1984-01-01';
$endrun = '2005-12-31';
$runid = 1;


// linkage to cbp database with ICPRB info
$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

// get all possible unique ids from the shed table
$listobject->querystring = "  select a.elementid, b.uniq_id, b.shed_merge ";
$listobject->querystring .= " from scen_model_element as a, tmp_icprb_localshapes as b ";
$listobject->querystring .= " where a.elemname = b.shed_merge ";
$listobject->querystring .= "    and a.scenarioid = $scid ";
print("$listobject->querystring \n");
$listobject->performQuery();
$shedrecs = $listobject->queryrecords;

// iterate through all csv files in the baseline directory that match the unique ID code
// get the shed_merge code that matches the unique id
foreach ($shedrecs as $thisrec) {
   $uid = $thisrec['uniq_id'];
   $elementid = $thisrec['elementid'];
   $elemname = $thisrec['shed_merge'];
   $outfile = "./output_runid$runid/$uid" . ".csv";
   print("Checking for $outfile \n");
   if (file_exists($outfile)) {
      
      // get the elementid that corresponds to this shed code
      // get the run log file for the basleine run
      $listobject->querystring = "  select a.output_file, b.elementid ";
      $listobject->querystring .= " from scen_model_run_elements as a ";
      $listobject->querystring .= " where a.elementid = $elementid ";
      $listobject->querystring .= "    and a.runid = $runid ";
      $listobject->querystring .= "    and a.starttime >= '$startrun' ";
      $listobject->querystring .= "    and a.endtime <= '$endrun' ";
      print("$listobject->querystring \n");
      $listobject->performQuery();
      if (count($listobject->queryrecords) == 0) {
         $runfile = $outdir . "/runlog$runid" . ".$elementid" . ".log";
      } else {
         $runfile = $listobject->getRecordValue(1,'output_file');
      }
      
      // if file does not exist, copy the file from the baseline archive directory
      if (!file_exists($runfile)) {
         print("Need to copy $outfile to $runfile \n");
         copy($outfile, $runfile);
         //die;
      }

   }
   
}





?>