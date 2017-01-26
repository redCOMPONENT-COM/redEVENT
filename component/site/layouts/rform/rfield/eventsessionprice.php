<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$field = $displayData;
$selectProperties = $field->getSelectProperties();
?>
<select <?php echo $field->propertiesToString($selectProperties); ?>>
	<?php foreach ($field->options as $option): ?>
		<?php $properties = $field->getOptionProperties($option); ?>
		<option <?php echo $field->propertiesToString($properties); ?>
			<?= ($option->value == $field->getValue()) ? 'selected="selected"' : '' ?>>
			<?php echo $option->label; ?>
		</option>
	<?php endforeach; ?>
</select>
