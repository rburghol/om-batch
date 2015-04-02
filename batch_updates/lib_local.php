<?php


#########################################
# BEGIN - Menu Scripts
#########################################

function formatMenuLink($thislink) {
   $name = $thislink['name'];
   $label = $thislink['label'];
   $info = $thislink['info'];
   $onClick = $thislink['onClick'];
   $iconHTML = '';
   if (isset($thislink['icon'])) {
      $icon = $thislink['icon'];
      $label = "<img src='$icon'> ";
   }
   $href = "<a id=\"$name\" class=\"mE\" title=\"$info\" onClick=\"$onClick\" border=1>$label</a>";
   return $href;
}
         
function formatMenuObject($thisobject, $vis_allobjects) {
   # $vis_allobjects - whether the menu is open or closed by default
   $name = $thisobject['name'];
   $label = $thisobject['label'];
   $info = $thisobject['info'];
   $links = $thisobject['links'];
   $children = $thisobject['children'];
   $icon = $thisobject['icon'];
   $id = $thisobject['id'];
   if (!isset($thisobject['onClick'])) {
      $onClick = $thisobject['links']['edit']['onClick'];
   }else {
      $onClick = $thisobject['onClick'];
   }
   if (!isset($thisobject['onDblclick'])) {
      $onDblclick = $thisobject['links']['edit']['onDblclick'];
   }else {
      $onDblclick = $thisobject['onDblclick'];
   }
   
   $innerHTML = '';
   $iconHTML = '[Icon]';
   if (strlen($icon) > 0) {
      $iconHTML = "<img src='$icon'>";
   }
   if (isset($thisobject['button'])) {
      $buttonHTML = $thisobject['button'] . '&nbsp;';
   } else {
      $buttonHTML = '';
   }
   $innerHTML .= "<div id=\"unit_$id\" style=\"width: 360px;float: left;\">";
   if (count($children) > 0) {
      // include a toggle button if this has children
      $innerHTML .= "<span id=\"togbut$id\" onclick=\"toggleMenu('$id'); toggle_button('togbut$id');\" class=\"mHier\">&#9658;</span>";
   } else {
      $innerHTML .= "<span class=\"mHier\">&nbsp;&nbsp;&nbsp;&nbsp;</span>";
   }
   
   $innerHTML .= "$buttonHTML<a class=\"mHier\" id=\"toggle$id\" onclick=\"$onClick ;\" onDblclick=\"$onDblclick ;\" title=\"$info\"><b> $iconHTML $label </b></a>";
   
   foreach ($links as $thislink) {
      $innerHTML .= " " . formatMenuLink($thislink);
   }
   $innerHTML .= "</div>";
   $innerHTML .= "\n<div id=\"$id\"  style=\"display: $vis_allobjects;\" class=\"mC\" >";
   if (count($children) > 0) {
      $innerHTML .= "<ul class=\"mHier\">";
      foreach ($children as $thischild) {
         $innerHTML .= '<li class="mHier">' . formatMenuObject($thischild, $vis_allobjects) . " ";
      }
      $innerHTML .= "</ul>";
   }
   $innerHTML .= "</div>";
   
   return $innerHTML;
}
         
function formatHierarchicalSelect($fieldname, $thisobject, $vis_allobjects) {
   # $vis_allobjects - whether the menu is open or closed by default
   $name = $thisobject['name'];
   $label = $thisobject['label'];
   $info = $thisobject['info'];
   $links = $thisobject['links'];
   $children = $thisobject['children'];
   $icon = $thisobject['icon'];
   $id = $thisobject['id'];
   
   $innerHTML = '';
   $iconHTML = '[Icon]';
   if (strlen($icon) > 0) {
      $iconHTML = "<img src='$icon'>";
   }

   $innerHTML .= "\n<div style=\"width: 360px;float: left;\">";
   $innerHTML .= "<a class=\"mHier\" id=\"toggle$id\" ";
   $innerHTML .= "onclick=\"toggleMenu('$id')\" title=\"$info\"><b>$iconHTML $label </b></a>";
   
   foreach ($links as $thislink) {
      $innerHTML .= " " . formatMenuLink($thislink);
   }
   $innerHTML .= "</div>";
   $innerHTML .= "\n<div id=\"$id\"  style=\"display: $vis_allobjects;\" class=\"mC\" >";
   if (count($children) > 0) {
      $innerHTML .= "<ul>";
      foreach ($children as $thischild) {
         $innerHTML .= '<li>' . showRadioButton($fieldname, $name, $fieldvalue, $onclick='', $silent=0, $disabled=0) . formatHierarchicalSelect($thischild, $vis_allobjects) . " ";
      }
      $innerHTML .= "</ul>";
   }
   $innerHTML .= "</div>";
   
   return $innerHTML;
}

function getChildInfo($elementid, $listobject, $formname, $view='edit', $fieldname='select') {
   global $icons;
   
   $listobject->querystring = "  select a.elementid, a.scenarioid, a.elemname, a.objectclass ";
   $listobject->querystring .= " from scen_model_element as a, map_model_linkages as b ";
   $listobject->querystring .= " where a.elementid = b.src_id ";
   $listobject->querystring .= "    and b.linktype = 1 ";
   $listobject->querystring .= "    and b.dest_id = $elementid ";
   $listobject->querystring .= " group by a.elementid, a.scenarioid, a.elemname, a.objectclass ";
   $listobject->querystring .= " order by a.elemname ";
   $listobject->performQuery();
   $obrecs = $listobject->queryrecords;
   $levelobjects = array();
   $qs = "$listobject->querystring ; <br>";
   #error_log($qs);
   foreach ($obrecs as $thisrec) {
      $branchid = $thisrec['elementid'];
      $name = $thisrec['elemname'];
      $scid = $thisrec['scenarioid'];
      $icon = $icons['default']; # unless we have one set below
      if (isset($thisrec['objectclass'])) {
         if (in_array($thisrec['objectclass'], array_keys($icons)) ) {
            $icon = $icons[$thisrec['objectclass']];
         }
      }
      
      switch ($view) {
         case 'edit':
            $ob = model_menuEditItem($listobject, $formname, $elementid, $branchid, $name, $scid, $icon, $view);
         break;
         
         case 'select':
            $ob = model_menuSelectItem($listobject, $formname, $fieldname, $elementid, $branchid, $name, $scid, $icon, $view);
         break;
         
         case 'multiselect':
            $ob = model_menuMultiSelectItem($listobject, $formname, $fieldname, $elementid, $branchid, $name, $scid, $icon, $view);
         break;
      }

      array_push($levelobjects, $ob);
   }
   return $levelobjects;
}

