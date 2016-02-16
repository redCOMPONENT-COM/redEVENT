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

$fieldId = JFactory::getApplication()->input->get('fieldId');

if (!$this->form->getValue('country'))
{
	$this->form->setValue('country', null, 'DK');
}

$this->form->setValue('categories', null, '1');


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

		
		var maxlengths;
		var controls;
		var findelement;
		var text_placeholder;
		function set_max_limit_for_element(inputs,maxlengths,controls,findelement)
		{
			jQuery(inputs).attr('maxlength',maxlengths );
			/*Set limit text when keyup*/
			jQuery(inputs).keyup(function () {
			var left = maxlengths - jQuery(this).val().length;
			if (left < 0) {
				left = 0;
			}
			jQuery(controls).find('.charleft').text( left);
			});

			if(jQuery(controls).find('input').val()=='')
			{
				jQuery(controls).find('.charleft').text( maxlengths);
			}
			else
			{
				var length_title_input=jQuery(controls).find(findelement).val().length;
				var length_title_left=maxlengths - length_title_input;
				jQuery(controls).find('.charleft').text( length_title_left);
			}

		}
		function set_placeholder(input,text_placeholder)
		{
			jQuery(input).attr('placeholder',text_placeholder) ;
		}
		// Disable click function on btn-group
		jQuery(".btn-group").each(function(index){
			if (jQuery(this).hasClass('disabled'))
			{
				jQuery(this).find("label").off('click');
			}
		});
		set_max_limit_for_element('.edit-venue .jform_venue input',50,'.edit-venue .jform_venue .controls ','input');
		set_max_limit_for_element('.edit-venue .jform_locdescription textarea',150,'.edit-venue .jform_locdescription .controls ','textarea');
		set_placeholder('.edit-venue .jform_venue .controls input','Mødested');
		set_placeholder('.edit-venue .jform_street input','Gade');
		set_placeholder('.edit-venue .jform_plz input','Postnummer');
		set_placeholder('.edit-venue .jform_city input','By');
		set_placeholder('.edit-venue .jform_url input','Webadressen skal være i dette format -http://www.google.dk');
		set_placeholder('.edit-venue .jform_company .controls input','Virksomhed knyttet til dette mødested');
		jQuery('.edit-venue .jform_locdescription textarea').attr('maxlength', '150');


		//jQuery("select#jform_country option[value=\"DK\"]").prop('selected', 'selected');
		jQuery("select#jform_country").find("option:contains('DK')").each(function(){
	     if( jQuery(this).text() == 'DK - Denmark' ) {
	        jQuery(this).attr("selected","selected");
	     }
 });
		//jQuery("select").select2();

	});

	function venueSubmit(task)
	{
		if (task == 'editvenue.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			Joomla.submitform(task);
		}
	}
</script>

<form enctype="multipart/form-data" class="edit-venue form-validate"
      action="<?php echo JRoute::_('index.php?option=com_redevent&tmpl=component&modal=1&view=editvenue&id=' . $this->item->id); ?>"
      method="post" name="adminForm" class=""
      id="adminForm">



	
	<fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('venue') as $field) :
		//print_r($field);die();
			if ($field->name == 'jform[locdescription]'): ?>
			<div class="control-group  field <?php echo $field->id ?>">


	<fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('venue') as $field) :
			if ($field->name == 'jform[locdescription]'): ?>
			<div class="control-group  field <?php echo $field->name ?>">

				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<textarea name="jform[locdescription]"

					          class="locdescription" cols="50" rows="2"><?php echo $this->item->locdescription; ?></textarea>
					<div style="clear:both;" class="charleft badge shl-char-counter-title-joomla-be badge-success" title="Show recommended character count: stay green!"></div>

				</div>
			</div>
			<?php elseif($field->name!='jform[alias]' && $field->name!='jform[venue_code]' && $field->name!='jform[published]'
				&& $field->name!='jform[language]' && $field->name!='jform[access]' && $field->name!='jform[status]' && $field->name!='jform[locimage]'
				):
			?>
			<div class="control-group  field <?php echo $field->id ?>">

					          class="locdescription" cols="50" rows="10"><?php echo $this->item->locdescription; ?></textarea>
				</div>
			</div>
			<?php elseif($field->name!='jform[alias]' && $field->name!='jform[venue_code]' && $field->name!='jform[published]'
				&& $field->name!='jform[language]' && $field->name!='jform[access]' && $field->name!='jform[status]'
				):
			?>
			<div class="control-group  field <?php echo $field->name ?>">

				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
				<?php echo $field->input; ?>
				<?php if($field->required=='true'):?>
					<span class="required">*</span>
					<div style="clear:both;" class="charleft badge shl-char-counter-title-joomla-be badge-success" title="Show recommended character count: stay green!"></div>

				<?php endif;?>
				</div>
			</div>
		<?php endif; endforeach; ?>
	</fieldset>

	<fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('address') as $field) :
			if ($field->name == 'jform[locdescription]'): ?>
				<textarea name="jform[locdescription]" class="locdescription">
					<?php echo $this->item->locdescription; ?>
				</textarea>

			<?php elseif ($field->name != 'jform[state]' && $field->name!='jform[latitude]' && $field->name!='jform[longitude]' && $field->name!='jform[map]'): ?>
			<div class="control-group <?php echo $field->id ?>">
			<?php elseif ($field->name != 'jform[state]'): ?>
			<div class="control-group <?php echo $field->name ?>">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endif; endforeach; ?>

		<div class="control-group hidden">

		<div class="control-group">

			<div class="control-label"></div>
			<div class="controls">
				<?php echo RedeventHelperOutput::pinpointicon($this->item); ?>
			</div>
		</div>
	</fieldset>

	<div class="row">
	<div class="btn-toolbar">
		<div class="btn-group">
			<button type="submit" class="btn btn-primary btn-save-event" onclick="venueSubmit('editvenue.save')">
				<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="cancel" class="btn btn-cancel-event" onclick="venueSubmit('editvenue.cancel')">
				<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
	</div>
	</div>
	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="fieldId" value="<?php echo $fieldId; ?>" />
	<input type="hidden" name="modal" value="1" />
	<?php echo JHtml::_('form.token'); ?>
</form>
