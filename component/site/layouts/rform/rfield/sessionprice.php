<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;
$properties = $data->getInputProperties();
$params = JComponentHelper::getParams('com_redevent');
?>
<?php if (!count($data->options)): ?>
	<?php if ($params->get('show_no_price_as_free', 1)): ?>
		<?php echo JText::_('COM_REDEVENT_EVENT_PRICE_FREE'); ?>
	<?php else: ?>
		<?= RHelperCurrency::getFormattedPrice(0, $data->getDefaultCurrency()) ?>
	<?php endif; ?>
<?php else: ?>
	<?php if ($data->readonly || !empty($properties['readonly'])): ?>
		<?php
			$option = $data->getSelectedOption();
			$properties = $data->getInputProperties();
			$properties['type'] = 'hidden';
			$properties['value'] = $option->value;
			$properties['price'] = $option->price;
			$properties['vat'] = $option->vat;
			$properties['readonly'] = 'readonly';
		?>
		<input <?php echo $data->propertiesToString($properties); ?>/>
		<?php echo RedeventHelperOutput::formatprice($option->price, $option->currency); ?>
	<?php else: ?>
		<?php if ($params->get('price_select_layout', 'select') == 'select'): ?>
			<select <?php echo $data->propertiesToString($properties); ?>>
				<?php foreach ($data->options as $option): ?>
					<?php $properties = $data->getOptionProperties($option); ?>
					<option value="<?= $option->value ?>" price="<?= $option->price ?>" vat="<?= $option->vat ?>" currency="<?= $option->currency ?>"
						<?= ($option->value == $data->getValue()) ? 'selected="selected"' : '' ?>>
						<?php echo $option->label; ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<div class="fieldoptions">
				<?php foreach ($data->options as $option): ?>
					<div class="fieldoption">
						<?php $properties = $data->getOptionProperties($option); ?>
						<input <?php echo $data->propertiesToString($properties); ?>/>
						<?php echo $option->label; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
