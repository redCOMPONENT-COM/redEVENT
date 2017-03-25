<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * ICS simple events list View class
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewSimpleList extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$settings = RedeventHelper::config();

		// Get data from the model
		$model = $this->getModel();
		$model->setLimit($settings->get('ical_max_items', 100));
		$model->setLimitstart(0);
		$rows = $model->getData();

		// Initiate new CALENDAR
		$vcal = RedeventHelper::getCalendarTool();
		$vcal->setProperty('unique_id', 'allevents@' . $mainframe->getCfg('sitename'));
		$vcal->setConfig("filename", "events.ics");

		foreach ( $rows as $row )
		{
			RedeventHelper::icalAddEvent($vcal, $row);
		}

		$vcal->returnCalendar();

		$mainframe->close();

		return true;
	}
}
