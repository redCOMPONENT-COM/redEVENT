<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.formvalidation');
JHtml::_('rjquery.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'editvenue.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . $this->form->getField('locdescription')->save() . "
			Joomla.submitform(task);
		}
	};
");
$fieldId = JFactory::getApplication()->input->get('fieldId');
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

	function venueSubmit(task)
	{
		if (task == 'editvenue.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			Joomla.submitform(task);
		}
	}
</script>


<form enctype="multipart/form-data"
      action="<?php echo JRoute::_('index.php?option=com_redevent&tmpl=component&modal=1&view=editvenue&id=' . $this->item->id); ?>"
      method="post" name="adminForm" class="form-validate"
      id="adminForm">

	<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('editvenue.save')">
		<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
	</button>
	<button type="button" class="btn" onclick="Joomla.submitbutton('editvenue.cancel')">
		<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
	</button>

	<fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('venue') as $field) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</fieldset>

	<fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('address') as $field) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="control-group">
			<div class="control-label"></div>
			<div class="controls">
				<?php echo RedeventHelperOutput::pinpointicon($this->item); ?>
			</div>
		</div>
	</fieldset>

	<button type="button" class="btn btn-primary" onclick="venueSubmit('editvenue.save')">
		<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
	</button>
	<button type="button" class="btn" onclick="venueSubmit('editvenue.cancel')">
		<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
	</button>

	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="fieldId" value="<?php echo $fieldId; ?>" />
	<input type="hidden" name="modal" value="1" />
	<?php echo JHtml::_('form.token'); ?>
</form>
