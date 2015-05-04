<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Categoriesdetailed View
 *
 * @package    Joomla
 * @subpackage redEVENT
 * @since      0.9
 */
class RedeventViewCategoriesdetailed extends RViewSite
{
	/**
	 * Creates the Categoriesdetailed View
	 *
	 * @since 0.9
	 */
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		//initialise variables
		$document = JFactory::getDocument();
		$elsettings = RedeventHelper::config();
		$model = $this->getModel();
		$menu = $mainframe->getMenu();
		$item = $menu->getActive();
		$params = $mainframe->getParams();

		//get vars
		$pathway = $mainframe->getPathWay();
		$pop = JRequest::getBool('pop');
		$task = JRequest::getWord('task');

		//Get data from the model
		$categories = $this->get('Data');
		$customs = $this->get('ListCustomFields');
		$pageNav = $this->get('pagination');

		//add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$params->def('page_title', $item->title);

		if ($task == 'archive')
		{
			$pathway->addItem(JText::_('COM_REDEVENT_ARCHIVE'), JRoute::_(RedeventHelperRoute::getCategoriesDetailedRoute(null, 'archive')));
			$print_link = JRoute::_(RedeventHelperRoute::getCategoriesDetailedRoute(null, 'archive') . '&pop=1&tmpl=component');
			$pagetitle = $params->get('page_title') . ' - ' . JText::_('COM_REDEVENT_ARCHIVE');
		}
		else
		{
			$print_link = JRoute::_(RedeventHelperRoute::getCategoriesDetailedRoute() . '&pop=1&tmpl=component');
			$pagetitle = $params->get('page_title');
		}
		//set Page title
		$this->document->setTitle($pagetitle);
		$document->setMetadata('keywords', $pagetitle);

		//Print
		$params->def('print', !$mainframe->getCfg('hidePrint'));
		$params->def('icons', $mainframe->getCfg('icons'));

		if ($pop)
		{
			$params->set('popup', 1);
		}

		//Check if the user has access to the form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		// Create the pagination object
		jimport('joomla.html.pagination');

		$this->assignRef('categories', $categories);
		$this->assignRef('customs', $customs);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('params', $params);
		$this->assignRef('dellink', $dellink);
		$this->assignRef('item', $item);
		$this->assignRef('model', $model);
		$this->assignRef('pageNav', $pageNav);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('task', $task);
		$this->assignRef('pagetitle', $pagetitle);

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns', $cols);

		parent::display($tpl);
	}
}
