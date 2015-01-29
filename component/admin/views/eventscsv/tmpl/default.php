<?php
/**
 * @package    Redevent.admin
 *
 * @copyright  Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

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

	<ul class="nav nav-tabs" id="eventTab">
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
					action="index.php?option=com_redevent&task=csvevents.export"
					method="post" name="adminForm" class="form-validate form-horizontal" id="adminForm" enctype="multipart/form-data">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('categories'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('categories'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('venues'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('venues'); ?>
						</div>
					</div>

					<input type="hidden" name="task" value="" />
					<?php echo JHtml::_('form.token'); ?>
				</form>
			</div>
		</div>

		<div class="tab-pane" id="importtab">
			<div class="row-fluid">
				<form
					action="index.php?option=com_redevent&task=csvevents.import"
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
					<button type="submit" class="btn"><?php echo JText::_('COM_REDEVENT_IMPORT')?></button>
					<?php echo JHtml::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>

<?php echo $pane->startPane('iopane'); ?>
<?php echo $pane->startPanel(Jtext::_('COM_REDEVENT_EXPORT'), 'export'); ?>

<p><?php echo Jtext::_('COM_REDEVENT_EVENTS_EXPORT_INTRO'); ?></p>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<table class="adminlist exportcsv" cellspacing="1">
	<tbody>
	<tr id="export-categories-row">
		<td class="label" width="150px"><?php echo JText::_('COM_REDEVENT_EVENTS_CSV_EXPORT_CATEGORIES'); ?></td>
		<td><?php echo $this->lists['categories']; ?></td>
	</tr>
	<tr id="export-venues-row">
		<td class="label" width="150px"><?php echo JText::_('COM_REDEVENT_EVENTS_CSV_EXPORT_VENUES'); ?></td>
		<td><?php echo $this->lists['venues']; ?></td>
	</tr>
	</tbody>
</table>

<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="events" />
<input type="hidden" name="task" value="" />
</form>
<?php $pane->endPanel(); ?>

<?php echo $pane->startPanel(Jtext::_('COM_REDEVENT_IMPORT'), 'import'); ?>
<p><?php echo Jtext::_('COM_REDEVENT_EVENTS_IMPORT_INTRO'); ?></p>
<form action="index.php" method="post" name="importform" id="importform"  enctype="multipart/form-data" >

<table class="adminlist exportcsv" cellspacing="1">
	<tbody>
	<tr>
		<td class="label" width="150px"><?php echo JText::_('COM_REDEVENT_EVENTS_CSV_IMPORT_FILE'); ?></td>
		<td><input type="file" name="import" /><button type="submit"><?php echo JText::_('COM_REDEVENT_IMPORT')?></button></td>
	</tr>
	<tr>
		<td class="label hasTip" rel="<?php echo JText::_('COM_REDEVENT_CSV_IMPORT_HANDLE_DUPLICATE_METHOD_TIP'); ?>" width="150px"><?php echo JText::_('COM_REDEVENT_CSV_IMPORT_HANDLE_DUPLICATE_METHOD'); ?></td>
		<td>
			<select name="duplicate_method" id="duplicate_method">
				<option value="ignore"><?php echo JText::_('COM_REDEVENT_CSV_IMPORT_HANDLE_DUPLICATE_METHOD_OPTION_IGNORE'); ?></option>
				<option value="create_new"><?php echo JText::_('COM_REDEVENT_CSV_IMPORT_HANDLE_DUPLICATE_METHOD_OPTION_CREATE_NEW'); ?></option>
				<option value="update"><?php echo JText::_('COM_REDEVENT_CSV_IMPORT_HANDLE_DUPLICATE_METHOD_OPTION_UPDATE'); ?></option>
			</select>
		</td>
	</tr>
	</tbody>
</table>

<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="events" />
<input type="hidden" name="task" value="import" />
</form>
<?php $pane->endPanel(); ?>
<?php $pane->endPane(); ?>
