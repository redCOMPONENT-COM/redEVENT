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

/**
 * @var RForm
 */
$form = $this->form;
$descriptionField = $this->form->getField('datdescription', 'event');

JFactory::getDocument()->addScriptDeclaration(
	"var easySubmitButton = function(task) {
		if (task == 'editsession.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . ($descriptionField ? $descriptionField->save() : '') . "
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

<?php if ($this->params->def('show_page_title', 1)): ?>
	<h1 class='componentheading'>
		<?php echo $this->getTitle(); ?>
	</h1>
<?php endif; ?>

<div class="redevent-edit-form">
	<form enctype="multipart/form-data"
	      action="<?php echo $action; ?>"
	      method="post" name="adminForm" class="form-validate"
	      id="adminForm">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="easySubmitButton('editsession.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="easySubmitButton('editsession.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<div class="row-fluid">
			<fieldset class="form-horizontal">
					<div class="span12">
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('title', 'event'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('title', 'event'); ?>
							</div>
						</div>
						<?php if ($this->params->get('edit_categories')): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('categories', 'event'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('categories', 'event'); ?>
							</div>
						</div>
						<?php endif; ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('venueid'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('venueid'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('dates'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('dates'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('times'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('times'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('enddates'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('enddates'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('endtimes'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('endtimes'); ?>
							</div>
						</div>

						<?php if (($this->params->get('edit_image', 1) == 2) || ($this->params->get('edit_image', 1) == 1)) : ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('datimage', 'event'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('datimage', 'event'); ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($this->params->get('edit_description')): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('datdescription', 'event'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('datdescription', 'event'); ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if ($this->params->get('edit_session_details')): ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('details'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('details'); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
			</fieldset>
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
