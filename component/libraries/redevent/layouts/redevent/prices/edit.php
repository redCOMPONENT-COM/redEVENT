<?php
/**
 * @package     RedEVENT
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

RHelperAsset::load('xref_prices.js');
RHelperAsset::load('editprices.css');

$view = $displayData;
?>
<table id="re-prices">
	<?php if ($view->prices): ?>
	<?php foreach ((array) $view->prices as $k => $r): ?>
		<tr>
			<td>
				<?php echo JHTML::_('select.genericlist', $view->pricegroupsoptions, 'jform[pricegroup][]', '', 'value', 'text', $r->pricegroup_id); ?>
			</td>
			<td>
				<input type="text" name="jform[price][]" class="price-val" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_PRICE'); ?>" value="<?php echo $r->price; ?>"/>
				<input type="text" name="jform[vatrate][]" class="price-vatrate" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_VAT'); ?>" value="<?php echo $r->vatrate; ?>"/>
				<input type="text" name="jform[sku][]" class="price-sku" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_SKU'); ?>" value="<?php echo $r->sku; ?>"/>
				<?php echo JHTML::_('select.genericlist', $view->currencyoptions, 'jform[currency][]', '', 'value', 'text', $r->currency); ?>
			</td>
			<td>
				<button type="button" class="btn price-button remove-price"><?php echo Jtext::_('COM_REDEVENT_REMOVE'); ?></button>
			</td>
		</tr>
	<?php endforeach; ?>
<?php endif; ?>
<tr id="trnewprice">
	<td>
		<?php echo JHTML::_('select.genericlist', $view->pricegroupsoptions, 'jform[pricegroup][]', array('id' => 'newprice', 'class' => 'price-group'), 'value', 'text'); ?>
	</td>
	<td>
		<input type="text" name="jform[price][]" class="price-val" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_PRICE'); ?>"/>
		<input type="text" name="jform[vatrate][]" class="price-vatrate" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_VAT'); ?>"/>
		<input type="text" name="jform[sku][]" class="price-sku" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_SKU'); ?>"/>
		<?php echo JHTML::_('select.genericlist', $view->currencyoptions, 'jform[currency][]', array('class' => 'price-currency'), 'value', 'text'); ?>
	</td>
	<td>
		<button type="button" class="btn price-button" id="add-price"><?php echo JText::_('COM_REDEVENT_add'); ?></button>
	</td>
</tr>
</table>

