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

<ul class="nav nav-tabs" id="venueTab">
	<li class="active">
		<a href="#exporttab" data-toggle="tab">
			<strong><?php echo JText::_('COM_REDEVENT_EXPORT'); ?></strong>
		</a>
	</li>
	<li>
		<a href="#importtab" data-toggle="tab">
			<strong><?php echo JText::_('COM_REDEVENT_IMPORT'); ?></strong>
		</a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="exporttab">
		<div class="row-fluid">

			<form
				action="index.php?option=com_redevent&task=venuescsv.export&format=csv"
				method="post" name="adminForm" class="form-validate form-horizontal" id="adminForm" enctype="multipart/form-data">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('categories'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('categories'); ?>
					</div>
				</div>

				<div class="submit-btn">
					<button type="submit" class="btn"><?php echo JText::_('COM_REDEVENT_EXPORT')?></button>
				</div>
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>

	<div class="tab-pane" id="importtab">
		<div class="row-fluid">
			<form
				action="index.php?option=com_redevent"
				method="post" name="adminForm" class="form-validate form-horizontal" id="adminForm" enctype="multipart/form-data">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('import'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('import'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('duplicate_method'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('duplicate_method'); ?>
					</div>
				</div>
				<div class="submit-btn">
					<button type="submit" class="btn"><?php echo JText::_('COM_REDEVENT_IMPORT')?></button>
				</div>
				<input type="hidden" name="task" value="venuescsv.import" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
