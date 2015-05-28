<?php
/**
 * @package     Redeventsync
 * @subpackage  Admin
 * @copyright   Redeventsync (C) 2008-2015 Julien Vonthron. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

RHelperAsset::load('highlight/styles/googlecode.css');
RHelperAsset::load('highlight/highlight.pack.js');

JFactory::getDocument()->addScriptDeclaration('hljs.initHighlightingOnLoad();');
?>
<form
	action="index.php?option=com_redeventsync&view=log&id=<?php echo $this->item->id; ?>"
	method="post" name="adminForm" class="form-horizontal" id="adminForm">
	<div class="row-fluid">
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_REDEVENTSYNC_LOGS_TYPE'); ?></label>
			</div>
			<div class="controls">
				<?php echo $this->escape($this->item->type); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_REDEVENTSYNC_LOGS_DATE'); ?></label>
			</div>
			<div class="controls">
				<?php echo $this->escape($this->item->date); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_REDEVENTSYNC_LOGS_DIRECTION'); ?></label>
			</div>
			<div class="controls">
				<?php if ($this->item->direction): ?>
					<i class="icon-arrow-up"></i>
				<?php else: ?>
					<i class="icon-arrow-down"></i>
				<?php endif; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_REDEVENTSYNC_LOGS_TRANSACTIONID'); ?></label>
			</div>
			<div class="controls">
				<?php echo $this->escape($this->item->transactionid); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_REDEVENTSYNC_LOGS_FIELD_STATUS'); ?></label>
			</div>
			<div class="controls">
				<?php echo $this->escape($this->item->status); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_REDEVENTSYNC_LOGS_FIELD_REQUEST_MESSAGE'); ?></label>
			</div>
			<div class="controls">
				<?php
				if ($xmlDoc = DOMDocument::loadXML($this->item->message))
				{
					$xmlDoc->preserveWhiteSpace = false;
					$xmlDoc->formatOutput = true;
					echo '<pre><code class="xml">' . htmlentities($xmlDoc->saveXML()) . '</code></pre>';
				}
				else
				{
					echo $this->escape($this->item->message);
				}
				?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_REDEVENTSYNC_LOGS_FIELD_DEBUG'); ?></label>
			</div>
			<div class="controls">
				<?php echo $this->escape($this->item->debug); ?>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
