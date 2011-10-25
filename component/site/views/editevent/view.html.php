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
 * HTML View class for the EditeventView
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewEditevent extends JView
{
	/**
	 * Creates the output for event submissions
	 *
	 * @since 0.4
	 *
	 */
	function display( $tpl=null )
	{
		$mainframe = & JFactory::getApplication();
		$user      = & JFactory::getUser();

		if (!$user->get('id')) {
			echo JText::_('COM_REDEVENT_LOGIN_TO_SUBMIT_EVENT');
			return;			
		}
		
		if ($this->getLayout() == 'selectvenue') {
			$this->_displayselectvenue($tpl);
			return;
		}
		else if($this->getLayout() == 'eventdate') {
			$this->_displayEventdate($tpl);
			return;
		}

		$useracl = &UserAcl::getInstance();
		if (!$useracl->canAddEvent()) 
		{
			echo JText::_('COM_REDEVENT_EDIT_EVENT_NOT_ALLOWED');
			return;
		}

		// Initialize variables
		$editor 	  = & JFactory::getEditor();
		$document 	= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();
    $params     = & $mainframe->getParams();

		//Get Data from the model
		$row 		  = &$this->get('Event');
		$customs  = &$this->get('Customfields');
		$xcustoms = &$this->get('XrefCustomfields');
    $roles    = &$this->get('SessionRoles');
    $prices   = &$this->get('SessionPrices');
    
		//Get requests
		$id					= JRequest::getInt('id');

		//Clean output
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'datdescription' );

		JHTML::_('behavior.formvalidation');
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.modal', 'a.vmodal');

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/editevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');
		
		$document->addScript('components/com_redevent/assets/js/attachments.js' );
		$document->addScriptDeclaration('var removemsg = "'.JText::_('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG').'";' );
				
    $document->addScript('components/com_redevent/assets/js/xref_roles.js');
    $document->addScriptDeclaration('var txt_remove = "'.JText::_('COM_REDEVENT_REMOVE').'";');
    $document->addScript('components/com_redevent/assets/js/xref_prices.js');
    
		//Set page title
		$id ? $title = $row->title.' - '.JText::_('COM_REDEVENT_EDIT_EVENT' ) : $title = JText::_('COM_REDEVENT_ADD_EVENT' );
		$document->setTitle($title);

		// Get the menu object of the active menu item
		$menu	  = & JSite::getMenu();
		$item   = $menu->getActive();
		$params = & $mainframe->getParams('com_redevent');

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		$pathway->addItem($title, '');

		//Has the user access to the editor and the add venue screen
		$editoruser = $params->get('edit_description_allow_editor', 0) || ELUser::editoruser();
		
		$canpublish = $useracl->canPublishEvent($id);
		
		//transform <br /> and <br> back to \r\n for non editorusers
		if (!$editoruser) {
			$row->datdescription = redEVENTHelper::br2break($row->datdescription);
		}

		//Get image information
		$dimage = redEVENTImage::flyercreator($row->datimage, 'event');

		//Set the info image
		$infoimage = JHTML::_('image', 'components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) );

		//Create the stuff required for the venueselect functionality
		$url	= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$js = "
		function reSelectVenue(id, venue) {
			document.getElementById('a_id').value = id;
			document.getElementById('a_name').value = venue;
			document.getElementById('sbox-window').close();
		}";

		$document->addScriptDeclaration($js);
								
		// categories selector
		$selected = array();
		foreach ((array)$row->categories as $cat) {
			$selected[] = $cat->id;
		}
		$catoptions = $this->get('CategoryOptions');
		if (!$catoptions) {
			echo JText::_('COM_REDEVENT_EDITEVENT_FORBIDDEN_NO_CATEGORY_AVAILABLE');
			return;
		}
		$lists['categories'] = JHTML::_('select.genericlist', $catoptions, 'categories[]', 'class="inputbox required validate-categories" multiple="multiple" size="10"', 'value', 'text', $selected);
		
		if ($params->get('edit_recurrence', 0))
		{
			$document->addScript('components/com_redevent/assets/js/xref_recurrence.js' );
			
			// Recurrence selector
			$recur_type = array( JHTML::_('select.option', 'NONE', JText::_('COM_REDEVENT_NO_REPEAT')),
			JHTML::_('select.option', 'DAILY', JText::_('COM_REDEVENT_DAILY')),
			                         JHTML::_('select.option', 'WEEKLY', JText::_('COM_REDEVENT_WEEKLY')),
			                         JHTML::_('select.option', 'MONTHLY', JText::_('COM_REDEVENT_MONTHLY')),
			                         JHTML::_('select.option', 'YEARLY', JText::_('COM_REDEVENT_YEARLY'))
			                       );
			$lists['recurrence_type'] = JHTML::_('select.radiolist', $recur_type, 'recurrence_type', '', 'value', 'text', ($row->rrules->type ? $row->rrules->type : 'NONE'));
		}
		
    // published state selector
    $published = array( JHTML::_('select.option', '1', JText::_('COM_REDEVENT_PUBLISHED')),
                         JHTML::_('select.option', '0', JText::_('COM_REDEVENT_UNPUBLISHED')),
                       );
    $lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $row->published);
    
		$rolesoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_role')));
		$rolesoptions = array_merge($rolesoptions, $this->get('RolesOptions'));
		
		$pricegroupsoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_PRICEGROUP')));
		$pricegroupsoptions = array_merge($pricegroupsoptions, $this->get('PricegroupsOptions'));
		
		$this->assignRef('row',        $row);
		$this->assignRef('customs',    $customs);
		$this->assignRef('xcustoms',   $xcustoms);
		$this->assignRef('categories', $categories);
		$this->assignRef('editor',     $editor);
		$this->assignRef('dimage',     $dimage);
		$this->assignRef('infoimage',  $infoimage);
		$this->assignRef('editoruser', $editoruser);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('item',       $item);
		$this->assignRef('params',     $params);
		$this->assignRef('lists',      $lists);
		$this->assignRef('canpublish', $canpublish);
		$this->assignRef('referer',    JRequest::getWord('referer'));
		$this->assign('title',         $title);
		$this->assignRef('access'	, redEVENTHelper::getAccesslevelOptions());
		$this->assignRef('roles'        , $roles);
		$this->assignRef('rolesoptions' , $rolesoptions);
		$this->assignRef('prices'       , $prices);
		$this->assignRef('pricegroupsoptions' , $pricegroupsoptions);
		
		parent::display($tpl);

	}

	/**
	 * Creates the output for the venue select listing
	 *
	 * @since 0.9
	 *
	 */
	function _displayselectvenue($tpl)
	{
		global $mainframe;

		$document	= & JFactory::getDocument();
		$params 	= & $mainframe->getParams();

		$limitstart			= JRequest::getVar('limitstart', 0, '', 'int');
		$limit				= $mainframe->getUserStateFromRequest('com_redevent.selectvenue.limit', 'limit', $params->def('display_num', 0), 'int');
		$filter_order		= JRequest::getCmd('filter_order', 'l.venue');
		$filter_order_Dir	= JRequest::getWord('filter_order_Dir', 'ASC');;
		$filter				= JRequest::getString('filter');
		$filter_type		= JRequest::getInt('filter_type');

		// Get/Create the model
		$rows 	= $this->get('Venues');
		$total 	= $this->get('Countitems');
		
		JHTML::_('behavior.modal', 'a.modal');

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		// table ordering
		$lists['order_Dir'] 	= $filter_order_Dir;
		$lists['order'] 		= $filter_order;

		$document->setTitle(JText::_('COM_REDEVENT_SELECTVENUE' ));
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }

		$filters = array();
		$filters[] = JHTML::_('select.option', '1', JText::_('COM_REDEVENT_VENUE' ) );
		$filters[] = JHTML::_('select.option', '2', JText::_('COM_REDEVENT_CITY' ) );
		$searchfilter = JHTML::_('select.genericlist', $filters, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type );

		$this->assignRef('rows' , 				$rows);
		$this->assignRef('searchfilter' , 		$searchfilter);
		$this->assignRef('pageNav' , 			$pageNav);
		$this->assignRef('lists' , 				$lists);
		$this->assignRef('filter' , 			$filter);


		parent::display($tpl);
	}
	
	function _displayEventdate($tpl = null)
	{
		$mainframe = &Jfactory::getApplication();

		$document	= & JFactory::getDocument();
		$params 	= & $mainframe->getParams();
		
		$editor 	= & JFactory::getEditor();
//		echo '<pre>';print_r($this); echo '</pre>';exit;
				
		JHTML::_('behavior.mootools');

    $document->addScript('components/com_redevent/assets/js/xref_roles.js');
    $document->addScriptDeclaration('var txt_remove = "'.JText::_('COM_REDEVENT_REMOVE').'";');
    $document->addScript('components/com_redevent/assets/js/xref_prices.js');
    $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/editevent.css');
    
		// get xref data
		$xref     = &$this->get('SessionDetails');
		$customs  = &$this->get('XrefCustomfields');
    $roles    = &$this->get('SessionRoles');
    $prices   = &$this->get('SessionPrices');
		
		// form elements
		$lists = array();

		// events
		if ($xref->eventid) {
			$lists['event'] = $xref->event_title;
		}
		else 
		{
			$events = array();
			$events[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_SELECT_EVENT' ) );
			$events= array_merge($events, $this->get('EventOptions'));
			$lists['event'] = JHTML::_('select.genericlist', $events, 'eventid', 'size="1" class="inputbox validate-event"', 'value', 'text', $xref->eventid );
			unset($events);
		}
		
		// venues
		$venues = array();
		$venues[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_SELECT_VENUE' ) );
		$venues = array_merge($venues, $this->get('VenueOptions'));
		$lists['venue'] = JHTML::_('select.genericlist', $venues, 'venueid', 'size="1" class="inputbox validate-venue"', 'value', 'text', $xref->venueid );
		unset($venues);
		
		// groups
		$groups = array();
		$groups[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_SELECT_GROUP' ) );
		$groups = array_merge($groups, $this->get('GroupOptions'));
		$lists['group'] = JHTML::_('select.genericlist', $groups, 'groupid', 'size="1" class="inputbox"', 'value', 'text', $xref->groupid );
		unset($groups);
		
    // published state selector
    $published = array( JHTML::_('select.option', '1', JText::_('COM_REDEVENT_PUBLISHED')),
                         JHTML::_('select.option', '0', JText::_('COM_REDEVENT_UNPUBLISHED')),
                       );
    $lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $xref->published);
		
		$rolesoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_role')));
		$rolesoptions = array_merge($rolesoptions, $this->get('RolesOptions'));
		
		$pricegroupsoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_PRICEGROUP')));
		$pricegroupsoptions = array_merge($pricegroupsoptions, $this->get('PricegroupsOptions'));
		
		if ($params->get('edit_recurrence', 0))
		{ 
			$document->addScript('components/com_redevent/assets/js/xref_recurrence.js' );
			// Recurrence selector
			$recur_type = array( JHTML::_('select.option', 'NONE', JText::_('COM_REDEVENT_NO_REPEAT')),
			JHTML::_('select.option', 'DAILY', JText::_('COM_REDEVENT_DAILY')),
			JHTML::_('select.option', 'WEEKLY', JText::_('COM_REDEVENT_WEEKLY')),
					                         JHTML::_('select.option', 'MONTHLY', JText::_('COM_REDEVENT_MONTHLY')),
					                         JHTML::_('select.option', 'YEARLY', JText::_('COM_REDEVENT_YEARLY'))
					                       );
			$lists['recurrence_type'] = JHTML::_('select.radiolist', $recur_type, 'recurrence_type', '', 'value', 'text', ($xref->rrules->type ? $xref->rrules->type : 'NONE'));
		}
		
		$this->assignRef('params',       $params);
		$this->assignRef('editor',       $editor);
		$this->assignRef('xref',         $xref);
		$this->assignRef('lists',        $lists);
		$this->assignRef('customfields', $customs);
		$this->assignRef('roles'        , $roles);
		$this->assignRef('rolesoptions' , $rolesoptions);
		$this->assignRef('prices'       , $prices);
		$this->assignRef('pricegroupsoptions' , $pricegroupsoptions);
		
		parent::display($tpl);
	}
	

  /**
   * Displays a calendar control field
   *
   * @param string  The date value
   * @param string  The name of the text field
   * @param string  The id of the text field
   * @param string  The date format
   * @param array Additional html attributes
   */
  function calendar($value, $name, $id, $format = '%Y-%m-%d', $onUpdate = null, $attribs = null)
  {
    JHTML::_('behavior.calendar'); //load the calendar behavior

    if (is_array($attribs)) {
      $attribs = JArrayHelper::toString( $attribs );
    }
    $document =& JFactory::getDocument();
    $document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
        inputField     :    "'.$id.'",     // id of the input field
        ifFormat       :    "'.$format.'",      // format of the input field
        button         :    "'.$id.'_img",  // trigger for the calendar (button ID)
        align          :    "Tl",           // alignment (defaults to "Bl")
        onUpdate       :    '.($onUpdate ? $onUpdate : 'null').',
        singleClick    :    true
    });});');

    return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" '.$attribs.' />'.
         '<img class="calendar" src="'.JURI::root(true).'/templates/system/images/calendar.png" alt="calendar" id="'.$id.'_img" />';
  }
}
?>