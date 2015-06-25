<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the featured View
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewFeatured extends RedeventViewSessionlist
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
		$params = $mainframe->getParams();

		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('featured.css');
		}

		parent::display($tpl);
	}

	/**
	 * Get feed link
	 *
	 * @return void
	 */
	protected function getFeedLink()
	{
		return 'index.php?option=com_redevent&view=featured&format=feed';
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

		$params->def('page_title', (isset($menuItem->title) ? $menuItem->title : JText::_('COM_REDEVENT_VIEW_TITLE_FEATURED_SESSIONS')));

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
		$this->action = JRoute::_(RedeventHelperRoute::getFeaturedRoute());
	}
}
