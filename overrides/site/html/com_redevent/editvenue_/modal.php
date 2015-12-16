<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */
//print_r($this->form->getFieldset('venue'));//die();
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.formvalidation');
JHtml::_('rjquery.chosen', 'select');
JHtml::_('formbehavior.chosen', 'select');

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
		//jQuery("select").select2();
	});
</script>


<form enctype="multipart/form-data" class="edit-venue"
      action="<?php echo JRoute::_('index.php?option=com_redevent&tmpl=component&modal=1&view=editvenue&id=' . $this->item->id); ?>"
      method="post" name="adminForm" class="form-validate"
      id="adminForm">

	
	
	<fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('venue') as $field) : 
			if($field->name!='jform[alias]' && $field->name!='jform[venue_code]' && $field->name!='jform[published]'
				&& $field->name!='jform[language]' && $field->name!='jform[access]' 
				):
			?>
			
			<div class="control-group  field">
				<div class="control-label">
					<?php echo $field->label; ?>
					
				</div>
				<div class="controls">
				<?php echo $field->input; ?>
				<?php if($field->required=='true'):?>
					<span class="required">*</span>
				<?php endif;?>
				</div>
			</div>
		<?php endif; endforeach; ?>
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

	<!-- <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('editvenue.save')">
		<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
	</button>
	<button type="button" class="btn" onclick="Joomla.submitbutton('editvenue.cancel')">
		<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
	</button> -->
	<div class="btn-toolbar">
		<div class="btn-group">
			<button type="button" class="btn btn-primary btn-save-event" onclick="Joomla.submitbutton('editvenue.save')">
				<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn btn-cancel-event" onclick="Joomla.submitbutton('editvenue.cancel')">
				<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
	</div>
	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="fieldId" value="<?php echo $fieldId; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
