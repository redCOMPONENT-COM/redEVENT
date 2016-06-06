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
		<?php echo $this->form->getLabel('registra'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('registra'); ?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('unregistra'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('unregistra'); ?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('max_multi_signup'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('max_multi_signup'); ?>
	</div>
</div>
