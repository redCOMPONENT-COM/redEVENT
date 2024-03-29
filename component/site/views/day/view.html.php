<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the day View
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewDay extends RedeventViewSessionlist
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
		$settings = RedeventHelper::config();

		$day = $this->get('Day');

		$daydate = RedeventHelperDate::formatdate($day);

		$print_link = JRoute::_('index.php?view=day&tmpl=component&pop=1');

		$pathway = $app->getPathWay();
		$pathway->addItem($daydate, '');

		// Check if the user has access to the form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		$this->print_link = $print_link;
		$this->dellink = $dellink;
		$this->daydate = $daydate;
		$this->dayId = $app->input->getString('id');

		return parent::display($tpl);
	}

	/**
	 * Prepare form action
	 *
	 * @return void
	 */
	protected function prepareAction()
	{
		parent::prepareAction();
		$this->action = JRoute::_(RedeventHelperRoute::getDayRoute(JFactory::getApplication()->input->getString('id')));
	}
}
