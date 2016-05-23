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
?>
<div class="fieldoptions">
	<fieldset class="checkboxes<?php echo $field->required ? ' required' : ''; ?>">
		<?php foreach ($field->getOptions() as $option): ?>
			<?php $properties = $field->getOptionsProperties($option); ?>
			<div class="checkbox"><input <?php echo $field->propertiesToString($properties); ?>/>
				<label for="<?php echo $properties['name']; ?>"><?php echo $option->text; ?></label></div>
		<?php endforeach; ?>
	</fieldset>
</div>
