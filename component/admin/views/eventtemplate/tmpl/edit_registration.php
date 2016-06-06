<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');
?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('juser'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('juser'); ?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('redform_id'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('redform_id'); ?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('show_names'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('show_names'); ?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('showfields'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('showfields'); ?>
	</div>
</div>
