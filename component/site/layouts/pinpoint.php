<?php

?>
<!-- Button to trigger modal -->
<a href="#pinpointModal" role="button" class="btn" data-toggle="modal"><i class="icon-map-marker"></i> <?php echo JText::_('COM_REDEVENT_BUTTON_LABEL_PINPOINT'); ?></a>

<!-- Modal -->
<div id="pinpointModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="pinpointModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="pinpointModalLabel"><?php echo JText::_('COM_REDEVENT_PINPOINT_MODAL_TITLE'); ?></h3>
	</div>
	<div class="modal-body">
		<div id="pinpointMapCanvas" style="height: 400px; width: 100%;"></div>
	</div>
	<div class="modal-footer">
		<p><?php echo JText::_('COM_REDEVENT_PINPOINT_MODAL_INSTRUCTIONS'); ?></p>
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_REDEVENT_CLOSE'); ?></button>
		<button class="btn btn-primary" type="button" id="locationSave"><?php echo JText::_('COM_REDEVENT_PINPOINT_MODAL_UPDATE_LOCATION'); ?></button>
	</div>
</div>
