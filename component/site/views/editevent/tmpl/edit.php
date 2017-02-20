<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.formvalidation');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'editevent.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . ($this->params->get('edit_description', 0) ? $this->form->getField('datdescription')->save() : '') . "
			" . ($this->params->get('edit_summary', 1) ? $this->form->getField('summary')->save() : '') . "
			Joomla.submitform(task);
		}
	};
");
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

<?php if ($this->params->def('show_page_heading', 1)): ?>
	<h1 class='componentheading'>
		<?php echo $this->getTitle(); ?>
	</h1>
<?php endif; ?>

<div class="redevent-edit-form<?= $this->params->get('pageclass_sfx') ?>">
	<form enctype="multipart/form-data"
	      action="<?php echo JRoute::_('index.php?option=com_redevent&view=editevent&e_id=' . $this->item->id); ?>"
	      method="post" name="adminForm" class="form-validate"
	      id="adminForm">

		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('editevent.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('editevent.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>

		<ul class="nav nav-tabs" id="userTab">
			<li class="active">
				<a href="#editmain" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_DETAILS'); ?></strong>
				</a>
			</li>

			<?php if ($this->params->get('allow_attachments', 1)): ?>
				<li>
					<a href="#attachments" data-toggle="tab">
						<strong><?php echo JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'); ?></strong>
					</a>
				</li>
			<?php endif; ?>
		</ul>

		<div class="tab-content">
			<div class="tab-pane active" id="editmain">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<div class="span9">
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('title'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('title'); ?>
									</div>
								</div>

								<?php if ($this->params->get('edit_categories', 0)): ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('categories'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('categories'); ?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ($this->canpublish): ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('published'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('published'); ?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ($this->params->get('edit_customs', 0) && count($this->customfields)): ?>
									<?php foreach ($this->customfields as $field): ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $field->getLabel(); ?>
											</div>
											<div class="controls">
												<?php echo $field->render(); ?>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>

								<?php if (($this->params->get('edit_image', 1) == 2) || ($this->params->get('edit_image', 1) == 1)) : ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('datimage'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('datimage'); ?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ($this->params->get('edit_summary', 1)) :?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('summary'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('summary'); ?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ($this->params->get('edit_description', 0)) :?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('datdescription'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('datdescription'); ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
						</fieldset>
					</div>
				</div>
			</div>

			<?php if ($this->params->get('allow_attachments', 1)): ?>
			<div class="tab-pane" id="attachments">
				<div class="row-fluid">
					<div class="span12">
						<?php echo RedeventLayoutHelper::render('attachments.edit', $this); ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<?php echo $this->form->getInput('id'); ?>
		<input type="hidden" name="option" value="com_redevent" />
		<input type="hidden" name="task" value="" />
		<?php if ($this->return): ?>
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<?php endif; ?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
