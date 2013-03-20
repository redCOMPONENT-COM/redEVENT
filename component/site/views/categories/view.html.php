<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Categories View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventViewCategories extends JView
{
	function display( $tpl=null )
	{
		$mainframe = &JFactory::getApplication();

		$document 	= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();
		$params   = & $mainframe->getParams();

		$rows 		= & $this->get('Data');
		$total 		= & $this->get('Total');

		//add css file
		if (!$params->get('custom_css')) {
			$document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
		}
		else {
			$document->addStyleSheet($params->get('custom_css'));
		}
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams('com_redevent');

		// Request variables
		$limitstart		= JRequest::getInt('limitstart');
		$limit			= JRequest::getInt('limit', $params->get('cat_num'));
		$task			= JRequest::getWord('task');

		$params->def( 'page_title', $item->title);

		//pathway
		$pathway 	= & $mainframe->getPathWay();

		if ( $task == 'archive' ) {
			$pathway->addItem(JText::_('COM_REDEVENT_ARCHIVE' ), JRoute::_(RedeventHelperRoute::getCategoriesRoute(null, 'archive')) );
			$pagetitle = $params->get('page_title').' - '.JText::_('COM_REDEVENT_ARCHIVE' );
		} else {
			$pagetitle = $params->get('page_title');
		}

		//Set Page title
		$this->document->setTitle($pagetitle);

		//get icon settings
		$params->def( 'icons', $mainframe->getCfg( 'icons' ) );

		//add alternate feed link
		$link    = RedeventHelperRoute::getSimpleListRoute();
// 		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
// 		$document->addHeadLink(JRoute::_($link.'&format=feed&type=rss'), 'alternate', 'rel', $attribs);
// 		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
// 		$document->addHeadLink(JRoute::_($link.'&format=feed&type=atom'), 'alternate', 'rel', $attribs);

		//Check if the user has access to the form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		// Create the pagination object
		jimport('joomla.html.pagination');

		$pageNav = new JPagination($total, $limitstart, $limit);

		$this->assignRef('rows' , 					$rows);
		$this->assignRef('task' , 					$task);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('item' , 					$item);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('pagetitle' , 				$pagetitle);

		parent::display($tpl);
	}
}
