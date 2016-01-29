<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Upcoming events View
 *
 * @package  Redevent.Site
 * @since    2.5
 */
class RedeventViewUpcomingVenueevents extends RViewSite
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
		$document = JFactory::getDocument();
		$document->link = JRoute::_('index.php?option=com_redevent&view=upcomingvenueevents');
		$upcomingevents = $this->get('UpcomingVenueEvents');

		foreach ((array) $upcomingevents as $key => $event)
		{
			$event_url = RedeventHelperRoute::getDetailsRoute($event->slug, $event->xslug);
			$description = RedeventLayoutHelper::render('feed.description', $event);

			$item = new JFeedItem;
			$item->title = RedeventHelper::getSessionFullTitle($event);
			$item->link = $event_url;
			$item->description = $description;
			$item->date = '';
			$item->category = $event->venue;

			// Loads item info into rss array
			$document->addItem($item);
		}
	}
}
