<?php
/**
 * @package     Redevent
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

RHelperAsset::load('csvtool.js');
?>

<div class="row-fluid">
	<form action="index.php?option=com_redevent&view=attendeescsv" method="post" name="adminForm" id="export-form">

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('form'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('form'); ?>
			</div>
		</div>

		<div class="conditional-form">
			<div class="control-group" id="export-category-row">
				<div class="control-label">
					<?php echo $this->form->getLabel('category'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('category'); ?>
				</div>
			</div>

			<div class="control-group" id="export-venue-row">
				<div class="control-label">
					<?php echo $this->form->getLabel('venue'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('venue'); ?>
				</div>
			</div>

			<div class="control-group" id="export-state-row">
				<div class="control-label">
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('state'); ?>
				</div>
			</div>

			<div class="control-group" id="export-attending-row">
				<div class="control-label">
					<?php echo $this->form->getLabel('attending'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('attending'); ?>
				</div>
			</div>

			<div class="control-group" id="export-event-row">
				<div class="control-label">
					<?php echo $this->form->getLabel('event'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('event'); ?>
				</div>
			</div>

			<div class="submit-btn" id="export-button-row">
				<button type="submit" class="btn"><?php echo JText::_('COM_REDEVENT_EXPORT')?></button>
			</div>
		</div>

		<input type="hidden" name="task" value="attendeescsv.import" />
		<input type="hidden" name="format" value="csv" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
