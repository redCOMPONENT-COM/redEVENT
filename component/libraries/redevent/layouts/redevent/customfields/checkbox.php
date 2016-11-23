<?php
/**
 * @package     RedEVENT
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

extract($displayData);

?>
<?php if ($options): ?>
	<?php foreach ($options as $option): ?>
		<?php $checked = in_array($option->value, $selected) ? ' checked="checked"' : ''; ?>
		<div class="checkbox">
			<label>
				<input type="hidden" name="jform[<?php echo $field->fieldname; ?>][]" value="0">
				<input type="checkbox" name="jform[<?php echo $field->fieldname; ?>][]" value="<?php echo $option->value; ?>"<?php echo $checked; ?>
				<?php echo $attributes; ?>/>
				<?php echo $option->text; ;?>
			</label>
		</div>
	<?php endforeach; ?>
<?php endif; ?>

