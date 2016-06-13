<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('rbootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('rjquery.chosen', 'select', array('width' => '150px'));
JHtml::_('rsearchtools.main');

RHelperAsset::load('redevent-backend.css');

$fieldSets = $this->form->getFieldsets('params');

$tab = JFactory::getApplication()->input->getString('tab');
?>

<?php if ($this->item->id) : ?>
	<script type="text/javascript">
		(function ($) {
			$(function () {
				// Perform the ajax request
				$.ajax({
					url: 'index.php?option=com_redevent&task=event.ajaxGetSessions&view=event&id=<?php echo $this->item->id ?>',
					cache: false,
					beforeSend: function (xhr) {
						$('div#sessions .spinner').show();
					}
				}).done(function (data) {
					$('div#sessions .spinner').hide();
					$('div#sessions div').html(data);
					$('div#sessions select').chosen();
					$('div#sessions .chzn-search').hide();
					$('div#sessions .hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
						"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});

					// Auto submit search fields after loading AJAX
					$('div#sessions .js-enter-submits').enterSubmits();

					// @TODO: In theory, this is not necessary, but .ajax is not triggering it
					$("div#sessions").find("script").each(function(){
						eval($(this).text());
					});
				});
			});
		})(jQuery);
	</script>
	<?php if ($tab) : ?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				// Show the corresponding tab
				jQuery('#eventTab a[href="#<?php echo $tab ?>"]').tab('show');
			});
		</script>
	<?php endif; ?>
<?php endif; ?>

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

<form action="index.php?option=com_redevent&task=event.edit&id=<?php echo $this->item->id ?>"
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
			<a href="#attachments" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#sessions" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_SESSIONS'); ?></strong>
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

		<div class="tab-pane" id="attachments">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('attachments'); ?>
			</div>
		</div>

		<div class="tab-pane" id="sessions">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('sessions'); ?>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
