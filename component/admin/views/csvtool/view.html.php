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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the EventList categoryelement screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewCsvtool extends JView {

	function display($tpl = null)
	{
		global $mainframe, $option;

		//initialise variables
		$document	= & JFactory::getDocument();
		$db			= & JFactory::getDBO();
    $url    = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		
		JHTML::_('behavior.mootools');

		//prepare document
		$document->setTitle(JText::_( 'COM_REDEVENT_TOOLS_CSV'));
		
		//add css to document
		$document->addStyleSheet($url.'/administrator/components/com_redevent/assets/css/redeventbackend.css');
		$document->addStyleSheet($url.'/administrator/components/com_redevent/assets/css/csvtool.css');
		
		// js
		$document->addScript($url.'/administrator/components/com_redevent/assets/js/csvtool.js');
		
		//Create Submenu
    ELAdmin::setMenu();

		//create the toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_TOOLS_CSV' ), 'tools' );
		JToolBarHelper::back();

		$lists = array();
		
		$forms = $this->get('FormOptions');
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_TOOLS_CSV_SELECT_FORM')));
		$options = array_merge($options, $forms);
		$lists['form_filter'] = JHTML::_('select.genericlist', $options, 'form_filter');
		
		$forms = ELAdmin::getCategoriesOptions();
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_TOOLS_CSV_SELECT_CATEGORY')));
		$options = array_merge($options, $forms);
		$lists['category_filter'] = JHTML::_('select.genericlist', $options, 'category_filter');
		
		$forms = ELAdmin::getVenuesOptions();
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_TOOLS_CSV_SELECT_VENUE')));
		$options = array_merge($options, $forms);
		$lists['venue_filter'] = JHTML::_('select.genericlist', $options, 'venue_filter');
		
		$this->assignRef('lists', $lists);
		
		parent::display($tpl);
	}
}
?>