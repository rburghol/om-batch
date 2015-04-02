<?php
// checks for files, copies them to directory
$noajax = 1;
$projectid = 3;
include("./xajax_modeling.element.php");
include("./lib_verify.php");
error_reporting(E_ERROR);

//$rundate = '2010-08-13 03:30:00';
$rundate = '2010-09-16 15:30:00';
$starttime = '1984-01-01 00:00:00';
$endtime = '2005-12-31 00:00:00';
//$endrun = '2010-08-05 12:00:00';
$anal_start = '1984-10-01';
$anal_end = '2005-09-30';
$loval = 0.5;
$hival = 2.0;

$thisdate = date('Y-m-d-h-m-s');
print("$thisdate \n");


//$shed_list = "'PL0_4510_0001A01B04','PM1_3450_3400A01B01','PM1_3710_4040A02B01','PS5_4380_4370A03B01C01'";


// create a linkage to the deq2 database in order to use the plR stats package
$dbip2 = 'localhost';
$dbname2 = 'cbp';
$dbconn = pg_connect("host=$dbip2 port=5432 dbname=$dbname2 user=$dbuser password=$dbpass");
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;


if (isset($argv[1])) {
   $runid = $argv[1];
} else {
   $runid = 1;
}
if (isset($argv[2])) {
   $verify = $argv[2];
} else {
   $verify = 1;
}
if (isset($argv[3])) {
   $copydir = $argv[3];
} else {
   $copydir = '.';
}
if (isset($argv[4])) {
   $target_order = $argv[4];
} else {
   $target_order = '';
}
if (isset($argv[5])) {
   $rundate = $argv[5];
} else {
   $rundate = '';
}


// obtain data file
$listobject->querystring = "  select a.elementid, a.elemname, d.unique_id, ";
$listobject->querystring .= "    b.output_file as run_file,  ";
$listobject->querystring .= "    b.run_date as run_date ";
$listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b, ";
$listobject->querystring .= "    icprb_watersheds as d ";
$listobject->querystring .= " where a.scenarioid = 28 ";
$listobject->querystring .= " and a.objectclass = 'modelContainer' ";
$listobject->querystring .= " and a.elementid = b.elementid ";
$listobject->querystring .= " and b.runid = $runid ";
$listobject->querystring .= " and b.starttime = '$starttime' ";
$listobject->querystring .= " and b.endtime = '$endtime' ";
$listobject->querystring .= " and b.run_date >= '$rundate' ";
$listobject->querystring .= " and a.elemname = d.shed_merge ";
$listobject->querystring .= " and d.mainstem_segment <> 'Y' ";
if ($shed_list <> '') {
   $listobject->querystring .= " and d.shed_merge in ($shed_list) ";
}
   
$listobject->querystring .= " order by elemname ";
print("$listobject->querystring \n");
$listobject->performQuery();
print("Records: " . count($listobject->queryrecords) . "<br>\n");
$file_recs = $listobject->queryrecords;
$run_names = array(1=>'baseline',2=>'current');
$passed = 0;
$failed = 0;
$skipped = 0;
$newfiles = '';

// insert into R-enabled db on deq2 
foreach ($file_recs as $this_filerec) {
   $elementid = $this_filerec['elementid'];
   $elemname = $this_filerec['elemname'];
   error_log("Verifying $elementid - $elemname  \n");
   $run_file = $this_filerec['run_file'];
   $run_date = $this_filerec['run_date'];
   $sname = $run_names[$runid];
   $uid = getUID($listobject, $elementid);
   $destfile = "$copydir/modelout.$uid" . ".csv";
   $order = getElementOrder($listobject, $elementid);
   $flow_check = verifyRunVars($run_file,array('discharge_mgd'),100);
   $ps = $flow_check['discharge_mgd']['mean'];
   if ( ($target_order <> '') and ($target_order == $order) ) {
      $flunked = 0;
      if ($verify) {
         $thishi = $hival;
         $thislo = $loval;
         // get land area
         $child_recs = getChildComponentType($listobject, $elementid, 'CBPLandDataConnection', 1);
         foreach($child_recs as $thischild) {
            $cid = $thischild['elementid'];
            $objresult = unSerializeSingleModelObject($cid);
            $thisobj = $objresult['object'];
            $lseg = $thisobj->id2;
            error_log("Obtaining unit area runoff for land segment $lseg \n");
            $foro = getLandUseRunoff($cbp_listobject, $lseg, 'for', 0);
            if (count($foro) > 0) {
               $thisflo = $foro['avg_cfs_sqmi'];
               if ( ($thisflo > 0) and ($thisflo > $hival) ) {
                  $thishi = $thisflo;
                  error_log("Setting highest runoff to $thisflo \n");
               }
               if ( ($thisflo > 0) and ($thisflo < $loval) ) {
                  $thislo = $thisflo;
                  error_log("Setting lowest runoff to $thisflo \n");
               }
            }
         }
         $container_tree = getNestedContainers($listobject, $elementid);
         foreach ($container_tree as $thisone) {
            $thisid = $thisone['elementid'];
            $flunked = checkTreeRunMode($listobject, $cbp_listobject, $thisid,$sname, $thislo, $thishi + $ps);
         }
      }
      // check for file existance, we use this instead of file_exists because it works with remote as well as local files
      $fe = fopen($run_file,'r');
      if ($fe) {
         $file_exists = 1;
         fclose($fe);
      } else {
         $file_exists = 0;
      }

      if (!$flunked and $file_exists) {
         $docopy = 0;
         if (file_exists($destfile)) {
            // check to see if these are different files
            if ( (files_identical($run_file, $destfile, 5, 1, 1)) ) {
               print("$run_file is identical to $destfile \n");
               $skipped++;
            } else {
               
               $passed++;
               $docopy = 1;
            }
         } else {
            $passed++;
            $docopy = 1;
         }
         if ($docopy) {
            print("Copying $run_file to $destfile \n");
            $newfiles .= $destfile . "\n";
            //die;
            copy($run_file,$destfile);
         }
      } else {
         $failed++;
         print("Element $elementid / $elemname / $uid failed verification \n");
         if (!file_exists($run_file)) {
            print("File $run_file does not exist.\n");
         }
      }
   } else {
      $skipped++;
      print("Element $elementid / $elemname / $uid order $order skipped (target order $target_order) \n");
   }
}

print("Summarizing Copied Files in $copydir/copied$thisdate " . ".txt\n");

$fp = fopen("$copydir/copied$thisdate" . '.txt', 'w');
fwrite($fp, $newfiles);
fclose($fp);

print("Done. $passed elements copied, $failed elements failed. $skipped elements skipped.\n");

?>
