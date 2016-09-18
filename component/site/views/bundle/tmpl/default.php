<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );

RHelperAsset::load('lib/handlebars.js');
RHelperAsset::load('site/bundle-addtocart.js');
?>
<div id="redevent" class="bundle-details">
<h2><?= $this->bundle->name ?></h2>

	<div class="description"><?= $this->bundle->description ?></div>

	<div id="courses">
		<h3><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SELECT_EVENTS_DATES'); ?></h3>

		<?php foreach ($this->bundle->getBundleEvents() as $bundleEvent): ?>
			<div class="bundle-event" total="<?= count($bundleEvent->getSessions()); ?>">
				<?php $session = $bundleEvent->getNext(); ?>
				<h4><?= $bundleEvent->getEvent()->title ?></h4>

				<table class="selected-date table">
					<thead>
						<tr>
							<th class="session-date"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_SESSION'); ?></th>
							<th class="session-participants"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_PARTICIPANTS'); ?></th>
							<th class="session-price"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_PRICE'); ?></th>
							<th class="session-total"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_TOTAL'); ?></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				<input type="hidden" name="bundleevent[]" value="<?= $bundleEvent->id ?>"/>

				<div class="select-date-button">
					<button type="button" class="btn btn-primary"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_SELECT_SESSION'); ?></button>
				</div>

			</div>
		<?php endforeach; ?>
	</div>

	<div id="grand-total">
		<div class="label"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_GRAND_TOTAL'); ?></div>
	</div>

	<div class="add-to-cart-button">
		<button type="button" class="btn btn-success"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_ADD_TO_CART'); ?></button>
	</div>
</div>

<script id="selected-session-template" type="text/x-handlebars-template">
	<tr>
		<td class="session-date">
			<input type="hidden" name="selected[]" value="{{ id }}"/>{{ label }}
		</td>
		<td class="session-participants"><input name="participants[]" value="1" size="3"/></td>
		<td class="session-price">
			{{#if prices.length}}
				{{#if singleprice}}
					<input type="hidden" name="sessionpricegroup[]" value="{{ prices.0.value }}"/>{{ prices.0.text }}
				{{else}}
					<select name="sessionpricegroup[]">
						{{#each prices}}
						<option value="{{ value }}">{{ text }}</option>
						{{/each}}
					</select>
				{{/if}}
			{{/if}}
		</td>
		<td class="session-total">{{ total }}</td>
	</tr>
</script>

<?php echo $this->loadTemplate('select');
