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
		global $mainframe;

		if($this->getLayout() == 'selectvenue') {
			$this->_displayselectvenue($tpl);
			return;
		}

		// Initialize variables
		$editor 	= & JFactory::getEditor();
		$doc 		= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();

		//Get Data from the model
		$row 		= $this->Get('Event');
		$categories	= $this->Get('Categories');

		//Get requests
		$id					= JRequest::getInt('id');

		//Clean output
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'datdescription' );

		JHTML::_('behavior.formvalidation');
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.modal', 'a.modal');

		//add css file
		$doc->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/eventlist.css');
		$doc->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');
		
		/* Add jQuery */
		$doc->addCustomTag( '<script type="text/javascript" src="'.JURI::root().'administrator/components/com_redform/js/jquery.js"></script>' );
		$doc->addCustomTag( '<script type="text/javascript">jQuery.noConflict();</script>' );
		$doc->addCustomTag( '<script type="text/javascript" src="'.JURI::root().'administrator/components/com_redform/js/jquery.random.js"></script>');
		
		//Set page title
		$id ? $title = JText::_( 'EDIT EVENT' ) : $title = JText::_( 'ADD EVENT' );

		$doc->setTitle($title);

		// Get the menu object of the active menu item
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams('com_redevent');

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		if ($item) $pathway->setItemName(1, $item->name);
		$pathway->addItem($title, '');

		//Has the user access to the editor and the add venue screen
		$editoruser = ELUser::editoruser();
		$delloclink = ELUser::validate_user( $elsettings->locdelrec, $elsettings->deliverlocsyes );
		
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
		function elSelectVenue(id, venue) {
			document.getElementById('a_id').value = id;
			document.getElementById('a_name').value = venue;
			document.getElementById('sbox-window').close();
		}";

		$doc->addScriptDeclaration($js);
		// include the recurrence script
		$doc->addScript($url.'components/com_redevent/assets/js/recurrence.js');
		// include the unlimited script
		$doc->addScript($url.'components/com_redevent/assets/js/unlimited.js');

		
		/* Check if a redform ID exists, if so, get the fields */
		if (isset($row->redform_id) && $row->redform_id > 0) {
			$formfields = $this->get('formfields');
			if (!$formfields) $formfields = array();
		}
		
		/* Get a list of redforms */
		$redforms = $this->get('RedForms');
		if ($redforms) $lists['redforms'] = JHTML::_('select.genericlist', $redforms, 'redform_id', '', 'id', 'formname', $row->redform_id );
		
		/* Create venue selection tab */
		$lists['venueselectbox'] = '';
		$venueslist = $this->get('Venues');
		$eventvenue = $this->get('EventVenue');
		
		$infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) );
		foreach ($venueslist as $key => $venue) {
			if (isset($eventvenue[$venue->id])) {
				$lists['venueselectbox'] .= '<div id="locid'.$venue->id.'"><input type="checkbox" name="locid[]" value="'.$venue->id.'"';
				if (isset($eventvenue[$venue->id])) $lists['venueselectbox'] .= ' checked="checked" /> '.$venue->venue;
				$lists['venueselectbox'] .= '<div class="adddatetime"><input type="button" name="adddatetime" value="'.JText::_('ADD_DATE_TIME').'" /></div>';
				$lists['venueselectbox'] .= '<div class="showalldatetime"><input type="button" name="showalldatetime" value="'.JText::_('SHOW_ALL_DATE_TIME').'" /></div>';
				foreach ($eventvenue[$venue->id] as $evkey => $ev) {
					$random = $eventvenue[$venue->id][$evkey]->id;
					$lists['venueselectbox'] .= '<div id="datetimecontainer'.$venue->id.$random.'" style="display: block;"><input type="button" name="removedatetime" value="'.JText::_('SHOW_HIDE_DATE_TIME').'" onClick=\'jQuery("#datetime'.$venue->id.'-'.$eventvenue[$venue->id][$evkey]->id.'").toggle("slideUp"); return false;\'/><input type="button" name="removedatetime" value="'.JText::_('REMOVE_DATE_TIME').'" onClick=\'removeDateTimeFields('.$venue->id.$eventvenue[$venue->id][$evkey]->id.');\'; return false;\'/>';
					$lists['venueselectbox'] .= '<div id="datetime'.$venue->id.'-'.$eventvenue[$venue->id][$evkey]->id.'" style="display: none;">';
					$lists['venueselectbox'] .= '<table class="adminform">';
						/* start date and start time */
						$lists['venueselectbox'] .= '<tr class="row0"><td class="redevent_settings_details">'.JText::_('DATE').'</td><td>'.JHTML::_('calendar', $eventvenue[$venue->id][$evkey]->dates, "locid$venue->id[$random][dates]", "dates$random").'</td>';
						$lists['venueselectbox'] .= '<td>'.JText::_('TIME').'</td><td><input type="text" name="locid'.$venue->id.'['.$random.'][times]" value="'.$eventvenue[$venue->id][$evkey]->times.'" /></td></tr>';
						/* End date and end time */
						$lists['venueselectbox'] .= '<tr class="row1"><td>'.JText::_('ENDDATE').'</td><td>'.JHTML::_('calendar', $eventvenue[$venue->id][$evkey]->enddates, "locid$venue->id[$random][enddates]", "enddates$random").'</td>';
						$lists['venueselectbox'] .= '<td>'.JText::_('ENDTIMES').'</td><td><input type="text" name="locid'.$venue->id.'['.$random.'][endtimes]" value="'.$eventvenue[$venue->id][$evkey]->endtimes.'" /></td></tr>';
						/* Attendees and waitinglist */
						$lists['venueselectbox'] .= '<tr class="row0"><td><span class="editlinktip hasTip" title="'.JText::_( 'MAXIMUM_ATTENDEES' ).'::'.JText::_('MAXIMUM_ATTENDEES_TIP').'"'.$infoimage.'</span>'.JText::_('MAXIMUM_ATTENDEES').'</td><td><input class="inputbox" name="locid'.$venue->id.'['.$random.'][maxattendees]" value="'.$eventvenue[$venue->id][$evkey]->maxattendees.'" size="15" maxlength="8" /></td>';
						$lists['venueselectbox'] .= '<td><span class="editlinktip hasTip" title="'.JText::_( 'MAXIMUM_WAITINGLIST' ).'::'.JText::_('MAXIMUM_WAITINGLIST_TIP').'"'.$infoimage.'</span>'.JText::_('MAXIMUM_WAITINGLIST').'</td><td><input class="inputbox" name="locid'.$venue->id.'['.$random.'][maxwaitinglist]" value="'.$eventvenue[$venue->id][$evkey]->maxwaitinglist.'" size="15" maxlength="8" /></td></tr>';
						/* Course price and credit */
						$lists['venueselectbox'] .= '<tr class="row1"><td>'.JText::_('COURSE_PRICE').'</td><td><input class="inputbox" name="locid'.$venue->id.'['.$random.'][course_price]" value="'.$eventvenue[$venue->id][$evkey]->course_price.'" size="15" maxlength="8" /></td>';
						$lists['venueselectbox'] .= '<td>'.JText::_('COURSE_CREDIT').'</td><td><input class="inputbox" name="locid'.$venue->id.'['.$random.'][course_credit]" value="'.$eventvenue[$venue->id][$evkey]->course_credit.'" size="15" maxlength="8" /></td></tr>';
					$lists['venueselectbox'] .= '</table>';
					$lists['venueselectbox'] .= '</div></div>';
				}
				$lists['venueselectbox'] .= '</div>';
			}
			else {
				$lists['venueselectbox'] .= '<div id="locid'.$venue->id.'"><input type="checkbox" name="locid[]" value="'.$venue->id.'"';
				$lists['venueselectbox'] .= ' />'.$venue->venue;   
				$lists['venueselectbox'] .= '<div class="adddatetime" style="display: none;"><input type="button" name="adddatetime" value="'.JText::_('ADD_DATE_TIME').'" /></div>';
				$lists['venueselectbox'] .= '<div class="showalldatetime" style="display: none;"><input type="button" name="showalldatetime" value="'.JText::_('SHOW_ALL_DATE_TIME').'" /></div>';
				$lists['venueselectbox'] .= '</div>';
			}
		}
		
		
		$this->assignRef('row' , 					$row);
		$this->assignRef('categories' , 			$categories);
		$this->assignRef('editor' , 				$editor);
		$this->assignRef('dimage' , 				$dimage);
		$this->assignRef('infoimage' , 				$infoimage);
		$this->assignRef('delloclink' , 			$delloclink);
		$this->assignRef('editoruser' , 			$editoruser);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);
		$this->assignRef('params' , 				$params);
		$this->assignRef('formfields'	, $formfields);
		$this->assignRef('lists'	, $lists);
		
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
		$document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/eventlist.css');

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
}
?>