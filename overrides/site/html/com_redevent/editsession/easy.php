<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.formvalidation');

if (RedeventHelper::config()->get('frontendsubmit_allow_past_dates', 0) == 0)
{
	JFactory::getDocument()->addScriptDeclaration(<<<JS
		jQuery(document).ready(function($) {
			document.formvalidator.setHandler('futuredate', function(value) {
				if (!value) {
					return true;
				}

				var today = new Date();
				today = new Date(today.toDateString());
				var val = new Date(value);

				return val >= today;
			});

			// Make sure end date is not before start date
			document.formvalidator.setHandler('datesafter', function(value) {
				var regex = /201[0-9]-[0-1][0-9]-[0-3][0-9]/;
				if (!regex.test(value)) {
					return true;
				}

				var start = document.id('jform_dates').get('value');

				if (!regex.test(start))
				{
					document.id('jform_enddates').setCustomValidity('End date cannot be set if start date is not')
					return false;
				}

				var startDate = new Date(start);
				var endDate = new Date(value);

				if (startDate > endDate)
				{
					document.id('jform_enddates').setCustomValidity('End date cannot be set after start date')
					return false;
				}

				document.id('jform_enddates').setCustomValidity('');

				return true;
			});

			document.formvalidator.setHandler('timesafter', function(value) {
				var regexDate = /201[0-9]-[0-1][0-9]-[0-3][0-9]/;
				var regexTime = /[0-2][0-9]:[0-5][0-9](:[0-5][0-9])*/;

				var startDate = document.id('jform_dates').get('value');
				var startTime = document.id('jform_times').get('value');

				if (!(regexDate.test(startDate) && regexTime.test(startTime)))
				{
					document.id('jform_enddates').setCustomValidity('End date time cannot be set if start date time is not')
					return false;
				}

				var endDate = document.id('jform_enddates').get('value');
				var endTime = document.id('jform_endtimes').get('value');

				if (!regexDate.test(endDate))
				{
					endDate = startDate;
				}

				if (!regexTime.test(endTime))
				{
					endTime = '00:00:00';
				}

				var fullStart = new Date(startDate + ' ' + startTime);
				var fullEnd = new Date(endDate + ' ' + endTime);

				if (fullStart > fullEnd)
				{
					document.id('jform_endtimes').setCustomValidity('End time cannot be set after start time')
					return false;
				}

				document.id('jform_endtimes').setCustomValidity('');

				return true;
			});
		});
JS
	);
}

$viewName = 'editsession';
$option   = 'com_redevent';

$action = JRoute::_('index.php?option=' . $option . '&view=' . $viewName);

/**
 * @var RForm
 */
$form = $this->form;
$imageField = $this->customfields[12];

$short_intro=$this->customfields[6];

$sted=$this->customfields[3];
$full_description=$this->customfields[4];
$note=$this->customfields[5];
$ved=$this->customfields[0];

//print_r($this->customfields);die();
JFactory::getDocument()->addScriptDeclaration(
		"var easySubmitButton = function(task) {
		if (task == 'editsession.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . $this->form->getField('datdescription', 'event')->save() . "
			Joomla.submitform(task);
		}
	};"
);

// Specific validation
$enddatesClassAttributeClassAttribute = $form->getFieldAttribute('enddates', 'class');
$enddatesClassAttributeClassAttribute = $timesClassAttribute ? $timesClassAttribute . ' ' . 'validate-datesafter' : 'validate-datesafter';
$form->setFieldAttribute('enddates', 'class', $enddatesClassAttributeClassAttribute);

