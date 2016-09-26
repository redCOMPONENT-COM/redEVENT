<?php
/**
 * @package    Redevent.admin
 *
 * @copyright  Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

JHtml::_('rjquery.chosen', 'select');

RHelperAsset::load('redevent-backend.css');

$tab = JFactory::getApplication()->input->getString('tab');
?>

<?php if ($tab) : ?>
	<script type="text/javascript">
		jQuery(function () {
			// Show the corresponding tab
			jQuery('#bundleTab a[href="#<?php echo $tab ?>"]').tab('show');
		});
	</script>
<?php endif; ?>

<script type="text/javascript">
	jQuery(function()
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
<form
	action="index.php?option=com_redevent&task=bundle.edit&id=<?php echo $this->item->id; ?>"
	method="post" name="adminForm" class="form-validate form-horizontal" id="adminForm">

	<ul class="nav nav-tabs" id="eventTab">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_DETAILS'); ?></strong>
			</a>
		</li>
		<li>
			<a href="#events" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_EVENTS'); ?></strong>
			</a>
		</li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('details'); ?>
			</div>
		</div>
		<div class="tab-pane" id="events">
			<div class="row-fluid">
				<?php echo $this->loadTemplate('events'); ?>
			</div>
		</div>
	</div>
	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
