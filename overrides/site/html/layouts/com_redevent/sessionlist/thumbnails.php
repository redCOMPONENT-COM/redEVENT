<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$rows = $displayData['rows'];
?>
<ul class="rf_thumbevents vcalendar">
	<?php foreach ($rows as $row):?>
		<?php $img = RedeventImage::getThumbUrl($row->datimage);
		$img = ($img ? JHTML::image($img, RedeventHelper::getSessionFullTitle($row)) : false);
		$detaillink = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug));
		$venuelink  = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug));
		?>
		<li class="rf_thumbevent vevent<?php echo ($row->featured ? ' featured' : ''); ?>">
			<?php if ($img): ?>
				<?php echo JHTML::_('link', JRoute::_($detaillink), $img, array('class' => 'rf_img')); ?>
			<?php else: ?>
				<div class="rf_img"></div>
			<?php endif; ?>
			<p class="rf_thumbevent_title">
				<span class="summary"><?php echo JHTML::_('link', JRoute::_($detaillink), RedeventHelper::getSessionFullTitle($row)); ?></span> @ <span class="location"><?php echo JHTML::_('link', JRoute::_($venuelink), $row->venue); ?></span>
			</p>
			<p class="rf_thumbevent_date">
				<span class="dtstart"><?php echo RedeventHelperOutput::formatdate($row->dates, $row->times); ?></span>
				<?php
				if (RedeventHelper::isValidDate($row->enddates) && $row->enddates != $row->dates) :
					echo ' - <span class="dtend">' . RedeventHelperOutput::formatdate($row->enddates, $row->endtimes) . '</span>';
				endif;
				?>
			</p>
		</li>
	<?php endforeach; ?>
</ul>
