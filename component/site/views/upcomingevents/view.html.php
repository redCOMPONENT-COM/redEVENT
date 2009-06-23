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
 * HTML View class for the Upcoming events View
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewUpcomingevents extends JView
{
	/**
	 * Creates the Venueevents View
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		global $mainframe, $option;
		
		//initialize variables
		$document 	= & JFactory::getDocument();
		$menu		= & JSite::getMenu();
		$elsettings = & redEVENTHelper::config();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams('com_redevent');
		$uri 		= & JFactory::getURI();
		$upcomingevents = $this->get('UpcomingEvents');
		
		//add css file
		$document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/eventlist.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');
		
		/* Add rss link */
		$link	= '&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		
		$this->assignRef('upcomingevents' , $upcomingevents);
    $this->assignRef('params' , $params);
		parent::display($tpl);
	}
}
?>