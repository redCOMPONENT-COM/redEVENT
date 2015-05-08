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

$paid = ($session->custom8 == 'yes' ? true : false);
?>

<div class="session-left-infor">
	<?php if (!empty($session->eventImage)): ?>
		<img class="session-left-infor-img" src="<?php echo JUri::root() . $session->eventImage; ?>" />
	<?php endif; ?>
	<h3 class="session-left-infor-time"><?php echo $date->format('l d') . ' ' . ucfirst($date->format('F')); ?> kl. <?php echo $startTime->format('H.i') . ' - ' . $endTime->format('H.i'); ?></h3>
	<h3 class="session-left-infor-venue"><?php echo $session->venue; ?></h3>

	<div class="session-<?php echo $paid ? 'paid' : 'free'; ?>">
		<?php if (!$paid): ?>
			<label><?php echo JText::_('COM_REDEVENT_TIMELINE_FREE'); ?></label>
		<?php endif; ?>

		<?php if ($session->external_registration_url): ?>
			<?php echo JHtml::link($session->external_registration_url, JText::_('COM_REDEVENT_TIMELINE_GET_TICKET'), array('class' => 'timeline-getticket')); ?>
		<?php endif; ?>
	</div>

	<div class="link-full"><?php
		echo JHtml::link(RedeventHelperRoute::getDetailsRoute($session->slug, $session->xslug), JText::_('COM_REDEVENT_TIMELINE_READMORE')); ?></div>

	<div class="social-share">

		<?php
			$url_share = JUri::root() . JRoute::_(RedeventHelperRoute::getDetailsRoute($session->slug, $session->xslug));
		?>
		<div class="addthis_sharing_toolbox" data-url="<?php echo $url_share; ?>"></div>
	</div>
</div>

<div class="session-right-infor">
	<?php echo $session->details; ?>
</div>
