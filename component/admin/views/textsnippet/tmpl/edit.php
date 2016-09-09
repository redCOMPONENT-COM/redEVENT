<?php
/**
 * @package    Redevent.admin
 *
 * @copyright  Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

JHtml::_('rjquery.chosen', 'select');

RHelperAsset::load('redevent-backend.css');
?>
<script type="text/javascript">
	jQuery(document).ready(function()
	{
		// Disable click function on btn-group
		jQuery(".btn-group").each(function(index){
			if (jQuery(this).hasClass('disabled'))
			{
				jQuery(this).find("label").off('click');
			}
		});
	});
</script>
<form
	action="index.php?option=com_redevent&task=textsnippet.edit&id=<?php echo $this->item->id; ?>"
	method="post" name="adminForm" class="form-validate form-horizontal" id="adminForm">
	<div class="row-fluid">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('text_name'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('text_name'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('text_description'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('text_description'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('language'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('language'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('text_field'); ?>
			</div>
			<div class="controls">
				<div class="tags-info"><?= RedeventHelperOutput::getTagsEditorInsertModal($this->form->getField('submission_type_webform')) ?></div>
				<?php echo $this->form->getInput('text_field'); ?>
			</div>
		</div>
	</div>
	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
