<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Upcoming events View
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewUpcomingevents extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// Initialize variables
		$document = JFactory::getDocument();
		$menu = $mainframe->getMenu();
		$elsettings = RedeventHelper::config();
		$item = $menu->getActive();
		$params = $mainframe->getParams('com_redevent');
		$uri = JFactory::getURI();
		$upcomingevents = $this->get('UpcomingEvents');

		// Add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$params->def('page_title', JText::_('COM_REDEVENT_UPCOMING_EVENTS_TITLE'));

		/* Add rss link */
		$link = '&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);

		$this->assignRef('upcomingevents', $upcomingevents);
		$this->assignRef('params', $params);
		$this->assign('action', JRoute::_('index.php?option=com_redevent&view=upcomingevents'));

		return parent::display($tpl);
	}
}
