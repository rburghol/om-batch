<?php

# xajax based library - watersupply

#include_once("xajax_config.php");

include_once("xajax_modeling.common.php");
#require_once ("$libpath/xajax/xajax_core/xajax.inc.php");

if (!$noajax) {
   $xajax->processRequest();
}


function collapseableMenu($indata, $styles=array('Container'=>'mC','Header'=>'mH','Line'=>'mL','Value'=>'mO')) {


}



function showModelRunForm($formValues) {
   include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $controlHTML = modelRunForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}


function showModelSearchForm($formValues) {
   include_once("adminsetup.php");
   if (!isset($formValues['result_type'])) {
      $result_type = 'model_edit_panel';
   }
   if (!isset($formValues['divname'])) {
      $divname = 'controlpanel';
   } else {
      $divname = $formValues['divname'];
   }
   $objResponse = new xajaxResponse();
   //$controlHTML = print_r($formValues,1) . "<hr>";
   $controlHTML .= modelSearchForm($formValues);
   if ($formValues['actiontype'] == 'do_search') {
      $controlHTML .= "<hr>" . modelSearchResults($formValues);
   }
   $objResponse->assign($divname,"innerHTML",$controlHTML);
   return $objResponse;
}


function showModelActivity($formValues) {
   global $listobject;
   if (!isset($formValues['divname'])) {
      $divname = "controlpanel";
   } else {
      $divname = $formValues['divname'];
   }
   $objResponse = new xajaxResponse();
   $mins = 60;
   $controlHTML = "<b>Last $mins minutes model activity</b><br>";
   $activity = getModelActivity($mins);
   $controlHTML .= $activity;
   //error_log("Model activity - $activity");
   $objResponse->assign($divname,"innerHTML",$controlHTML);
   return $objResponse;
}

function getModelActivity($mins) {
   global $listobject;
   $innerHTML = '';
   
   $listobject->querystring = "  select a.elementid, a.elemname, b.status_mesg, b.runid, b.host ";
   $listobject->querystring .= " from scen_model_element as a, system_status as b ";
   $listobject->querystring .= " where a.elementid = b.element_key ";
   $listobject->querystring .= " and b.last_updated >= now() - interval '$mins minutes' ";
   $listobject->querystring .= " order by last_updated ";
   //error_log("$listobject->querystring ");
   $listobject->performQuery();
   $n = count($listobject->queryrecords);
   //$listobject->show = 0;
   //$listobject->showList();

   $qrecs = $listobject->queryrecords;
   $qlinks = array();
   
   $innerHTML .= "<form id=modelsearch name=modelsearch>";
   
   $formname = 'elementtree';
   
   foreach($qrecs as $thiskey=>$thisrec) {
      $rec = array();
      $elid =  $thisrec['elementid'];
      $rec['elementid'] = $elid;
      $info = getElementInfo($listobject, $elid);
      $listobject->querystring = "select dest_id from map_model_linkages where linktype = 1 and src_id = $elid ";
      $listobject->performQuery();
      if (count($listobject->queryrecords) > 0) {
         $container = $listobject->getRecordvalue(1,'dest_id');
      } else {
         $container = $elid;
      }
      $scenarioid = $info['scenarioid'];

      $clickscript = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$elid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$container; document.forms['$formname'].elements.scenarioid.value=$scenarioid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
      
      $qrecs[$thiskey]['elemname'] = "<a onclick=\"$clickscript ;\" >" . $thisrec['elemname'] . "</a><br>";
      $rec['elemname'] = "<a onclick=\"$clickscript ;\" >" . $thisrec['elemname'] . "</a><br>";
      $qlinks[] = $rec;
   }
   //$listobject->queryrecords = $qlinks;
   $listobject->queryrecords = $qrecs;
   $listobject->show = 0;
   $listobject->showList();
   $innerHTML .= $listobject->outstring;
   
   $innerHTML .= showHiddenField('projectid',$projectid, 1);
   $innerHTML .= "</form>";
   
   return "Modifed view: $n records returned <br>" . $innerHTML;
   
   //return "$n records returned <br>" . $listobject->outstring;
}

function showModelRunResult($formValues) {
   global $libpath, $adminsetuparray;
   include_once("adminsetup.php");
   #include_once("who_xmlobjects.php");
   $objResponse = new xajaxResponse();
   $innerHTML = modelRunResult($formValues);
   $controlHTML = modelRunForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}


