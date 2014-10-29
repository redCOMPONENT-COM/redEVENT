<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');


/**
 * RedEvent Category form field
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class JFormFieldRECategory extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'recategory';

	/**
	 * display reset button
	 * @var boolean
	 */
	protected $reset;

	/**
	* Method to get the field input markup
	 *
	 * @return string
	*/
	protected function getInput()
	{
		// Load modal behavior
		JHtml::_('behavior.modal', 'a.modal_' . $this->id);

		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : ' size="35"';
		$reset = (string) $this->element['reset'];
		$reset = ($reset == 'true' || $reset == '1');

		// Build the script
		$script = array();
		$script[] = '    function jSelectCategory_' . $this->id . '(id, title) {';
		$script[] = '        document.id("' . $this->id . '_id").value = id;';
		$script[] = '        document.id("' . $this->id . '_name").value = title;';
		$script[] = '        SqueezeBox.close();';
		$script[] = '    }';

		if ($reset)
		{
			$script[] = ' window.addEvent("domready", function(){';
			$script[] = '    document.id("reset' . $this->id . '").addEvent("click", function() {';
			$script[] = '        document.id("' . $this->id . '_id").value = 0;';
			$script[] = '        document.id("' . $this->id . '_name").value = "' . JText::_('COM_REDEVENT_SELECT_CATEGORY', true) . '";';
			$script[] = '    });';
			$script[] = ' });';
		}

		// Add to document head
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display
		$html = array();
		$link = 'index.php?option=com_redevent&amp;view=categories&amp;layout=element&amp;tmpl=component'
			. '&amp;function=jSelectCategory_' . $this->id;

		$tmp = RTable::getInstance('Category', 'RedeventTable');
		$category = clone $tmp;

		if ($this->value)
		{
			$category->load($this->value);
		}
		else
		{
			$category->name = JText::_('COM_REDEVENT_SELECT_CATEGORY');
		}

		if ($this->value)
		{
			$title = $category->name;
		}

		if (empty($title))
		{
			$title = JText::_('COM_REDEVENT_SELECT_CATEGORY');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current input field
		$html[] = '<div class="input-append">';
		$html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled"' . $size . ' />';

		// The select button
		$html[] = '    <a class="btn btn-primary modal_' . $this->id . '" title="' . JText::_('COM_REDEVENT_SELECT_CATEGORY') . '" href="' . $link .
			'" rel="{handler: \'iframe\', size: {x:700, y:450}}">' .
			JText::_('COM_REDEVENT_SELECT_CATEGORY') . '</a>';

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

		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
