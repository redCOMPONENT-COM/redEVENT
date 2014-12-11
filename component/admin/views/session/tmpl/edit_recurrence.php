<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

RHelperAsset::load('xref_recurrence.js');
?>
<div id="recurrence">
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('type', 'recurrence'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('type', 'recurrence'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('interval', 'recurrence'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('interval', 'recurrence'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('repeat_type', 'recurrence'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('repeat_type', 'recurrence'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('repeat_until_count', 'recurrence'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('repeat_until_count', 'recurrence'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('repeat_until_date', 'recurrence'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('repeat_until_date', 'recurrence'); ?>
		</div>
	</div>

	<div id="recurrence_repeat_weekly">
		<fieldset class="adminform editevent">
			<legend><?php echo JText::_('COM_REDEVENT_RECURRENCE_WEEK_BY_DAY'); ?></legend>
			<div class="control-label">
				<?php echo $this->form->getLabel('wweekdays', 'recurrence'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('wweekdays', 'recurrence'); ?>
			</div>
		</fieldset>
	</div>

	<?php echo $this->form->getInput('recurrenceid', 'recurrence'); ?>
	<?php echo $this->form->getInput('repeat', 'recurrence'); ?>
</div>
