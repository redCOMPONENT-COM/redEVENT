<?php
/**
 * @version 1.0 $Id: archive.php 30 2009-05-08 10:22:21Z roland $
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the redevent component
 *
 * @static
 * @package		redevent
 * @since 2.0
 */
class RedeventViewPricegroup extends JView
{
	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();

		if($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		//get the object
		$object =& $this->get('data');
		
		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		
		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();
		$url 		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		
    $document = & JFactory::getDocument();
    $document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		$lists = array();
		//get the project
		$object	=& $this->get('data');
		$isNew  = ($object->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'COM_REDEVENT_DESCBEINGEDITTED', JText::_( 'COM_REDEVENT_PRICEGROUPS_PRICEGROUP' ), $object->name );
			$mainframe->redirect( 'index.php?option='. $option, $msg );
		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout( $user->get('id') );
		}
		else
		{
			// initialise new record
			//$season->published = 1;
			$object->order 	= 0;
		}
		  
		// build the html select list for ordering
		$query = 'SELECT ordering AS value, name AS text'
			. ' FROM #__redevent_pricegroups'
			. ' ORDER BY ordering';

		$lists['ordering'] 			= JHTML::_('list.specificordering',  $object, $object->id, $query, 1 );
						
		$this->assignRef('lists',		$lists);
		$this->assignRef('object',		$object);

		parent::display($tpl);
	}
}
