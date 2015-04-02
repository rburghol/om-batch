<?php
# this is to over-ride newer PHP defaults that turn off error reporting by default
# this is to prevent an unexpected error from revealing information that could be
# used to compromise security

# shutdown function
function halted()
{
    global $listobject, $cropobject;
#    $listobject->cancel();
    #$cropobject->cancel();
    pg_close($listobject->dbconn);
    #pg_close($cropobject->dbconn);
    #print("Query Cancelled!<br>");
}
//register_shutdown_function('halted');
if (!isset($debug)) {
   $debug = 0;
}
#$debug = 1;
#ini_set('display_errors', 'On');
#ini_set('error_reporting', 'E_ALL');
#error_reporting(E_ALL & ~E_STRICT);
//error_reporting(E_ALL);
error_reporting(E_ERROR);
#error_reporting(E_NONE);

$scriptname = $_SERVER['PHP_SELF'];

include_once('./config.local.php');

if (isset($_SESSION['projectid'])) {
   $projectid = $_SESSION['projectid'];
}
$indir = "$basedir/in";
$compdir = "$datadir/proj$projectid/components";
$outdir = "$datadir/proj$projectid/out";
$outurl = "$dataurl/proj$projectid/out";
$ucidir = "$httppath/uci/";
$glibdir = "$libpath/jpgraph";
$goutdir = "$httproot/tmp/";
$goutpath = "$httproot/tmp";
# location of the graphics library - jpgraph
$glibdir = "$libpath/jpgraph";
#include_once("$libpath/module_activemap.php");

# get database and file libraries
include_once("$libpath/psql_functions.php");
include_once("$libpath/lib_oracle.php");
include_once("$libpath/lib_odbc.php");
include_once("$libpath/file_functions.php");
# custom stream definition to write and read excel files
#include_once("$libpath/xlsstream/excel.php");

# security related libraries
include_once("$libpath/sanitize.inc.php");

# get PEAR libraries
if ($debug) {
   error_log("Loading PEAR Libraries");
}
#if (!class_exists('PEAR')) {
#   include_once("$libpath/PEAR/PEAR.php");
#}
include_once("$libpath/PEAR/Tar.php");
include_once("$libpath/PEAR/Serializer.php");
include_once("$libpath/PEAR/Unserializer.php");
require_once("$libpath/magpierss/rss_fetch.inc"); 


if ($debug) {
   error_log("Loading Misc Libraries");
}
include_once("$libpath/misc_functions.php");
include_once("$libpath/db_functions.php");
include_once("$libpath/data_functions.php");
include_once("$libpath/phpmath/Matrix.php");


if ($debug) {
   error_log("Loading Modeling Libraries");
}
# get application libraries
include_once("$libpath/HSPFFunctions.php");
include_once("$libpath/lib_source_assessment.php");
include_once("$libpath/lib_hydro.php");
include_once("$libpath/lib_hydrology.php");
if ($debug) {
   error_log("Loading Math Libraries");
}
include_once("$libpath/lib_equation2.php");
if ($debug) {
   error_log("Loading GIS Libraries");
}
include_once("$libpath/lib_gis.php");
if ($debug) {
   error_log("Loading Remote Data Aquisition Libraries");
}
include_once("$libpath/lib_usgs.php");
if ($debug) {
   error_log("Loading Graphing Libraries");
}
include_once("$libpath/lib_plot.php");
define('DEFAULT_GFORMAT',$default_imagetype);
if ($debug) {
   error_log("Loading Water Supply Libraries");
}
include_once("$libpath/lib_vwuds.php");
include_once("$libpath/lib_batchmodel.php");

# email libraries
#require_once("$libpath/class.phpmailer.php");
#require_once("$libpath/class.smtp.php");

if ($debug) {
   error_log("Loading FileNice Libraries");
}
# file nice library
include_once("$libpath/fileNice/fileNice.php");
include_once("$libpath/fn_object.php");
$fno = new FNObject;
$fno->silent = 1;
$fno->init();
$fno->fnscript = "$liburl/fn_object.php";
$fno->dirPath = $compdir;
$fno->skindir = "$liburl/fileNice/skins";
$fno->scriptdir = "$liburl/fileNice";


if ($debug) {
   error_log("Loading Local Libraries");
}
# get local libraries
include_once("$basedir/local_functions.php");
include_once("$basedir/archive.php");
include_once("$basedir/lib_bmp.php");
include_once("$basedir/lib_transform.php");
include_once("$basedir/lib_sourcepop.php");
include_once("$basedir/lib_report.php");
include_once("$basedir/lib_import.php");
include_once("$basedir/lib_export.php");
include_once("$basedir/lib_crop.php");
include_once("$basedir/lib_modelinputs.php");
include_once("$basedir/lib_scenario.php");
include_once("$basedir/lib_admin.php");
include_once("$basedir/lib_local.php");
include_once("$libpath/lib_wooomm.php");

# get modeling components
#include_once("$basedir/who_xmlobjects.php");


if ($debug) {
   error_log("Loading Variable Defaults");
}
# get local default values
include_once("$libpath/hspf.defaults.php");
include_once("$basedir/adminsetup.php");
include_once("$basedir/local_variables.php");
include_once("$basedir/lib_local.php");

# get local form functions
#include_once("$basedir/forms/form_modeldata.php");


if ($debug) {
   error_log("Connecting to Database Object");
}
// START - set up database connections
$connstring = "host=$dbip dbname=$dbname user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$listobject = new pgsql_QueryObject;
$listobject->connstring = $connstring;
$listobject->ogis_compliant = 1;
$listobject->dbconn = $dbconn;
$listobject->adminsetuparray = $adminsetuparray;

$session_connstring = "host=$session_dbip dbname=$session_dbname user=$session_dbuser password=$session_dbpass";
$session_dbconn = pg_connect($session_connstring, PGSQL_CONNECT_FORCE_NEW);
$session_db = new pgsql_QueryObject;
$session_db->connstring = $session_connstring;
$session_db->ogis_compliant = 1;
$session_db->dbconn = $session_dbconn;
$session_db->adminsetuparray = $adminsetuparray;

// create a linkage to the deq2 database in order to use the plR stats package
$dbconn = pg_connect("host=$dbip2 port=5432 dbname=$dbname2 user=$dbuser password=$dbpass");
$analysis_db = new pgsql_QueryObject;
$analysis_db->dbconn = $dbconn;

// linkage to cbp database with ICPRB info
$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

print("$connstring \n");

// END - set up database connections
# Timer
$timer = new timerObject;


$uciobject = new HSPF_UCIobject;
$uciobject->ucidir = $ucidir;
$uciobject->listobject = $listobject;
$uciobject->ucitables = $ucitables;

# create a separate db object for the crop database
# should merge these dbs in the future
#$cropobject = new pgsql_QueryObject;
#$cropobject->dbconn = $cropdbconn;
#$cropobject->adminsetuparray = $adminsetuparray;

# get standard masslinks
#$masslinks =  file ("$libpath/mass-link.txt");

$loggedin = 0;

?>
