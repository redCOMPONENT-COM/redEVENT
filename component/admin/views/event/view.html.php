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

		if($this->getLayout() == 'editxref') {
			$this->_displayeditxref($tpl);
			return;
		}
		else if($this->getLayout() == 'closexref') {
      $this->_displayclosexref($tpl);
      return;
    }

		//Load behavior
		jimport('joomla.html.pane');
		JHTML::_('behavior.tooltip');
    JHTML::_('behavior.formvalidation');
    JHTML::_('behavior.mootools');
    
		require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'helper.php');
		require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'output.class.php');
		
		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$pane 		= & JPane::getInstance('tabs');
		$user 		= & JFactory::getUser();
		$elsettings = ELAdmin::config();
		$params   = JComponentHelper::getParams('com_redevent');

		//get vars
		$cid		= JRequest::getVar( 'cid' );
		$task		= JRequest::getVar('task');
		$url 		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITEVENT'));
		//add the custom stylesheet and the seo javascript
		$document->addStyleSheet($url.'administrator/components/com_redevent/assets/css/redeventbackend.css');
		$document->addScript($url.'administrator/components/com_redevent/assets/js/seo.js');
		
    $document->addScript($url.'administrator/components/com_redevent/assets/js/xrefedit.js');
    $document->addScript($url.'administrator/components/com_redevent/assets/js/editevent.js');
    $document->addScript($url.'components/com_redevent/assets/js/attachments.js');
		$document->addScriptDeclaration('var removemsg = "'.JText::_('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG').'";' );

		//get data from model
		$model		= & $this->getModel();
		
		if ($task == 'add') {
			$model->setId($params->get('default_content'));
		}
		$row     	= & $this->get('Data');
		if ($task == 'copy') 
		{
			$row->id = null;
			$row->title .= ' '.JText::_('copy');
			$row->alias = '';
		}
		if ($task == 'add') 
		{
			$row->id = null;
			$row->title = '';
			$row->alias = '';
		}
    $customfields =& $this->get('Customfields');
		
		/* Check if we have a redFORM id */
		if (empty($row->redform_id)) $row->redform_id = $elsettings->defaultredformid;

		// fail if checked out not by 'me'
		if ($row->id) {
			if ($model->isCheckedOut( $user->get('id') )) {
				JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', $row->titel.' '.JText::_( 'EDITED BY ANOTHER ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=events' );
			}
		}

		//make data safe
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'datdescription' );

		//Create category list
		$lists = array();
		$lists['category'] = $model->getCategories();
		
		/* Create venue selection tab */
		$venueslist = $this->get('Venues');
		$xrefs = $this->get('xrefs');
				
		// categories selector
    $selected = array();
    foreach ((array) $row->categories_ids as $cat) {
      $selected[] = $cat;
    }
    $lists['categories'] = JHTML::_('select.genericlist', (array) $this->get('Categories'), 'categories[]', 'class="inputbox required validate-categories" multiple="multiple" size="10"', 'value', 'text', $selected); 
		    
    // event layout
    $options = array(
                 JHTML::_('select.option', 0, JText::_('COM_REDEVENT_DEFAULT')),
                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_EVENT_LAYOUT_TAGS')),
                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_EVENT_LAYOUT_FIXED')),
                 );
		$lists['details_layout'] = JHTML::_('select.genericlist', $options, 'details_layout', '', 'value', 'text', $row->details_layout);
    
    // enable ical button
    $options = array(
                 JHTML::_('select.option', 0, JText::_('COM_REDEVENT_DEFAULT')),
                 JHTML::_('select.option', 1, JText::_('Yes')),
                 JHTML::_('select.option', 2, JText::_('No')),
                 );
		$lists['enable_ical'] = JHTML::_('select.genericlist', $options, 'enable_ical', '', 'value', 'text', $row->enable_ical);
		
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
		$imageselect = "\n<input style=\"background: #ffffff;\" type=\"text\" id=\"a_imagename\" value=\"$row->datimage\" disabled=\"disabled\" /><br />";

		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".JText::_('Upload')."\" href=\"$link\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('Upload')."</a></div></div>\n";
		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".JText::_('SELECTIMAGE')."\" href=\"$link2\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('SELECTIMAGE')."</a></div></div>\n";

		$imageselect .= "\n&nbsp;<input class=\"inputbox\" type=\"button\" onclick=\"elSelectImage('', '".JText::_('SELECTIMAGE')."' );\" value=\"".JText::_('Reset')."\" />";
		$imageselect .= "\n<input type=\"hidden\" id=\"a_image\" name=\"datimage\" value=\"$row->datimage\" />";
		
		/* Check if redFORM is installed */
		$redform_install = $this->get('CheckredFORM');
		
		if ($redform_install) {
			/* Get a list of redFORM forms */
			$redforms = $this->get('RedForms');
			if ($redforms) $lists['redforms'] = JHTML::_('select.genericlist', $redforms, 'redform_id', '', 'id', 'formname', $row->redform_id );
			else $lists['redforms'] = '';
			
			/* Check if a redform ID exists, if so, get the fields */
			if (isset($row->redform_id) && $row->redform_id > 0) {
				$formfields = $this->get('formfields');
				if (!$formfields) $formfields = array();
			}
		}
		else {
			$lists['redforms'] = '';
			$formfields = '';
		}
				
		JHTML::script('modal.js');
		JHTML::stylesheet('modal.css');

		//build toolbar		
		if ($task == 'copy') {
		  	JToolBarHelper::title( JText::_( 'COPY EVENT'), 'eventedit');		
		} elseif ( $cid ) {
			JToolBarHelper::title( JText::_( 'EDIT EVENT' ).' - '.$row->title, 'eventedit' );
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
		//If the AutoTweet NG Component is installed 
		if (JComponentHelper::getComponent('com_autotweet', true)->enabled)
		{
			//If the redEVENT twitter plugin is installed
			if (JPluginHelper::isEnabled("system", "autotweetredevent"))
			{
				JToolBarHelper::save('saveAndTwit', 'Save & twit');
			}
		}
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.editevents', true );
		      		
		//assign vars to the template
		$this->assignRef('lists'      	, $lists);
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
    $this->assignRef('customfields'  , $customfields);
		$this->assignRef('access'	, redEVENTHelper::getAccesslevelOptions());
    $this->assignRef('xrefs'  , $xrefs);

    if (!$row->id) {
    	$this->_prepareSessionTab();
    }
    
		parent::display($tpl);
	}

	/**
	 * Creates the output for the add venue screen
	 *
	 * @since 0.9
	 *
	 */
	function _displayeditxref($tpl)
	{
		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$uri 		= & JFactory::getURI();
		$elsettings = ELAdmin::config();

		//add css and js to document
		//JHTML::_('behavior.modal', 'a.modal');
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.formvalidation');
		
		jimport('joomla.html.pane');

    $document->addScript('components/com_redevent/assets/js/xref_recurrence.js');
    
		//Build the image select functionality
		$js = "
		function elSelectImage(image, imagename) {
			document.getElementById('a_image').value = image;
			document.getElementById('a_imagename').value = imagename;
			document.getElementById('sbox-window').close();
		}";

		$xref = $this->get('xref');
		$xref->eventid = ($xref->eventid) ? $xref->eventid : JRequest::getVar('eventid', 0, 'request', 'int'); 		
    $customfields =& $this->get('XrefCustomfields');
    
		$lists = array();
		
		// venues selector
    $venues = array(JHTML::_('select.option', 0, JText::_('Select Venue')));
		$venues = array_merge($venues, $this->get('VenuesOptions'));
		$lists['venue'] = JHTML::_('select.genericlist', $venues, 'venueid', 'class="validate-venue"', 'value', 'text', $xref->venueid);
		
		// group selector
    $options = array(JHTML::_('select.option', 0, JText::_('Select group')));
		$options = array_merge($options, $this->get('GroupsOptions'));
		$lists['group'] = JHTML::_('select.genericlist', $options, 'groupid', '', 'value', 'text', $xref->groupid);
		
    // if this is not the first xref of the recurrence, we shouldn't modify it
    $lockedrecurrence = ($xref->count > 0); 

    // Recurrence selector
    $recur_type = array( JHTML::_('select.option', 'NONE', JText::_('NO REPEAT')),
                         JHTML::_('select.option', 'DAILY', JText::_('DAILY')),
                         JHTML::_('select.option', 'WEEKLY', JText::_('WEEKLY')),
                         JHTML::_('select.option', 'MONTHLY', JText::_('MONTHLY')),
                         JHTML::_('select.option', 'YEARLY', JText::_('YEARLY'))
                       );
    $lists['recurrence_type'] = JHTML::_('select.radiolist', $recur_type, 'recurrence_type', '', 'value', 'text', $xref->rrules->type);
    
    // published state selector
    $published = array( JHTML::_('select.option', '1', JText::_('PUBLISHED')),
                         JHTML::_('select.option', '0', JText::_('UNPUBLISHED')),
                         JHTML::_('select.option', '-1', JText::_('ARCHIVED'))
                       );
    $lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $xref->published);
    
    // featured state selector
    $options = array( JHTML::_('select.option', '0', JText::_('COM_REDEVENT_SESSION_NOT_FEATURED')),
                         JHTML::_('select.option', '1', JText::_('COM_REDEVENT_SESSION_IS_FEATURED'))
                       );
    $lists['featured'] = JHTML::_('select.booleanlist', 'featured', '', $xref->featured);
		
		$pane 		= & JPane::getInstance('tabs');
		
		//assign to template
    $this->assignRef('xref'         , $xref);
		$this->assignRef('editor'      	, $editor);
    $this->assignRef('lists'        , $lists);
		$this->assignRef('request_url'	, $uri->toString());
		$this->assignRef('elsettings'	  , $elsettings);
    $this->assignRef('customfields' , $customfields);
		$this->assignRef('pane'			    , $pane);

		parent::display($tpl);
	}
	
	function _displayclosexref($tpl)
	{	
    $document = & JFactory::getDocument();
    $elsettings = ELAdmin::config();
    
    $xref = $this->get('xref');
    
    /* Get the date */
    $date = (!redEVENTHelper::isValidDate($xref->dates) ? Jtext::_('Open date') : strftime( $elsettings->formatdate, strtotime( $xref->dates )));
    $enddate  = (!redEVENTHelper::isValidDate($xref->enddates) || $xref->enddates == $xref->dates) ? '' : strftime( $elsettings->formatdate, strtotime( $xref->enddates ));
    $displaydate = $date. ($enddate ? ' - '.$enddate: '');

    $displaytime = '';
    /* Get the time */
    if (isset($xref->times) && $xref->times != '00:00:00') {
    	$displaytime = strftime( $elsettings->formattime, strtotime( $xref->times )).' '.$elsettings->timename;

    	if (isset($xref->endtimes) && $xref->endtimes != '00:00:00') {
    		$displaytime .= ' - '.strftime( $elsettings->formattime, strtotime( $xref->endtimes )). ' '.$elsettings->timename;
    	}
    }
    $json_data = array( 'id'        => $xref->id,
                        'venue'     => $xref->venue,
                        'date'      => $displaydate,
                        'time'      => $displaytime,
                        'published' => $xref->published,
                        'note'      => $xref->note,
                        'featured'  => $xref->featured,
                      );
		if (function_exists('json_encode')) {
  	  $js = 'window.parent.updatexref('.json_encode($json_data).');';
	    $document->addScriptDeclaration($js);		
		}
		else {
			echo JText::_('ERROR: JSON IS NOT ENABLED');
		}
		return;
	}
	
	/**
	 * prints the code for tags display
	 * 
	 * @param array tags to exclude from printing 
	 */
	function printTags($field = '')
	{ 
    ?>	
	  <div class="tagsdiv">
	  	<?php echo JHTML::link('index.php?option=com_redevent&view=tags&tmpl=component&field='.$field, JText::_('TAGS'), 'class="modal"'); ?>
    </div>  
	  <?php 
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
  
  protected function _prepareSessionTab()
  {
  	$document = &JFactory::getDocument();
  	
    $document->addScript('components/com_redevent/assets/js/xref_recurrence.js');
    $document->addScript('components/com_redevent/assets/js/xref_roles.js');
    $document->addScript('components/com_redevent/assets/js/xref_prices.js');
    $document->addScriptDeclaration('var txt_remove = "'.JText::_('COM_REDEVENT_REMOVE').'";');
    
  	$model = JModel::getInstance('Session', 'RedeventModel');
  	  	 		
    $customfields =& $model->getXrefCustomfields();
    
		// venues selector
    $venues = array(JHTML::_('select.option', 0, JText::_('Select Venue')));
		$venues = array_merge($venues, $model->getVenuesOptions());
		$this->lists['venue'] = JHTML::_('select.genericlist', $venues, 'venueid', 'class="validate-venue"', 'value', 'text');
		
		// group selector
    $options = array(JHTML::_('select.option', 0, JText::_('Select group')));
		$options = array_merge($options, $model->getGroupsOptions());
		$this->lists['group'] = JHTML::_('select.genericlist', $options, 'groupid', '', 'value', 'text');
		
    // if this is not the first xref of the recurrence, we shouldn't modify it
    $lockedrecurrence = false; 

    // Recurrence selector
    $recur_type = array( JHTML::_('select.option', 'NONE', JText::_('NO REPEAT')),
                         JHTML::_('select.option', 'DAILY', JText::_('DAILY')),
                         JHTML::_('select.option', 'WEEKLY', JText::_('WEEKLY')),
                         JHTML::_('select.option', 'MONTHLY', JText::_('MONTHLY')),
                         JHTML::_('select.option', 'YEARLY', JText::_('YEARLY'))
                       );
    $this->lists['recurrence_type'] = JHTML::_('select.radiolist', $recur_type, 'recurrence_type', '', 'value', 'text');
    
    // published state selector
    $published = array( JHTML::_('select.option', '1', JText::_('PUBLISHED')),
                         JHTML::_('select.option', '0', JText::_('UNPUBLISHED')),
                         JHTML::_('select.option', '-1', JText::_('ARCHIVED'))
                       );
    $this->lists['session_published'] = JHTML::_('select.radiolist', $published, 'session_published', '', 'value', 'text', 1);
    
    // featured state selector
    $options = array( JHTML::_('select.option', '0', JText::_('COM_REDEVENT_SESSION_NOT_FEATURED')),
                         JHTML::_('select.option', '1', JText::_('COM_REDEVENT_SESSION_IS_FEATURED'))
                       );
    $this->lists['featured'] = JHTML::_('select.booleanlist', 'featured');
				
		$rolesoptions = array(JHTML::_('select.option', 0, JText::_('Select role')));
		$rolesoptions = array_merge($rolesoptions, $model->getRolesOptions());
		
		$pricegroupsoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_PRICEGROUP')));
		$pricegroupsoptions = array_merge($pricegroupsoptions, $model->getPricegroupsOptions());
				
		$this->assign('roles'        , false);
		$this->assign('rolesoptions' , $rolesoptions);
		$this->assign('prices'       , false);
		$this->assign('pricegroupsoptions' , $pricegroupsoptions);
    $this->assignRef('xrefcustomfields' , $customfields);
  }
}
?>