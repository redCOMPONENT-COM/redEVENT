<?php
/**
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

// Load FOF
include_once JPATH_LIBRARIES . '/fof/include.php';
if (!defined('FOF_INCLUDED'))
{
	JError::raiseError('500', 'FOF is not installed');
}

/**
 * Session form field class
 *
 * @package  Joomla
 * @since    2.0
*/
class JFormFieldREEvent extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'reevent';

	/**
	 * Method to get the field input markup
	 *
	 * @return void
	 */
	protected function getInput()
	{
		// Load modal behavior
		JHtml::_('behavior.modal', 'a.modal');

		$size		= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : ' size="35"';
		$reset	= (string) $this->element['reset'];
		$reset  = ($reset == 'true' || $reset == '1');

		// Build the script
		$script = array();
		$script[] = '    function jSelectEvent_' . $this->id . '(id, title, object) {';
		$script[] = '        document.id("' . $this->id . '_id").value = id;';
		$script[] = '        document.id("' . $this->id . '_name").value = title;';
		$script[] = '        SqueezeBox.close();';
		$script[] = '    }';

		if ($reset)
		{
			$script[] = ' window.addEvent("domready", function(){';
			$script[] = '    document.id("reset' . $this->id . '").addEvent("click", function() {';
			$script[] = '        document.id("' . $this->id . '_id").value = 0;';
			$script[] = '        document.id("' . $this->id . '_name").value = "' . JText::_('COM_REDEVENT_SELECT_EVENT', true) . '";';
			$script[] = '    });';
			$script[] = ' });';
		}

		// Add to document head
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display
		$html = array();
		$link = 'index.php?option=com_redevent&controller=events&amp;view=eventelement&amp;tmpl=component'
		. '&amp;function=jSelectEvent_' . $this->id;

		JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redevent' . DS . 'tables');

		$event =& JTable::getInstance('redevent_events', '');

		if ($this->value)
		{
			$event->load($this->value);
		}
		else
		{
			$event->title = JText::_('COM_REDEVENT_SELECT_EVENT');
		}

		if ($this->value)
		{
			$title = $event->title;
		}

		if (empty($title))
		{
			$title = JText::_('COM_REDEVENT_SELECT_EVENT');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current input field
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled"' . $size . ' />';
		$html[] = '</div>';

		// The select button
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '    <a class="modal" title="' . JText::_('COM_REDEVENT_SELECT_EVENT') . '" href="' . $link .
		'" rel="{handler: \'iframe\', size: {x:700, y:450}}">' .
		JText::_('COM_REDEVENT_SELECT_EVENT') . '</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		if ($reset)
		{
			$html[] = '<div class="button2-left">';
			$html[] = '  <div class="blank">';
			$html[] = '    <a id="reset' . $this->id . '" title="' . JText::_('COM_REDEVENT_RESET') . '">' .
			JText::_('COM_REDEVENT_RESET') . '</a>';
			$html[] = '  </div>';
			$html[] = '</div>';
		}

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
}
