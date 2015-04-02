<?php

// clean up session tables
$noajax = 1;
$projectid = 3;
include("./xajax_modeling.element.php");

$exp_interval = '7 days';

$session_db->querystring = "  select tablename "; 
$session_db->querystring .= " from session_tbl_log "; 
$session_db->querystring .= " where creation_date <= now() - interval '$exp_interval'";
$session_db->performQuery();

$tbls = $session_db->queryrecords;
foreach ($tbls as $thistab) {
   $tblname = $thistab['tablename'];
   $session_db->querystring = "  drop table \"$tblname\" ";
   \\print("$session_db->querystring ; \n");
   $session_db->performQuery();
   if (!$session_db->tableExists($tablename)) {
      $session_db->querystring = "  delete from session_tbl_log where tablename = '$tblname' ";
      \\print("$session_db->querystring ; \n");
      $session_db->performQuery();
   } else {
      print("Problem removing table $tablename \n");
   }
}

// clean up -1 runs
$listobject->querystring = "  delete from system_status where runid = -1 and status_flag = 0 ";
$listobject->performQuery();

?>
