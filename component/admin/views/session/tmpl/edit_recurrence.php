<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

RHelperAsset::load('xref_recurrence.js');
?>
<div id="recurrence" class="row-fluid">

	<div class="span4">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('type', 'recurrence'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type', 'recurrence'); ?>
			</div>
		</div>
	</div>

	<div class="span8">
		<div id="recurrence-settings">
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
			<div class="control-group repeat_type_option" id="repeat_type_count">
				<div class="control-label">
					<?php echo $this->form->getLabel('repeat_until_count', 'recurrence'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('repeat_until_count', 'recurrence'); ?>
				</div>
			</div>
			<div class="control-group repeat_type_option" id="repeat_type_date">
				<div class="control-label">
					<?php echo $this->form->getLabel('repeat_until_date', 'recurrence'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('repeat_until_date', 'recurrence'); ?>
				</div>
			</div>

			<div id="recurrence_repeat_weekly" class="recurrence-type-options">
				<fieldset>
					<div class="control-label">
						<?php echo $this->form->getLabel('wweekdays', 'recurrence'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('wweekdays', 'recurrence'); ?>
					</div>
				</fieldset>
			</div>

			<div id="recurrence_repeat_monthly" class="recurrence-type-options">
				<div class="control-label">
					<?php echo $this->form->getLabel('month_type', 'recurrence'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('month_type', 'recurrence'); ?>
				</div>

				<div class="month-type-options bymonthdays">
					<div class="control-label">
						<?php echo $this->form->getLabel('bymonthdays', 'recurrence'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('bymonthdays', 'recurrence'); ?>
					</div>

					<div class="control-label">
						<?php echo $this->form->getLabel('reverse_bymonthday', 'recurrence'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('reverse_bymonthday', 'recurrence'); ?>
					</div>
				</div>

				<div class="month-type-options byweeks">
					<div class="control-label">
						<?php echo $this->form->getLabel('mweeks', 'recurrence'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('mweeks', 'recurrence'); ?>
					</div>

					<div class="control-label">
						<?php echo $this->form->getLabel('mweekdays', 'recurrence'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('mweekdays', 'recurrence'); ?>
					</div>

					<div class="control-label">
						<?php echo $this->form->getLabel('mrweeks', 'recurrence'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('mrweeks', 'recurrence'); ?>
					</div>

					<div class="control-label">
						<?php echo $this->form->getLabel('mrweekdays', 'recurrence'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('mrweekdays', 'recurrence'); ?>
					</div>
				</div>
			</div>

			<div id="recurrence_repeat_yearly" class="recurrence-type-options">
				<div class="control-label">
					<?php echo $this->form->getLabel('byyeardays', 'recurrence'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('byyeardays', 'recurrence'); ?>
				</div>

				<div class="control-label">
					<?php echo $this->form->getLabel('reverse_byyearday', 'recurrence'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('reverse_byyearday', 'recurrence'); ?>
				</div>
			</div>

			<?php echo $this->form->getInput('recurrenceid', 'recurrence'); ?>
			<?php echo $this->form->getInput('repeat', 'recurrence'); ?>
		</div>
	</div>

</div>
