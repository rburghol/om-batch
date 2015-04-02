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

$src_elementid = 176244;
$destids = array(176251,176310);

foreach ($destids as $dest_elementid) {
   $src_opname = 'modeled_discharge';
   $dest_opname = 'modeled_discharge';

   copySubComponent($src_elementid, $src_opname, $dest_elementid, $dest_opname);
   $src_opname = 'modeled_wd';
   $dest_opname = 'modeled_wd';
   copySubComponent($src_elementid, $src_opname, $dest_elementid, $dest_opname);
   
   $src_opname = 'Send Parent withdrawals discharges';
   $dest_opname = 'Send Parent withdrawals discharges';
   copySubComponent($src_elementid, $src_opname, $dest_elementid, $dest_opname);
   
}

?>
</body>

</html>
