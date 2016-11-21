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
		<?php echo $this->form->getLabel('paymentprocessing'); ?>
		<em><?php echo JText::_('COM_REDEVENT_PAYMENTPROCESSING_INFO'); ?></em>
	</div>
	<div class="controls">
		<div class="tags-info"><?= RedeventHelperOutput::getTagsEditorInsertModal($this->form->getField('paymentprocessing')) ?></div>
		<?php echo $this->form->getInput('paymentprocessing'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('paymentaccepted'); ?>
		<em><?php echo JText::_('COM_REDEVENT_PAYMENTACCEPTED_INFO'); ?></em>
	</div>
	<div class="controls">
		<div class="tags-info"><?= RedeventHelperOutput::getTagsEditorInsertModal($this->form->getField('paymentaccepted')) ?></div>
		<?php echo $this->form->getInput('paymentaccepted'); ?>
	</div>
</div>
