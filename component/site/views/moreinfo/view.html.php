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
 * HTML View class for the moreinfo View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventViewMoreinfo extends JView
{
	/**
	 * Creates the output
	 *
	 * @since 0.5
	 * @param int $tpl
	 */
	function display( $tpl=null )
	{			
		$params = JComponentHelper::getParams('com_redevent');
		if (!$params->get('enable_moreinfo', 1)) {
			echo Jtext::_('COM_REDEVENT_MOREINFO_ERROR_DISABLED_BY_ADMIN');
			return;
		}
    if ($this->getLayout() == 'final')
    {
    	return $this->_displayFinal($tpl);
    }
    
    $xref     = JRequest::getInt('xref');
    $uri      = &JFactory::getUri();
    $document = JFactory::getDocument();
    $user     = &Jfactory::getUser();
    
    if (!$xref) {
    	echo JText::_('COM_REDEVENT_MOREINFO_ERROR_MISSING_XREF');
    }
		
    $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/moreinfo.css');
    
    
    $this->assign('xref',   $xref);
    $this->assign('action', $uri->toString());
    $this->assignRef('user', $user);
		
		parent::display($tpl);
	}
		
	function _displayFinal( $tpl=null )
	{
		parent::display($tpl);		
	}
}
