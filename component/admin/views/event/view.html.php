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
 * View class for the EventList event screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewEvent extends JView {

	function display($tpl = null)
	{
		global $mainframe;

		if($this->getLayout() == 'addvenue') {
			$this->_displayaddvenue($tpl);
			return;
		}

		//Load behavior
		jimport('joomla.html.pane');
		JHTML::_('behavior.tooltip');
    JHTML::_('behavior.formvalidation');
		require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'helper.php');
		require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'output.class.php');
		
		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$pane 		= & JPane::getInstance('tabs');
		$user 		= & JFactory::getUser();
		$elsettings = ELAdmin::config();

		//get vars
		$cid		= JRequest::getVar( 'cid' );
		$task		= JRequest::getVar('task');
		$url 		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		//add the custom stylesheet and the seo javascript
		$document->addStyleSheet($url.'administrator/components/com_redevent/assets/css/redeventbackend.css');
		$document->addScript($url.'administrator/components/com_redevent/assets/js/seo.js');
		$document->addScript($url.'components/com_redevent/assets/js/recurrence.js');
		// include the unlimited script
		$document->addScript($url.'components/com_redevent/assets/js/unlimited.js');

		//build toolbar
		
		if ($task == 'copy') {
		  	JToolBarHelper::title( JText::_( 'COPY EVENT'), 'eventedit');		
		} elseif ( $cid ) {
			JToolBarHelper::title( JText::_( 'EDIT EVENT' ), 'eventedit' );
		} else {
			JToolBarHelper::title( JText::_( 'ADD EVENT' ), 'eventedit' );

			//set the submenu
			JSubMenuHelper::addEntry( JText::_( 'REDEVENT' ), 'index.php?option=com_redevent');
			JSubMenuHelper::addEntry( JText::_( 'EVENTS' ), 'index.php?option=com_redevent&view=events');
			JSubMenuHelper::addEntry( JText::_( 'VENUES' ), 'index.php?option=com_redevent&view=venues');
			JSubMenuHelper::addEntry( JText::_( 'CATEGORIES' ), 'index.php?option=com_redevent&view=categories');
			JSubMenuHelper::addEntry( JText::_( 'ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive');
			JSubMenuHelper::addEntry( JText::_( 'GROUPS' ), 'index.php?option=com_redevent&view=groups');
			JSubMenuHelper::addEntry( JText::_( 'HELP' ), 'index.php?option=com_redevent&view=help');
			if ($user->get('gid') > 24) {
				JSubMenuHelper::addEntry( JText::_( 'SETTINGS' ), 'index.php?option=com_redevent&controller=settings&task=edit');
			}
		}
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.editevents', true );

		//get data from model
		$model		= & $this->getModel();
		$row     	= & $this->get('Data');
		
		/* Check if we have a redFORM id */
		if (empty($row->redform_id)) $row->redform_id = $elsettings->defaultredformid;

		// fail if checked out not by 'me'
		if ($row->id) {
			if ($model->isCheckedOut( $user->get('id') )) {
				JError::raiseWarning( 'SOME_ERROR_CODE', $row->titel.' '.JText::_( 'EDITED BY ANOTHER ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=events' );
			}
		}

		//make data safe
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'datdescription' );

		//Create category list
		$Lists = array();
		$Lists['category'] = $model->getCategories();
		
		/* Create venue selection tab */
		$Lists['venueselectbox'] = '';
		$venueslist = $this->get('Venues');
		$eventvenue = $this->get('EventVenue');
		$infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) );
		foreach ($venueslist as $key => $venue) {
			if (isset($eventvenue[$venue->id])) {
				$Lists['venueselectbox'] .= '<div id="locid'.$venue->id.'"><input type="checkbox" name="locid[]" value="'.$venue->id.'"';
				if (isset($eventvenue[$venue->id])) $Lists['venueselectbox'] .= ' checked="checked" /> '.$venue->venue;
				$Lists['venueselectbox'] .= '<div class="adddatetime"><input type="button" name="adddatetime" value="'.JText::_('ADD_DATE_TIME').'" /></div>';
				$Lists['venueselectbox'] .= '<div class="showalldatetime"><input type="button" name="showalldatetime" value="'.JText::_('SHOW_ALL_DATE_TIME').'" /></div>';
				foreach ($eventvenue[$venue->id] as $evkey => $ev) {
					$random = $eventvenue[$venue->id][$evkey]->id;
					$Lists['venueselectbox'] .= '<div id="datetimecontainer'.$venue->id.$random.'" style="display: block;"><input type="button" name="removedatetime" value="'.JText::_('SHOW_HIDE_DATE_TIME').'" onClick=\'jQuery("#datetime'.$venue->id.'-'.$eventvenue[$venue->id][$evkey]->id.'").toggle("slideUp"); return false;\'/><input type="button" name="removedatetime" value="'.JText::_('REMOVE_DATE_TIME').'" onClick=\'removeDateTimeFields('.$venue->id.$eventvenue[$venue->id][$evkey]->id.');\'; return false;\'/>';
					$Lists['venueselectbox'] .= '<div id="datetime'.$venue->id.'-'.$eventvenue[$venue->id][$evkey]->id.'" style="display: none;">';
					$Lists['venueselectbox'] .= '<table class="adminform">';
						/* start date and start time */
						$Lists['venueselectbox'] .= '<tr class="row0"><td class="redevent_settings_details">'.JText::_('DATE').'</td><td>'.JHTML::_('calendar', $eventvenue[$venue->id][$evkey]->dates, "locid$venue->id[$random][dates]", "dates$random").'</td>';
						$Lists['venueselectbox'] .= '<td>'.JText::_('TIME').'</td><td><input type="text" name="locid'.$venue->id.'['.$random.'][times]" value="'.$eventvenue[$venue->id][$evkey]->times.'" /></td></tr>';
						/* End date and end time */
						$Lists['venueselectbox'] .= '<tr class="row1"><td>'.JText::_('ENDDATE').'</td><td>'.JHTML::_('calendar', $eventvenue[$venue->id][$evkey]->enddates, "locid$venue->id[$random][enddates]", "enddates$random").'</td>';
						$Lists['venueselectbox'] .= '<td>'.JText::_('ENDTIMES').'</td><td><input type="text" name="locid'.$venue->id.'['.$random.'][endtimes]" value="'.$eventvenue[$venue->id][$evkey]->endtimes.'" /></td></tr>';
						/* Attendees and waitinglist */
						$Lists['venueselectbox'] .= '<tr class="row0"><td><span class="editlinktip hasTip" title="'.JText::_( 'MAXIMUM_ATTENDEES' ).'::'.JText::_('MAXIMUM_ATTENDEES_TIP').'"'.$infoimage.'</span>'.JText::_('MAXIMUM_ATTENDEES').'</td><td><input class="inputbox" name="locid'.$venue->id.'['.$random.'][maxattendees]" value="'.$eventvenue[$venue->id][$evkey]->maxattendees.'" size="15" maxlength="8" /></td>';
						$Lists['venueselectbox'] .= '<td><span class="editlinktip hasTip" title="'.JText::_( 'MAXIMUM_WAITINGLIST' ).'::'.JText::_('MAXIMUM_WAITINGLIST_TIP').'"'.$infoimage.'</span>'.JText::_('MAXIMUM_WAITINGLIST').'</td><td><input class="inputbox" name="locid'.$venue->id.'['.$random.'][maxwaitinglist]" value="'.$eventvenue[$venue->id][$evkey]->maxwaitinglist.'" size="15" maxlength="8" /></td></tr>';
						/* Course price and credit */
						$Lists['venueselectbox'] .= '<tr class="row1"><td>'.JText::_('COURSE_PRICE').'</td><td><input class="inputbox" name="locid'.$venue->id.'['.$random.'][course_price]" value="'.$eventvenue[$venue->id][$evkey]->course_price.'" size="15" maxlength="8" /></td>';
						$Lists['venueselectbox'] .= '<td>'.JText::_('COURSE_CREDIT').'</td><td><input class="inputbox" name="locid'.$venue->id.'['.$random.'][course_credit]" value="'.$eventvenue[$venue->id][$evkey]->course_credit.'" size="15" maxlength="8" /></td></tr>';
					$Lists['venueselectbox'] .= '</table>';
					$Lists['venueselectbox'] .= '</div></div>';
				}
				$Lists['venueselectbox'] .= '</div>';
			}
			else {
				$Lists['venueselectbox'] .= '<div id="locid'.$venue->id.'"><input type="checkbox" name="locid[]" value="'.$venue->id.'"';
				$Lists['venueselectbox'] .= ' />'.$venue->venue;
				$Lists['venueselectbox'] .= '<div class="adddatetime" style="display: none;"><input type="button" name="adddatetime" value="'.JText::_('ADD_DATE_TIME').'" /></div>';
				$Lists['venueselectbox'] .= '<div class="showalldatetime" style="display: none;"><input type="button" name="showalldatetime" value="'.JText::_('SHOW_ALL_DATE_TIME').'" /></div>';
				$Lists['venueselectbox'] .= '</div>';
			}
		}
		
		// categories selector
    $selected = array();
    foreach ((array) $row->categories_ids as $cat) {
      $selected[] = $cat;
    }
    $Lists['categories'] = JHTML::_('select.genericlist', (array) $this->get('Categories'), 'categories[]', 'class="inputbox required validate-categories" multiple="multiple" size="10"', 'value', 'text', $selected); 
		
		/* Create submission types */
		$submission_types = explode(',', $row->submission_types);
		
		//build image select js and load the view
		$js = "
		function elSelectImage(image, imagename) {
			document.getElementById('a_image').value = image;
			document.getElementById('a_imagename').value = imagename;
			document.getElementById('imagelib').src = '../images/redevent/events/' + image;
			document.getElementById('sbox-window').close();
		}";

		$link = 'index.php?option=com_redevent&amp;view=imagehandler&amp;layout=uploadimage&amp;task=eventimg&amp;tmpl=component';
		$link2 = 'index.php?option=com_redevent&amp;view=imagehandler&amp;task=selecteventimg&amp;tmpl=component';
		$document->addScriptDeclaration($js);
		$imageselect = "\n<input style=\"background: #ffffff;\" type=\"text\" id=\"a_imagename\" value=\"$row->datimage\" disabled=\"disabled\" onchange=\"javascript:if (document.forms[0].a_imagename.value!='') {document.imagelib.src='../images/redevent/events/' + document.forms[0].a_imagename.value} else {document.imagelib.src='../images/blank.png'}\"; /><br />";

		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".JText::_('Upload')."\" href=\"$link\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('Upload')."</a></div></div>\n";
		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".JText::_('SELECTIMAGE')."\" href=\"$link2\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('SELECTIMAGE')."</a></div></div>\n";

		$imageselect .= "\n&nbsp;<input class=\"inputbox\" type=\"button\" onclick=\"elSelectImage('', '".JText::_('SELECTIMAGE')."' );\" value=\"".JText::_('Reset')."\" />";
		$imageselect .= "\n<input type=\"hidden\" id=\"a_image\" name=\"datimage\" value=\"$row->datimage\" />";
		
		/* Check if redFORM is installed */
		$redform_install = $this->get('CheckredFORM');
		
		if ($redform_install) {
			/* Get a list of redFORM forms */
			$redforms = $this->get('RedForms');
			if ($redforms) $Lists['redforms'] = JHTML::_('select.genericlist', $redforms, 'redform_id', '', 'id', 'formname', $row->redform_id );
			else $Lists['redforms'] = '';
			
			/* Check if a redform ID exists, if so, get the fields */
			if (isset($row->redform_id) && $row->redform_id > 0) {
				$formfields = $this->get('formfields');
				if (!$formfields) $formfields = array();
			}
		}
		else {
			$Lists['redforms'] = '';
			$formfields = '';
		}
		
		//assign vars to the template
		$this->assignRef('Lists'      	, $Lists);
		$this->assignRef('row'      	, $row);
		$this->assignRef('formfields'  	, $formfields);
		$this->assignRef('imageselect'	, $imageselect);
		$this->assignRef('submission_types'	, $submission_types);
		$this->assignRef('editor'		, $editor);
		$this->assignRef('pane'			, $pane);
		$this->assignRef('task'			, $task);
		$this->assignRef('elsettings'	, $elsettings);
		$this->assignRef('formfields'	, $formfields);
		$this->assignRef('venueslist'	, $venueslist);
