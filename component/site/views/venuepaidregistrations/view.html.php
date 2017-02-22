<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML venues View class
 *
 * @package  Redevent.Site
 * @since    3.2.3
 */
class RedeventViewVenuepaidregistrations extends RedeventViewFront
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

		// Request variables
		$limit = $this->state->get('limit');

		$rows = $this->get('Items');
		$total = $this->get('Total');

		$pageNav = $this->get('pagination');

		$this->assignRef('rows', $rows);
		$this->assignRef('pageNav', $pageNav);
		$this->assignRef('limit', $limit);
		$this->assignRef('total', $total);

		parent::display($tpl);
	}
}
