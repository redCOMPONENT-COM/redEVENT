<?php
/**
 * @version 1.0 $Id: view.html.php 369 2009-07-01 17:41:32Z julien $
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
 * HTML Details View class of the redEVENT component
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventViewDetails extends JView
{
	var $_eventlinks = null;
	/**
	 * Creates the output for the details view
	 *
 	 * @since 0.9
	 */
	function display($tpl = null)
	{
		$mainframe = & JFactory::getApplication();		
    $document   = JFactory::getDocument();
		
    // load event details
    $row    = $this->get('Details');
    $xreflinks = $this->get('XrefLinks');
    $this->_eventlinks = $xreflinks;
        
    $document->setTitle($this->escape($row->full_title));
    $document->setDescription('');
    
    ob_start();
    $this->setLayout('courseinfo_rss');
    parent::display();
    $contents = ob_get_contents();
    ob_end_clean();
    
    $link = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref));
    
		// load individual item creator class
		$item = new JFeedItem();
		$item->title    = $row->full_title;
		$item->link     = JRoute::_($link);
		$item->description  = $contents;
		$item->date     = '';
		$item->category     = '';

		// loads item info into rss array
		$document->addItem( $item );
	}
}
?>