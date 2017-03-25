<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Feed View class for the simplelist sessions
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewCalendar extends RedeventViewListfeed
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

		if ($this->getLayout() == 'rsscal')
		{
			return $this->displayRssCal();
		}

		// Get data from the model
		$year = $app->input->getInt('yearID', strftime("%Y"));
		$month = $app->input->getInt('monthID', strftime("%m"));

		// Get data from model and set the month
		$model = $this->getModel();
		$model->setDate(mktime(0, 0, 1, $month, 1, $year));

		$rows = $model->getData();

		$doc = JFactory::getDocument();

		foreach ($rows as $row)
		{
			$item = RedeventFeedRssitem::getItem($row);

			// Add item info into rss array
			$doc->addItem($item);
		}

		return true;
	}
}
