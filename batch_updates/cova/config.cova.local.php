<?php

$connstring = "host=$dbip dbname=vwuds user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$model_db = $listobject;

$vwudsdb = new pgsql_QueryObject;
$vwudsdb->connstring = $connstring;
$vwudsdb->ogis_compliant = 1;
$vwudsdb->dbconn = $dbconn;
$vwudsdb->adminsetuparray = $adminsetuparray;

$connstring = "host=$dbip dbname=vpdes user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$vpdesdb = new pgsql_QueryObject;
$vpdesdb->connstring = $connstring;
$vpdesdb->ogis_compliant = 1;
$vpdesdb->dbconn = $dbconn;
$vpdesdb->adminsetuparray = $adminsetuparray;

$connstring = "host=$dbip dbname=va_hydro user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$usgsdb = new pgsql_QueryObject;
$usgsdb->connstring = $connstring;
$usgsdb->ogis_compliant = 1;
$usgsdb->dbconn = $dbconn;
$usgsdb->adminsetuparray = $adminsetuparray;

global $templateid, $t_reachid, $pswd_group_tid, $lstemplateid, $sw_tid, $gw_tid, $ps_contid, $ps_tid, $projectid, $cbp_scen, $cova_met_container, $cbp_met_template_id;

$templateid = 176615;
$t_reachid = 207667;
$pswd_group_tid = 207671;
$lstemplateid = 207683;
$sw_tid = 207673;
$gw_tid = 207961;
$ps_contid = 216384;
$ps_tid = 216385;
$gwd_tid = 215093; //generic withdrawal/discharge object
$projectid = 3;
$cbp_scen = 4;
$lutemplateid = 1111;
// meteorology
$cova_met_container = 207659;
$cbp_met_template_id = 320989;
// templates for ICPRB linkages 
$icprb_pstid = 319780;
$icprb_wdtid = 319782;

