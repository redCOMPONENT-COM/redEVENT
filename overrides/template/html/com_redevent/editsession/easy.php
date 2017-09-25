<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.formvalidation');

RHelperAsset::load('editsession.js');

$viewName = 'editsession';
$option   = 'com_redevent';

$action = JRoute::_('index.php?option=' . $option . '&view=' . $viewName);

$document = &JFactory::getDocument();
$renderer   = $document->loadRenderer('modules');
$position_breadcrumbs   = 'breadcrumbs';

/**
 * @var RForm
 */
$form = $this->form;
$descriptionField = $this->form->getField('datdescription', 'event');
$sessionDetailsField = $this->form->getField('details');

RHelperAsset::load('sessiondates.js');

JFactory::getDocument()->addScriptDeclaration(
	"var easySubmitButton = function(task) {
		if (task == 'editsession.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . ($descriptionField && $this->params->get('edit_description')? $descriptionField->save() : '') . "
			" . ($sessionDetailsField && $this->params->get('edit_session_details')? $sessionDetailsField->save() : '') . "
			Joomla.submitform(task);
		}
	};"
);
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

<div class="container myevents session-edit control-edit">
	<?php echo $renderer->render($position_breadcrumbs, $options, null); ?>
	<div class="aesir-container user-management-cotaniner">
		<div class="aesir-item-header reditem-content-header text-center">
			<h1 class="header-title"><?php echo $this->getTitle(); ?></h1>
		</div>
		<div class="redevent-edit-form<?= $this->params->get('pageclass_sfx') ?>">
			<form enctype="multipart/form-data" action="<?php echo $action; ?>" method="post" name="adminForm" class="form-validate form-control-custom" id="adminForm">
				<div class="row">
					<div class="left-sidebar col-md-5 col-lg-3">
						<div class="white-block-border">
							<div class="tab-header"><?php echo JText::_('COM_REDITEM_ITEM_LABEL_FIELD_GROUPS'); ?></div>
							<ul class="nav menu navbar-nav js-aesir-tabs-container" id="userTab">
								<li class="active">
									<a href="#eventmain" data-toggle="tab">
										<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_MAIN'); ?></strong>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="col-md-7 col-lg-9">
						<div class="white-block-border">
							<div class="tab-content">
								<div class="tab-pane active" id="eventmain">
									<div class="tab-header">
										<?php echo JText::_('COM_REDEVENT_SESSION_TAB_MAIN'); ?>
									</div>
									<fieldset>
										<div class="form-group">
											<?php echo $form->getLabel('title', 'event'); ?>
											<?php echo $form->getInput('title', 'event'); ?>
										</div>

										<div class="form-group">
											<?php echo $form->getLabel('categories', 'event'); ?>
											<?php echo $form->getInput('categories', 'event'); ?>
										</div>

										<div class="form-group">
											<?php echo $form->getLabel('venueid'); ?>
											<?php echo $form->getInput('venueid'); ?>
										</div>
										<div class="form-group">
											<?php echo $form->getLabel('allday'); ?>
											<?php echo $form->getInput('allday'); ?>
										</div>
										<div class="row">
											<div class="form-group col-xs-12 col-sm-6">
												<?php echo $form->getLabel('dates'); ?>
												<?php echo $form->getInput('dates'); ?>
											</div>
											<div class="form-group timefield col-xs-12 col-sm-6">
												<?php echo $form->getLabel('times'); ?>
												<?php echo $form->getInput('times'); ?>
											</div>
										</div>
										<div class="row">
											<div class="form-group col-xs-12 col-sm-6">
												<?php echo $form->getLabel('enddates'); ?>
												<?php echo $form->getInput('enddates'); ?>
											</div>
											<div class="form-group timefield col-xs-12 col-sm-6">
												<?php echo $form->getLabel('endtimes'); ?>
												<?php echo $form->getInput('endtimes'); ?>
											</div>
										</div>

										<div class="form-group">
											<?php echo $this->form->getLabel('registrationend'); ?>
											<?php echo $this->form->getInput('registrationend'); ?>
										</div>

										<?php if (($this->params->get('edit_image', 1) == 2) || ($this->params->get('edit_image', 1) == 1)) : ?>
											<div class="form-group hidden">
												<?php echo $this->form->getLabel('datimage', 'event'); ?>
												<?php echo $this->form->getInput('datimage', 'event'); ?>
											</div>
										<?php endif; ?>

										<?php if ($this->params->get('edit_description')): ?>
										<div class="form-group">
											<?php echo $form->getLabel('datdescription', 'event'); ?>
											<?php echo $form->getInput('datdescription', 'event'); ?>
										</div>
										<?php endif; ?>
										<?php if ($this->params->get('edit_session_details')): ?>
											<div class="form-group">
												<?php echo $form->getLabel('details'); ?>
												<?php echo $form->getInput('details'); ?>
											</div>
										<?php endif; ?>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="actions-btn-group">
					<button type="button" class="btn btn-default" onclick="easySubmitButton('editsession.cancel')"><?php echo JText::_('JCANCEL') ?></button>
					<button type="button" class="btn btn-success" onclick="easySubmitButton('editsession.save')"><?php echo JText::_('JSAVE') ?></button>
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
			</form>
		</div>
	</div>
</div>
