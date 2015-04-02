<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');
error_reporting(E_ERROR);

if (count($argv) < 3) {
   print("Usage: php cova_batch_landuse.php filename lu_matrix_name scenarioid [mode=cova, covalite, icprb] [minyear=1900] [maxyear=2050]\n");
   print("File header/format: riverseg,landseg,year,lu1,lu2,lu3,...\n");
   print("'riverseg' field matches parent object 'custom2' field...\n");
   print("mode: cova = COVA framework, covalite = liteweight cova framework, icprb - ICPRB (similar to covalite)\n");
   die;
}
$filename = $argv[1];
$lu_matrix_name = $argv[2];
$scenarioid = $argv[3];
if (isset ($argv[4])) {
   $mode = $argv[4];
} else {
   $mode = 'cova';
}
if (isset ($argv[5])) {
   $minyear = $argv[5];
} else {
   $minyear = 1900;
}
if (isset ($argv[6])) {
   $maxyear = $argv[6];
} else {
   $maxyear = 2050;
}

// read file into array
$lines = readDelimitedFile($filename,',', 1);
// iterate through array
// $lu array comes from each line that is NOT riverseg, or landseg
$nonlucols = array('riverseg', 'landseg', 'custom2');
//print_r($lines);
foreach($lines as $thisline) {
   
   $landseg = $thisline['landseg'];
   print("Searching for $landseg \n");
   if (isset($thisline['custom2'])) {
      $custom2 = $thisline['custom2'];
      $parentrec = getComponentCustom($listobject, $scenarioid, '', $custom2, -1, array(), 1);
      $parentid = $parentrec[0]['elementid'];
      print("Getting container from custom2 = $custom2 - $parentid\n");
   } else {
      $riverseg = $thisline['riverseg'];
      switch ($mode) {
         case 'cova':
            $parentid = getCOVACBPParent($scenarioid, $riverseg, $debug);
         break;

         case 'covalite':
            $parentid = getVAHydroLiteContainer($listobject, $scenarioid, $riverseg);
         break;

         case 'icprb':
            $parentid = getICPRBContainer($listobject, $scenarioid, $riverseg);
         break;
      }
   }
    
   switch ($mode) {
      case 'cova':
         $elid = getCOVACBPLRsegObject($listobject, $parentid, $landseg);
      break;
      
      case 'covalite':
         $elid = getVAHydroLiteLandseg($listobject, $parentid, $landseg, $debug);
      break;
      
      case 'icprb':
         $elid = getICPRBLandseg($listobject, $parentid, $landseg, 1);
      break;
   }
   
   $lr = array();
   foreach(array_keys($thisline) as $thiscol) {
      if (!in_array($thiscol, $nonlucols)) {
         if ( trim($thiscol) <> '') {
            $thisarea = $thisline[$thiscol];
            $lr[] = array('luname'=>$thiscol, $minyear => round($thisarea,3), $maxyear => round($thisarea,3));
         }
      }
   }
   setLUMatrix ($elid, $lu_matrix_name, $lr);

}


?>
