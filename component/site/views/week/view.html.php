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
 * HTML View class for the Day View
 *
 * @package Joomla
 * @subpackage Redevent
 * @since 2.0
 */
class RedeventViewWeek extends JView
{
	/**
	 * Creates the week View
	 *
	 * @since 2.0
	 */
	function display( $tpl = null )
	{
		$application = JFactory::getApplication();

		//initialize variables
		$document = JFactory::getDocument();
		$settings = redEVENTHelper::config();
		$menu		  =  JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= $application->getParams();
    $uri      = JFactory::getURI();

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/week.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
		
    // add js
    JHTML::_('behavior.mootools');

		$pop      = JRequest::getBool('pop');
		$pathway  = $application->getPathWay();

		//get data from model
		$rows = & $this->get('Data');
		$week = & $this->get('Day');
		
		//params
		if ($item) {
			$title = $item->name;
		}
		else {
			$title = JText::sprintf('COM_REDEVENT_WEEK_HEADER', $this->get('weeknumber'), $this->get('year'));
		}
		$params->def( 'page_title', $title);

		//pathway
		$pathway->addItem(JText::sprintf('COM_REDEVENT_WEEK_HEADER', $this->get('weeknumber'), $this->get('year')));

		//Set Page title
		if ($item && !$item->name) {
			$document->setTitle($params->get('page_title'));
			$document->setMetadata( 'keywords' , $params->get('page_title') );
		}
						
		$this->assignRef('data',	   $rows);
		$this->assignRef('title',	   $title);
		$this->assignRef('params',   $params);
		$this->assign('week',   $this->get('week'));
		$this->assign('weeknumber',   $this->get('weeknumber'));
		$this->assign('year',   $this->get('year'));
		$this->assign('weekdays',   $this->get('weekdays'));
		$this->assign('next',   $this->get('nextweek'));
		$this->assign('previous',   $this->get('previousweek'));
		
		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		array_unshift($cols, 'time');
		array_unique($cols);
		$exclude = array('date');
		$cols = array_diff($cols, $exclude);
		
		$cols = redEVENTHelper::validateColumns($cols);
		$this->assign('columns',        $cols);
		$start = JComponentHelper::getParams('com_redevent')->get('week_start') == 'MO' ? 1 : 0;
		$this->assign('start',        $start);
		
		parent::display($tpl);
	}
	
	public function sortByDay()
	{
		if (!$this->data) {
			return false;
		}
		$days = array();
		$format = JComponentHelper::getParams('com_redevent')->get('week_start') == 'MO' ? '%u' : '%w';
		foreach ($this->data as $ev)
		{
			$days[strftime($format, strtotime($ev->dates))][] = $ev;
		}
		return $days;
	}
	
	/**
	 * get day name from number
	 * @param int $number
	 * @return string
	 */
	public function getDayName($number)
	{
		$days = $this->get('WeekDays');
		$day = $days[$number-1];
		return date('l, j F Y', strtotime($day));
	}
}
