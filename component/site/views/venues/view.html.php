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
 * @since    0.9
 */
class RedeventViewVenues extends RedeventViewFront
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

		$rows = $this->get('Data');
		$total = $this->get('Total');

		// Add needed scripts if the lightbox effect is enabled
		JHTML::_('behavior.modal');

		$print_link = JRoute::_('index.php?option=com_redevent&view=venues&pop=1&tmpl=component');

		// Print function
		$params->def('print', !$mainframe->getCfg('hidePrint'));
		$params->def('icons', $mainframe->getCfg('icons'));

		// Check if the user has access to the create form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = $this->get('pagination');

		$this->assignRef('rows', $rows);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('dellink', $dellink);
		$this->assignRef('pageNav', $pageNav);
		$this->assignRef('limit', $limit);
		$this->assignRef('total', $total);

		return parent::display($tpl);
	}

	/**
	 * Get feed link
	 *
	 * @return void
	 */
	protected function getFeedLink()
	{
		return 'index.php?option=com_redevent&view=venues&format=feed';
	}
}
