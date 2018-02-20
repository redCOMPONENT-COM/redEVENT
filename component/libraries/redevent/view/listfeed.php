<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redevent/classes/rsscalCreator.class.php';

/**
 * HTML View class for the Venueevents View
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventViewListfeed extends RViewSite
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

		return true;
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

		$id = JFactory::getApplication()->input->getInt('id');

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

		return true;
	}
}
