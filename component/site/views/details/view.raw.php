<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . DS . 'components' . DS . 'com_redevent' . DS . 'classes' . DS . 'iCalcreator.class.php';

/**
 * CSV Details View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewDetails extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		// Get data from the model
		$row = $this->get('Details');

		// Initiate new CALENDAR
		$vcal = RedeventHelper::getCalendarTool();
		$vcal->setProperty('unique_id', 'session' . $row->xref . '@' . $mainframe->getCfg('sitename'));
		$vcal->setConfig("filename", "event" . $row->xref . ".ics");

		RedeventHelper::icalAddEvent($vcal, $row);

		$vcal->returnCalendar();

		$mainframe->close();

		return true;
	}
}
