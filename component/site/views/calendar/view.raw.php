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
class RedeventViewCalendar extends RViewSite
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
		$app = JFactory::getApplication();

		// Get data from the model
		$year = $app->input->getInt('yearID', strftime("%Y"));
		$month = $app->input->getInt('monthID', strftime("%m"));

		// Get data from model and set the month
		$model = $this->getModel();
		$model->setDate(mktime(0, 0, 1, $month, 1, $year));

		$rows = $model->getData();

		// Initiate new CALENDAR
		$vcal = RedeventHelper::getCalendarTool();
		$vcal->setProperty('unique_id', 'allevents@' . $app->getCfg('sitename'));
		$vcal->setConfig("filename", "events.ics");

		foreach ( $rows as $row )
		{
			RedeventHelper::icalAddEvent($vcal, $row);
		}

		$vcal->returnCalendar();

		$app->close();
	}
}
