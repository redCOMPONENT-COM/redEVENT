<?php
/**
 * sh404SEF support for com_search component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_search.php 229 2008-01-21 19:53:39Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG, $sefConfig, $shGETVars;  
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
$shLangIso = shLoadPluginLanguage( 'com_redevent', $shLangIso, '_COM_SEF_REDEVENT');
// ------------------  load language file - adjust as needed ----------------------------------------                                           

if ($shGETVars['task'] == 'createpdfemail') $dosef = false;
else if ($shGETVars['page'] == 'print') $dosef = false;
else {
	
	/* Remove some default values */
	shRemoveFromGETVarsList('option');
	shRemoveFromGETVarsList('lang');
	shRemoveFromGETVarsList('Itemid');
	shRemoveFromGETVarsList('limit');
	shRemoveFromGETVarsList('eventid');
	shRemoveFromGETVarsList('form_id');
	shRemoveFromGETVarsList('tmpl');
	shRemoveFromGETVarsList('pop');
	shRemoveFromGETVarsList('type');
	
	/* Get the DB connection */
	$db = JFactory::getDBO();
	
	/* Set the main title of the component */
	if (isset($shGETVars['view'])) {
		$title[] = $sh_LANG[$shLangIso][$shGETVars['view']];
		/* Check for calender entry */
		if ($shGETVars['view'] == 'day') {
			$title[] = $shGETVars['id'];
		}
		if ($shGETVars['view'] == 'categoryevents') {
			$q = "SELECT catname FROM #__redevent_categories WHERE id = ".$shGETVars['id'];
			$db->setQuery($q);
			$title[] = $db->loadResult();
			$title[] = $shGETVars['id'];
			/* Remove xref so no other course details are added */
			shRemoveFromGETVarsList('xref');
		}
		if ($shGETVars['view'] == 'venueevents') {
			$q = "SELECT venue FROM #__redevent_venues WHERE id = ".$shGETVars['id'];
			$db->setQuery($q);
			$title[] = $db->loadResult();
			/* Remove xref so no other course details are added */
			shRemoveFromGETVarsList('xref');
		}
		if ($shGETVars['view'] == 'upcomingvenueevents') {
			$q = "SELECT venue FROM #__redevent_venues WHERE id = ".$shGETVars['id'];
			$db->setQuery($q);
			$title[] = $db->loadResult();
			/* Remove xref so no other course details are added */
			shRemoveFromGETVarsList('xref');
		}		
		if ($shGETVars['view'] == 'editevent') {
			$title[] = $shGETVars['layout'];
		}
		if ($shGETVars['view'] == 'confirmation') {
			$title[] = $shGETVars['view'];
		}
	}
	
	/* Remove ID field as we no longer need it */
	shRemoveFromGETVarsList('id');
	shRemoveFromGETVarsList('layout');
	shRemoveFromGETVarsList('view');
	
	if (isset($shGETVars['xref'])) {
		if ($shGETVars['xref'] > 0) {
			/* Get the event name/place/start date/start time */
			$q = "SELECT e.title, v.city, DATE_FORMAT(x.dates, '%d-%m-%Y') AS dates, TIME_FORMAT(x.times, '%H-%i') AS times
				FROM #__redevent_event_venue_xref x
				LEFT JOIN #__redevent_events e
				ON e.id = x.eventid
				LEFT JOIN #__redevent_venues v
				ON v.id = x.venueid
				WHERE x.id = ".$shGETVars['xref'];
			$db->setQuery($q);
			$details = $db->loadObject();
			$title[] = $details->title;
			$title[] = $details->city;
			$title[] = $details->dates;
			$title[] = $details->times;
		}
		shRemoveFromGETVarsList('xref');
	}
	if (isset($shGETVars['subtype'])) {
		$title[] = $shGETVars['subtype'];
		shRemoveFromGETVarsList('subtype');
	}
	if (isset($shGETVars['submit_key'])) {
		if (isset($shGETVars['page'])) {
			switch($shGETVars['page']) {
				case 'final':
					$title[] = $shGETVars['action'];
					break;
				default:
					if ($shGETVars['page'] != 'confirmation') $title[] = $shGETVars['page'];
					break;
			}
			shRemoveFromGETVarsList('page');
			shRemoveFromGETVarsList('action');
		}
		else $title[] = 'submit';
	}
	
	if (isset($shGETVars['page'])) {
		$title[] = $shGETVars['page'];
		shRemoveFromGETVarsList('page');
	}
	
	if (isset($shGETVars['task'])) {
		if (strtolower($shGETVars['task']) == 'confirm') {
			$title[] = 'confirm';
			$title[] = $shGETVars['confirmid'];
		}
		shRemoveFromGETVarsList('task');
		shRemoveFromGETVarsList('confirmid');
	}
	
	/* Handle the RSS feed */
	if (isset($shGETVars['format'])) {
		if (strtolower($shGETVars['format']) == 'feed') $title[] = 'feed';
		shRemoveFromGETVarsList('format');
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