function model_menuEditItem($listobject, $formname, $elementid, $branchid, $name, $scid, $icon, $view) {
   global $icons;
   $clickscript = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$branchid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$elementid; document.forms['$formname'].elements.scenarioid.value=$scid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
   // this is identical to single-click, except that it sets the current object to the the active container, as well as the 
   // item to be edited
   $dbl_clickscript = "last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$branchid;  document.forms['$formname'].elements.actiontype.value='edit'; document.forms['$formname'].elements.activecontainerid.value=$branchid; document.forms['$formname'].elements.scenarioid.value=$scid; xajax_showModelDesktopView(xajax.getFormValues('$formname')); ";
   $ob = array(
      'name'=>$branchid,
      'id'=>'ob' . $branchid,
      'label'=>$name,
      'icon'=>$icon,
      'parent'=>$elementid,
      'info'=>$name . ' (click to Expand/Hide)',
      'onClick'=>$clickscript,
      'onDblclick'=>$dbl_clickscript,
      'links'=>array(
         'edit'=>array(
            'name'=>'Edit',
            'label'=>'Edit',
            'icon'=>$icons['edit'],
            'onClick'=>$clickscript,
            'onDblclick'=>$dbl_clickscript,
            'info'=>'Click to Edit this Element'
         ),
         'trash'=>array(
            'name'=>'Trash',
            'label'=>'Trash',
            'icon'=>$icons['trash'],
            'onClick'=>"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0';  show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$branchid;  document.forms['$formname'].elements.scenarioid.value=$scid; confirmDeleteElement('$name');", 
            'info'=>'Delete this object and any children objects.'
         ),
         'clone'=>array(
            'name'=>'Clone',
            'label'=>'Clone',
            'icon'=>$icons['clone'],
            'onClick'=>"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$branchid; document.forms['$formname'].elements.actiontype.value='edit'; xajax_insertComponentClone(xajax.getFormValues('$formname')); ", 
            'info'=>'Cick here to add an exact copy of this element to the active model.'
         )
      )
   );
   $children = getChildInfo($branchid, $listobject, $formname, $view);
   $ob['children'] = $children;
   if (count($ob['children']) > 0) {
      # add a deep cloning icon
      $deepclone = array(
         'name'=>'Deep Clone',
         'label'=>'Deep Clone',
         'icon'=>$icons['deepclone'],
         'onClick'=>"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0';  show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['$formname'].elements.elementid.value=$branchid;  document.forms['$formname'].elements.scenarioid.value=$scid; document.forms['$formname'].elements.actiontype.value='deepclone'; xajax_insertGroupClone(xajax.getFormValues('$formname')); ", 
         'info'=>'Clone Component and All Children'
      );
      $ob['links']['deepclone'] = $deepclone;
   } 
   return $ob;
}

function model_menuSelectItem($listobject, $formname, $fieldname, $elementid, $branchid, $name, $scid, $icon, $view) {
   global $icons;
   $sid = $formname . '_' . $fieldname;
   $id = 'ob' . '_' . $formname . '_' . $fieldname . $branchid;
   $clickscript = "";
   $ob = array(
      'name'=>$branchid,
      'id'=>$id,
      'label'=>$name,
      'icon'=>$icon,
      'parent'=>$elementid,
      'button'=>showRadioButton($fieldname, $branchid, '', '', 1, 0, $sid),
      'info'=>$name,
      'onClick'=>$clickscript
   );
   $children = getChildInfo($branchid, $listobject, $formname, $view, $fieldname);
   $ob['children'] = $children;
   return $ob;
}

function model_menuMultiSelectItem($listobject, $formname, $fieldname, $elementid, $branchid, $name, $scid, $icon, $view) {
   global $icons;
   $sid = $formname . '_' . $fieldname;
   $id = 'ob' . '_' . $formname . '_' . $fieldname . $branchid;
   $clickscript = "";
   $ob = array(
      'name'=>$branchid,
      'id'=>$id,
      'label'=>$name,
      'icon'=>$icon,
      'parent'=>$elementid,
      'button'=>showCheckBox($fieldname . '[' . $branchid . ']', $branchid, '', '', 1, 0),
      'info'=>$name,
      'onClick'=>$clickscript
   );
   $children = getChildInfo($branchid, $listobject, $formname, $view, $fieldname);
   $ob['children'] = $children;
   return $ob;
}

function getScenarioRoots($listobject, $scenid, $scenarioname, $formname, $view, $fieldname='') {
   global $icons;
   $thismenu = array(
      'name'=>$scenarioname,
      'id'=>$formname . '_sc' . $scenid,
      'scenid'=>$scenid,
      'label'=>$scenarioname,
      'icon'=>$icons['domain'],
      'info'=>'Model Domain (click to Expand/Hide)',
      'links'=>array(),
      'children'=>array()
   );
   
   switch ($view) {
      case 'edit':
      
         $thismenu['links'] = array(
            'edit'=>array(
               'name'=>'Edit',
               'label'=>'Edit',
               'icon'=>$icons['edit'],
               'onClick'=>"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['elementtree'].elements.scenarioid.value=$scenid; document.forms['elementtree'].elements.actiontype.value='edit';  document.forms['elementtree'].elements.activecontainerid.value=-1;  document.forms['elementtree'].elements.elementid.value=-1;  xajax_showScenarioEditor(xajax.getFormValues('elementtree')); ",
               'info'=>'Click to Edit this Element'
            )
         );
      break;
   }
   
   $listobject->querystring = "  select elementid, elemname, objectclass ";
   $listobject->querystring .= " from (select a.elementid, a.elemname, a.objectclass, b.dest_id  ";
   $listobject->querystring .= "       from scen_model_element as a  ";
   $listobject->querystring .= "       left outer join map_model_linkages as b  ";
   $listobject->querystring .= "          on (b.src_id = a.elementid and b.linktype = 1)  ";
   $listobject->querystring .= "       where a.scenarioid = $scenid ";
   $listobject->querystring .= " ) as foo ";
   $listobject->querystring .= " where dest_id is null ";
   $listobject->performQuery();
   $roots = $listobject->queryrecords;
   foreach ($roots as $branch) {
      $branchid = $branch['elementid'];
      $elementid = $branch['elementid'];
      $name = $branch['elemname'];
      $icon = $icons['default']; # unless we have one set below
      if (isset($branch['objectclass'])) {
         if (in_array($branch['objectclass'], array_keys($icons)) ) {
            $icon = $icons[$branch['objectclass']];
         }
      }
      //$thisbranch = getChildInfo($elementid, $listobject, $formname, 'edit');
      switch ($view) {
         case 'edit':
            $thisbranch = model_menuEditItem($listobject, $formname, $elementid, $branchid, $name, $scenid, $icon, $view);
         break;
         
         case 'select':
            $thisbranch = model_menuSelectItem($listobject, $formname, $fieldname, $elementid, $branchid, $name, $scenid, $icon, $view);
         break;
         
         case 'multiselect':
            $thisbranch = model_menuMultiSelectItem($listobject, $formname, $fieldname, $elementid, $branchid, $name, $scenid, $icon, $view);
         break;
         
      }
      array_push($thismenu['children'], $thisbranch);
   }
   return $thismenu;
}
         
function showHierarchicalMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug, $full_menu=1, $activecontainer = -1, $formValues = array()) {
   global $listobject, $projectid, $userid, $usergroupids, $debug, $iconurl, $icons;

   $menuobjects = array();
   $formname = 'elementtree';
   $view = 'edit';
   
   $menuHTML = '';
   # get base model domains, then later we will get the Model Containers from them
   $listobject->querystring = "  select scenarioid, scenario from scenario ";
   $listobject->querystring .= "    where projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
   $listobject->querystring .= "       or ( groupid in ($usergroupids) and gperms >= 4 ) ";
   $listobject->querystring .= "       or (pperms >= 4) ) ";
   if (!$full_menu) {
      // screen only for the desired scenarioid
      $listobject->querystring .= "    and ( (scenarioid = $scenarioid) or ($scenarioid = -1) ) ";
   }
   $listobject->querystring .= " order by scenario  ";
   if ($debug) {
      $menuHTML .= "$listobject->querystring <br>";
   }
   $listobject->performQuery();

   $scenrecs = $listobject->queryrecords;

   foreach ($scenrecs as $thisscen) {
      $thismenu = getScenarioRoots($listobject, $thisscen['scenarioid'],$thisscen['scenario'], $formname, $view);
      array_push($menuobjects, $thismenu);
   }

   
   if ($full_menu) {
      $menuHTML .= "<form name='elementtree' id='elementtree'>";
      $menuHTML .= showHiddenField('actiontype', 'editelement', 1);
      $menuHTML .= showHiddenField('projectid', $projectid, 1, 'projectid');
      $menuHTML .= showHiddenField('scenarioid', $scenarioid, 1, 'scenarioid');
      $menuHTML .= showHiddenField('activecontainerid', $activecontainer, 1, 'activecontainerid');
      $menuHTML .= showHiddenField('elementid', '', 1, 'elementid');
      $menuHTML .= showHiddenField('newcomponenttype', '', 1, 'newcomponenttype'); 
      $menuHTML .= showHiddenField('vis_allobjects', $vis_allobjects, 1, 'vis_allobjects');
   }
   $delim = '';

// comment this out to not show the hierarchical browser, but not break 
// other things

   foreach ($menuobjects as $thisobject) {
      $scid = $thisobject['scenid'];
      $checkname = "checkshow" . $scid;
      if (isset($formValues[$checkname])) {
         $entry = formatMenuObject($thisobject, 0);
         $checkvalue = $formValues[$checkname];
      } else {
         $entry = $thisobject['label'];
         $checkvalue = '';
      }
      $checkbox = showCheckBox($checkname, 1, $checkvalue, "xajax_refreshHierarchicalMenu(xajax.getFormValues('elementtree'));", 1, 0);
      $menuHTML .= $delim . "<span id=\"checkspan$scid\">" . $checkbox . $entry . "</span>";
      //$menuHTML .= $delim . $checkbox . $entry . "</span>";
      $delim = '<br>';
   }


   $menuHTML .= "</form>";
   return $menuHTML;
}
         
