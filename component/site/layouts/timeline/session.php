<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;
$session = $displayData['session'];

$date = new JDate($session->dates);
$startTime = new JDate($session->times);
$endTime = new JDate($session->endtimes);
?>

<div class="session-left-infor">
	<h3 class="session-left-infor-time"><?php echo $date->format('l d') . ' ' . ucfirst($date->format('F')); ?> kl. <?php echo $startTime->format('H.i') . ' - ' . $endTime->format('H.i'); ?></h3>
	<h3>
	<h3 class="session-left-infor-venue"><?php echo $session->venue; ?></h3>

		<?php if ($session->custom8 == 'no'): ?>
		<div class="session-free">
			<?php echo JText::_('COM_REDEVENT_TIMELINE_FREE'); ?>
		</div>
		<?php elseif ($session->external_registration_url): ?>
		<div class="session-paid">
			<?php echo JHtml::link($session->external_registration_url, JText::_('COM_REDEVENT_TIMELINE_GET_TICKET')); ?>
		</div>
		<?php endif; ?>
</div>
<div class="session-right-infor">
	<?php echo $session->details; ?>
</div>
