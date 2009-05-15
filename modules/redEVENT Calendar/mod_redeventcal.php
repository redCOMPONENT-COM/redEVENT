<?php
/**
* @version 0.8 $Id$
* @package Eventlist CalModuleQ for Joomla 1.5
* @copyright (C) 2008 Toni Smillie www.qivva.com
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Eventlist Calendar Q by Toni Smillie www.qivva.com
* 
 * Version 0.8
 * Changes for v0.8
 * 1. Roll over year end bug fixed
 * 2. Check for mb_substr
 * 3. Removed hard coded text align center (Now uses stylesheet)
 * Version 0.7
 * Changes for v0.7
 * 1. Removed JDate again - causing too many date/time problems
 * 2. Better Tooltips
 * Version 0.6
 * Changes for v0.6
 * 1. Use JDate for month and day languages
 * 2. XHTML validation fixes
 * 3. Tests if mbstring functions are installed before using them, defaults to ucfirst if no mb_convert_case
 * 4. Allows a calendar offset so can have multiple calendars displaying different months
 *
 * Version 0.5
 * Changes for v0.5
 * 1. Remember which month was being viewed, so doesn't revery back to "today" on a page change - controlled by Parameter
 * 2. Fix for Windows IIS servers
 * 3. Fix for SEF links
 * 4. Replace instead of concatenate month view changes
 * 5. Set $month_href = NULL; (bug fix)
 * 6. Use multibyte strings for days and months. Parameter overrides for locale and charset.
 * 
 * Version 0.4
 * Changes for v0.4
 * 1. New Parameters Category ID and Venue ID to allow for filtering of calendar module events
 * 2. Removed the 2 styling parameters form the parameter list. All styling is now done in the CSS
 * 3. Enhanced styling and new stylesheet
 * 
* Changes for v0.3
* 1. Fixed timeoffset properly for Joomla 1.5
* 2. Fixed problem that caused "Notice: Undefined index:" with PHP5
* 
* Changes for v0.2
* 1. Added Title on Tooltips
* 2. Fix for time offset
* 3 Bug fix - not picking up all events when on the same day
* 
* Original Eventlist calendar from Christoph Lukes www.schlu.net
* PHP Calendar (version 2.3), written by Keith Devens
* http://keithdevens.com/software/php_calendar
* see example at http://keithdevens.com/weblog
* License: http://keithdevens.com/software/license
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname(__FILE__).DS.'helper.php' );
require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');

// include mootools tooltip
JHTML::_('behavior.tooltip');

	// Parameters
	$day_name_length	= $params->get( 'day_name_length', '2' );
	$first_day			= $params->get( 'first_day', '1' );
	$Year_length		= $params->get( 'Year_length', '1' );
	$Month_length		= $params->get( 'Month_length', '0' );
	$Month_offset		= $params->get( 'Month_offset', '0' );	
	$Show_Tooltips		= $params->get( 'Show_Tooltips', '1' );	
	$Remember			= $params->get( 'Remember', '1' );
	$LocaleOverride		= $params->get( 'locale_override', '' );
	$CalTooltipsTitle		= $params->get( 'recal_tooltips_title', 'Events' );	
	$CharsetOverride		= $params->get( 'charset_override', '' );
	
	if (empty($LocaleOverride))
	{
	}
	else
	{		 
		$my_loc = setlocale(LC_TIME, $LocaleOverride ) ;
	}

	//get switch trigger
	$req_month 		= (int)JRequest::getVar( 're_mcal_month', '', 'request' );
	$req_year       = (int)JRequest::getVar( 're_mcal_year', '', 'request' );	
	
	if ($Remember == 1) // Remember which month / year is selected. Don't jump back to tday on page change
	{
		if ($req_month == 0) 
		{
			$req_month = $mainframe->getUserState("redeventcalmonth");
			$req_year = $mainframe->getUserState("redeventcalyear");	
		}
		else
		{
			$mainframe->setUserState("redeventcalmonth",$req_month);
			$mainframe->setUserState("redeventcalyear",$req_year);
		}
	}
	
	//set now
	$config =& JFactory::getConfig();
	$tzoffset = $config->getValue('config.offset');
	$time 			= time()  + ($tzoffset*60*60); //25/2/08 Change for v 0.6 to incorporate server offset into time;
	$today_month 	= date( 'm', $time);
	$today_year 	= date( 'Y', $time);
	$today          = date( 'j',$time);
	
	if ($req_month == 0) $req_month = $today_month;
	$offset_month = $req_month + $Month_offset;
	if ($req_year == 0) $req_year = $today_year;
	if ($offset_month >12) 
	{
		$offset_month = $offset_month -12; // Roll over year end	
		$req_year = $req_year + 1;
	}
	
	//Setting the previous and next month numbers
	$prev_month_year = $req_year;
	$next_month_year = $req_year;
	
	$prev_month = $req_month-1;
	if($prev_month < 1){
		$prev_month = 12;
		$prev_month_year = $prev_month_year-1;
	}
	
	$next_month = $req_month+1;
	if($next_month > 12){
		$next_month = 1;
		$next_month_year = $next_month_year+1;
	}
	
	//Requested URL
	$uri    = JURI::getInstance();
	$myurl = $uri->toString();
	
	if (empty($myurl)) $newuri = $uri->current();
	else $newuri = $myurl;
	
	// Clean up
	$find = array('/.re_mcal_month=[0-9]/', '/.re_mcal_year=[0-9]+/');
	$replace = '';
	$newuri = preg_replace($find,$replace,$newuri);
	
	$newuri .= (stristr($newuri, '?')) ? '&' : '?';

	//Create Links
 	$prev_link = $newuri.'re_mcal_month='.$prev_month.'&re_mcal_year='.$prev_month_year ;
 	$next_link = $newuri.'re_mcal_month='.$next_month.'&re_mcal_year='.$next_month_year ;

	$days = modredeventcalHelper::getdays($req_year, $offset_month, $params);
	
	require( JModuleHelper::getLayoutPath( 'mod_redeventcal' ) );	
	
	// reset the local
	setlocale(LC_TIME, NULL) ;
?> 