function openModelRunWorkspace($formValues) {
   global $libpath, $adminsetuparray, $scenarioid;
   include_once("adminsetup.php");
   #include_once("who_xmlobjects.php");
   $objResponse = new xajaxResponse();
   $innerHTML = modelRunResult($formValues);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function modelSearchForm($formValues, $formaction =  "xajax_showModelSearchForm(xajax.getFormValues(\"modelsearch\"))") {
   global $listobject, $projectid, $userid, $usergroupids, $debug, $adminsetuparray;
   
   $innerHTML = '';
   $scenarioid = $formValues['scenarioid'];
   if (isset($formValues['element_text'])) {
      $element_text = $formValues['element_text'];
   } else {
      $element_text = '';
   }
   if (!isset($formValues['divname'])) {
      $divname = 'controlpanel';
   } else {
      $divname = $formValues['divname'];
   }
   if (!isset($formValues['result_type'])) {
      $result_type = 'model_edit_panel';
   } else {
      $result_type = $formValues['result_type'];
   }
   
   //$innerHTML .= print_r($formValues,1) . "<br>";
   //$innerHTML .= "Result Type set to: $result_type <br>";
   # create a scenarioid query
   $scensql = " ( select scenarioid, scenario from scenario ";
   $scensql .= "  where projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
   $scensql .= "     or ( groupid in ($usergroupids) and gperms >= 4 ) ";
   $scensql .= "     or (pperms >= 4) ) ";
   $scensql .= "  order by scenario  ";
   $scensql .= " ) as foo ";
   
   //$innerHTML .= $scensql;

   $innerHTML .= "<form id=modelsearch name=modelsearch>";
   /*
   $innerHTML .= "Domain to Search: ";
   $innerHTML .= showActiveList($listobject, "scenarioid", $scensql, 'scenario','scenarioid', '', $scenarioid, '', '', $debug, 1, 0) . '<br>';

   $innerHTML .= "<br><b>Element Name (use '%' as wild-card): </b> ";
   $innerHTML .= showWidthTextField('element_text', $element_text, 30, '', 1);
   */
   
   // new search object version
   $aset = $adminsetuparray['scen_model_element'];
   $listobject->adminsetup = $aset;
   //$innerHTML .= "Adminsetup: " . print_r($aset,1) . "<br>";
   # set up the search object
   $searchobject = new listObjectSearchForm;
   # create a scenarioid query
   $scensql = "  select scenarioid from scenario ";
   $scensql .= "  where projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
   $scensql .= "     or ( groupid in ($usergroupids) and gperms >= 4 ) ";
   $scensql .= "     or (pperms >= 4) ) ";
   $scensql .= "  order by scenario  ";
   $aset['search info']['columns']['scenarioid']['params'] = "scenario:scenarioid:scenario:scenario:0:scenarioid in ($scensql)";
   $searchobject->listobject = $listobject;
   $searchobject->debug = FALSE;
   $searchobject->adminsetup = $aset;
   $searchobject->setVariableNames($formValues);
   $searchForm = $searchobject->showSearchForm($formValues);
   //$innerHTML .= "<hr>" . print_r($formValues,1) . "<hr>" . $searchForm['formHTML'];
   $innerHTML .= $searchForm['formHTML'];
   
   $innerHTML .= showHiddenField('projectid',$projectid, 1);
   $innerHTML .= showHiddenField('actiontype','do_search', 1);
   $innerHTML .= showHiddenField('result_type',$result_type, 1);
   $innerHTML .= showHiddenField('divname',$divname, 1);
   $innerHTML .= "<br>" . showGenericButton('search','Search', $formaction, 1);
   
   $innerHTML .= "</form>";
   
   return $innerHTML;


}

function modelSearchResults($formValues) {
   global $listobject, $projectid, $userid, $usergroupids, $debug, $adminsetuparray;
   $innerHTML = '';

   $element_text = $formValues['element_text'];
   $result_type = $formValues['result_type'];
   

   // new search object version
   $aset = $adminsetuparray['scen_model_element'];
   $listobject->adminsetup = $aset;   
   # set up the search object
   $searchobject = new listObjectSearchForm;
   $searchobject->listobject = $listobject;
   $searchobject->debug = FALSE;
   $searchobject->adminsetup = $aset;
   $searchobject->setVariableNames($formValues);
   $searchForm = $searchobject->showSearchForm($formValues);
   $subquery = " (" . $searchForm['query'] . " ) as foo ";
   $innerHTML .= "<hr>" . $subquery . "<hr>";
   
   // old school
   //$listobject->querystring = "select elementid, elemname from scen_model_element where scenarioid = $scenarioid and elemname ilike '$element_text' ";
   // new school - uses query form
   $listobject->querystring = "select scenarioid, elementid, elemname, custom1, custom2 from $subquery ";
   
   $innerHTML .= "$listobject->querystring ; <br>";
   $listobject->performQuery();
   $qrecs = $listobject->queryrecords;
   $qlinks = array();
   
   $formname = 'elementtree';
   
   foreach($qrecs as $thiskey=>$thisrec) {
      $rec = array();
      $elid =  $thisrec['elementid'];
      $rec['elementid'] = $elid;
      $listobject->querystring = "select dest_id from map_model_linkages where linktype = 1 and src_id = $elid ";
      $listobject->performQuery();
      if (count($listobject->queryrecords) > 0) {
         $container = $listobject->getRecordvalue(1,'dest_id');
      } else {
         $container = $elid;
      }
      $scenarioid = $thisrec['scenarioid'];

      switch ($result_type) {
         case 'model_edit_panel':
         $clickscript = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$elid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$container; document.forms['$formname'].elements.scenarioid.value=$scenarioid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
         break;

         case 'remote_link_search':
         $clickscript = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$elid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$container; document.forms['$formname'].elements.scenarioid.value=$scenarioid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
         break;
         
         case 'vwp_modelsearch':
         $clickscript = "document.forms['form1'].elements['actiontype'].value = 'edit';  document.forms['form1'].elements['switch_elid'].value = $elid; document.forms['form1'].submit() ";
         break;

         default:
         $clickscript = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$elid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$container; document.forms['$formname'].elements.scenarioid.value=$scenarioid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
         break;
      }
      
      $qrecs[$thiskey]['elemname'] = "<a onclick=\"$clickscript ;\" >" . $thisrec['elemname'] . "</a><br>";
      $rec['elemname'] = "<a onclick=\"$clickscript ;\" >" . $thisrec['elemname'] . "</a><br>";
      $qlinks[] = $rec;
   }
   //$listobject->queryrecords = $qlinks;
   $listobject->queryrecords = $qrecs;
   $listobject->show = 0;
   $listobject->showList();
   $innerHTML .= $listobject->outstring;
   
   return $innerHTML;
}

function insertComponent($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug, $libpath, $adminsetuparray;
   //include_once("adminsetup.php");
   #include_once("who_xmlobjects.php");
   $activecontainerid = $formValues['activecontainerid'];
   $insertresult = insertBlankComponent($formValues);
   //$formValues['scenarioid'] = $insertresult['scenarioid'];
   $innerHTML = $insertresult['innerHTML'] . "<br> Scenarioid from insertBlankComponent = " . $insertresult['scenarioid'] . "<br>";
   $elementid = $insertresult['elementid'];
   $formValues['elementid'] = $elementid;
   $objResponse = showModelDesktopView($formValues);
  // if ( ($scenarioid <> -1) ) {
  //    $browserHTML = showHierarchicalMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, 0, 0, $activecontainerid );
  //    $menutarget = 'unit_elementtree' . '_sc' . $scenarioid;
  // } else {
      //$browserHTML = "Insert component calling showHierarchicalmenu<br>";
      $browserHTML .= showHierarchicalMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, 0, 1, $activecontainerid);
      //$browserHTML .= "FINISHED showHierarchicalmenu<br>";
      $menutarget = 'objectbrowser';
//   }
   $objResponse->assign($menutarget,"innerHTML",$browserHTML);
   //$objResponse->assign("objectbrowser","innerHTML",$browserHTML);
   $objResponse->assign("commandresult","innerHTML",$innerHTML);
   return $objResponse;
}

