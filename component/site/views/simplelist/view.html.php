<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML events list View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewSimpleList extends RedeventViewSessionlist
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
		$this->assign('thumb_link', RedeventHelperRoute::getSimpleListRoute(null, 'thumb'));
		$this->assign('list_link', RedeventHelperRoute::getSimpleListRoute(null, 'default'));
		$this->assign('dellink', JFactory::getUser()->authorise('re.createevent'));

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

		$params->def('page_title', (isset($menuItem->title) ? $menuItem->title : JText::_('COM_REDEVENT_Events')));

		return $params->get('page_title');
	}
}
