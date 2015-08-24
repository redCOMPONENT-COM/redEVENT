<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * helper for rss item
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventFeedRssitem
{
	/**
	 * Get Item from session data
	 *
	 * @param   object  $session  session data
	 *
	 * @return JFeedItem
	 */
	public static function getItem($session)
	{
		$config = RedeventHelper::config();
		$title = RedeventHelper::getSessionFullTitle($session);

		// Handle categories
		if (!empty($session->categories))
		{
			$category = array();

			foreach ($session->categories AS $cat)
			{
				$category[] = $cat->name;
			}

			$category = implode(', ', $category);
		}
		else
		{
			$category = '';
		}

		// Format date
		$date = RedeventHelperDate::formatdate($session->dates);

		if (!$session->enddates)
		{
			$displaydate = $date;
		}
		else
		{
			$enddate = RedeventHelperDate::formatdate($session->enddates);
			$displaydate = $date . ' - ' . $enddate;
		}

		// Format time
		if ($session->times)
		{
			$time = RedeventHelperDate::formattime($session->times);
			$displaytime = $time;
		}

		if ($session->endtimes)
		{
			$endtime = RedeventHelperDate::formattime($session->endtimes);
			$displaytime = $time . ' - ' . $endtime;
		}

		// Url link to article
		// & used instead of &amp; as this is converted by feed creator
		$link = RedeventHelperRoute::getDetailsRoute($session->slug, $session->xslug);
		$link = JRoute::_($link);

		// Feed item description text
		$description = JText::_('COM_REDEVENT_TITLE') . ': ' . $title . '<br />';
		$description .= JText::_('COM_REDEVENT_VENUE') . ': ' . $session->venue . ' / ' . $session->city . '<br />';
		$description .= JText::_('COM_REDEVENT_CATEGORY') . ': ' . $category . '<br />';
		$description .= JText::_('COM_REDEVENT_DATE') . ': ' . $displaydate . '<br />';
		$description .= JText::_('COM_REDEVENT_TIME') . ': ' . $displaytime . '<br />';

		@$created = ($session->created ? date('r', strtotime($session->created)) : '');

		// Load individual item creator class
		$item = new JFeedItem;
		$item->title = $title;
		$item->link = $link;
		$item->description = $description;
		$item->date = $created;
		$item->category = $category;

		return $item;
	}
}