function insertComponentClone($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug, $libpath, $adminsetuparray;
   //include_once("adminsetup.php");
   #include_once("who_xmlobjects.php");
   if (isset($formValues['activecontainerid'])) {
      $activecontainerid = $formValues['activecontainerid'];
   }
   $elid = $formValues['elementid'];
   //error_log("Cloning $elid into $activecontainerid <br>" . print_r($formValues,1));
   $insertresult = cloneModelElement($scenarioid, $elid, $activecontainerid);
   $innerHTML = $insertresult['innerHTML'];
   $elementid = $insertresult['elementid'];
   $formValues['elementid'] = $elementid;
   $objResponse = showModelDesktopView($formValues);
   // force scenarioid = -1 since this forces full refresh of the menu, not optimal, but for now, it is what is needed
   //$browserHTML = showHierarchicalMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, 0);
   $browserHTML = showHierarchicalMenu($listobject, $projectid, -1, $userid, $usergroupids, 0);
   $objResponse->assign("commandresult","innerHTML",$innerHTML);
   $objResponse->assign("objectbrowser","innerHTML",$browserHTML);
   return $objResponse;
}

function refreshHierarchicalMenu($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug, $libpath, $adminsetuparray;
   //include_once("adminsetup.php");
   if (isset($formValues['activecontainerid'])) {
      $activecontainerid = $formValues['activecontainerid'];
   } else {
      $activecontainerid = -1;
   }
   $objResponse = new xajaxResponse();
   // force scenarioid = -1 since this forces full refresh of the menu, not optimal, but for now, it is what is needed
   //$browserHTML = showHierarchicalMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, 0);
   $browserHTML = showHierarchicalMenu($listobject, $projectid, -1, $userid, $usergroupids, 0, 1, $activecontainer, $formValues);
   $objResponse->assign("objectbrowser","innerHTML",$browserHTML);
   return $objResponse;
}

