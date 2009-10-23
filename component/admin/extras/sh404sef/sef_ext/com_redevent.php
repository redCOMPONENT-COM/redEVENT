<?php
/**
 * sh404SEF support for com_search component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id$
 * {shSourceVersionTag: Version x - 2007-09-20}
 */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG, $sefConfig; 
$shLangName = '';;
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
$shLangIso = shLoadPluginLanguage( 'com_redevent', $shLangIso, '_COM_SEF_REDEVENT');
// ------------------  load language file - adjust as needed ----------------------------------------                                           

if (isset($task) && $task == 'createpdfemail') $dosef = false;
else if (isset($page) && $page == 'print') $dosef = false;
else {
	  
  /* Get the DB connection */
  $db = JFactory::getDBO();
  
  $Itemid = isset($Itemid) ? @$Itemid : null; 
  if (!empty($Itemid))
  {
    $menu = JSite::getMenu();
    $menuparams = $menu->getParams( $Itemid );
  }
  else {
    $menuparams = null;     
  }
    
  // do something about that Itemid thing
  if (empty($Itemid)) 
  { // if no Itemid in non-sef URL
    // V 1.2.4.t moved back here
    // try to find an item id that belongs to redevent fo default routing.
    if (empty($shCurrentItemid)) 
    {
	    $component =& JComponentHelper::getComponent('com_redevent');
	
	    $menus  = & JSite::getMenu();
	    $items  = $menus->getItems('componentid', $component->id);
	    $access = 0;

	    if (isset($view) && !empty($view))
	    {
	      foreach($items as $item)
	      {
	        if ((@$item->query['view'] == $view) && ($item->published == 1) && ($item->access <= $access)) {
	          $shCurrentItemid = $item->id;
	          break;
	        }
	      }
	    }
      if (empty($shCurrentItemid))
      {
	      //no menuitem exists -> return first possible match
	      foreach($items as $item)
	      {
	        if ($item->published == 1 && $item->access <= $access) 
	        {
            $shCurrentItemid = $item->id;
            break;
	        }
	      }
      }
    }
    
    if ($sefConfig->shInsertGlobalItemidIfNone && !empty($shCurrentItemid)) 
    {
      $string .= '&Itemid='.$shCurrentItemid; ;  // append current Itemid
      $Itemid = $shCurrentItemid;
      shAddToGETVarsList('Itemid', $Itemid); // V 1.2.4.m
    }

    if ($sefConfig->shInsertTitleIfNoItemid)
    {
    $title[] = $sefConfig->shDefaultMenuItemName ?
      $sefConfig->shDefaultMenuItemName : getMenuTitle($option, (isset($view) ? @$view : null), $shCurrentItemid, null, $shLangName );  // V 1.2.4.q added forced language
    }
    
    $shItemidString = '';    
    if ($sefConfig->shAlwaysInsertItemid && (!empty($Itemid) || !empty($shCurrentItemid)))
    {
      $shItemidString = _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX
                      .$sefConfig->replacement
                      .(empty($Itemid)? $shCurrentItemid :$Itemid);
    }
  } 
  else 
  {  // if Itemid in non-sef URL
    $shItemidString = $sefConfig->shAlwaysInsertItemid ?
    _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX.$sefConfig->replacement.$Itemid
    : '';
    if ($sefConfig->shAlwaysInsertMenuTitle){
      //global $Itemid; V 1.2.4.g we want the string option, not current page !
      if ($sefConfig->shDefaultMenuItemName)
      $title[] = $sefConfig->shDefaultMenuItemName;// V 1.2.4.q added force language
      elseif ($menuTitle = getMenuTitle($option, (isset($view) ? @$view : null), $Itemid, '',$shLangName )) {
        //echo 'Menutitle = '.$menuTitle.'<br />';
        if ($menuTitle != '/') $title[] = $menuTitle;
      }
    }
  }
	
	if (!empty($Itemid))
	  shRemoveFromGETVarsList('Itemid');  
  
  /* Remove some default values */
//  shRemoveFromGETVarsList('option');
//  shRemoveFromGETVarsList('lang');
//  shRemoveFromGETVarsList('Itemid');
//  shRemoveFromGETVarsList('limit');
//  shRemoveFromGETVarsList('eventid');
//  shRemoveFromGETVarsList('form_id');
//  shRemoveFromGETVarsList('tmpl');
//  shRemoveFromGETVarsList('pop');
//  shRemoveFromGETVarsList('type');

  shRemoveFromGETVarsList('option');
  shRemoveFromGETVarsList('lang');
  // optional removal of limit and limitstart
  if (!empty($limit))                        // use empty to test $limit as $limit is not allowed to be zero
    shRemoveFromGETVarsList('limit'); 
  if (isset($limitstart))                    // use isset to test $limitstart, as it can be zero
    shRemoveFromGETVarsList('limitstart');
    
  /* Set the main title of the component */
  if (isset($view)) 
  {
  	if (isset($sh_LANG[$shLangIso][$view])) {
      $title[] = $sh_LANG[$shLangIso][$view];
  	}
  	else {
      $title[] = $view;  		
  	}
    
    // layout, no replacement
    if (isset($layout)) {
    	$title[] = $layout;
    	shRemoveFromGETVarsList('layout');
    }
    
    if (isset($xref) && $xref)
    {
	    $q = "SELECT e.title, v.city, DATE_FORMAT(x.dates, '%Y-%m-%d') AS dates, TIME_FORMAT(x.times, '%H-%i') AS times,
              CASE WHEN CHAR_LENGTH(v.alias) THEN v.alias ELSE v.id END as venueslug
	            FROM #__redevent_event_venue_xref x
	            LEFT JOIN #__redevent_events e
	            ON e.id = x.eventid
	            LEFT JOIN #__redevent_venues v
	            ON v.id = x.venueid
	            WHERE x.id = ".$db->Quote((int) $xref);
	    $db->setQuery($q);
	    $details = $db->loadObject();
    }
    else if (isset($id))
    {
	    $q = "SELECT e.title
	            FROM  #__redevent_events e
	            WHERE e.id = ".(int)$id;
	    $db->setQuery($q);
	    $details = $db->loadObject();
    }
    
    switch ($view)
    {
	    case 'day':
	    	if (isset($id)) {
	        $title[] = $id;    
	        shRemoveFromGETVarsList('id');		
	    	}
        if ($menuparams) 
        {
          $offset = $menuparams->get('days', 0);
          switch ($offset)
          {
            case 0: 
              $title[] = $sh_LANG[$shLangIso]['today']; 
              break;
            case 1:
              $title[] = $sh_LANG[$shLangIso]['tomorrow']; 
              break;
            case -1:
              $title[] = $sh_LANG[$shLangIso]['yesterday']; 
              break;
            default:
              $title[] = sprintf($sh_LANG[$shLangIso]['in x days'], $offset); 
              break;
          } 
        }	    	
	      break;
	      
	    case 'categoryevents':
	      $q = "SELECT catname FROM #__redevent_categories WHERE id = ".$db->Quote((int) $id);
	      $db->setQuery($q);
	      $title[] = $db->loadResult();
	      //$title[] = $id;
	      /* Remove xref so no other course details are added */
	      shRemoveFromGETVarsList('id');
	      shRemoveFromGETVarsList('xref');
	      break;
	      
      case 'categories':
        if ($menuparams && $menuparams->get('parentcategory', 0)) 
        {
          $vcat = $menuparams->get('parentcategory', 0);
          $q = "SELECT catname FROM #__redevent_categories WHERE id = ".$db->Quote((int) $vcat);
          $db->setQuery($q);
          $title[] = $db->loadResult();
        }
        break;
        
      case 'categoriesdetailed':
        if ($menuparams && $menuparams->get('parentcategory', 0)) 
        {
          $vcat = $menuparams->get('parentcategory', 0);
          $q = "SELECT catname FROM #__redevent_categories WHERE id = ".$db->Quote((int) $vcat);
          $db->setQuery($q);
          $title[] = $db->loadResult();
        }
        break;
        
      case 'venues':
        if ($menuparams && $menuparams->get('categoryid', 0)) 
        {
          $vcat = $menuparams->get('categoryid', 0);
          $q = "SELECT name FROM #__redevent_venues_categories WHERE id = ".$db->Quote((int) $vcat);
          $db->setQuery($q);
          $title[] = $db->loadResult();
        }
        break;
        
	   case 'venueevents':
	      $q = "SELECT venue FROM #__redevent_venues WHERE id = ".$db->Quote((int) $id);
	      $db->setQuery($q);
	      $title[] = $db->loadResult();
	      /* Remove xref so no other course details are added */
	      shRemoveFromGETVarsList('id');
	      break;
	      
	   case 'venuecategory':
        $q = "SELECT name FROM #__redevent_venues_categories WHERE id = ".$db->Quote((int) $id);
        $db->setQuery($q);
        $title[] = $db->loadResult();
        /* Remove xref so no other course details are added */
        shRemoveFromGETVarsList('id');
        break;
	      
	   case 'upcomingvenueevents':
	      $q = "SELECT venue FROM #__redevent_venues WHERE id = ".$db->Quote((int) $id);
	      $db->setQuery($q);
	      $title[] = $db->loadResult();
	      /* Remove xref so no other course details are added */
	      shRemoveFromGETVarsList('id');
        break;
        
	   case 'details':
	      if (isset($xref) && $xref)
	      {
		      $title[] = $xref.'-'.$details->title;
		      $title[] = $details->city;
		      $title[] = $details->dates;
		      if ($details->times != '00-00') $title[] = $details->times;
            shRemoveFromGETVarsList('xref');
				  if (!empty($id)) 
				    shRemoveFromGETVarsList('id');
	      }
	      else if (isset($id))
	      {
	        $title[] = $id.'-'.$details->title;
	        shRemoveFromGETVarsList('id');
	      }
	      break;
	      
	   case 'signup':
	     $title[] = $details->venueslug;
       $title[] = $details->dates;
       $title[] = $details->times;
       $title[] = $xref;
       shRemoveFromGETVarsList('xref');
       shRemoveFromGETVarsList('id');
	     break;
	     	     
     case 'venue': // ajax call, no need for sef
       $dosef = false;
       break;
	     
      default:
        break; 
    }
//    if ($shGETVars['view'] == 'editevent') {
//      $title[] = $shGETVars['layout'];
//    }
//    if ($shGETVars['view'] == 'confirmation') {
//      $title[] = $shGETVars['view'];
//      $title[] = $shGETVars['task'];
//      shRemoveFromGETVarsList('task');
//    }
  }
  
  /* Remove ID field as we no longer need it */
  shRemoveFromGETVarsList('view');
  


  if (isset($subtype)) {
	  $title[] = $subtype;
	  shRemoveFromGETVarsList('subtype');
  }
  if (isset($submit_key)) {
    if (isset($page)) {
      switch($page) {
        case 'final':
          $title[] = $action;
          break;
        default:
          if ($page != 'confirmation') $title[] = $page;
          else $title[] = $action;
          break;
      }
      shRemoveFromGETVarsList('page');
      shRemoveFromGETVarsList('action');
    }
    else $title[] = 'submit';
  }
  
  if (isset($page)) {
    $title[] = $page;
    shRemoveFromGETVarsList('page');
  }
  
  if (isset($task)) {
    if (strtolower($task) == 'confirm') {
      $title[] = 'confirm';
      $title[] = $confirmid;
      shRemoveFromGETVarsList('confirmid');
    }
    else {
      $title[] = $task;    	
    }
    shRemoveFromGETVarsList('task');
  }

  if (isset($tpl)) {
    $title[] = $tpl;
    shRemoveFromGETVarsList('tpl');
  }
  
  if (isset($pop)) {
    if ($pop == 1) {
      $title[] = 'print';
    }
    shRemoveFromGETVarsList('pop');
  }
  
  /* Handle the RSS feed */
  if (isset($format)) {
    if (strtolower($format) == 'feed') $title[] = 'feed';
    shRemoveFromGETVarsList('format');
  }
  if (isset($type)) {
    $title[] = $type;
    shRemoveFromGETVarsList('type');
  }
}
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
?>
