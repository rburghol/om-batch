<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('./xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;

if ( count($argv) < 4 ) {
   print("Usage: batchedit_subcomp_props.php scenarioid subcomp_name \"prop=value\"  [elementid] [elemname] [custom1] [custom2] \n");
   die;
}


$scenarioid = $argv[1];
$subcomps = explode(',', $argv[2]);
$pairs = explode(',', $argv[3]);
$props = array();
$values = array();
foreach ($pairs as $thispair) {
   list($prop,$value) = explode('=', $thispair);
   $props[] = $prop;
   $values[] = $value;
}

if (isset($argv[4])) {
   $elid = $argv[4];
} else {
   $elid = '';
}
if (isset($argv[5])) {
   $elemname = $argv[5];
} else {
   $elemname = '';
}
if (isset($argv[6])) {
   $custom1 = $argv[6];
} else {
   $custom1 = '';
}
if (isset($argv[7])) {
   $custom2 = $argv[7];
} else {
   $custom2 = '';
}

$segs = array();
$listobject->querystring = "  select elementid, elemname from scen_model_element where ( (scenarioid = $scenarioid) or ($scenarioid = -1) ) ";
if ($elid <> '') {
   $listobject->querystring .= " AND elementid = $elid ";
}
if ($elemname <> '') {
   $listobject->querystring .= " AND elemname = '$elemname' ";
}
if ($custom1 <> '') {
   $listobject->querystring .= " AND custom1 = '$custom1' ";
}
if ($custom2 <> '') {
   $listobject->querystring .= " AND custom2 = '$custom2' ";
}
print("Looking for match <br>\n");
print("$listobject->querystring ; <br>\n");
$listobject->performQuery();
$recs = $listobject->queryrecords;
$i = 0;

foreach ($recs as $thisrec) {
   $elid = $thisrec['elementid'];
   $elemname = $thisrec['elemname'];
   $loadres = unSerializeSingleModelObject($elid);
   $thisobject = $loadres['object'];

   if (is_object($thisobject)) {
      foreach ($subcomps as $subcomp_name) {
         if (isset($thisobject->processors[$subcomp_name])) {
            $vals = $values;
            foreach ($props as $thisprop) {
               $thisval = array_shift($vals);
               print("Updating $elemname ($elid) $subcomp_name -> $thisprop = $thisval \n;");
               $thisobject->processors[$subcomp_name]->$thisprop = $thisval;
            }
            saveObjectSubComponents($listobject, $thisobject, $elid );
         } else {
            error_log("Could not find $subcomp_name on element $elid ");
         }
      }
   }
   $i++;
   # Clear cache to save memory
   $unserobjects = array();
}
   
print("Finished - $i records modified.\n");

?>