$cbp_rivers = array(
   'SU' => array('name'=>'Upper Susquehanna River, above confluence with West Branch', 'terminal_seg'=>'SU0_0000_0000', 'terminal_name'=>'Susquehanna River'),
   'SW' => array('name'=>'Susquehanna River, West Branch', 'terminal_seg'=>'SU0_0000_0000', 'terminal_name'=>'Susquehanna River'),
   'SJ' => array('name'=>'Juniata River', 'terminal_seg'=>'SU0_0000_0000', 'terminal_name'=>'Susquehanna River'),
   'SL' => array('name'=>'Lower Susquehanna River below West Branch confluence not including the Juniata River', 'terminal_seg'=>'SU0_0000_0000', 'terminal_name'=>'Susquehanna River'),
   'PR' => array('name'=>'Potomac River', 'terminal_seg'=>'PR0_0000_0000', 'terminal_name'=>'Potomac River'),
   'PU' => array('name'=>'Upper Potomac River, above Shenandoah confluence', 'terminal_seg'=>'PR0_0000_0000', 'terminal_name'=>'Potomac River'),
   'PS' => array('name'=>'Shenandoah River', 'terminal_seg'=>'PR0_0000_0000', 'terminal_name'=>'Potomac River'),
   'PM' => array('name'=>'Middle Potomac River, including Monocacy River below Shenandoah confluence, above Chain Bridge', 'terminal_seg'=>'PR0_0000_0000', 'terminal_name'=>'Potomac River'),
   'PL' => array('name'=>'Lower Potomac River, below Chain Bridge', 'terminal_seg'=>'PR0_0000_0000', 'terminal_name'=>'Potomac River'),
   'JR' => array('name'=>'James River', 'terminal_seg'=>'JR0_0000_0000', 'terminal_name'=>'James River'),
   'JU' => array('name'=>'Upper James River, above the Maury River confluence', 'terminal_seg'=>'JR0_0000_0000', 'terminal_name'=>'James River'),
   'JL' => array('name'=>'Lower James River, below the Maury River confluence, above Richmond, Virginia', 'terminal_seg'=>'JR0_0000_0000', 'terminal_seg'=>'JR0_0000_0000', 'terminal_name'=>'James River'),
   'JA' => array('name'=>'Appomattox River', 'terminal_seg'=>'JR0_0000_0000', 'terminal_name'=>'James River'),
   'JB' => array('name'=>'James River, below Richmond, Virginia, not including the Appomattox River', 'terminal_seg'=>'JR0_0000_0000', 'terminal_seg'=>'JR0_0000_0000', 'terminal_name'=>'James River'),
   'YP' => array('name'=>'Pamunkey River', 'terminal_seg'=>'YR0_0000_0000', 'terminal_name'=>'York River'),
   'YM' => array('name'=>'Mattaponi River', 'terminal_seg'=>'YR0_0000_0000', 'terminal_name'=>'York River'),
   'YL' => array('name'=>'York River, below Mattaponi and Pamunkey confluence, including the Piankatank River', 'terminal_seg'=>'YR0_0000_0000', 'terminal_seg'=>'YR0_0000_0000', 'terminal_name'=>'York River'),
   'YR' => array('name'=>'York River', 'terminal_seg'=>'YR0_0000_0000', 'terminal_name'=>'York River'),
   'RR' => array('name'=>'Rappahannock River', 'terminal_seg'=>'RR0_0000_0000', 'terminal_seg'=>'RR0_0000_0000', 'terminal_name'=>'Rappahannock River'),
   'RU' => array('name'=>'Upper Rappahannock River', 'terminal_seg'=>'RR0_0000_0000', 'terminal_name'=>'Rappahannock River'),
   'RL' => array('name'=>'Lower Rappahannock River', 'terminal_seg'=>'RR0_0000_0000', 'terminal_name'=>'Rappahannock River'),
   'XU' => array('name'=>'Patuxent River above Bowie, Maryland', 'terminal_seg'=>'PX0_0000_0000', 'terminal_name'=>'Patuxent River'),
   'XL' => array('name'=>'Patuxent River below Bowie, Maryland', 'terminal_seg'=>'PX0_0000_0000', 'terminal_name'=>'Patuxent River'),
   'PX' => array('name'=>'Patuxent River', 'terminal_seg'=>'PX0_0000_0000', 'terminal_name'=>'Patuxent River'),
   'WS' => array('name'=>'Western shore', 'terminal_seg'=>'WS0_0000_0000', 'terminal_name'=>'Western Shore'),
   'WL' => array('name'=>'Lower Western shore', 'terminal_seg'=>'WS0_0000_0000', 'terminal_name'=>'Western Shore'),
   'WM' => array('name'=>'Middle Western shore, including the Patapsco and Back Rivers', 'terminal_seg'=>'WS0_0000_0000', 'terminal_name'=>'Western Shore'),
   'WU' => array('name'=>'Upper Western shore', 'terminal_seg'=>'WS0_0000_0000', 'terminal_name'=>'Western Shore'),
   'ES' => array('name'=>'Eastern Shore', 'terminal_seg'=>'ES0_0000_0000', 'terminal_name'=>'Eastern Shore'),
   'EU' => array('name'=>'Upper Eastern Shore', 'terminal_seg'=>'ES0_0000_0000', 'terminal_name'=>'Eastern Shore'),
   'EL' => array('name'=>'Lower Eastern Shore', 'terminal_seg'=>'ES0_0000_0000', 'terminal_name'=>'Eastern Shore'),
   'EM' => array('name'=>'Middle Eastern Shore, including the Choptank River', 'terminal_seg'=>'ES0_0000_0000', 'terminal_name'=>'Eastern Shore'),
   'GY' => array('name'=>'Part of the Youghiogheny River', 'terminal_seg'=>'GY0_0000_0000', 'terminal_name'=>'Youghiogheny River'),
   'DE' => array('name'=>'Delmarva Peninsula, outside the Chesapeake Bay watershed', 'terminal_seg'=>'DE0_0000_0000', 'terminal_name'=>'Delmarva Peninsula'),
   'TU' => array('name'=>'Part of the Upper Tennessee River', 'terminal_seg'=>'TU0_0000_0000', 'terminal_name'=>'Tennessee River'),
   'BS' => array('name'=>'Part of the Big Sandy River', 'terminal_seg'=>'BS0_0000_0000', 'terminal_name'=>'Big Sandy River'),
   'NR' => array('name'=>'Part of the New River', 'terminal_seg'=>'NR0_0000_0000', 'terminal_name'=>'New River'),
   'OD' => array('name'=>'Dan River, tributary of the Roanoke River', 'terminal_seg'=>'OD0_0000_0000', 'terminal_name'=>'Roanoke River'),
   'OR' => array('name'=>'Part of Roanoke River, not including the Dan River', 'terminal_seg'=>'OR0_0000_0000', 'terminal_name'=>'Roanoke River'),
   'MN' => array('name'=>'Meherrin and Nottoway rivers', 'terminal_seg'=>'MN0_0000_0000', 'terminal_name'=>'Nottoway River')
);

?>
