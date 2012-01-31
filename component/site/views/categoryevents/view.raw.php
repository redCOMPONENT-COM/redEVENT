<?php
/**
 * @version 1.0 $Id: view.html.php 1625 2009-11-18 16:54:27Z julien $
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

require_once JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'iCalcreator.class.php';

jimport( 'joomla.application.component.view');

/**
 * ICS CategoryEvents View class of the redEVENT component
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventViewCategoryEvents extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 2.0
	 */
	function display($tpl = null)
	{		
		$mainframe = &JFactory::getApplication();
    		
		$settings = redEVENTHelper::config();
		
		// Get data from the model
		$model = $this->getModel();
		$model->setLimit($settings->params->get('ical_max_items', 100));
		$model->setLimitstart(0);
		$rows = & $model->getData();
				
    // initiate new CALENDAR
		$vcal = redEVENTHelper::getCalendarTool();
		$catid = JRequest::getInt('id');		
		$vcal->setProperty('unique_id', 'category'.$catid.'@'.$mainframe->getCfg('sitename'));
		$vcal->setConfig( "filename", "category".$catid.".ics" );
		
		foreach ( $rows as $row )
		{				
			redEVENTHelper::icalAddEvent($vcal, $row);	
		}
		$vcal->returnCalendar();                       // generate and redirect output to user browser
//		echo $vcal->createCalendar(); // debug
		$mainframe->close();					
	}
}
