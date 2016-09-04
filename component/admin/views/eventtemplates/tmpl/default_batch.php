<?php
/**
 * @package     RedEVENT.Backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;
?>
<script type="text/javascript">
	batchFormReset = function(){
		location.reload();
	};
</script>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_REDEVENT_TEMPLATES_BATCH_TITLE');?></legend>
	<div class="row-fluid">

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->filterForm->getLabel('merge_target'); ?>
			</div>
			<div class="controls">
				<?php echo $this->filterForm->getInput('merge_target'); ?>
			</div>
		</div>

		<button class="btn" type="submit" onclick="Joomla.submitbutton('eventtemplates.batch');">
			<?php echo JText::_('COM_REDEVENT_BATCH_FORM_PROCESS'); ?>
		</button>

		<button class="btn" id="batchButtonClear" onclick="batchFormReset();return false;">
			<?php echo JText::_('COM_REDEVENT_BATCH_FORM_RESET'); ?>
		</button>
	</div>
</fieldset>

