<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

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
		$document = JFactory::getDocument();

		// Load event details
		$row = $this->get('Details');

		$document->setTitle($this->escape(RedeventHelper::getSessionFullTitle($row)));
		$document->setDescription('');

		$link = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug));

		// Load individual item creator class
		$item = new JFeedItem;
		$item->title = RedeventHelper::getSessionFullTitle($row);
		$item->link = JRoute::_($link);
		$item->description = $row->summary;
		$item->date = '';
		$item->category = '';

		// Loads item info into rss array
		$document->addItem($item);
	}
}
