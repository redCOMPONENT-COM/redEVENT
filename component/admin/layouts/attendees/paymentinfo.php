<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$row = $displayData;

$link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&submit_key=' . $row->submit_key), JText::_('COM_REDEVENT_history'));
?>
<?php if ($row->paymentRequests): ?>
	<ul class="paymentrequest unstyled">
		<?php foreach ($row->paymentRequests as $pr): ?>
		<li>
			<?php $link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&pr=' . $pr->id), JText::_('COM_REDEVENT_history')); ?>
			<?php if (!$pr->paid): ?>
				<span class="hasTip" title="<?php echo JText::_('COM_REDEVENT_REGISTRATION_NOT_PAID'); ?>"><i class="icon-remove"></i><?php echo $link; ?></span>
				<?php echo ' '.JHTML::link(JURI::root().'index.php?option=com_redform&task=payment.select&key=' . $row->submit_key, JText::_('COM_REDEVENT_link')); ?>
			<?php else: ?>
				<span class="hasTip" title="<?php echo JText::_('COM_REDEVENT_REGISTRATION_PAID'); ?>"><i class="icon-ok"></i><?php echo $link; ?></span>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
