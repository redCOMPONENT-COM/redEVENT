<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.formvalidation');

RHelperAsset::load('editsession.js');

$sessionDetailsField = $this->form->getField('details');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'editsession.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . ($sessionDetailsField && $this->params->get('edit_session_details')? $sessionDetailsField->save() : '') . "
			Joomla.submitform(task);
		}
	}
");

$viewName = 'editsession';
$option   = 'com_redevent';

$action = JRoute::_('index.php?option=' . $option . '&view=' . $viewName);
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
	      action="<?php echo $action; ?>"
	      method="post" name="adminForm" class="form-validate"
	      id="adminForm">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('editsession.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('editsession.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<ul class="nav nav-tabs" id="userTab">
			<li class="active">
				<a href="#eventmain" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_MAIN'); ?></strong>
				</a>
			</li>

			<?php if ($this->params->get('edit_customs', 0) && count($this->customfields)):?>
				<li>
					<a href="#customfields" data-toggle="tab">
						<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_CUSTOM_FIELDS'); ?></strong>
					</a>
				</li>
			<?php endif; ?>

			<?php if ($this->params->get('edit_registration', 0)) :?>
			<li>
				<a href="#registration" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_REGISTRATION'); ?></strong>
				</a>
			</li>
			<?php endif; ?>

			<?php if ($this->params->get('edit_price', 0)): ?>
			<li>
				<a href="#prices" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_PRICES'); ?></strong>
				</a>
			</li>
			<?php endif; ?>

			<?php if ($this->params->get('edit_recurrence', 0)) :?>
			<li>
				<a href="#recurrence" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_RECURRENCE'); ?></strong>
				</a>
			</li>
			<?php endif; ?>

			<?php if ($this->params->get('edit_roles', 0)): ?>
			<li>
				<a href="#roles" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_ROLES'); ?></strong>
				</a>
			</li>
			<?php endif; ?>

			<li>
				<a href="#ical" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_ICAL'); ?></strong>
				</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="eventmain">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php echo $this->loadTemplate('main'); ?>
						</fieldset>
					</div>
				</div>
			</div>

			<?php if ($this->params->get('edit_customs', 0) && count($this->customfields)):?>
			<div class="tab-pane" id="customfields">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php echo $this->loadTemplate('customfields'); ?>
						</fieldset>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('edit_registration', 0)) :?>
			<div class="tab-pane" id="registration">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php foreach ($this->form->getFieldset('registration') as $field) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('edit_price', 0)): ?>
			<div class="tab-pane" id="prices">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php echo $this->loadTemplate('prices'); ?>
						</fieldset>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('edit_recurrence', 0)) :?>
			<div class="tab-pane" id="recurrence">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php echo $this->loadTemplate('recurrence'); ?>
						</fieldset>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('edit_roles', 0)): ?>
			<div class="tab-pane" id="roles">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php echo $this->loadTemplate('roles'); ?>
						</fieldset>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="tab-pane" id="ical">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php foreach ($this->form->getFieldset('ical') as $field) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>">
		<input type="hidden" name="s_id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php if ($this->return): ?>
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<?php endif; ?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