function showHierarchicalSelectMenu($listobject, $view, $formname, $fieldname, $projectid, $scenarioid, $userid, $usergroupids, $debug) {
   global $iconurl, $icons;

   $menuHTML = '';
   $menuobjects = array();
   
   # get base model domains, then later we will get the Model Containers from them
   $listobject->querystring = "  select scenarioid, scenario from scenario ";
   $listobject->querystring .= "    where projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
   $listobject->querystring .= "       or ( groupid in ($usergroupids) and gperms >= 4 ) ";
   $listobject->querystring .= "       or (pperms >= 4) ) ";
   $listobject->querystring .= "       and ( (scenarioid = $scenarioid) or ($scenarioid = -1) ) ";
   $listobject->querystring .= " order by scenario  " ;
   if ($debug) {
      $menuHTML .= "$listobject->querystring <br>";
   }
   $listobject->performQuery();

   $scenrecs = $listobject->queryrecords;

   foreach ($scenrecs as $thisscen) {
      $thismenu = getScenarioRoots($listobject, $thisscen['scenarioid'],$thisscen['scenario'], $formname, $view, $fieldname);
      array_push($menuobjects, $thismenu);
   }

   $delim = '';
   foreach ($menuobjects as $thisobject) {
      $menuHTML .= $delim . formatMenuObject($thisobject, 0);
      $delim = '<br>';
   }
   return $menuHTML;
}
         
function showSystemToolsMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug) {
   global $listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug, $iconurl, $icons;
   $menu_names = array(
      "Model Workspace"=>"xajax_showModelDesktopView(xajax.getFormValues('elementtree'));",
      "Object Copier"=>"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); xajax_showCopyModelGroupForm2(xajax.getFormValues('elementtree'))",
      "Object Search"=>"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); xajax_showModelSearchForm(xajax.getFormValues('elementtree'))",
      "Domain Manager"=>"alert('This function is not yet enabled.')",
      "Group Manager"=>"alert('This function is not yet enabled.')",
      "Preferences"=>"alert('This function is not yet enabled.')"
   );
   $sysid = 0;
   $delim = '';
   foreach ($menu_names as $thisname=>$thisfunction) {
      $thismenu = array(
         'name'=>$thisname,
         'id'=>'systool' . $sysid,
         'label'=>$thisname,
         'icon'=>$icons['tools'],
         'info'=>$thisname,
         'links'=>array(
            'edit'=>array(
               'name'=>'Edit',
               'label'=>'Edit',
               'icon'=>$icons['edit'],
               'onClick'=>$thisfunction,
               'info'=>"Click to Use $thisname"
            )
         ),
         'children'=>array()
      );
      //array_push($menu, $thismenu);
      $menuHTML .= $delim . formatMenuObject($thismenu, 0);
      $delim = '<br>';
      $sysid++;
   }
   
   return $menuHTML;
}
         
function showToolboxMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug) {
   global $listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug, $iconurl, $icons;
   
   $menuobjects = array();

   $menuHTML = '';
   # get base model domains, then later we will get the Model Containers from them
   $listobject->querystring = "  select * from wooomm_toolgroups ";
   $listobject->querystring .= " order by groupname  ";
   if ($debug) {
      $menuHTML .= "$listobject->querystring ;<br>";
   }
   $listobject->performQuery();

   $scenrecs = $listobject->queryrecords;

   foreach ($scenrecs as $thisscen) {
      $thismenu = array(
         'name' => $thisscen['groupname'],
         'id' => 'tool' . $thisscen['groupname'],
         'label' => $thisscen['groupname'],
         'icon' => "$iconurl/toolfolder.png",
         'info' => $thisscen['description'],
         'children' => array()
      );
      $tgroup = $thisscen['groupid'];
      $listobject->querystring = "  select name, classname, description ";
      $listobject->querystring .= " from who_xmlobjects ";
      $listobject->querystring .= " where type <> 2 ";
      $listobject->querystring .= "    and toolgroup = $tgroup";
      if ($debug) {
         $menuHTML .= "$listobject->querystring ;<br>";
      }
      $listobject->performQuery();
      foreach ($listobject->queryrecords as $thisrec) {
         $icon = $icons['default']; # unless we have one set below
         $classname = $thisrec['classname'];
         if (in_array($thisrec['classname'], array_keys($icons)) ) {
            $icon = $icons[$thisrec['classname']];
         }
         $tooltype = array(
            'name'=>$thisrec['name'],
            'label'=>$thisrec['name'],
            'icon'=>$icon,
            'info'=>$thisrec['description'],
            'links'=>array()
         );
         # add an insert icon
         $insert = array(
            'name'=>'Insert',
            'label'=>'Insert',
            'icon'=>$icons['add-sibling'],
            'onClick'=>"last_tab['model_element']='model_element_data0'; last_button['model_element']='model_element_0'; last_tab['modelout']='modelout_data0'; last_button['modelout']='modelout_0'; show_next('map_window_data0', 'map_window_0', 'map_window'); document.forms['elementtree'].elements.newcomponenttype.value='$classname';  document.forms['elementtree'].elements.actiontype.value='insert'; xajax_insertComponent(xajax.getFormValues('elementtree')); ", 
            'info'=>'Add Component to Current Model'
         );
         array_push($tooltype['links'],$insert);
         array_push($thismenu['children'],$tooltype);
      }
      array_push($menuobjects, $thismenu);
   }
   $delim = '';
   foreach ($menuobjects as $thisobject) {
      $menuHTML .= $delim . formatMenuObject($thisobject, 0);
      $delim = '<br>';
   }
   return $menuHTML;
}

##############################################
###     END - MEnu Scripts                 ###
##############################################

