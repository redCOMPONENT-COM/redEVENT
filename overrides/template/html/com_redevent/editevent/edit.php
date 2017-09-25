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

$document = &JFactory::getDocument();
$renderer   = $document->loadRenderer('modules');
$position_breadcrumbs   = 'breadcrumbs';
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

<div class="container myevents event-create control-create">
	<?php echo $renderer->render($position_breadcrumbs, $options, null); ?>
	<div class="aesir-container user-management-cotaniner">
		<div class="aesir-item-header reditem-content-header text-center">
			<h1 class="header-title"><?php echo $this->getTitle(); ?></h1>
		</div>
		<div class="redevent-edit-form<?= $this->params->get('pageclass_sfx') ?>">
			<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_redevent&view=editevent&e_id=' . $this->item->id); ?>" method="post" name="adminForm" class="form-validate form-control-custom" id="adminForm">
				<div class="row">
					<div class="left-sidebar col-md-5 col-lg-3">
						<div class="white-block-border">
							<div class="tab-header"><?php echo JText::_('COM_REDITEM_ITEM_LABEL_FIELD_GROUPS'); ?></div>
							<ul class="nav menu navbar-nav js-aesir-tabs-container" id="userTab">
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
						</div>
					</div>
					<div class="col-md-7 col-lg-9">
						<div class="white-block-border">
							<div class="tab-content">
								<div class="tab-pane active" id="editmain">
									<div class="tab-header">
										<?php echo JText::_('COM_REDEVENT_DETAILS'); ?>
									</div>
									<fieldset>
										<div class="form-group">
											<?php echo $this->form->getLabel('title'); ?>
											<?php echo $this->form->getInput('title'); ?>
										</div>

										<?php if ($this->params->get('edit_categories', 0)): ?>
											<div class="form-group">
												<?php echo $this->form->getLabel('categories'); ?>
												<?php echo $this->form->getInput('categories'); ?>
											</div>
										<?php endif; ?>

										<?php if ($this->canpublish): ?>
											<div class="form-group">
												<?php echo $this->form->getLabel('published'); ?>
												<?php echo $this->form->getInput('published'); ?>
											</div>
										<?php endif; ?>

										<?php if ($this->params->get('edit_customs', 0) && count($this->customfields)): ?>
											<?php foreach ($this->customfields as $field): ?>
												<div class="form-group">
													<?php echo $field->getLabel(); ?>
													<?php echo $field->render(); ?>
												</div>
											<?php endforeach; ?>
										<?php endif; ?>

										<?php if (($this->params->get('edit_image', 1) == 2) || ($this->params->get('edit_image', 1) == 1)) : ?>
											<div class="form-group hidden hidden">
												<?php echo $this->form->getLabel('datimage'); ?>
												<?php echo $this->form->getInput('datimage'); ?>
											</div>
										<?php endif; ?>

										<?php if ($this->params->get('edit_summary', 1)) :?>
											<div class="form-group">
												<?php echo $this->form->getLabel('summary'); ?>
												<?php echo $this->form->getInput('summary'); ?>
											</div>
										<?php endif; ?>

										<?php if ($this->params->get('edit_description', 0)) :?>
											<div class="form-group">
												<?php echo $this->form->getLabel('datdescription'); ?>
												<?php echo $this->form->getInput('datdescription'); ?>
											</div>
										<?php endif; ?>
									</fieldset>
								</div>
								<?php if ($this->params->get('allow_attachments', 1)): ?>
									<div class="tab-pane" id="attachments">
										<div class="tab-header">
											<?php echo JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'); ?>
										</div>
										<fieldset>	
											<?php echo RedeventLayoutHelper::render('attachments.edit', $this); ?>
										</fieldset>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="actions-btn-group">
					<button type="button" class="btn btn-default" onclick="Joomla.submitbutton('editevent.cancel')"><?php echo JText::_('JCANCEL') ?></button>
					<button type="button" class="btn btn-success" onclick="Joomla.submitbutton('editevent.save')"><?php echo JText::_('JSAVE') ?></button>
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
	</div>
</div>
