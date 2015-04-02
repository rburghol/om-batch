<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('./xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/

$srcid = $argv[1];
$destids = split(',',$argv[2]);
$src_opname = $argv[3];
if (isset($argv[4])) {
   $dest_opname = $argv[4];
} else {
   $dest_opname = $src_opname;
}

foreach ($destids as $dest_elementid) {

   print("  copySubComponent($srcid, $src_opname, $dest_elementid, $dest_opname); ");
   copySubComponent($srcid, $src_opname, $dest_elementid, $dest_opname);
   
}

?>
</body>

</html>
