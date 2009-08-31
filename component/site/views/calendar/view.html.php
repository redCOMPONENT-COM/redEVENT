<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Upcoming events View
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewCalendar extends JView
{
	/**
	 * Creates the Venueevents View
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		global $mainframe, $option;
		
		//initialize variables
		$document 	= & JFactory::getDocument();
		$menu		= & JSite::getMenu();
		$elsettings = & redEVENTHelper::config();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams('com_redevent');
		$uri 		= & JFactory::getURI();
		JView::loadHelper('route');
		
		//add css file
		if (!$params->get('custom_css')) {
		  $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
		}
		else {
      $document->addStyleSheet($params->get('custom_css'));		  
		}
		
		$document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redeventcalendar.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');
		
		// Parameters
		$day_name_length	= $params->get( 'day_name_length', '2' );
		$first_day			= $params->get( 'first_day', '1' );
		$Year_length		= $params->get( 'Year_length', '1' );
		$Month_length		= $params->get( 'Month_length', '0' );
		$Month_offset		= $params->get( 'Month_offset', '0' );	
		$Show_Tooltips		= $params->get( 'Show_Tooltips', '1' );	
		$Remember			= $params->get( 'Remember', '1' );
		$LocaleOverride		= $params->get( 'locale_override', '' );
		$CalTooltipsTitle		= $params->get( 'cal15q_tooltips_title', 'Events' );	
		$CharsetOverride		= $params->get( 'charset_override', '' );
		
		if (empty($LocaleOverride))
		{
		}
		else
		{
			setlocale(LC_ALL, $LocaleOverride ) ;
		}
	
		//get switch trigger
		$req_month 		= (int)JRequest::getVar( 'el_mcal_month', '', 'request' );
		$req_year       = (int)JRequest::getVar( 'el_mcal_year', '', 'request' );	
		
		if ($Remember == 1) // Remember which month / year is selected. Don't jump back to tday on page change
		{
			if ($req_month == 0) 
			{
				$req_month = $mainframe->getUserState("eventlistcalqmonth");
				$req_year = $mainframe->getUserState("eventlistcalqyear");	
			}
			else
			{
				$mainframe->setUserState("eventlistcalqmonth",$req_month);
				$mainframe->setUserState("eventlistcalqyear",$req_year);
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
		$find = array('/.el_mcal_month=[0-9]/', '/.el_mcal_year=[0-9]+/');
		$replace = '';
		$newuri = preg_replace($find,$replace,$newuri);
		
		$newuri .= (stristr($newuri, '?')) ? '&' : '?';
	
		//Create Links
		$prev_link = $newuri.'el_mcal_month='.$prev_month.'&el_mcal_year='.$prev_month_year ;
		$next_link = $newuri.'el_mcal_month='.$next_month.'&el_mcal_year='.$next_month_year ;
		
		$model_days = $this->getModel('calendar');
		
		$days = $model_days->getdays($req_year, $offset_month, $params);
		
		$this->assignRef('days', $days);
		$this->assignRef('req_month', $req_month);
		$this->assignRef('req_year', $req_year);
		$this->assignRef('prev_link', $prev_link);
		$this->assignRef('next_link', $next_link);
		$this->assignRef('prev_month', $prev_month);
		$this->assignRef('next_month', $next_month);
		$this->assignRef('first_day', $first_day);
		$this->assignRef('Year_length', $Year_length);
		$this->assignRef('Month_length', $Month_length);
		$this->assignRef('offset_month', $offset_month);
		$this->assignRef('day_name_length', $day_name_length);
		$this->assignRef('Show_Tooltips', $Show_Tooltips);
		$this->assignRef('CalTooltipsTitle', $CalTooltipsTitle);
		
		parent::display($tpl);
	}
}
?>