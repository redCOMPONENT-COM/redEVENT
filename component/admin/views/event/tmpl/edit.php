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
		<?php if ($this->item->id):?>
		<li>
			<a href="#session" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SESSION'); ?></strong>
			</a>
		</li>
		<?php endif; ?>
		<li>
			<a href="#details" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_EVENT_INFO_TAB'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#details" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_EVENT_INFO_TAB'); ?></strong>
			</a>
		</li>
	</ul>


	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<div class="row-fluid">
				<div class="span9">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('title'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('title'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('course_code'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('course_code'); ?>
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
							<?php echo $this->form->getLabel('published'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('published'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('created_by'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('created_by'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('categories'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('categories'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('datimage'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('datimage'); ?>
						</div>
					</div>

					<?php if (file_exists(JPATH_SITE . '/components/com_redmailflow') && JComponentHelper::isEnabled('com_redmailflow')): ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('mailflow_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('mailflow_id'); ?>
						</div>
					</div>
					<?php endif; ?>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('details_layout'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('details_layout'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('datdescription'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('datdescription'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('summary'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('summary'); ?>
						</div>
					</div>
				</div>
				<div class="span3">
					<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
