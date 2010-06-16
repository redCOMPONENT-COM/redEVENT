<?php
/**
 * @version 1.0 $Id: view.html.php 1586 2009-11-17 16:39:21Z julien $
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the EventList group ACL screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewGroupacl extends JView {

	function display($tpl = null)
	{
		global $mainframe;

		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$document	= & JFactory::getDocument();
		$user 		= & JFactory::getUser();

		//get vars
		$group_id 			= JRequest::getInt( 'group_id' );

		//add css
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//Get data from the model
		$group        = & $this->get('group');
		$gcategories	= & $this->get('MaintainedCategories');
		$gvenues	    = & $this->get('MaintainedVenues');
		$gvcategories = & $this->get('MaintainedVenuesCategories');

		//build toolbar
		JToolBarHelper::title( JText::_( 'EDIT GROUP ACL' ) .' - '. $group->name, 'groupedit' );
		JToolBarHelper::spacer();
		JToolBarHelper::apply('applyacl');
		JToolBarHelper::save('saveacl');
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancelacl');
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.editgroupacl', true );

		//create selectlists
		$lists = array();
		
		$lists['maintaincategories'] = JHTML::_('select.genericlist', $this->get('CategoriesOptions'), 'maintaincategories[]'
		                                        , 'size="15" multiple="multiple"', 'value', 'text', $gcategories);
		$lists['maintainvenuescategories'] = JHTML::_('select.genericlist', $this->get('VenuesCategoriesOptions'), 'maintainvenuescategories[]'
		                                        , 'size="15" multiple="multiple"', 'value', 'text', $gvcategories);
		$lists['maintainvenues'] = JHTML::_('select.genericlist', $this->get('VenuesOptions'), 'maintainvenues[]'
		                                        , 'size="15" multiple="multiple"', 'value', 'text', $gvenues);
		
		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assign('group_id'      	, $group_id);

		parent::display($tpl);
	}
}
?>