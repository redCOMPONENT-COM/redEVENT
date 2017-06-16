<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redevent/classes/iCalcreator.class.php';

/**
 * ICS CategoryEvents View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewCategoryEvents extends RViewSite
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

		$settings = RedeventHelper::config();

		// Get data from the model
		$model = $this->getModel();
		$model->setLimit($settings->get('ical_max_items', 100));
		$model->setLimitstart(0);
		$rows = $model->getSessions();

		// Initiate new CALENDAR
		$vcal = RedeventHelper::getCalendarTool();
		$catid = JFactory::getApplication()->input->getInt('id');
		$vcal->setProperty('unique_id', 'category' . $catid . '@' . $mainframe->getCfg('sitename'));
		$vcal->setConfig("filename", "category" . $catid . ".ics");

		foreach ($rows as $row)
		{
			RedeventHelper::icalAddEvent($vcal, $row);
		}

		$vcal->returnCalendar();

		$mainframe->close();

		return true;
	}
}
