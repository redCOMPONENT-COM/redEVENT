<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML archive View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewArchive extends RedeventViewSessionlist
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
		$this->prepareView();

		$mainframe = JFactory::getApplication();
		$params = $mainframe->getParams();

		$list_link = RedeventHelperRoute::getSimpleListRoute();
		$print_link = JRoute::_(RedeventHelperRoute::getArchiveRoute() . '&pop=1');

		$this->assignRef('print_link', $print_link);
		$this->assignRef('list_link', $list_link);

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns', $cols);

		parent::display($tpl);
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

		$params->def('page_title', (isset($menuItem->title) ? $menuItem->title : JText::_('COM_REDEVENT_ARCHIVED_Events')));

		return $params->get('page_title');
	}
}