$this->assignRef('redform_install'	, $redform_install);

		parent::display($tpl);
	}

	/**
	 * Creates the output for the add venue screen
	 *
	 * @since 0.9
	 *
	 */
	function _displayaddvenue($tpl)
	{
		global $mainframe;

		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$uri 		= & JFactory::getURI();
		$elsettings = ELAdmin::config();

		//add css and js to document
		JHTML::_('behavior.modal', 'a.modal');
		JHTML::_('behavior.tooltip');

		//Build the image select functionality
		$js = "
		function elSelectImage(image, imagename) {
			document.getElementById('a_image').value = image;
			document.getElementById('a_imagename').value = imagename;
			document.getElementById('sbox-window').close();
		}";

		$link = 'index.php?option=com_redevent&amp;view=imagehandler&amp;layout=uploadimage&amp;task=venueimg&amp;tmpl=component';
		$link2 = 'index.php?option=com_redevent&amp;view=imagehandler&amp;task=selectvenueimg&amp;tmpl=component';
		$document->addScriptDeclaration($js);
		$imageselect = "\n<input style=\"background: #ffffff;\" type=\"text\" id=\"a_imagename\" value=\"".JText::_('SELECTIMAGE')."\" disabled=\"disabled\" onchange=\"javascript:if (document.forms[0].a_imagename.value!='') {document.imagelib.src='../images/redevent/events/' + document.forms[0].a_imagename.value} else {document.imagelib.src='../images/blank.png'}\"; /><br />";

		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".JText::_('Upload')."\" href=\"$link\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('Upload')."</a></div></div>\n";
		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".JText::_('SELECTIMAGE')."\" href=\"$link2\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('SELECTIMAGE')."</a></div></div>\n";

		$imageselect .= "\n&nbsp;<input class=\"inputbox\" type=\"button\" onclick=\"elSelectImage('', '".JText::_('SELECTIMAGE')."' );\" value=\"".JText::_('Reset')."\" />";
		$imageselect .= "\n<input type=\"hidden\" id=\"a_image\" name=\"locimage\" value=\"".JText::_('SELECTIMAGE')."\" />";

		//set published
		$published = 1;

		//assign to template
		$this->assignRef('editor'      	, $editor);
		$this->assignRef('imageselect' 	, $imageselect);
		$this->assignRef('published' 	, $published);
		$this->assignRef('request_url'	, $uri->toString());
		$this->assignRef('elsettings'	, $elsettings);

		parent::display($tpl);
	}
}
?>