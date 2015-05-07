<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redevent/classes/rsscalCreator.class.php';

/**
 * HTML View class for the Venueevents View
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewVenueevents extends RViewSite
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

		$doc = JFactory::getDocument();

		// Get data from the model
		$app->input->get('limit', $app->getCfg('feed_limit'));
		$rows = $this->get('Data');

		foreach ($rows as $row)
		{
			$item = RedeventFeedRssitem::getItem($row);

			// Add item info into rss array
			$doc->addItem($item);
		}
	}

	/**
	 * Execute and display a template script.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function displayRssCal()
	{
		define('CACHE', './cache');

		$mainframe = JFactory::getApplication();
		$config = RedeventHelper::config();

		$id = JRequest::getInt('id');

		$offset = (float) $mainframe->getCfg('offset');
		$hours = ($offset >= 0) ? floor($offset) : ceil($offset);
		$mins = abs($offset - $hours) * 60;
		$utcoffset = sprintf('%+03d:%02d', $hours, $mins);

		$feed = new rsscalCreator('redEVENT feed', JURI::base(), '');
		$feed->setFilename(CACHE, 'venue' . $id . '.rss');

		$model = $this->getModel();
		$model->setLimit($config->get('ical_max_items', 100));
		$model->setLimitstart(0);

		$rows = $this->get('Data');

		foreach ($rows as $row)
		{
			$item = RedeventFeedRsscalitem::getItem($row);

			$feed->addItem($item);
		}

		$feed->returnRSS(CACHE);
	}
}