function insertGroupClone($formValues) {
   global $listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug, $libpath, $adminsetuparray;
   //include_once("adminsetup.php");
   #include_once("who_xmlobjects.php");
   $clonedata = array();
   if (isset($formValues['activecontainerid'])) {
      $clonedata['dest_parent'] = $formValues['activecontainerid'];
   }
   $elid = $formValues['elementid'];
   $clonedata['elements'] = array($elid);
   $clonedata['projectid'] = $projectid;
   $clonedata['scenarioid'] = $formValues['scenarioid'];
   $clonedata['dest_scenarioid'] = $formValues['scenarioid'];
   $innerHTML = "Cloning $elid into $activecontainerid <br>" . print_r($clonedata,1) . " <br>" . print_r($formValues,1) . " <br>";
   $insertresult = copyModelGroupFull($clonedata);
   $innerHTML .= "Clone routine output <br>" . print_r($insertresult,1) . " <br>";
   $innerHTML .= $insertresult['innerHTML'];
   $elementid = $insertresult['elementid'];
   $formValues['elementid'] = $elementid;
   $objResponse = showModelDesktopView($formValues);
   // force scenarioid = -1 since this forces full refresh of the menu, not optimal, but for now, it is what is needed
   $browserHTML = showHierarchicalMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, 0);
   $browserHTML = showHierarchicalMenu($listobject, $projectid, -1, $userid, $usergroupids, 0);
   
   $objResponse = new xajaxResponse();
   $objResponse->assign("status_bar","innerHTML",$innerHTML);
   //$objResponse->assign("commandresult","innerHTML",$innerHTML);
   $objResponse->assign("objectbrowser","innerHTML",$browserHTML);
   return $objResponse;
}

function showStatus($formValues, $mins = 60) {
   global $listobject, $userid, $scenarioid;
   $objResponse = new xajaxResponse();
   $statusHTML = '';
   $eid = $formValues['elementid'];
   $listobject->querystring = " select runid, status_flag, status_mesg, last_updated, element_key from system_status where  element_key = $eid and last_updated >= now() - interval '$mins minutes' ";
   $listobject->querystring .= " ORDER BY last_updated DESC ";
   //error_log($listobject->querystring);
   //$statusHTML .= $listobject->querystring . "<br>" . print_r($formValues,1) . "<br>";
   
   $listobject->performQuery();
   $listobject->show = 0;
   $listobject->showList();
   $now = date('r');
   $status_mesg = $listobject->outstring;
   $statusHTML .= "Current System Status: $now<br>$status_mesg";
   
   $objResponse->assign("status_bar","innerHTML",$statusHTML);
   return $objResponse;
}

function showRecentStatus($formValues, $mins = -1) {
   global $listobject, $userid, $scenarioid;
   $objResponse = new xajaxResponse();
   $statusHTML = '';
   $eid = $formValues['elementid'];
   $listobject->querystring = " select a.runid, a.status_flag, a.status_mesg, a.last_updated, a.element_key ";
   $listobject->querystring .= " from system_status as a, (";
   $listobject->querystring .= "    select runid, max(last_updated) as last_updated ";
   $listobject->querystring .= "    from system_status where element_key = $eid ";
   $listobject->querystring .= "    group by runid ";
   $listobject->querystring .= " ) as b ";
   $listobject->querystring .= " where a.element_key = $eid ";
   $listobject->querystring .= " and a.last_updated = b.last_updated ";
   if ($mins > 0) {
      $listobject->querystring .= " and a.last_updated >= now() - interval '$mins minutes' ";
   }
   $listobject->querystring .= " ORDER BY a.last_updated DESC ";
   //error_log($listobject->querystring);
   //$statusHTML .= $listobject->querystring . "<br>";
   //$statusHTML .= print_r($formValues,1) . "<br>";
   
   $listobject->performQuery();
   $listobject->show = 0;
   $listobject->showList();
   $now = date('r');
   $status_mesg = $listobject->outstring;
   $statusHTML .= "Current System Status: $now<br>$status_mesg";
   
   $objResponse->assign("status_bar","innerHTML",$statusHTML);
   return $objResponse;
}

function showLocationSelector($formValues) {
   global $baseurl;
   $latdd = $formValues['wd_lat'];
   $londd = $formValues['wd_lon'];
   $url = 'http://' . $_SERVER['HTTP_HOST'] . "/$baseurl/" . "nhd_tools/cova_locator.php" . "?latdd=$latdd&londd=$londd";
   //$innerHTML = $url . "<br>";
   $innerHTML .= file_get_contents($url);
   $objResponse = new xajaxResponse();
   $objResponse->assign("cova_location_selector","innerHTML",$innerHTML);
   return $objResponse;
}

?>
