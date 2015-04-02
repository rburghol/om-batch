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

$src_elementid = 176257;
$destids = array(176308,176247);

foreach ($destids as $dest_elementid) {
   $src_opname = 'Listen Inflows Demand Withdrawals';
   $dest_opname = 'Listen Inflows Demand Withdrawals';

   copySubComponent($src_elementid, $src_opname, $dest_elementid, $dest_opname);
   $src_opname = 'Broadcast Outflows';
   $dest_opname = 'Broadcast Outflows';

   copySubComponent($src_elementid, $src_opname, $dest_elementid, $dest_opname);
   $src_opname = 'Qin';
   $dest_opname = 'Qin';

   copySubComponent($src_elementid, $src_opname, $dest_elementid, $dest_opname);
   
}

?>
</body>

</html>
