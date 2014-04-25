<?php
/**
 * @package    Redevent.Admin
 * @copyright  redEVENT (C) 2008-2013 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * View class for the EventList event screen
 *
 * @package  Redevent.Admin
 * @since    0.9
 */
class RedEventViewSession extends JView
{

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		// Initialise variables
		$editor   = JFactory::getEditor();
		$document = JFactory::getDocument();
		$uri      = JFactory::getURI();
		$elsettings = JComponentHelper::getParams('com_redevent');

		$mainframe = & JFactory::getApplication();

		// Data
		$xref = $this->get('xref');

		// Ajax in event form, or standalone ?
		$standalone = JRequest::getVar('standalone', 0);

		if (!$standalone && $this->getLayout() == 'closexref')
		{
			$this->_displayclosexref($tpl);

			return;
		}

		if ($standalone)
		{
			$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITSESSION'));
			FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

			// Set toolbar items for the page
			$edit = JRequest::getVar('edit', true);
			$text = !$xref->id ? JText::_('COM_REDEVENT_New') : JText::_('COM_REDEVENT_Edit');
			JToolBarHelper::title(JText::sprintf('COM_REDEVENT_SESSION_FOR_S', $xref->event_title) . ': <small><small>[ ' . $text . ' ]</small></small>');
			JToolBarHelper::save();
			JToolBarHelper::apply();

			if (JPluginHelper::isEnabled('system', 'autotweetredevent'))
			{
				// If the AutoTweet NG Component is installed
				// Ignore warnings because component may not be installed
				$warnHandlers = JERROR::getErrorHandling(E_WARNING);
				JERROR::setErrorHandling(E_WARNING, 'ignore');

				if (JComponentHelper::isEnabled('com_autotweet', true))
				{
					JToolBarHelper::save('saveAndTwit', 'Save & twit');
				}

				// Reset the warning handler(s)
				foreach ($warnHandlers as $mode)
				{
					JERROR::setErrorHandling(E_WARNING, $mode);
				}
			}

			if (!$edit)
			{
				JToolBarHelper::cancel();
			}
			else
			{
				// For existing items the button is renamed `close`
				JToolBarHelper::cancel('cancel', 'Close');
			}
		}


		// Add css and js to document
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.formvalidation');

		jimport('joomla.html.pane');

		$document->addScript(JURI::root() . 'components/com_redevent/assets/js/xref_recurrence.js');
		$document->addScript(JURI::root() . 'components/com_redevent/assets/js/xref_roles.js');
		$document->addScript(JURI::root() . 'components/com_redevent/assets/js/xref_prices.js');
		$document->addScriptDeclaration('var txt_remove = "' . JText::_('COM_REDEVENT_REMOVE') . '";');

		// Build the image select functionality
		$js = "
		function elSelectImage(image, imagename) {
			document.getElementById('a_image').value = image;
			document.getElementById('a_imagename').value = imagename;
			document.getElementById('sbox-window').close();
		}";

		$xref->eventid = ($xref->eventid) ? $xref->eventid : JRequest::getVar('eventid', 0, 'request', 'int');
		$customfields =& $this->get('XrefCustomfields');

		$roles =& $this->get('SessionRoles');
		$prices =& $this->get('SessionPrices');

		$lists = array();

		// Venues selector
		$venues = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_Venue')));
		$venues = array_merge($venues, $this->get('VenuesOptions'));
		$lists['venue'] = JHTML::_('select.genericlist', $venues, 'venueid', 'class="validate-venue"', 'value', 'text', $xref->venueid);

		// If this is not the first xref of the recurrence, we shouldn't modify it
		$lockedrecurrence = ($xref->count > 0);

		// Recurrence selector
		$recur_type = array(JHTML::_('select.option', 'NONE', JText::_('COM_REDEVENT_NO_REPEAT')),
			JHTML::_('select.option', 'DAILY', JText::_('COM_REDEVENT_DAILY')),
			JHTML::_('select.option', 'WEEKLY', JText::_('COM_REDEVENT_WEEKLY')),
			JHTML::_('select.option', 'MONTHLY', JText::_('COM_REDEVENT_MONTHLY')),
			JHTML::_('select.option', 'YEARLY', JText::_('COM_REDEVENT_YEARLY'))
		);
		$lists['recurrence_type'] = JHTML::_('select.radiolist', $recur_type, 'recurrence_type', '', 'value', 'text', $xref->rrules->type);