$timesClassAttribute = $form->getFieldAttribute('endtimes', 'class');
$timesClassAttribute = $timesClassAttribute ? $timesClassAttribute . ' ' . 'validate-timesafter' : 'validate-timesafter';
$form->setFieldAttribute('endtimes', 'class', $timesClassAttribute);
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
		//jQuery('.redevent-edit-form.style-1 .title-event .controls ').find('.charleft').text( '150');
		jQuery('.redevent-edit-form.style-1 .title-event .controls input').keyup(function () {
			var left = 150 - jQuery(this).val().length;
			if (left < 0) {
				left = 0;
			}
			jQuery('.redevent-edit-form.style-1 .title-event .controls ').find('.charleft').text( left);
		});

		if(jQuery('.redevent-edit-form.style-1 .title-event .controls ').find('input').val()=='')
		{
			jQuery('.redevent-edit-form.style-1 .title-event .controls ').find('.charleft').text( '150');
		}
		else
		{
			var length_title_input=jQuery('.redevent-edit-form.style-1 .title-event .controls ').find('input').val().length;
			var length_title_left=150 - length_title_input;
			jQuery('.redevent-edit-form.style-1 .title-event .controls ').find('.charleft').text( length_title_left);
		}




		jQuery('select').select2();




		jQuery('.redevent-edit-form.style-1 .customfield-hos .controls input').keyup(function () {
			var left = 100 - jQuery(this).val().length;
			if (left < 0) {
				left = 0;
			}
			jQuery('.redevent-edit-form.style-1 .customfield-hos .controls ').find('.charleft').text( left);
		});
		if(jQuery('.redevent-edit-form.style-1 .customfield-hos .controls ').find('input').val()=='')
		{
			jQuery('.redevent-edit-form.style-1 .customfield-hos .controls ').find('.charleft').text( '100');
		}
		else
		{
			var length_title_input=jQuery('.redevent-edit-form.style-1 .customfield-hos .controls ').find('input').val().length;
			var length_title_left=100 - length_title_input;
			jQuery('.redevent-edit-form.style-1 .customfield-hos .controls ').find('.charleft').text(length_title_left);
		}





		jQuery('.redevent-edit-form.style-1 .customfield-ved .controls ').find('.charleft').text( '100');
		jQuery('.redevent-edit-form.style-1 .customfield-ved .controls input').keyup(function () {
			var left = 100 - jQuery(this).val().length;
			if (left < 0) {
				left = 0;
			}
			jQuery('.redevent-edit-form.style-1 .customfield-ved .controls ').find('.charleft').text( left);
		});

		jQuery('.redevent-edit-form.style-1 .customfield-short-intro .controls ').find('.charleft').text( '250');
		jQuery('.redevent-edit-form.style-1 .customfield-short-intro .controls textarea').keyup(function () {
			var left = 250 - jQuery(this).val().length;
			if (left < 0) {
				left = 0;
			}
			jQuery('.redevent-edit-form.style-1 .customfield-short-intro .controls ').find('.charleft').text( left);
		});
		jQuery('.redevent-edit-form.style-1 .customfield-note .controls ').find('.charleft').text( '250');
		jQuery('.redevent-edit-form.style-1 .customfield-note .controls textarea').keyup(function () {
			var left = 250 - jQuery(this).val().length;
			if (left < 0) {
				left = 0;
			}
			jQuery('.redevent-edit-form.style-1 .customfield-note .controls ').find('.charleft').text( left);
		});





	});

</script>

<?php if ($this->params->def('show_page_title', 1)): ?>
	<h1 class='componentheading'>
		<?php echo $this->getTitle(); ?>
	</h1>
