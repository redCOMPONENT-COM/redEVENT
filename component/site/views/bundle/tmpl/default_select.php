<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );
?>
<script id="select-session-template" type="text/x-handlebars-template">
	<div class="select-session-list">
		<h5><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_TITLE'); ?></h5>
		<div class="filters">
			<div class="total-available">
				{{ total }} <?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_TOTAL'); ?>
			</div>
			<div class="lists">
				<select name="filter[venue]">
					<option value=""><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_VENUE'); ?></option>
					{{#each venueoptions}}
					<option value="{{ value }}">{{ text }}</option>
					{{/each}}
				</select>

				<select name="filter[time]">
					<option value=""><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_TIME'); ?></option>
					{{#each timeoptions}}
					{{/each}}
				</select>

				<select name="filter[language]">
					<option value=""><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_LANGUAGE'); ?></option>
					{{#each languageoptions}}
					{{/each}}
				</select>
			</div>
		</div>

		<table class="table">
			<thead>
				<tr>
					<th><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_DATE'); ?></th>
					<th><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_DURATION'); ?></th>
					<th><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_LANGUAGE'); ?></th>
					<th><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_VENUE'); ?></th>
					<th><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_PRICE'); ?></th>
					<th><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_SEATS'); ?></th>
					<th><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_BOOK_COURSE'); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

		<div class="show-more-button">
			<button type="button" class="btn btn-primary"><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SESSION_LIST_SHOW_MORE'); ?></button>
		</div>
	</div>
</script>
