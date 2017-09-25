<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

// Add script to make sure end happens after start
RHelperAsset::load('sessiondates.js');
?>
<div class="row">
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('eventid'); ?>
		<?php echo $this->form->getInput('eventid'); ?>
	</div>
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('venueid'); ?>
		<?php echo $this->form->getInput('venueid'); ?>
	</div>
</div>

<?php if ($this->params->get('edit_session_title', 1)) :?>
<div class="form-group">
	<?php echo $this->form->getLabel('title'); ?>
	<?php echo $this->form->getInput('title'); ?>
</div>
<?php endif; ?>

<div class="row">
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('alias'); ?>
		<?php echo $this->form->getInput('alias'); ?>
	</div>
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('session_code'); ?>
		<?php echo $this->form->getInput('session_code'); ?>
	</div>
</div>

<div class="form-group">
	<?php echo $this->form->getLabel('allday'); ?>
	<?php echo $this->form->getInput('allday'); ?>
</div>

<div class="row">
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('dates'); ?>
		<?php echo $this->form->getInput('dates'); ?>
	</div>
	<div class="form-group timefield col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('times'); ?>
		<?php echo $this->form->getInput('times'); ?>
	</div>
</div>

<div class="row">
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('enddates'); ?>
		<?php echo $this->form->getInput('enddates'); ?>
	</div>
	<div class="form-group timefield col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('endtimes'); ?>
		<?php echo $this->form->getInput('endtimes'); ?>
	</div>
</div>

<div class="form-group">
	<?php echo $this->form->getLabel('registrationend'); ?>
	<?php echo $this->form->getInput('registrationend'); ?>
</div>

<div class="row">
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('external_registration_url'); ?>
		<?php echo $this->form->getInput('external_registration_url'); ?>
	</div>
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('language'); ?>
		<?php echo $this->form->getInput('language'); ?>
	</div>
</div>

<div class="row">
	<?php if ($this->canpublish): ?>
	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('published'); ?>
		<?php echo $this->form->getInput('published'); ?>
	</div>
	<?php endif; ?>

	<div class="form-group col-xs-12 col-sm-6">
		<?php echo $this->form->getLabel('featured'); ?>
		<?php echo $this->form->getInput('featured'); ?>
	</div>
</div>

<?php if ($this->params->get('edit_session_details', 1)): ?>
<div class="form-group">
	<?php echo $this->form->getLabel('details'); ?>
	<?php echo $this->form->getInput('details'); ?>
</div>
<?php endif; ?>

