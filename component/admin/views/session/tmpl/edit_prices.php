<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

RHelperAsset::load('xref_prices.js');
?>
<table class="adminform" id="re-prices">
	<?php if ($this->prices): ?>
	<?php foreach ((array) $this->prices as $k => $r): ?>
		<tr>
			<td>
				<?php echo JHTML::_('select.genericlist', $this->pricegroupsoptions, 'jform[pricegroup][]', '', 'value', 'text', $r->pricegroup_id); ?>
			</td>
			<td>
				<input type="text" name="jform[price][]" class="price-val" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_PRICE'); ?>" value="<?php echo $r->price; ?>"/>
				<input type="text" name="jform[vatrate][]" class="price-vatrate" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_VAT'); ?>" value="<?php echo $r->vatrate; ?>"/>
				<input type="text" name="jform[sku][]" class="price-sku" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_SKU'); ?>" value="<?php echo $r->sku; ?>"/>
				<?php echo JHTML::_('select.genericlist', $this->currencyoptions, 'jform[currency][]', '', 'value', 'text', $r->currency); ?>
			</td>
			<td>
				<button type="button" class="btn price-button remove-price"><?php echo Jtext::_('COM_REDEVENT_REMOVE'); ?></button>
			</td>
		</tr>
	<?php endforeach; ?>
	<?php endif; ?>
	<tr id="trnewprice">
		<td>
			<?php echo JHTML::_('select.genericlist', $this->pricegroupsoptions, 'jform[pricegroup][]', array('id' => 'newprice', 'class' => 'price-group'), 'value', 'text'); ?>
		</td>
		<td>
			<input type="text" name="jform[price][]" class="price-val" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_PRICE'); ?>"/>
			<input type="text" name="jform[vatrate][]" class="price-vatrate" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_VAT'); ?>"/>
			<input type="text" name="jform[sku][]" class="price-sku" placeholder="<?php echo JText::_('COM_REDEVENT_SESSION_PRICEGROUP_SKU'); ?>"/>
			<?php echo JHTML::_('select.genericlist', $this->currencyoptions, 'jform[currency][]', array('class' => 'price-currency'), 'value', 'text'); ?>
		</td>
		<td>
			<button type="button" class="btn price-button" id="add-price"><?php echo JText::_('COM_REDEVENT_add'); ?></button>
		</td>
	</tr>
</table>
