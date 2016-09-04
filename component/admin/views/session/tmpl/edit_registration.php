<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');
?>
<div class="span9">
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('registrationend'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('registrationend'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('external_registration_url'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('external_registration_url'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('maxattendees'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('maxattendees'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('maxwaitinglist'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('maxwaitinglist'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('course_credit'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('course_credit'); ?>
		</div>
	</div>
</div>
