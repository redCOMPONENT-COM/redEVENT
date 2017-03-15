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
$current = array_reduce(
	$view->prices,
	function($carry, $price)
	{
		return $price->active ? $price : $carry;
	}
	, false
);
?>
<table id="re-prices">
	<tr>
		<td>
			<?php echo JHTML::_('select.genericlist', $view->pricegroupsoptions, 'jform[new_prices][pricegroup][]', '', 'value', 'text', $current ? $current->pricegroup_id : ''); ?>
		</td>
		<td>
			<input type="text" name="jform[new_prices][price][]" class="price-val" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_PRICE'); ?>" value="<?php echo $current ? $current->price : ''; ?>"/>
			<input type="text" name="jform[new_prices][vatrate][]" class="price-vatrate" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_VAT'); ?>" value="<?php echo $current ? $current->vatrate : ''; ?>"/>
			<input type="text" name="jform[new_prices][sku][]" class="price-sku" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_SKU'); ?>" value="<?php echo $current ? $current->sku : ''; ?>"/>
			<?php echo JHTML::_('select.genericlist', $view->currencyoptions, 'jform[new_prices][currency][]', '', 'value', 'text', $current ? $current->currency : ''); ?>
		</td>
	</tr>
</table>

