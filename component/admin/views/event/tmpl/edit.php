<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('rbootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('rjquery.chosen', 'select');

$fieldSets = $this->form->getFieldsets('params');
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

<form action="index.php?option=com_redevent&task=venue.edit&id=<?php echo $this->item->id ?>"
      method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate form-horizontal">

	<ul class="nav nav-tabs" id="eventTab">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_EVENT_INFO_TAB'); ?></strong>
			</a>
		</li>

		<?php if (count($this->customfields)):?>
			<li>
				<a href="#customfields" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_CUSTOM_FIELDS'); ?></strong>
				</a>
			</li>
		<?php endif; ?>

		<li>
			<a href="#registration" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_REGISTRATION'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#submission_types" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SUBMIT_TYPES'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#activation" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_ACTIVATION'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#confirmation" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_CONFIRMATION'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#payment" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_PAYMENT'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#attachments" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'); ?></strong>
			</a>
		</li>
	</ul>


	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('event'); ?>
			</div>
		</div>

		<?php if (count($this->customfields)):?>
			<div class="tab-pane" id="customfields">
				<div class="row-fluid">
					<?php echo $this->loadTemplate('customfields'); ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="tab-pane" id="registration">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('registration'); ?>
			</div>
		</div>

		<div class="tab-pane" id="submission_types">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('submission_types'); ?>
			</div>
		</div>

		<div class="tab-pane" id="activation">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('activation'); ?>
			</div>
		</div>

		<div class="tab-pane" id="confirmation">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('confirmation'); ?>
			</div>
		</div>

		<div class="tab-pane" id="payment">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('payment'); ?>
			</div>
		</div>

		<div class="tab-pane" id="attachments">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('attachments'); ?>
			</div>
		</div>

	</div>

	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
