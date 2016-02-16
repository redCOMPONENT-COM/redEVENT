<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Feed venues View class
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewVenues extends RViewSite
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
		$app = JFactory::getApplication();

		$doc = JFactory::getDocument();

		// Get some data from the model
		$app->input->set('limit', $app->getCfg('feed_limit'));
		$rows = $this->get('Data');

		foreach ($rows as $row)
		{
			// Strip html from feed item title
			$title = $this->escape($row->venue);
			$title = html_entity_decode($title);

			$link = RedeventHelperRoute::getVenueEventsRoute($row->slug);
			$link = JRoute::_($link);

			// Strip html from feed item description text
			$description = $row->locdescription;
			@$created = ($row->created ? date('r', strtotime($row->created)) : '');

			$item = new JFeedItem;
			$item->title = $title;
			$item->link = $link;
			$item->description = $description;
			$item->date = $created;

			$doc->addItem($item);
		}
	}
}
