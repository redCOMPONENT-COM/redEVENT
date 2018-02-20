<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Categories View class
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewCategories extends RedeventViewFront
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

		$app = JFactory::getApplication();

		$rows = $this->get('Items');

		$params = $app->getParams('com_redevent');

		// Get icon settings
		$params->def('icons', $app->getCfg('icons'));

		// Check if the user has access to the form
		$canCreate = JFactory::getUser()->authorise('re.createevent');

		$pageNav = $this->get('Pagination');

		$this->assignRef('rows', $rows);
		$this->assignRef('canCreate', $canCreate);
		$this->assignRef('pageNav', $pageNav);

		return parent::display($tpl);
	}
}