<?php endif; ?>
<div class="redevent-edit-form style-1">


	<form enctype="multipart/form-data"
	      action="<?php echo $action; ?>"
	      method="post" name="adminForm" class="form-validate"
	      id="adminForm">
		<div class="short-description"><?php echo JText::_('JTEXT_CREATE_EVENT');?></div>






		<div class="detail-edit-event">
			<div class="form edititem">
				<div class="control-group title-event form-group">
					<div class="control-label title-event">
						<?php echo $this->form->getLabel('title', 'event'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('title', 'event'); ?><span class="required">*</span>
						<div style="clear:both;" class="charleft badge shl-char-counter-title-joomla-be badge-success" title="Show recommended character count: stay green!"></div>
					</div>
				</div>


				<div class="control-group customfield-short-intro form-group">
					<div class="control-label">
						<?php echo $short_intro->getLabel(); ?>
					</div>
					<div class="controls">
						<?php echo $short_intro->render(); ?><span class="required">*</span>
						<div style="clear:both;" class="charleft badge shl-char-counter-title-joomla-be badge-success" title="Show recommended character count: stay green!"></div>

					</div>
				</div>

				<!-- Image -->
				<div class="control-group customfield-images form-group">
					<div class="control-label">
						<label for="12" id="12-lbl" title="" data-original-title="Image">Billede til arrangements oversigten</label>
					</div>
					<div class="controls">
						<?php echo $imageField->render(); ?>
					</div>
				</div>

				<!-- Category -->
				<div class="extra-wrapper">
					<div class="groupWrapper">
						<div class="control-group customfield-datetime form-group">
							<div class="control-label">
								<?php echo $form->getLabel('dates'); ?>
							</div>
							<div class="controls">
								<?php //echo $date_time->render(array('class' => 'validate-futuredate')); ?>
								<?php echo $form->getInput('dates'); ?>
								<span class="required">*</span>
							</div>

						</div>

						<div style="" class="customfield-time  form-group">
							<div class="control-label">
								<?php //echo $date_time->getLabel(); ?>
								<?php echo $form->getLabel('times'); ?>
							</div>
							<div class="controls control-time">
								<div class="input-append clockpicker" data-autoclose="true">
									<?php echo $form->getInput('times'); ?>
									<button type="button" class="btn" id="jform_times_img"><span class="glyphicon glyphicon-time"></span></button>
								</div>
								<span class="required">*</span>
							</div>
						</div>
					</div>

					<div class="groupWrapper">
						<div class="control-group customfield-datetime enddate form-group">
							<div class="control-label">
								<?php echo $form->getLabel('enddates'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('enddates'); ?><span class="required">*</span>
							</div>


						</div>

						<div style="" class="customfield-time endtime" >
							<div class="control-label">
								<?php echo $form->getLabel('endtimes'); ?>
							</div>
							<div class="controls control-time">
								<div class="input-append clockpicker" data-autoclose="true">
									<?php echo $form->getInput('endtimes'); ?>
									<button type="button" class="btn input-group-addons" id="jform_times_img"><span class="glyphicon glyphicon-time"></span></button>
								</div>
								<span class="required">*</span>
							</div>
						</div>
					</div>


					<div class="control-group categories groupWrapper form-group">
						<div class="control-label">
							<?php echo $form->getLabel('categories', 'event'); ?>
						</div>
						<div class="controls">
							<?php echo $form->getInput('categories', 'event'); ?><span class="required">*</span>
						</div>
					</div>

					<div class="control-group customfield-sted groupWrapper form-group">
						<div class="control-label">
							<?php echo $form->getLabel('venueid'); ?>
						</div>
						<div class="controls">
							<?php echo $form->getInput('venueid'); ?><span class="required">*</span>
						</div>

					</div>

					<!-- Hos .... -->
					<div class="control-group customfield-hos groupWrapper form-group">
						<div class="control-label">
							<?php echo $this->customfields[9]->getLabel(); ?>
						</div>
						<div class="controls">
							<?php echo $this->customfields[9]->render(); ?><span class="required">*</span>
							<div style="clear:both;" class="charleft badge shl-char-counter-title-joomla-be badge-success" title="Show recommended character count: stay green!"></div>
						</div>

					</div>
					<!-- ved ...-->
					<div class="control-group customfield-ved groupWrapper form-group">
						<div class="control-label">
							<?php echo $this->customfields[1]->getLabel(); ?>
						</div>
						<div class="controls">
							<?php echo $this->customfields[1]->render(); ?><span class="required">*</span>
							<div style="clear:both;" class="charleft badge shl-char-counter-title-joomla-be badge-success" title="Show recommended character count: stay green!"></div>

						</div>

					</div>

				</div>

				<?php if ($this->params->get('edit_description', 0)) :?>
					<div class="control-group  customfield-full-description form-group">
						<div class="control-label">
							<label for="12" id="12-lbl" title="" data-original-title="Image">Arrangements beskrivelse</label>

						</div>
						<div class="controls table-responsive">
							<?php echo $form->getInput('datdescription', 'event'); ?><span class="required">*</span>
						</div>
					</div>
				<?php endif; ?>
				<div class="control-group customfield-note form-group">

					<div class="controls">

						<?php echo $this->customfields[11]->render(); ?>
						<div style="clear:both;" class="charleft badge shl-char-counter-title-joomla-be badge-success" title="Show recommended character count: stay green!"></div>

					</div>
					<div class="control-label">
						<label for="12" id="12-lbl" title="" data-original-title="Image">F.eks. information om at huske bærbar eller at der serveres kaffe og sandwiches</label>

					</div>

				</div>


			</div>
		</div>


		<input type="hidden" name="option" value="<?php echo $option; ?>">
		<input type="hidden" name="layout" value="easy">
		<input type="hidden" name="s_id" value="<?php echo $this->item->id; ?>">
		<?php echo $form->getInput('id', 'event'); ?>
		<input type="hidden" name="task" value="">
		<?php if ($this->return): ?>
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<?php endif; ?>
		<?php echo JHtml::_('form.token'); ?>
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary btn-save-event" onclick="easySubmitButton('editsession.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn btn-cancel-event" onclick="easySubmitButton('editsession.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>



	</form>
</div>
