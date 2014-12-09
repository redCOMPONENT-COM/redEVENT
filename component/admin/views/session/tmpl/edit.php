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

<form action="index.php?option=com_redevent&task=session.edit&id=<?php echo $this->item->id ?>"
      method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate form-horizontal">

	<ul class="nav nav-tabs" id="sessionTab">
		<li class="active">
			<a href="#main" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_MAIN'); ?></strong>
			</a>
		</li>

		<?php if (count($this->customfields)):?>
			<li>
				<a href="#customfields" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_CUSTOM_FIELDS'); ?></strong>
				</a>
			</li>
		<?php endif; ?>

		<li>
			<a href="#registration" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_REGISTRATION'); ?></strong>
			</a>
		</li>

		<li>
			<a href="#prices" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_PRICES'); ?></strong>
			</a>
		</li>

		<li>
			<a href="#recurrence" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_RECURRENCE'); ?></strong>
			</a>
		</li>

		<li>
			<a href="#roles" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_ROLES'); ?></strong>
			</a>
		</li>

		<li>
			<a href="#ical" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_ICAL'); ?></strong>
			</a>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="main">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('main'); ?>
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
				<div class="span9">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('maxattendees'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('maxattendees'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('maxwaitinglist'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('maxwaitinglist'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('course_credit'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('course_credit'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="prices">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('prices'); ?>
			</div>
		</div>

		<div class="tab-pane" id="recurrence">
			<div class="row-fluid">
				<?php //echo $this->loadTemplate('recurrence'); ?>
			</div>
		</div>

		<div class="tab-pane" id="roles">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('roles'); ?>
			</div>
		</div>

		<div class="tab-pane" id="ical">
			<div class="row-fluid">
				<div class="span9">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('icaldetails'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('icaldetails'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('icalvenue'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('icalvenue'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('id'); ?>
	<?php echo $this->form->getInput('recurrenceid'); ?>
	<?php echo $this->form->getInput('repeat'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
