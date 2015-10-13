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
		if (task == 'editsession.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . $this->form->getField('details')->save() . "
			Joomla.submitform(task);
		}
	}
");

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

		<div class="row-fluid">
			<div class="span12">
				<fieldset class="form-horizontal">
					<div class="control-group">
						<div class="control-label">
							<?php echo $form->getLabel('title', 'event'); ?>
						</div>
						<div class="controls">
							<?php echo $form->getInput('title', 'event'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $form->getLabel('categories', 'event'); ?>
						</div>
						<div class="controls">
							<?php echo $form->getInput('categories', 'event'); ?>
						</div>
					</div>
				</fieldset>
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
