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
 * HTML View class for the Venues View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventViewVenues extends JView
{
	/**
	 * Creates the Venuesview
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		$mainframe = &JFactory::getApplication();

		$document 	= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();

		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// Request variables
		$limitstart		= JRequest::getInt('limitstart');
		$limit			= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$pop			= JRequest::getBool('pop', 0, '', 'int');
		$task 			= JRequest::getWord('task');

		$rows 		= & $this->get('Data');
		$total 		= & $this->get('Total');

		//Add needed scripts if the lightbox effect is enabled
		JHTML::_('behavior.modal');

		//add alternate feed link
		$link    = 'index.php?option=com_redevent&view=venues&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		
		if ( $task == 'archive' ) {
			$pathway->addItem(JText::_('COM_REDEVENT_ARCHIVE' ), JRoute::_('index.php?option=com_redevent&view=venues&task=archive') );
			$pagetitle = $params->get('page_title').' - '.JText::_('COM_REDEVENT_ARCHIVE' );
			$print_link = JRoute::_('index.php?option=com_redevent&view=venues&task=archive&pop=1&tmpl=component');
		} else {
			$pagetitle = $params->get('page_title');
			$print_link = JRoute::_('index.php?option=com_redevent&view=venues&pop=1&tmpl=component');
		}
		
		//Set Page title
		$mainframe->setPageTitle( $pagetitle );
   		$mainframe->addMetaTag( 'title' , $pagetitle );
   		$document->setMetadata('keywords', $pagetitle );


		//Printfunction
		$params->def( 'print', !$mainframe->getCfg( 'hidePrint' ) );
		$params->def( 'icons', $mainframe->getCfg( 'icons' ) );

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

		//Check if the user has access to the form
		$maintainer = ELUser::ismaintainer();
		$genaccess 	= ELUser::validate_user( $elsettings->evdelrec, $elsettings->delivereventsyes );

		if ($maintainer || $genaccess ) $dellink = 1;

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = $this->get('pagination');

		$this->assignRef('rows' , 					$rows);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('limit' , 					$limit);
		$this->assignRef('total' , 					$total);
		$this->assignRef('item' , 					$item);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('task' , 					$task);
		$this->assignRef('pagetitle' , 				$pagetitle);

		parent::display($tpl);
	}
}