		// Published state selector
		$published = array(JHTML::_('select.option', '1', JText::_('COM_REDEVENT_PUBLISHED')),
			JHTML::_('select.option', '0', JText::_('COM_REDEVENT_UNPUBLISHED')),
			JHTML::_('select.option', '-1', JText::_('COM_REDEVENT_ARCHIVED'))
		);
		$lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $xref->published);

		// Featured state selector
		$options = array(JHTML::_('select.option', '0', JText::_('COM_REDEVENT_SESSION_NOT_FEATURED')),
			JHTML::_('select.option', '1', JText::_('COM_REDEVENT_SESSION_IS_FEATURED'))
		);
		$lists['featured'] = JHTML::_('select.booleanlist', 'featured', '', $xref->featured);

		$pane = & JPane::getInstance('tabs');

		$rolesoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_role')));
		$rolesoptions = array_merge($rolesoptions, $this->get('RolesOptions'));

		$pricegroupsoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_PRICEGROUP')));
		$pricegroupsoptions = array_merge($pricegroupsoptions, $this->get('PricegroupsOptions'));

		JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
		$currencyoptions = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_CURRENCY')));
		$currencyoptions = array_merge($currencyoptions, RHelperCurrency::getCurrencyOptions());

		if (JRequest::getVar('task') == 'copy')
		{
			$xref->id = null;
			$xref->recurrence_id = null;
		}

		// Assign to template
		$this->assignRef('xref', $xref);
		$this->assignRef('editor', $editor);
		$this->assignRef('lists', $lists);
		$this->assignRef('request_url', $uri->toString());
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('customfields', $customfields);
		$this->assignRef('pane', $pane);
		$this->assignRef('roles', $roles);
		$this->assignRef('rolesoptions', $rolesoptions);
		$this->assignRef('prices', $prices);
		$this->assignRef('pricegroupsoptions', $pricegroupsoptions);
		$this->assignRef('currencyoptions',    $currencyoptions);
		$this->assign('standalone', $standalone);

		parent::display($tpl);
	}

	/**
	 * Display close window
	 *
	 * @param   string  $tpl  template
	 *
	 * @return void
	 */
	public function _displayclosexref($tpl)
	{
		$document   = JFactory::getDocument();
		$elsettings = JComponentHelper::getParams('com_redevent');

		$xref = $this->get('xref');

		/* Get the date */
		$date = (!RedeventHelper::isValidDate($xref->dates) ? JText::_('COM_REDEVENT_Open_date') : strftime($elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime($xref->dates)));
		$enddate = (!RedeventHelper::isValidDate($xref->enddates) || $xref->enddates == $xref->dates) ? '' : strftime($elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime($xref->enddates));
		$displaydate = $date . ($enddate ? ' - ' . $enddate : '');

		$displaytime = '';

		/* Get the time */
		if (isset($xref->times) && $xref->times != '00:00:00')
		{
			$displaytime = strftime($elsettings->get('formattime', '%H:%M'), strtotime($xref->times));

			if (isset($xref->endtimes) && $xref->endtimes != '00:00:00')
			{
				$displaytime .= ' - ' . strftime($elsettings->get('formattime', '%H:%M'), strtotime($xref->endtimes));
			}
		}

		$json_data = array('id' => $xref->id,
			'venue' => $xref->venue,
			'date' => $displaydate,
			'time' => $displaytime,
			'published' => $xref->published,
			'note' => $xref->note,
			'featured' => $xref->featured,
			'eventid' => $xref->eventid,
		);

		if (function_exists('json_encode'))
		{
			$js = 'window.parent.updatexref(' . json_encode($json_data) . ');';
			$document->addScriptDeclaration($js);
		}
		else
		{
			echo JText::_('COM_REDEVENT_ERROR_JSON_IS_NOT_ENABLED');
		}

		return;
	}

	/**
	 * prints the code for tags display
	 *
	 * @param   string  $field  field name
	 *
	 * @return void
	 */
	public function printTags($field = '')
	{
		?>
		<div class="tagsdiv">
			<?php echo JHTML::link('index.php?option=com_redevent&view=tags&tmpl=component&field=' . $field, JText::_('COM_REDEVENT_TAGS'), 'class="modal"'); ?>
		</div>
		<?php
	}

	/**
	 * Return a html calendar control field
	 *
	 * @param   string  $value     The date value
	 * @param   string  $name      The name of the text field
	 * @param   string  $id        The id of the text field
	 * @param   string  $format    The date format
	 * @param   string  $onUpdate  function to execute on update
	 * @param   array   $attribs   Additional html attributes
	 *
	 * @return string
	 */
	public function calendar($value, $name, $id, $format = '%Y-%m-%d', $onUpdate = null, $attribs = null)
	{
		// Load the calendar behavior
		JHTML::_('behavior.calendar');

		if (is_array($attribs))
		{
			$attribs = JArrayHelper::toString($attribs);
		}

		$document = JFactory::getDocument();
		$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
        inputField     :    "' . $id . '",     // id of the input field
        ifFormat       :    "' . $format . '",      // format of the input field
        button         :    "' . $id . '_img",  // trigger for the calendar (button ID)
        align          :    "Tl",           // alignment (defaults to "Bl")
        onUpdate       :    ' . ($onUpdate ? $onUpdate : 'null') . ',
        singleClick    :    true
    });});');

		return '<input type="text" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' />' .
		'<img class="calendar" src="' . JURI::root(true) . '/templates/system/images/calendar.png" alt="calendar" id="' . $id . '_img" />';
	}
}
