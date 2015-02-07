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

<form action="index.php?option=com_redevent&task=category.edit&id=<?php echo $this->item->id ?>"
      method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate form-horizontal">

	<ul class="nav nav-tabs" id="categoryTab">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_EVENT_INFO_TAB'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#rules" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_CATEGORY_FIELDSET_RULES'); ?></strong>
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
				<div class="span9">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('name'); ?>
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
							<?php echo $this->form->getLabel('published'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('published'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('parent_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('parent_id'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('access'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('color'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('color'); ?>
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
							<?php echo $this->form->getLabel('event_template'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('event_template'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('description'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('description'); ?>
						</div>
					</div>
				</div>
				<div class="span3">
					<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="rules">
			<div class="row-fluid">
				<?php echo $this->form->renderField('rules'); ?>
			</div>
		</div>

		<div class="tab-pane" id="attachments">
			<div class="row-fluid">
				<?php echo RLayoutHelper::render('attachments.edit', $this, null, array('component' => 'com_redevent')); ?>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
