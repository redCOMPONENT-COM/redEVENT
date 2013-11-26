<?php
// Protect from unauthorized access
defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('behavior.modal');

$option = 'com_redeventsync';

FOFTemplateUtils::addCSS('media://com_redeventsync/css/backend.css');
FOFTemplateUtils::addJS('media://com_redeventsync/js/sync.js');
?>

<div id="sync" class="span12">
	<form action="index.php" method="post" name="adminForm" id="adminForm">

		<div class="width-60 fltlft" id="theform">
			<fieldset class="adminform">
				<ul class="adminformlist">
					<li><label><?php echo JText::_('COM_REDEVENTSYNC_SELECTSESSIONS_FROM'); ?></label>
						<?php echo JHtml::calendar(null, 'sessionsfrom', 'sessionsfrom'); ?></li>

					<li><label><?php echo JText::_('COM_REDEVENTSYNC_SELECTSESSIONS_TO'); ?></label>
						<?php echo JHtml::calendar(null, 'sessionsto', 'sessionsto'); ?></li>
				</ul>
			</fieldset>
		</div>
		<button type="button" id="startsync"><?php echo JText::_('COM_REDEVENTSYNC_SELECTSESSIONS_START_SYNC'); ?></button>
	</form>
</div>
<div class="clr"></div>
<div id="results"></div>
