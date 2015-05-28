<?php
// Protect from unauthorized access
defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('behavior.modal');

$option = 'com_redeventsync';

//RHelperAsset::load('backend.css');
RHelperAsset::load('sync.js');
?>
<div id="sync" class="span12">
	<form
		action="index.php?option=com_redeventsync&view=sync"
		method="post" name="adminForm" class="form-validate form-horizontal" id="adminForm">
		<div class="row-fluid">
			<div class="control-group">
				<div class="control-label">
					<label><?php echo JText::_('COM_REDEVENTSYNC_SELECTSESSIONS_FROM'); ?></label>
				</div>
				<div class="controls">
					<?php echo JHtml::calendar(null, 'sessionsfrom', 'sessionsfrom'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label><?php echo JText::_('COM_REDEVENTSYNC_SELECTSESSIONS_TO'); ?></label>
				</div>
				<div class="controls">
					<?php echo JHtml::calendar(null, 'sessionsto', 'sessionsto'); ?>
				</div>
			</div>
		</div>
		<button type="button" id="startsync"><?php echo JText::_('COM_REDEVENTSYNC_SELECTSESSIONS_START_SYNC'); ?></button>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
<div class="clr"></div>
<div id="results"></div>
