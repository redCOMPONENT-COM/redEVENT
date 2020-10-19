<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Categoriesdetailed View
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewCategoriesdetailed extends RedeventViewFront
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

		// Initialise variables
		$model = $this->getModel();
		$params = $mainframe->getParams();

		// Get data from the model
		$categories = $this->get('Data');
		$customs = $this->get('ListCustomFields');
		$pageNav = $this->get('pagination');

		$print_link = JRoute::_(RedeventHelperRoute::getCategoriesDetailedRoute() . '&pop=1&tmpl=component');

		// Print
		$params->def('print', !$mainframe->getCfg('hidePrint'));
		$params->def('icons', $mainframe->getCfg('icons'));

		// Check if the user has access to the create form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		$this->assignRef('categories', $categories);
		$this->assignRef('customs', $customs);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('dellink', $dellink);
		$this->assignRef('model', $model);
		$this->assignRef('pageNav', $pageNav);

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns', $cols);

		return parent::display($tpl);
	}
}
