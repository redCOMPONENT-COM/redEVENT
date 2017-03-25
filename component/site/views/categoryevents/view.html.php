<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Categoryevents View
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewCategoryevents extends RedeventViewSessionlist
{
	protected $category;

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
		$document = JFactory::getDocument();
		$pathway = $app->getPathWay();

		$category = $this->get('Item');

		if (!$category->id)
		{
			throw new RuntimeException(JText::sprintf('COM_REDEVENT_Category_d_not_found', $category->id), 0);
		}

		$document->setMetadata('keywords', $category->meta_keywords);
		$document->setDescription(strip_tags($category->meta_description));

		$link = RedeventHelperRoute::getCategoryEventsRoute($category->slug);
		$print_link = JRoute::_($link . '&pop=1&tmpl=component');

		$thumb_link = RedeventHelperRoute::getCategoryEventsRoute($category->slug, null, 'thumb');
		$list_link = RedeventHelperRoute::getCategoryEventsRoute($category->slug, null, 'default');

		// Check if the user has access to the edit form
		$dellink = JFactory::getUser()->authorise('re.manageevents', $category->asset_name);

		// Generate Categorydescription
		if (empty($category->description))
		{
			$description = JText::_('COM_REDEVENT_NO_DESCRIPTION');
		}
		else
		{
			$description = JHTML::_('content.prepare', $category->description);
		}

		$this->assignRef('category', $category);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('dellink', $dellink);
		$this->assignRef('description', $description);
		$this->assignRef('thumb_link', $thumb_link);
		$this->assignRef('list_link', $list_link);

		return parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$app = JFactory::getApplication();
		$menuItem = $app->getMenu()->getActive();
		$params = $app->getParams();

		$params->def('page_title', (isset($menuItem->title) ? $menuItem->title : $this->category->name));

		return $params->get('page_title');
	}

	/**
	 * Prepare form action
	 *
	 * @return void
	 */
	protected function prepareAction()
	{
		parent::prepareAction();

		$this->assign('action', JRoute::_(RedeventHelperRoute::getCategoryEventsRoute($this->category->id)));
	}

	/**
	 * Get feed link
	 *
	 * @return string
	 */
	protected function getFeedLink()
	{
		return RedeventHelperRoute::getCategoryEventsRoute($this->category->slug) . '&format=feed';
	}

	/**
	 * Method to build the sort lists
	 *
	 * @return void
	 */
	protected function buildSortLists()
	{
		parent::buildSortLists();

		// We don't want the category filter...
		unset($this->lists['categoryfilter']);
	}
}
