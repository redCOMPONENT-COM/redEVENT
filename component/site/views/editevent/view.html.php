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
			echo JText::_('REDEVENT_LOGIN_TO_SUBMIT_EVENT');
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
			echo JText::_('EDIT EVENT NOT ALLOWED');
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
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');
				
		//Set page title
		$id ? $title = JText::_( 'EDIT EVENT' ) : $title = JText::_( 'ADD EVENT' );

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
		$infoimage = JHTML::_('image', 'components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) );

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
		$lists['categories'] = JHTML::_('select.genericlist', $catoptions, 'categories[]', 'class="inputbox required validate-categories" multiple="multiple" size="10"', 'value', 'text', $selected);

				
    // published state selector
    $published = array( JHTML::_('select.option', '1', JText::_('PUBLISHED')),
                         JHTML::_('select.option', '0', JText::_('UNPUBLISHED')),
                       );
    $lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $row->published);
    
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

		$document->setTitle(JText::_( 'SELECTVENUE' ));
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }

		$filters = array();
		$filters[] = JHTML::_('select.option', '1', JText::_( 'VENUE' ) );
		$filters[] = JHTML::_('select.option', '2', JText::_( 'CITY' ) );
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

		// get xref data
		$xref     = $this->get('SessionDetails');
		$customs  = &$this->get('XrefCustomfields');
		
		// form elements
		$lists = array();

		// events
		if (!empty($xref->title)) {
			$lists['event'] = $xref->title;
		}
		else 
		{
			$events = array();
			$events[] = JHTML::_('select.option', '0', JText::_( 'SELECT EVENT' ) );
			$events= array_merge($events, $this->get('EventOptions'));
			$lists['event'] = JHTML::_('select.genericlist', $events, 'eventid', 'size="1" class="inputbox validate-event"', 'value', 'text', $xref->eventid );
			unset($events);
		}
		
		// venues
		$venues = array();
		$venues[] = JHTML::_('select.option', '0', JText::_( 'SELECT VENUE' ) );
		$venues = array_merge($venues, $this->get('VenueOptions'));
		$lists['venue'] = JHTML::_('select.genericlist', $venues, 'venueid', 'size="1" class="inputbox validate-venue"', 'value', 'text', $xref->venueid );
		unset($venues);
		
		// groups
		$groups = array();
		$groups[] = JHTML::_('select.option', '0', JText::_( 'SELECT GROUP' ) );
		$groups = array_merge($groups, $this->get('GroupOptions'));
		$lists['group'] = JHTML::_('select.genericlist', $groups, 'groupid', 'size="1" class="inputbox"', 'value', 'text', $xref->groupid );
		unset($groups);
		
    // published state selector
    $published = array( JHTML::_('select.option', '1', JText::_('PUBLISHED')),
                         JHTML::_('select.option', '0', JText::_('UNPUBLISHED')),
                       );
    $lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $xref->published);
		
		$this->assignRef('params',       $params);
		$this->assignRef('editor',       $editor);
		$this->assignRef('xref',         $xref);
		$this->assignRef('lists',        $lists);
		$this->assignRef('customfields', $customs);
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