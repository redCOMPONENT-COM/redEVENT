<?php
/**
 * shCustomTags support for com_content
 * Yannick Gaultier, shumisha
 * shumisha@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     $Id$
 *
 *  This module must set $shCustomTitleTag, $shCustomDescriptionTag, $shCustomKeywordsTag,$shCustomLangTag, $shCustomRobotsTag according to specific component output
 *
 * if you set a variable to '', this will ERASE the corresponding meta tag
 * if you set a variable to null, this will leave the corresponding meta tag UNCHANGED
 *
 * {shSourceVersionTag: Version x - 2007-09-20}
 *
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

global 	$shCustomTitleTag, $shCustomDescriptionTag, $shCustomKeywordsTag, $shCustomLangTag, $shCustomRobotsTag;
$subtype = JREQUEST::getVar('subtype', null);
$xref = JRequest::getInt('xref', false);

$app = & JFactory::getApplication();

if (!is_null($subtype) && $xref) {
	$db = JFactory::getDBO();
	/* Get the event name/place/start date/start time */
	$q = "SELECT e.title, v.city, DATE_FORMAT(x.dates, '%d-%m-%Y') AS dates, TIME_FORMAT(x.times, '%H-%i') AS times
		FROM #__redevent_event_venue_xref x
		LEFT JOIN #__redevent_events e
		ON e.id = x.eventid
		LEFT JOIN #__redevent_venues v
		ON v.id = x.venueid
		WHERE x.id = ".$xref;
	$db->setQuery($q);
	$details = $db->loadObject();
	// 'website' Online Signup | Date | Location | Coursetitle
	$shCustomTitleTag = $app->getCfg('sitename'). ' Online Signup'.' | '.$details->dates.' | '.ucfirst($details->city).' | '.ucfirst($details->title);
}
?>
