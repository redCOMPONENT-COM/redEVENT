<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Session form field class
 *
 * @package  Redevent.admin
 * @since    2.0
*/
class JFormFieldSession extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'Session';

	/**
	 * Method to get the field input markup
	 *
	 * @return void
	 */
	protected function getInput()
	{
		// Load modal behavior
		JHtml::_('behavior.modal', 'a.modal_' . $this->id);

		$size		= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : ' size="35"';
		$reset	= (string) $this->element['reset'];
		$reset  = ($reset == 'true' || $reset == '1');

		// Build the script
		$script = array();
		$script[] = '    function jSelectSession_' . $this->id . '(id, title, object) {';
		$script[] = '        document.id("' . $this->id . '_id").value = id;';
		$script[] = '        document.id("' . $this->id . '_name").value = title;';
		$script[] = '        SqueezeBox.close();';
		$script[] = '    }';

		if ($reset)
		{
			$script[] = ' window.addEvent("domready", function(){';
			$script[] = '    document.id("reset' . $this->id . '").addEvent("click", function() {';
			$script[] = '        document.id("' . $this->id . '_id").value = 0;';
			$script[] = '        document.id("' . $this->id . '_name").value = "' . JText::_('COM_REDEVENT_SELECT_SESSION', true) . '";';
			$script[] = '    });';
			$script[] = ' });';
		}

		// Add to document head
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display
		$html = array();
		$link = 'index.php?option=com_redevent&amp;view=sessions&amp;layout=element&amp;tmpl=component'
		. '&amp;function=jSelectSession_' . $this->id;

		if ($this->element['event'])
		{
			$link .= '&jForm[filter.event]=' . $this->element['event'];
		}

		if ($this->value)
		{
			$title = $this->getSessionTitle($this->value);
		}
		else
		{
			$title = JText::_('COM_REDEVENT_SELECT_SESSION');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current input field
		$html[] = '<div class="input-append">';
		$html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled"' . $size . ' />';

		// The select button
		$html[] = '    <a class="btn btn-primary modal_' . $this->id . '" title="' . JText::_('COM_REDEVENT_SELECT_SESSION') . '" href="' . $link .
		'" rel="{handler: \'iframe\', size: {x:700, y:450}}">' .
		JText::_('COM_REDEVENT_SELECT_SESSION') . '</a>';

		if ($reset)
		{
			$html[] = '    <a id="reset' . $this->id . '" class="btn" title="' . JText::_('COM_REDEVENT_RESET') . '">' .
			JText::_('COM_REDEVENT_RESET') . '</a>';
		}

		$html[] = '</div>';

		// The active id field
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// Class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}

	/**
	 * Get title
	 *
	 * @param   int  $sessionId  session if
	 *
	 * @return string
	 */
	private function getSessionTitle($sessionId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('e.title, x.dates');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x');
		$query->where('x.id = ' . (int) $sessionId);

		$db->setQuery($query, 0, 1);

		$res = $db->loadObject();

		if ($res->dates)
		{
			return $res->title . ' - ' . $res->dates;
		}
		else
		{
			return $res->title;
		}
	}
}
