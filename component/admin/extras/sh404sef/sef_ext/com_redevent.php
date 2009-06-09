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
  
  // do something about that Itemid thing
  if (eregi('Itemid=[0-9]+', $string) === false) { // if no Itemid in non-sef URL
    // V 1.2.4.t moved back here
    if ($sefConfig->shInsertGlobalItemidIfNone && !empty($shCurrentItemid)) {
      $string .= '&Itemid='.$shCurrentItemid; ;  // append current Itemid
      $Itemid = $shCurrentItemid;
      shAddToGETVarsList('Itemid', $Itemid); // V 1.2.4.m
    }

    if ($sefConfig->shInsertTitleIfNoItemid)
    $title[] = $sefConfig->shDefaultMenuItemName ?
      $sefConfig->shDefaultMenuItemName : getMenuTitle($option, (isset($view) ? @$view : null), $shCurrentItemid, null, $shLangName );  // V 1.2.4.q added forced language
      $shItemidString = '';
      if ($sefConfig->shAlwaysInsertItemid && (!empty($Itemid) || !empty($shCurrentItemid)))
    $shItemidString = _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX.$sefConfig->replacement
    .(empty($Itemid)? $shCurrentItemid :$Itemid);
  } else {  // if Itemid in non-sef URL
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
  
  /* Get the DB connection */
  $db = JFactory::getDBO();
  
  /* Set the main title of the component */
  if (isset($view)) {
    $title[] = $sh_LANG[$shLangIso][$view];
    /* Check for calender entry */
    if ($view == 'day') {
      //$title[] = $shGETVars['id'];
    }
    if ($view == 'categoryevents') {
      $q = "SELECT catname FROM #__redevent_categories WHERE id = ".$id;
      $db->setQuery($q);
      $title[] = $db->loadResult();
      //$title[] = $id;
      /* Remove xref so no other course details are added */
      shRemoveFromGETVarsList('id');
      shRemoveFromGETVarsList('xref');
    }
    if ($view == 'venueevents') {
      $q = "SELECT venue FROM #__redevent_venues WHERE id = ".$id;
      $db->setQuery($q);
      $title[] = $db->loadResult();
      /* Remove xref so no other course details are added */
      shRemoveFromGETVarsList('id');
    }
    if ($view == 'upcomingvenueevents') {
      $q = "SELECT venue FROM #__redevent_venues WHERE id = ".$id;
      $db->setQuery($q);
      $title[] = $db->loadResult();
      /* Remove xref so no other course details are added */
      shRemoveFromGETVarsList('id');
    }
    if ($view == 'details') {
      if (isset($xref))
      {
	      $q = "SELECT e.title, v.city, DATE_FORMAT(x.dates, '%d-%m-%Y') AS dates, TIME_FORMAT(x.times, '%H-%i') AS times
	        FROM #__redevent_event_venue_xref x
	        LEFT JOIN #__redevent_events e
	        ON e.id = x.eventid
	        LEFT JOIN #__redevent_venues v
	        ON v.id = x.venueid
	        WHERE x.id = ".$xref;
	      $db->setQuery($q);
	      $details = $db->loadObject();
	      $title[] = $xref.'-'.$details->title;
	      $title[] = $details->city;
	      $title[] = $details->dates;
	      $title[] = $details->times;
        shRemoveFromGETVarsList('xref');
			  if (!empty($id)) 
			    shRemoveFromGETVarsList('id');
      }
      else if (isset($id))
      {
        $q = "SELECT e.title 
          FROM  #__redevent_events e
          WHERE e.id = ".$id;
        $db->setQuery($q);
        $details = $db->loadObject();
        $title[] = $id.'-'.$details->title;
        shRemoveFromGETVarsList('id');
      }
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
//  shRemoveFromGETVarsList('id');
//  shRemoveFromGETVarsList('layout');
  shRemoveFromGETVarsList('view');
  
//  if (isset($shGETVars['xref'])) {
//    if ($shGETVars['xref'] > 0) {
//      /* Get the event name/place/start date/start time */
//      $q = "SELECT e.title, v.city, DATE_FORMAT(x.dates, '%d-%m-%Y') AS dates, TIME_FORMAT(x.times, '%H-%i') AS times
//        FROM #__redevent_event_venue_xref x
//        LEFT JOIN #__redevent_events e
//        ON e.id = x.eventid
//        LEFT JOIN #__redevent_venues v
//        ON v.id = x.venueid
//        WHERE x.id = ".$shGETVars['xref'];
//      $db->setQuery($q);
//      $details = $db->loadObject();
//      $title[] = $shGETVars['xref'].'-'.$details->title;
//      $title[] = $details->city;
//      $title[] = $details->dates;
//      $title[] = $details->times;
//    }
//    shRemoveFromGETVarsList('xref');
//  }
//  if (isset($shGETVars['subtype'])) {
//    $title[] = $shGETVars['subtype'];
//    shRemoveFromGETVarsList('subtype');
//  }
//  if (isset($shGETVars['submit_key'])) {
//    if (isset($shGETVars['page'])) {
//      switch($shGETVars['page']) {
//        case 'final':
//          $title[] = $shGETVars['action'];
//          break;
//        default:
//          if ($shGETVars['page'] != 'confirmation') $title[] = $shGETVars['page'];
//          else $title[] = $shGETVars['action'];
//          break;
//      }
//      shRemoveFromGETVarsList('page');
//      shRemoveFromGETVarsList('action');
//    }
//    else $title[] = 'submit';
//  }
//  
//  if (isset($shGETVars['page'])) {
//    $title[] = $shGETVars['page'];
//    shRemoveFromGETVarsList('page');
//  }
//  
//  if (isset($shGETVars['task'])) {
//    if (strtolower($shGETVars['task']) == 'confirm') {
//      $title[] = 'confirm';
//      $title[] = $shGETVars['confirmid'];
//    }
//    shRemoveFromGETVarsList('task');
//    shRemoveFromGETVarsList('confirmid');
//  }
//  
//  if (isset($shGETVars['pop'])) {
//    if ($shGETVars['pop'] == 1) {
//      $title[] = 'print';
//    }
//    shRemoveFromGETVarsList('pop');
//  }
//  
//  /* Handle the RSS feed */
//  if (isset($shGETVars['format'])) {
//    if (strtolower($shGETVars['format']) == 'feed') $title[] = 'feed';
//    shRemoveFromGETVarsList('format');
//  }
}
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
?>
