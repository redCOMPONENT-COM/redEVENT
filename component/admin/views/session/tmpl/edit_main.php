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
			<?php echo $this->form->getLabel('eventid'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('eventid'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('venueid'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('venueid'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('title'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('title'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('alias'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('alias'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('session_code'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('session_code'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('dates'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('dates'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('times'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('times'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('enddates'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('enddates'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('endtimes'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('endtimes'); ?>
		</div>
	</div>

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
			<?php echo $this->form->getLabel('published'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('published'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('featured'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('featured'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('details'); ?>
		</div>
		<div class="controls">
			<div class="tags-info"><?php echo RedeventHelperOutput::getTagsModalLink('session.details'); ?></div>
			<?php echo $this->form->getInput('details'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('language'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('language'); ?>
		</div>
	</div>
</div>
