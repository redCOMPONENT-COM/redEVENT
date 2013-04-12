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
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewSession extends JView {

	function display($tpl = null)
	{

		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$uri 		= & JFactory::getURI();
		$elsettings = JComponentHelper::getParams('com_redevent');

		$mainframe = &JFactory::getApplication();

		// data
		$xref = $this->get('xref');

		// ajax in event form, or standalone ?
		$standalone = Jrequest::getVar('standalone', 0);

		if (!$standalone && $this->getLayout() == 'closexref') {
			$this->_displayclosexref($tpl);
			return;
		}

		if ($standalone)
		{
			$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITSESSION'));
			$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

			// Set toolbar items for the page
			$edit		= JRequest::getVar('edit',true);
			$text = !$xref->id ? JText::_('COM_REDEVENT_New' ) : JText::_('COM_REDEVENT_Edit' );
			JToolBarHelper::title(   JText::sprintf( 'COM_REDEVENT_SESSION_FOR_S',$xref->event_title ).': <small><small>[ ' . $text.' ]</small></small>' );
			JToolBarHelper::save();
			JToolBarHelper::apply();

			if (JPluginHelper::isEnabled('system', 'autotweetredevent'))
			{
				//If the AutoTweet NG Component is installed
				// Ignore warnings because component may not be installed
				$warnHandlers = JERROR::getErrorHandling( E_WARNING );
				JERROR::setErrorHandling( E_WARNING, 'ignore' );
				if (JComponentHelper::isEnabled('com_autotweet', true))
				{
					JToolBarHelper::save('saveAndTwit', 'Save & twit');
				}
				// Reset the warning handler(s)
				foreach( $warnHandlers as $mode ) {
					JERROR::setErrorHandling( E_WARNING, $mode );
				}
			}

			if (!$edit)  {
				JToolBarHelper::cancel();
			} else {
				// for existing items the button is renamed `close`
				JToolBarHelper::cancel( 'cancel', 'Close' );
			}
		}


		//add css and js to document
		//JHTML::_('behavior.modal', 'a.modal');
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.formvalidation');

		jimport('joomla.html.pane');

		$document->addScript(JURI::root().'components/com_redevent/assets/js/xref_recurrence.js');
		$document->addScript(JURI::root().'components/com_redevent/assets/js/xref_roles.js');
		$document->addScript(JURI::root().'components/com_redevent/assets/js/xref_prices.js');
		$document->addScriptDeclaration('var txt_remove = "'.JText::_('COM_REDEVENT_REMOVE').'";');

		//Build the image select functionality
		$js = "
		function elSelectImage(image, imagename) {
			document.getElementById('a_image').value = image;
			document.getElementById('a_imagename').value = imagename;
			document.getElementById('sbox-window').close();
		}";

		$xref->eventid = ($xref->eventid) ? $xref->eventid : JRequest::getVar('eventid', 0, 'request', 'int');
		$customfields =& $this->get('XrefCustomfields');

		$roles  =& $this->get('SessionRoles');
		$prices =& $this->get('SessionPrices');

		$lists = array();

		// venues selector
		$venues = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_Venue')));
		$venues = array_merge($venues, $this->get('VenuesOptions'));
		$lists['venue'] = JHTML::_('select.genericlist', $venues, 'venueid', 'class="validate-venue"', 'value', 'text', $xref->venueid);

		// if this is not the first xref of the recurrence, we shouldn't modify it
		$lockedrecurrence = ($xref->count > 0);

	    // Recurrence selector
	    $recur_type = array( JHTML::_('select.option', 'NONE', JText::_('COM_REDEVENT_NO_REPEAT')),
	                         JHTML::_('select.option', 'DAILY', JText::_('COM_REDEVENT_DAILY')),
	                         JHTML::_('select.option', 'WEEKLY', JText::_('COM_REDEVENT_WEEKLY')),
	                         JHTML::_('select.option', 'MONTHLY', JText::_('COM_REDEVENT_MONTHLY')),
	                         JHTML::_('select.option', 'YEARLY', JText::_('COM_REDEVENT_YEARLY'))
	                       );
	    $lists['recurrence_type'] = JHTML::_('select.radiolist', $recur_type, 'recurrence_type', '', 'value', 'text', $xref->rrules->type);

	    // published state selector
	    $published = array( JHTML::_('select.option', '1', JText::_('COM_REDEVENT_PUBLISHED')),
	    		JHTML::_('select.option', '0', JText::_('COM_REDEVENT_UNPUBLISHED')),
	    		JHTML::_('select.option', '-1', JText::_('COM_REDEVENT_ARCHIVED'))
	    );
	    $lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $xref->published);

	    // featured state selector
	    $options = array( JHTML::_('select.option', '0', JText::_('COM_REDEVENT_SESSION_NOT_FEATURED')),
	    		JHTML::_('select.option', '1', JText::_('COM_REDEVENT_SESSION_IS_FEATURED'))
	    );
	    $lists['featured'] = JHTML::_('select.booleanlist', 'featured', '', $xref->featured);

		$pane 		= & JPane::getInstance('tabs');

		$rolesoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_role')));
		$rolesoptions = array_merge($rolesoptions, $this->get('RolesOptions'));

		$pricegroupsoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_PRICEGROUP')));
		$pricegroupsoptions = array_merge($pricegroupsoptions, $this->get('PricegroupsOptions'));

		if (JRequest::getVar('task') == 'copy')
		{
			$xref->id = null;
			$xref->recurrence_id = null;
		}

		//assign to template
		$this->assignRef('xref'         , $xref);
		$this->assignRef('editor'      	, $editor);
		$this->assignRef('lists'        , $lists);
		$this->assignRef('request_url'	, $uri->toString());
		$this->assignRef('elsettings'	  , $elsettings);
		$this->assignRef('customfields' , $customfields);
		$this->assignRef('pane'			    , $pane);
		$this->assignRef('roles'        , $roles);
		$this->assignRef('rolesoptions' , $rolesoptions);
		$this->assignRef('prices'       , $prices);
		$this->assignRef('pricegroupsoptions' , $pricegroupsoptions);
		$this->assign('standalone' , $standalone);

		parent::display($tpl);
	}

	function _displayclosexref($tpl)
	{
		$document = & JFactory::getDocument();
		$elsettings = JComponentHelper::getParams('com_redevent');

		$xref = $this->get('xref');

		/* Get the date */
		$date = (!redEVENTHelper::isValidDate($xref->dates) ? JText::_('COM_REDEVENT_Open_date') : strftime( $elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $xref->dates )));
		$enddate  = (!redEVENTHelper::isValidDate($xref->enddates) || $xref->enddates == $xref->dates) ? '' : strftime( $elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $xref->enddates ));
		$displaydate = $date. ($enddate ? ' - '.$enddate: '');

		$displaytime = '';

		/* Get the time */
		if (isset($xref->times) && $xref->times != '00:00:00')
		{
			$displaytime = strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $xref->times ));

			if (isset($xref->endtimes) && $xref->endtimes != '00:00:00')
			{
				$displaytime .= ' - '.strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $xref->endtimes ));
			}
		}

		$json_data = array( 'id'        => $xref->id,
			'venue'     => $xref->venue,
			'date'      => $displaydate,
			'time'      => $displaytime,
			'published' => $xref->published,
			'note'      => $xref->note,
			'featured'  => $xref->featured,
			'eventid'   => $xref->eventid,
        );

		if (function_exists('json_encode')) {
			$js = 'window.parent.updatexref('.json_encode($json_data).');';
			$document->addScriptDeclaration($js);
		}
		else {
			echo JText::_('COM_REDEVENT_ERROR_JSON_IS_NOT_ENABLED');
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
			<?php echo JHTML::link('index.php?option=com_redevent&view=tags&tmpl=component&field='.$field, JText::_('COM_REDEVENT_TAGS'), 'class="modal"'); ?>
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
}
