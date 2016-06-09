<?php
/**
 * @package     Redcore
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

$data = $displayData;

/** @var JFormFieldTimePicker $field */
$field = $data['field'];
$class = $data['class'];
$id = $data['id'];
$required = (bool) $data['required'];
$value = $data['value'];
$name = $data['name'];

// Add jquery UI js.
JHtml::_('rjquery.datepicker');
RHelperAsset::load('jquery-ui-timepicker-addon.js', 'com_redevent');
RHelperAsset::load('jquery-ui-timepicker-addon.css', 'com_redevent');

$script = "(function($){
		$(document).ready(function(){
			$('#$id').timepicker();
		});
	})(jQuery);
";

// Add the script to the document.
JFactory::getDocument()->addScriptDeclaration($script);
?>
<div class="input-append">
	<?php if ($required) : ?>
		<input class="required <?php echo $class ?> " name="<?php echo $name ?>" type="text"
		       id="<?php echo $id ?>" required="required" value="<?php echo $value ?>" />
	<?php else : ?>
		<input class="<?php echo $class ?>" name="<?php echo $name ?>" type="text"
		        id="<?php echo $id ?>" value="<?php echo $value ?>" />
	<?php endif; ?>
</div>
