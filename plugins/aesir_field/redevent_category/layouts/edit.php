<?php
/**
 * @package     RedITEM
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

extract($displayData);

$isNew = JFactory::getApplication()->input->getInt('id', 0) == 0;

if (!empty($default) && $isNew)
{
	$value = $default;
}

$attributes['id'] = $id;
?>
<div class="<?php echo $attribs['class']; ?>" datal-fieldId="<?php echo $field->id; ?>">
	<?php echo JHtml::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $value); ?>
</div>
