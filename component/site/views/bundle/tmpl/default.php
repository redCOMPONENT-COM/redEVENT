<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );

RHelperAsset::load('lib/handlebars.js');
RHelperAsset::load('site/bundle-addtocart.js');
RHelperAsset::load('site/bundle-addtocart.css');
?>
<div id="redevent" class="bundle-details<?= $this->params->get('pageclass_sfx') ?>">

	<?php if ($this->params->def( 'show_page_heading', 1 )) : ?>
		<h1 class="componentheading">
			<?php echo $this->escape($this->pagetitle); ?>
		</h1>
	<?php endif; ?>

	<h2><?= $this->bundle->name ?></h2>

	<div class="description"><?= $this->bundle->description ?></div>

	<form id="courses" method="post" action="index.php?option=com_redevent&task=bundle.addtocart">
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

		<div id="grand-total">
			<div class="label"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_GRAND_TOTAL'); ?></div>
			<div class="price"></div>
		</div>

		<div id="add-to-cart-button">
			<button type="button" class="btn btn-success"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_ADD_TO_CART'); ?></button>
		</div>

		<input type="hidden" name="id" value="<?= $this->bundle->id ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

<script id="selected-session-template" type="text/x-handlebars-template">
	<tr>
		<td class="session-date">
			<input type="hidden" name="selected[]" value="{{ id }}"/>{{ label }}
		</td>
		<td class="session-participants"><input name="participants[]" value="1" size="3"/>{{#if hasLimit}} <?= JText::_('COM_REDEVENT_VIEW_BUNDLE_EVENT_MAX') ?>{{ left }}{{/if}}</td>
		<td class="session-price">
			{{#if prices.length}}
				{{#if singleprice}}
					<input type="hidden" name="sessionpricegroup[]" value="{{ prices.0.id }}" price="{{ prices.0.price }}" currency="{{ prices.0.currency }}"/>
					{{ prices.0.currency }} <span class="price">{{ prices.0.price }}</span>
				{{else}}
					<select name="sessionpricegroup[]">
						{{#each prices}}
						<option value="{{ id }}" price="{{ price }} currency="{{ currency }}">{{ currency }} {{ price }}</option>
						{{/each}}
					</select>
				{{/if}}
			{{else}}
			<input type="hidden" name="sessionpricegroup[]" value="0"/>-
			{{/if}}
		</td>
		<td class="session-total">{{ total }}</td>
	</tr>
</script>

<?php echo $this->loadTemplate('select');