function createUSGSTimeSeries($wbname, $staid, $startdate, $enddate, $period, $rectype, $debug) {
   
   # part of modeling widgets, expects lib_hydrology,php, and lib_usgs.php to be included
   # creates a time series object, and returns it
   $flow2 = new timeSeriesInput;
   $flow2->init();
   $flow2->name = $wbname;
   $flow2->maxflow = 0;
   $dataitems = '00060,00010';
   $code_name = array('00060'=>'Qout', '00010'=>'Temp');
   
   if ($debug) {
      print("Obtaining Physical Data for station: $staid <br>");
   }
   $usgs_result = retrieveUSGSData($staid, $period, $debug, '', '', 3, '', '', '');
   $sitedata = $usgs_result['row_array'][0];
   #print_r($sitedata);
   $dav = $sitedata['drain_area_va'];
   #print("<br>Area = $dav<br>");
   $flow2->state['area'] = $dav;
   $flow2->ddnu = $sitedata['dd_nu'];
      
   # gets daily flow values for indicated period
   if ($debug) {
      print("Obtaining Flow Data for station: $staid $startdate to $enddate<br>");
   }
   $site_result = retrieveUSGSData($staid, $period, $debug, $startdate, $enddate, 1, '', 'rdb', $dataitems);
   $gagedata = $site_result['row_array'];
   $thisno = $gagedata[0]['site_no'];
   #print($site_result['uri'] . "<br>");
   foreach ($gagedata as $thisdata) {
      if ($debug) {
         print_r($thisdata);
      }
      $thisdate = new DateTime($thisdata['datetime']);
      $ts = $thisdate->format('r');
      $thisflag = '';
      # default to missing
      $thisval = 0.0;
      foreach (split(',', $dataitems) as $dataitem) {
         
         foreach (array_keys($thisdata) as $thiscol) {
            if (substr_count($thiscol, $dataitem)) {
               # this is a flow related column, check if it is a flag or data
               if (!substr_count($thiscol, 'cd')) {
                  # must be a valid value
                  if ($thisdata[$thiscol] <> '') {
                     $thisval = $thisdata[$thiscol];
                  } else {
                     $thisval = '0.0';
                  }
               }
            }
         }
         $dataname = $code_name[$dataitem];
         # multiply by area factor to adjust for area factor at inlet
         $flow2->addValue($ts, $dataname, floatval($thisval));
         if ($dataname == 'Qout') {
            # add a watershed inch conversion if area > 0.0
            if ($dav > 0) {
               $flow2->addValue($ts, 'Qinches', (floatval($thisval) * 0.9917 * 0.0015625 * (1.0/$dav) * 24.0) );
            }
            if ($thisval > $flow2->maxflow) {
               $flow2->maxflow = $thisval;
            }
         }
      }
      $flow2->addValue($ts, 'timestamp', $ts);
      $flow2->addValue($ts, 'thisdate', $thisdate->format('m-d-Y'));
   }
   
   return $flow2;
}


function createFlowZoneGraph($goutdir, $gouturl, $dr_zones, $staid, $days, $overwrite, $debug) {

   if ($debug) {
      print("Creating Flow Zone Graph.<br>");
   }
   $outarr = array();
 
   $thisdate = date('Y-m-d');
   $fname = "fzone_$staid-$days.png";
   $floc = $goutdir . "/fzone_$staid-$days.png";
   $flog = $goutdir . "/fzone_$staid-$days.log";
   $furl = $gouturl . "/fzone_$staid-$days.png";
   if (!$overwrite) {
      # check for existing graph file, if it exists, we just return its URL
      if (file_exists($floc)) {
         # get station info anyhow, even though we have the flow and image already
         $usgs_result = retrieveUSGSData($staid, $period, $debug, '', '', 3, '', '', '');
         $sitedata = $usgs_result['row_array'][0];
         #print_r($sitedata);
         $dav = $sitedata['drain_area_va'];
         $outarr['imageurl'] = $furl;
         $outarr['data'] = readDelimitedFile($flog, ',', 1);
         $outarr['area'] = $dav;
         return $outarr;
      }
   }
   
   $calibflow = createUSGSTimeSeries($staid, $staid, '', '', $days, 1, $debug);
   $calibflow->outdir = $goutdir;
   $calibflow->logfile = "fzone_$staid.$thisdate-$days.log";
   #$calibflow->debug = 1;
   $calibflow->tsvalues2file();
   $flows = $calibflow->tsvalues;
   $flow = array();

   foreach($flows as $thisflow) {
      array_push($flow, $thisflow['Qout']);
   }
   $m = max($flow) * 1.5;
   if ($m <= 100) {
      $m = 150;
   }
   #print_r($flows);

   $zones = array(
      array('name'=>'Normal', 'color'=>'green', 'value'=>$m),
      array('name'=>'Watch', 'color'=>'yellow', 'value'=>$dr_zones['Watch'][$staid]),
      array('name'=>'Warning', 'color'=>'orange', 'value'=>$dr_zones['Warning'][$staid]),
      array('name'=>'Emergency', 'color'=>'red', 'value'=>$dr_zones['Emergency'][$staid])
   );
   
   $graphs = array();

   foreach($flows as $thisflow) {
      # construct zonal curves
      $thisdate = $thisflow['thisdate'];
      $i = 0;

      foreach($zones as $thiszone) {
         if (!isset($graphs['bargraphs'][$i]['graphrecs'])) {
            $graphs['bargraphs'][$i]['graphrecs'] = array();
         }
         array_push($graphs['bargraphs'][$i]['graphrecs'], array('thisdate'=>$thisdate, 'flow'=>$thiszone['value']) );
         $graphs['bargraphs'][$i]['xcol'] = 'thisdate';
         $graphs['bargraphs'][$i]['ycol'] = 'flow';
         $graphs['bargraphs'][$i]['color'] = $thiszone['color'];
         $graphs['bargraphs'][$i]['fillcolor'] = $thiszone['color'];
         $graphs['bargraphs'][$i]['ylegend'] = $thiszone['name'];
         $i++;
      }
   }
   $graphs['title'] = $staid . ': Mean Daily Flow - Last ' . $days . ' Days';
   $graphs['labelangle'] = 90;
   $graphs['xlabel'] = '';
   $graphs['gwidth'] = 360;
   $graphs['gheight'] = 270;
   $graphs['color'] = 'green';
   $graphs['filename'] = $fname;
   $graphs['bargraphs'][$i]['mark'] = MARK_UTRIANGLE;
   $graphs['bargraphs'][$i]['graphrecs'] = $flows;
   $graphs['bargraphs'][$i]['xcol'] = 'thisdate';
   $graphs['bargraphs'][$i]['ycol'] = 'Qout';
   $graphs['bargraphs'][$i]['color'] = 'black';
   $graphs['bargraphs'][$i]['ylegend'] = 'Flow';
   #print_r($graphs);

   $thisimg = showGenericMultiLine($goutdir, $gouturl, $graphs, $debug);
   #print("$thisimg returned.<br>");
   $outarr['imageurl'] = $thisimg;
   $outarr['data'] = $flows;
   $outarr['area'] = $calibflow->state['area'];
   return $outarr;
}



