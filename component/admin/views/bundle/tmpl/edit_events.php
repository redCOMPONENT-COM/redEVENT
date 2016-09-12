<?php
/**
 * @package    Redevent.admin
 *
 * @copyright  Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

RHelperAsset::load('lib/handlebars.js');
RHelperAsset::load('backend/bundle-events.js');
?>
<div class="row-fluid">
	<table class="table" id="bundle-events">
		<thead>
			<th><?= JText::_('COM_REDEVENT_EVENT') ?></th>
			<th><?= JText::_('COM_REDEVENT_SESSION') ?></th>
			<th><button type="button" class="btn btn-primary add-row">+</button></th>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<!-- Modal -->
<div id="eventModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="eventModalLabel"><?php echo JText::_('COM_REDEVENT_EVENTS'); ?></h3>
	</div>
	<iframe src="" style="border: 0px none transparent; padding: 0px; overflow: hidden;" frameborder="0" width="95%" class="modal-body"></iframe>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_REDEVENT_CLOSE'); ?></button>
	</div>
</div>
<div id="sessionModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="sessionModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="sessionModalLabel"><?php echo JText::_('COM_REDEVENT_SESSIONS'); ?></h3>
	</div>
	<iframe src="" style="border: 0px none transparent; padding: 0px; overflow: hidden;" frameborder="0" width="95%" class="modal-body"></iframe>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_REDEVENT_CLOSE'); ?></button>
	</div>
</div>

<script id="row-template" type="text/x-handlebars-template">
	<tr class="event-row">
		<td>
			<div class="input-append">
				<input type="text" name="event_name[]" value="{{event.title}}" placeholder="<?= JText::_('COM_REDEVENT_SELECT_EVENT') ?>" disabled="disabled"/>
				<button type="button"class="btn btn-primary select-event"><?= JText::_('JSELECT') ?></button>
				<input type="hidden" name="event_id[]" value="{{event.id}}" />
			</div>
		</td>
		<td>
			<div class="input-append">
				<input type="text" name="session_name[]" value="{{session.title}}" placeholder="<?= JText::_('JALL') ?>" disabled="disabled"/>
				<button type="button" class="btn btn-primary select-session"><?= JText::_('JSELECT') ?></button>
				<button type="button" class="btn btn-success reset-session"><?= JText::_('JALL') ?></button>
				<input type="hidden" name="session_id[]" value="{{session.id}}" />
			</div>
		</td>
		<td>
			<button type="button" class="btn btn-primary add-row">+</button>
			<button type="button" class="btn btn-danger delete-row">-</button>
		</td>
	</tr>
</script>
