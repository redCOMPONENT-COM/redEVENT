<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

// Add script to make sure end happens after start
RHelperAsset::load('sessiondates.js');
?>
<div class="span7">
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
			<?php echo $this->form->getLabel('allday'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('allday'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('dates'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('dates'); ?> <span class="timefield"><?php echo $this->form->getInput('times'); ?></span>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('enddates'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('enddates'); ?> <span class="timefield"><?php echo $this->form->getInput('endtimes'); ?></span>
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
</div>

<div class="span5">
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
			<?php echo $this->form->getLabel('language'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('language'); ?>
		</div>
	</div>
</div>