function createPctFlowZoneGraph($listobject, $goutdir, $gouturl, $staid, $days, $overwrite, $debug) {

   if ($debug) {
      print("Creating Flow Zone Graph.<br>");
   }
   $outarr = array();
 
   $thisdate = date('Y-m-d');
   $fname = "fzone_$staid.$thisdate-$days.png";
   $floc = $goutdir . "/fzone_$staid.$thisdate-$days.png";
   $flog = $goutdir . "/fzone_$staid.$thisdate-$days.log";
   $furl = $gouturl . "/fzone_$staid.$thisdate-$days.png";
   if (!$overwrite) {
      # check for existing graph file, if it exists, we just return its URL
      if (file_exists($floc)) {
         # get station info anyhow, even though we have the flow and image already
         $usgs_result = retrieveUSGSData($staid, $period, $debug, '', '', 3, '', '', '');
         $sitedata = $usgs_result['row_array'][0];
         #print_r($sitedata);
         $dav = $sitedata['drain_area_va'];
         $outarr['imageurl'] = $furl;
         $outarr['data'] = readDelimitedFile($flog, ',', 1);
         $outarr['area'] = $dav;
         return $outarr;
      }
   }
   
   $calibflow = createUSGSTimeSeries($staid, $staid, '', '', $days, 1, $debug);
   $calibflow->outdir = $goutdir;
   $calibflow->logfile = "fzone_$staid.$thisdate-$days.log";
   #$calibflow->debug = 1;
   $calibflow->tsvalues2file();
   $flows = $calibflow->tsvalues;
   $flow = array();

   foreach($flows as $thisflow) {
      array_push($flow, $thisflow['Qout']);
   }
   $m = max($flow) * 1.5;
   if ($m <= 100) {
      $m = 150;
   }
   #print_r($flows);

   # retrieve these values from USGS
   $ddnu = $calibflow->ddnu;
   $site_result = retrieveUSGSData($staid, '', 0, '', '', 2, '', '', '00060', 'va', $siteid, $ddnu);
   $gagedata = $site_result['row_array'];

   $numstats = count($gagedata);

   if ($numstats == 0) {
      # try ddnu = 2
      $ddnu = 2;
      $site_result = retrieveUSGSData($siteno, '', 0, '', '', 2, '', '', '00060', 'va', $siteid, $ddnu);
      $gagedata = $site_result['row_array'];
      $numstats = count($gagedata);
   }

   foreach($flows as $thisflow) {
      # construct zonal curves
      $thisdate = $thisflow['thisdate'];
      $i = 0;

      foreach($zones as $thiszone) {
         if (!isset($graphs['bargraphs'][$i]['graphrecs'])) {
            $graphs['bargraphs'][$i]['graphrecs'] = array();
         }
         array_push($graphs['bargraphs'][$i]['graphrecs'], array('thisdate'=>$thisdate, 'flow'=>$thiszone['value']) );
         $graphs['bargraphs'][$i]['xcol'] = 'thisdate';
         $graphs['bargraphs'][$i]['ycol'] = 'flow';
         $graphs['bargraphs'][$i]['color'] = $thiszone['color'];
         $graphs['bargraphs'][$i]['fillcolor'] = $thiszone['color'];
         $graphs['bargraphs'][$i]['ylegend'] = $thiszone['name'];
         $i++;
      }
   }
   $graphs['title'] = $staid . ': Mean Daily Flow - Last ' . $days . ' Days';
   $graphs['labelangle'] = 90;
   $graphs['xlabel'] = '';
   $graphs['gwidth'] = 360;
   $graphs['gheight'] = 270;
   $graphs['color'] = 'green';
   $graphs['filename'] = $fname;
   $graphs['bargraphs'][$i]['mark'] = MARK_UTRIANGLE;
   $graphs['bargraphs'][$i]['graphrecs'] = $flows;
   $graphs['bargraphs'][$i]['xcol'] = 'thisdate';
   $graphs['bargraphs'][$i]['ycol'] = 'Qout';
   $graphs['bargraphs'][$i]['color'] = 'black';
   $graphs['bargraphs'][$i]['ylegend'] = 'Flow';
   #print_r($graphs);

   $thisimg = showGenericMultiLine($goutdir, $gouturl, $graphs, $debug);
   #print("$thisimg returned.<br>");
   $outarr['imageurl'] = $thisimg;
   $outarr['data'] = $flows;
   $outarr['area'] = $calibflow->state['area'];
   return $outarr;
}

function createSyntheticFlowFromUSGS($sourcegages, $rectype, $wsarea, $startdate, $enddate, $tstep, $weightmethod, $debug) {
   
   # expects the lib_usgs.php, and lib_hydrology.php
   # $sourcegages - csv list of USGS gage IDs
   # $rectype - 0 - realtime value source data, 1 - daily value
   # $wsarea - the area of the watershed to estimate flow for
   # $startdate, $enddate - range desired, in the format 'YYY-MM-DD'
   
   # returns a model object with the completed flow simulation
   # $tstep - time step of resulting data set in hours (tstep may be less than the source data time step, linear interpolation will take place)
   $innerHTML = '';

   $timer = new simTimer;
   $timer->dt = $tstep * 3600.0;
   $timer->setTime($startdate, $enddate);
   $su = $timer->thistime->format('U');
   $eu = $timer->endtime->format('U');
   $total_days = ($eu - $su) / (3600.0 * 24.0);
   $timestep_hrs = $tstep;
   $timestep_sec = 3600 * $timestep_hrs;
   $numsteps = $total_days * 24.0 / $timestep_hrs;
   $innerHTML .= "Total Steps: $numsteps <br>";
   
   
   $r2 = new flowTransformer;
   $r2->name = 'Simulated HUC';
   $r2->timer = $timer;
   $r2->debug = $debug;
   $r2->method = $weightmethod;
   $r2->state['area'] = $wsarea;

#$staid = $hucsites[0];
   $k = 0;
   $outarr = array();
   $outarr['inflows'] = array();
   foreach ($sourcegages as $staid) {
      # flow input - check area first
      $usgs_result = retrieveUSGSData($staid, '', $debug, '', '', 3, '', '', '');
      $sitedata = $usgs_result['row_array'][0];
      #print_r($sitedata);
      $dav = $sitedata['drain_area_va'];
      #print("<br>Area = $dav<br>");
      $innerHTML .= "Retrieving Flow Info for: $staid <br>";
      $innerHTML .= "Drainage area: $dav <br>";
      $flow[$k] = createUSGSTimeSeries($staid, $staid, $startdate, $enddate, '', $rectype, $debug);
      $flow[$k]->debug = 0;
      $flow[$k]->name = $staid;
      $flow[$k]->outdir = './out';
      $flow[$k]->logfile = $staid . '.log';
      $flcol = 'Qout';
      $flow[$k]->tsvalues2file();
      $flow[$k]->timer = $timer;
      $flow[$k]->extflag = 1; # do not extrapolate NULL values
      #$flow[$k]->debug = 0;
      #$flow[$k]->state['area'] = 1.0;
      $thisarea = $flow[$k]->state['area'];
      # set up flow per unit area (flowtransformer expects this input)
      $thisop = new Equation();
      $thisop->equation = "$flcol * area";
      $thisop->debug = 0;
      $thisop->init();
      $flow[$k]->addOperator('flowtimesa', $thisop , 0.0);
      # set up flow per unit area (flowtransformer expects this input)
      $thisop = new Equation();
      $thisop->equation = "$flcol / area";
      $thisop->debug = 0;
      $thisop->init();
      $flow[$k]->addOperator('flowpera', $thisop , 0.0);
      # create the "activearea" property
      # this causes the area to be set to NULL if the flow is NULL
      # when the flowtransformer gets a NULL input for one of its flows, it ignores that input
      $thisop = new Equation();
      $thisop->equation = "area + (0.0 * $flcol)";
      $thisop->debug = 0;
      $thisop->init();
      $flow[$k]->addOperator('activearea', $thisop , 0.0);
      $flow[$k]->orderOperations();
      $r2->addInput('flow', $flcol, $flow[$k]);
      $r2->addInput('flowpera', 'flowpera', $flow[$k]);
      $r2->addInput('activearea', 'activearea', $flow[$k]);
      array_push($outarr['inflows'], $flow[$k]);
      $k++;
      #print_r($flow[$k]->tsvalues);
   }
   
   $r2->loglist = array('time', 'Qout');
   $innerHTML .= "Executing step ";
   
   $i = 0;
   $outint = 100;
   while (!$timer->finished) {
      for ($d = 0; $d < count($flow); $d++) {
         $flow[$d]->step();
      }
      $r2->step();
      $timer->step();
      #print_r($r2->state);
      if ( intval($i/$outint) == ($i / $outint) ) {
         $innerHTML .= " ... $i / $numsteps ";
      }
      $i++;
   }
   $innerHTML .= "<br>Finished.";
   $outarr['robject'] = $r2;
   $outarr['innerHTML'] = $innerHTML;
   
   return $outarr;
}


function getCBPTotalContribArea($listobject, $tablename, $colname, $areacolname, $gcolname, $srid, $cfact, $segid, $debug) {
   
   $listobject->querystring = "  select split_part($colname, '_', 2) as segid, split_part($colname, '_', 3) as dsegid ";
   $listobject->querystring .= " from $tablename  ";
   $listobject->querystring .= " where split_part($colname, '_', 3) = '$segid' ";
   if ($debug) {
      print("$listobject->querystring ; <br>");
   }
   $listobject->performQuery();
   $qrecs = $listobject->queryrecords;
   $area = 0;
   $listobject->querystring = " select $cfact * area2d(transform($gcolname,$srid)) as localarea from $tablename where split_part($colname, '_', 2) = '$segid' ";
   if ($debug) {
      print("$listobject->querystring ; <br>");
   }
   $listobject->performQuery();
   $area = $listobject->getRecordValue(1,'localarea');
   foreach ($qrecs as $thisrec) {
      $tribsegid = $thisrec['segid'];
      if ($debug) {
         print("Obtaining area for contributing watershed $tribsegid <br>");
      }
      if ($tribsegid <> $segid) {
         $area += getCBPTotalContribArea($listobject, $tablename, $colname, $areacolname, $gcolname, $srid, $cfact, $tribsegid, $debug);
      } else {
         print("$tribsegid = $segid - no further action needed.<br>");
      }
   }
   
   $listobject->querystring = " update $tablename set $areacolname = $area where split_part($colname, '_', 2) = '$segid' ";
   if ($debug) {
      print("$listobject->querystring ; <br>");
   }
   $listobject->performQuery();
   
   return $area;   
   
}



function getCBPLandSegmentLanduse($dbobj, $scenid, $landsegid, $debug, $rseg = NULL) {
   
   $seginfo = array();
   //$debug = 1;
   
   // total contributing area, sum up the areas, by year
   $basetable = "tmp_local$segid";
   $dbobj->querystring = "  create temp table $basetable as ";
   $dbobj->querystring .= " select extract(year from b.starttime) as thisyear, ";
   $dbobj->querystring .= " c.id4 as luname, c.id3 as landseg, ";
   $dbobj->querystring .= " sum(b.thisvalue) as thisvalue ";
   $dbobj->querystring .= " from cbp_scenario_param_temporal as b, cbp_model_location as c ";
   $dbobj->querystring .= " where c.location_id = b.location_id ";
   $dbobj->querystring .= " and c.id3 = '$landsegid' ";
   $dbobj->querystring .= " and c.id1 = 'lrseg' ";
   if (!($rseg === NULL)) {
      $dbobj->querystring .= " and c.id2 = '$rseg' ";
   }
   $dbobj->querystring .= " and c.scenarioid = $scenid ";
   $dbobj->querystring .= " and b.scenarioid = $scenid ";
   $dbobj->querystring .= " group by thisyear, luname, landseg ";
   $dbobj->querystring .= " order by thisyear, luname, landseg ";

   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   
   $groupcols = 'luname';
   $crosscol = 'thisyear';
   $valcol = 'thisvalue';

   $crosstab_query = doGenericCrossTab ($dbobj, $basetable, $groupcols, $crosscol, $valcol, 1, 0);
   
   $dbobj->querystring = $crosstab_query;
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   $seginfo['local_annual'] = $dbobj->queryrecords;
   // now clean up
   $dbobj->querystring = "  drop table $basetable ";
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   
   return $seginfo;
}

function getCBPSegmentLanduse($dbobj, $scenid, $segid, $tablename = 'sc_cbp5', $colname = 'catcode2') {
   
   $seginfo = array();
   //$debug = 1;
   
   $tribinfo = getCBPSegList($dbobj, $tablename, $colname, $segid, $debug, -1, array());
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $tribs = $tribinfo['segnames'];
   $in_tribs = "'" . join ("','", $tribs) . "'";
   $outlet = $tribinfo['outlet_name'];
   
   // total contributing area, sum up the areas, by year
   $basetable = "tmp_trib$segid";
   $dbobj->querystring = "  create temp table $basetable as ";
   $dbobj->querystring .= " select a.id4 as luname, extract(year from b.starttime) as thisyear, ";
   $dbobj->querystring .= "    sum(b.thisvalue) as thisvalue ";
   $dbobj->querystring .= " from cbp_model_location as a, cbp_scenario_param_temporal as b ";
   $dbobj->querystring .= " where a.id1 = 'lrseg' ";
   $dbobj->querystring .= " and a.scenarioid = '$scenid'";
   $dbobj->querystring .= " and a.id2 in ($in_tribs)";
   $dbobj->querystring .= " and a.location_id = b.location_id ";
   $dbobj->querystring .= " group by a.id4, b.starttime ";
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   
   $groupcols = 'luname';
   $crosscol = 'thisyear';
   $valcol = 'thisvalue';

   //error_log("$basetable_sql<br>");
   //error_log("$debug_str<br>");
   $crosstab_query = doGenericCrossTab ($dbobj, $basetable, $groupcols, $crosscol, $valcol, 1, 0);
   
   $dbobj->querystring = $crosstab_query;
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   $seginfo['contrib_annual'] = $dbobj->queryrecords;
   $dbobj->querystring = "drop table $basetable ";
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   
   // total local area, sum up the landuses, by year
   $basetable = "tmp_local$segid";
   $dbobj->querystring = "  create temp table $basetable as ";
   $dbobj->querystring .= " select a.id4 as luname, extract(year from b.starttime) as thisyear, ";
   $dbobj->querystring .= "    sum(b.thisvalue) as thisvalue ";
   $dbobj->querystring .= " from cbp_model_location as a, cbp_scenario_param_temporal as b ";
   $dbobj->querystring .= " where a.id1 = 'lrseg' ";
   $dbobj->querystring .= " and a.id2 = '$outlet'";
   $dbobj->querystring .= " and a.scenarioid = '$scenid'";
   $dbobj->querystring .= " and a.location_id = b.location_id ";
   $dbobj->querystring .= " group by a.id4, b.starttime ";
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   
   $groupcols = 'luname';
   $crosscol = 'thisyear';
   $valcol = 'thisvalue';

   $crosstab_query = doGenericCrossTab ($dbobj, $basetable, $groupcols, $crosscol, $valcol, 1, 0);
   
   $dbobj->querystring = $crosstab_query;
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   $seginfo['local_annual'] = $dbobj->queryrecords;
   // now clean up
   $dbobj->querystring = "  drop table $basetable ";
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   
   return $seginfo;
}

function getCBPLandSegments($dbobj, $scenid, $segid, $debug) {
   $seginfo = array();
   // now get local land use by contributing land segment
   $dbobj->querystring = "  select id3 from cbp_model_location ";
   $dbobj->querystring .= " where scenarioid = $scenid ";
   $dbobj->querystring .= "    and id1 = 'lrseg' ";
   $dbobj->querystring .= "    and id2 = '$segid' ";
   $dbobj->querystring .= " group by id3 ";
   $dbobj->querystring .= " order by id3 ";
   if ($debug) {
      $seginfo['debug'] .= "$dbobj->querystring <br>";
   }
   $dbobj->performQuery();
   $lsegs = $dbobj->queryrecords;
   foreach ($lsegs as $thislseg) {
      $lseg = $thislseg['id3'];
      $seginfo['local_landsegs'][] = $lseg;
   }
   return $seginfo;
}

function getCBPSegList($listobject, $tablename, $colname, $segid, $debug, $max_levels=-1, $criteria = array()) {
   # user passes uin tablename and colname just so this can be flexible if we should ever have a 
   # new table with the CBP data in it
   # if $max_levels = -1, then go all the way to the headwater, otherwise, keep a count of how many levels and 
   # do not iterate further than the desired upstream depth
   
   # returns the tributaries to this segment
   $trib_segs = array();
   $trib_segs['outlet'] = $segid;
   $trib_segs['segments'] = array($segid);
   $trib_segs['info'] = '';
   
   $critclause = "(1 = 1)";
   foreach ($criteria as $thiskey => $thisval) {
      $critclause .= " AND $thiskey = '$thisval'";
   }

   $listobject->querystring = "  select $colname as segname ";
   $listobject->querystring .= " from $tablename  ";
   $listobject->querystring .= " where split_part($colname, '_', 2) = '$segid' ";
   $listobject->querystring .= "    AND $critclause ";
   if ($debug) {
      $trib_segs['info'] .= "$listobject->querystring ; <br>";
   }
   $listobject->performQuery();
   $segname = $listobject->getRecordValue(1,'segname');
   $trib_segs['segnames'] = array($segname);
   $trib_segs['outlet_name'] = $segname;
   
   if ( ($max_levels > 0) or ($max_levels == -1) ) {
   
      $listobject->querystring = "  select split_part($colname, '_', 2) as segid, split_part($colname, '_', 3) as dsegid, ";
      $listobject->querystring .= " $colname as segname ";
      $listobject->querystring .= " from $tablename  ";
      $listobject->querystring .= " where split_part($colname, '_', 3) = '$segid' ";
      $listobject->querystring .= "    AND $critclause ";
      if ($debug) {
         $trib_segs['info'] .= "$listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $qrecs = $listobject->queryrecords;
      if ($max_levels > 0) {
         $max_levels = $max_levels - 1;
      }
      foreach ($qrecs as $thisrec) {
         $tribsegid = $thisrec['segid'];
         if ($debug) {
            //print("Obtaining area for contributing watershed $tribsegid <br>");
         }
         if ($tribsegid <> $segid) {
            $upstream_segs = getCBPSegList($listobject, $tablename, $colname, $tribsegid, $debug, $max_levels, $criteria);
            $tmp_segs = array_merge($trib_segs['segments'], $upstream_segs['segments']);
            $tmp_segnames = array_merge($trib_segs['segnames'], $upstream_segs['segnames']);
            $trib_segs['segments'] = $tmp_segs;
            $trib_segs['segnames'] = $tmp_segnames;
            $trib_segs['info'] .= $upstream_segs['info'];
         } else {
            $trib_segs['info'] .= "$segid is a headwater segment.<br>";
         }
      }
   }
   
   return $trib_segs;   
   
}


function getSegList($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $segid, $max_levels=-1, $criteria = array(), $debug) {
   # user passes uin tablename and colname just so this can be flexible if we should ever have a 
   # new table with the CBP data in it
   # if $max_levels = -1, then go all the way to the headwater, otherwise, keep a count of how many levels and 
   # do not iterate further than the desired upstream depth
   
   # returns the tributaries to this segment
   $trib_segs = array();
   $trib_segs['outlet'] = $segid;
   $trib_segs['segments'] = array($segid);
   $trib_segs['info'] = '';
   
   $critclause = "(1 = 1)";
   foreach ($criteria as $thiskey => $thisval) {
      $critclause .= " AND $thiskey = '$thisval'";
   }

   $listobject->querystring = "  select $name_col as segname ";
   $listobject->querystring .= " from $tablename  ";
   $listobject->querystring .= " where $fnode_col = '$segid' ";
   $listobject->querystring .= "    AND $critclause ";
   //if ($debug) {
      $trib_segs['info'] .= "$listobject->querystring ; <br>";
   //}
   $listobject->performQuery();
   $segname = $listobject->getRecordValue(1,'segname');
   $trib_segs['segnames'] = array($segname);
   
   if ( ($max_levels > 0) or ($max_levels == -1) ) {
   
      $listobject->querystring = "  select $fnode_col as segid, $tnode_col as dsegid, ";
      $listobject->querystring .= " $name_col as segname ";
      $listobject->querystring .= " from $tablename  ";
      $listobject->querystring .= " where $tnode_col = '$segid' ";
      $listobject->querystring .= "    AND $critclause ";
      //if ($debug) {
         $trib_segs['info'] .= "$listobject->querystring ; <br>";
      //}
      $listobject->performQuery();
      $qrecs = $listobject->queryrecords;
      if ($max_levels > 0) {
         $max_levels = $max_levels - 1;
      }
      foreach ($qrecs as $thisrec) {
         $tribsegid = $thisrec['segid'];
         if ($debug) {
            //print("Obtaining area for contributing watershed $tribsegid <br>");
         }
         if ($tribsegid <> $segid) {
            $upstream_segs = getSegList($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $tribsegid, $max_levels, $criteria, $debug);
            $tmp_segs = array_merge($trib_segs['segments'], $upstream_segs['segments']);
            $tmp_segnames = array_merge($trib_segs['segnames'], $upstream_segs['segnames']);
            $trib_segs['segments'] = $tmp_segs;
            $trib_segs['segnames'] = $tmp_segnames;
            $trib_segs['info'] .= $upstream_segs['info'];
         } else {
            $trib_segs['info'] .= "$segid is a headwater segment.<br>";
         }
      }
   }
   
   return $trib_segs;   
   
}

function getCBPTerminalNode($listobject, $tablename, $colname, $criteria = array(), $debug = 0) {
   # user passes uin tablename and colname just so this can be flexible if we should ever have a 
   # new table with the CBP data in it
   # criteria is an associative array to specify the grouping columns, such as watershed, minbas, majbas, rivername, etc.
   $terminal_nodes = array();
   $terminal_nodes['destinations'] =  array();
   $terminal_nodes['segments'] = array();
   $terminal_nodes['segnames'] = array();
   $whereclause = "(1 = 1)";
   foreach ($criteria as $thiskey => $thisval) {
      $whereclause .= " AND $thiskey = '$thisval'";
   }

   $listobject->querystring = "  select split_part($colname, '_', 2) as segid, split_part($colname, '_', 3) as dsegid, ";
   $listobject->querystring .= " $colname as segname ";
   $listobject->querystring .= " from $tablename  ";
   $listobject->querystring .= " where $whereclause ";
   $listobject->querystring .= "    AND split_part($colname, '_', 3) not in ";
   $listobject->querystring .= "    ( ";
   $listobject->querystring .= "       select split_part($colname, '_', 2) as destsegs ";
   $listobject->querystring .= "       from $tablename ";
   $listobject->querystring .= "       where $whereclause ";
   $listobject->querystring .= "       group by destsegs ";
   $listobject->querystring .= "    )";
   $listobject->querystring .= " GROUP BY segid, dsegid, segname ";
   if ($debug) {
      $terminal_nodes['info'] .= "$listobject->querystring ; <br>";
   }
   $listobject->performQuery();
   $qrecs = $listobject->queryrecords;
   foreach ($qrecs as $thisrec) {
      if (!in_array($thisrec['segid'], $terminal_nodes['segments'])) {
         array_push($terminal_nodes['segments'], $thisrec['segid']);
      }
      if (!in_array($thisrec['dsegid'], $terminal_nodes['destinations'])) {
         array_push($terminal_nodes['destinations'], $thisrec['dsegid']);
      }
      if (!in_array($thisrec['segname'], $terminal_nodes['segnames'])) {
         array_push($terminal_nodes['segnames'], $thisrec['segname']);
      }
   }
   
   return $terminal_nodes;   
   
}




function getTerminalNode($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $criteria = array(), $debug = 0) {
   # user passes uin tablename and colname just so this can be flexible if we should ever have a 
   # new table with the CBP data in it
   # criteria is an associative array to specify the grouping columns, such as watershed, minbas, majbas, rivername, etc.
   $terminal_nodes = array();
   $terminal_nodes['destinations'] =  array();
   $terminal_nodes['segments'] = array();
   $terminal_nodes['segnames'] = array();
   $whereclause = "(1 = 1)";
   foreach ($criteria as $thiskey => $thisval) {
      $whereclause .= " AND $thiskey = '$thisval'";
   }

   $listobject->querystring = "  select \"$fnode_col\" as segid, \"$tnode_col\" as dsegid, ";
   $listobject->querystring .= " \"$name_col\" as segname ";
   $listobject->querystring .= " from $tablename  ";
   $listobject->querystring .= " where $whereclause ";
   $listobject->querystring .= "    AND $tnode_col not in ";
   $listobject->querystring .= "    ( ";
   $listobject->querystring .= "       select $fnode_col as destsegs ";
   $listobject->querystring .= "       from $tablename ";
   $listobject->querystring .= "       where $whereclause ";
   $listobject->querystring .= "       group by destsegs ";
   $listobject->querystring .= "    )";
   $listobject->querystring .= " GROUP BY segid, dsegid, segname ";
   //if ($debug) {
      $terminal_nodes['info'] .= "$listobject->querystring ; <br>";
   //}
   $listobject->performQuery();
   $qrecs = $listobject->queryrecords;
   foreach ($qrecs as $thisrec) {
      if (!in_array($thisrec['segid'], $terminal_nodes['segments'])) {
         array_push($terminal_nodes['segments'], $thisrec['segid']);
      }
      if (!in_array($thisrec['dsegid'], $terminal_nodes['destinations'])) {
         array_push($terminal_nodes['destinations'], $thisrec['dsegid']);
      }
      if (!in_array($thisrec['segname'], $terminal_nodes['segnames'])) {
         array_push($terminal_nodes['segnames'], $thisrec['segname']);
      }
   }
   
   return $terminal_nodes;   
   
}


function getCBPBranch($listobject, $tablename, $colname, $criteria = array(), $debug = 0) {
   # user passes uin tablename and colname just so this can be flexible if we should ever have a 
   # new table with the CBP data in it
   # criteria is an associative array to specify the grouping columns, such as watershed, minbas, majbas, rivername, etc.
   $terminal_nodes = getCBPTerminalNode($listobject, $tablename, $colname, $criteria, $debug );
   $branch_nodes = array();
   $branch_nodes['segments'] = $terminal_nodes['segments'];
   $branch_nodes['info'] = $terminal_nodes['info'];
   
   foreach ($terminal_nodes['segments'] as $segid) {
      $branch_segs = getCBPSegList($listobject, $tablename, $colname, $segid, $debug, -1, $criteria);
      $branch_nodes['info'] .= $branch_segs['info'];
      foreach ($branch_segs['segments'] as $this_twig) {
         if (!in_array($this_twig, $branch_nodes['segments'])) {
            array_push($branch_nodes['segments'], $this_twig);
         }
      }
   }
   
   return $branch_nodes;   
   
}


function getBranchNodes($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $outlet_node = -1, $criteria = array(), $debug = 0) {
   # user passes uin tablename and colname just so this can be flexible if we should ever have a 
   # new table with the CBP data in it
   # criteria is an associative array to specify the grouping columns, such as watershed, minbas, majbas, rivername, etc.
   if ($outlet_node == -1) {
      $terminal_nodes = getTerminalNode($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $criteria, $debug );
   } else {
      $terminal_nodes = array('segments'=>array($outlet_node));
   }
   $branch_nodes = array();
   $branch_nodes['segments'] = $terminal_nodes['segments'];
   $branch_nodes['info'] = $terminal_nodes['info'];
   
   foreach ($terminal_nodes['segments'] as $segid) {
      $branch_segs = getSegList($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $segid, -1, $criteria, $debug);
      $branch_nodes['info'] .= $branch_segs['info'];
      //error_log("Branch node output: " . print_r($branch_nodes, 1));
      foreach ($branch_segs['segments'] as $this_twig) {
         if (!in_array($this_twig, $branch_nodes['segments'])) {
            array_push($branch_nodes['segments'], $this_twig);
         }
      }
   }
   
   return $branch_nodes;   
   
}



function getCBPBranchTribs($listobject, $tablename, $colname, $criteria = array(), $debug = 0) {
   # user passes uin tablename and colname just so this can be flexible if we should ever have a 
   # new table with the CBP data in it
   # criteria is an associative array to specify the grouping columns, such as watershed, minbas, majbas, rivername, etc.
   $terminal_nodes = getCBPTerminalNode($listobject, $tablename, $colname, $criteria, $debug );
   $branch_nodes = array();
   $trib_nodes = array('segments'=>array(), 'terminal_nodes'=>$terminal_nodes['segments']);
   $branch_nodes['terminal_nodes'] = $terminal_nodes['segments'];
   $branch_nodes['segments'] = $terminal_nodes['segments'];
   
   foreach ($terminal_nodes['segments'] as $segid) {
      $branch_segs = getCBPSegList($listobject, $tablename, $colname, $segid, $debug, -1, $criteria);
      foreach ($branch_segs['segments'] as $this_twig) {
         if (!in_array($this_twig, $branch_nodes['segments'])) {
            array_push($branch_nodes['segments'], $this_twig);
         }
      }
   }
   
   
   foreach ($branch_nodes['segments'] as $thisseg) {
      # this passes 1 for levels and NO criteria, so that we can get the objects that flow into this one
      # later, we screen for anything in this branch list already, so that we idenify only local tribs NOT in branch
      $trib_segs = getCBPSegList($listobject, $tablename, $colname, $thisseg, $debug, 1, array());
      foreach ($trib_segs['segments'] as $this_trib) {
         if (!in_array($this_trib, $branch_nodes['segments']) ) {
            array_push($trib_nodes['segments'], $this_trib);
         }
      }
   }
   
   return $trib_nodes;   
   
}

function getBranchTribs($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $criteria = array(), $debug = 0) {
   # user passes uin tablename and colname just so this can be flexible if we should ever have a 
   # new table with the CBP data in it
   # criteria is an associative array to specify the grouping columns, such as watershed, minbas, majbas, rivername, etc.
   $terminal_nodes = getTerminalNode($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $criteria, $debug );
   $branch_nodes = array();
   $trib_nodes = array('segments'=>array(), 'terminal_nodes'=>$terminal_nodes['segments']);
   $branch_nodes['terminal_nodes'] = $terminal_nodes['segments'];
   $branch_nodes['segments'] = $terminal_nodes['segments'];
   
   foreach ($terminal_nodes['segments'] as $segid) {
      $branch_segs = getSegList($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $segid, -1, $criteria, $debug);
      foreach ($branch_segs['segments'] as $this_twig) {
         if (!in_array($this_twig, $branch_nodes['segments'])) {
            array_push($branch_nodes['segments'], $this_twig);
         }
      }
   }
   
   
   foreach ($branch_nodes['segments'] as $thisseg) {
      # this passes 1 for levels and NO criteria, so that we can get the objects that flow into this one
      # later, we screen for anything in this branch list already, so that we idenify only local tribs NOT in branch
      $trib_segs = getSegList($listobject, $tablename, $name_col, $tnode_col, $fnode_col, $thisseg, 1, array(), $debug);
      foreach ($trib_segs['segments'] as $this_trib) {
         if (!in_array($this_trib, $branch_nodes['segments']) ) {
            array_push($trib_nodes['segments'], $this_trib);
         }
      }
   }
   
   return $trib_nodes;   
   
}

?>
