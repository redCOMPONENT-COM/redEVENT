<?php
/**
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="mod_re_att">
<?php if ($type > 0): ?>
<form class="mod_re_att_nav">
	<span class="nav-prec"><?php echo JHTML::link($previous, '<<'); ?></span>
	<span class="nav-select"><?php echo $select; ?></span>
	<span class="nav-next"><?php echo JHTML::link($next, '>>'); ?></span>
	<input type="hidden" name="currenturi" value="<?php echo htmlspecialchars($curi->toString()); ?>"/>
</form>
<?php endif; ?>

<table class="redeventmod">
<thead>
	<tr>
		<th class="event-date"><?php echo JText::_('MOD_REDEVENT_ATTENDING_HEADER_DATE'); ?></th>
		<th class="event-title"><?php echo JText::_('MOD_REDEVENT_ATTENDING_HEADER_TITLE'); ?></th>
		<?php if ($params->get('showvenue', 1)): ?>	
		<th class="event-venue"><?php echo JText::_('MOD_REDEVENT_ATTENDING_HEADER_VENUE'); ?></th>
		<?php	endif; ?>
		<?php if ($params->get('show_picture', 1)):?>
		<th class="event-thumb"><?php echo JText::_('MOD_REDEVENT_ATTENDING_HEADER_PIC'); ?></th>
		<?php	endif; ?>
		<?php if ($params->get('show_price_column', 1)):?>
		<th class="event-price"><?php echo JText::_('MOD_REDEVENT_ATTENDING_HEADER_PRICE'); ?></th>
		<?php	endif; ?>
	</tr>
</thead>
<tbody>
<?php foreach ($list as $item) :  ?>
	<?php $isover = (redEVENTHelper::isOver($item) ? ' isover' : ''); ?>
	<tr class="<?php echo $isover; ?>">
		<td class="event-date">
			<?php echo $item->dateinfo; ?>
		</td>
		<td class="event-title">
			<?php if ($params->get('linkevent', 1)) : ?>
				<?php echo JHTML::link($item->link, $item->title_short);?>
			<?php else : ?>
				<?php echo $item->title_short; ?>
			<?php endif; ?>
		</td>			
		
		<?php if ($params->get('showvenue', 1)): ?>		
		<td class="event-venue">
			<?php if ($params->get('linkloc', 1)) : ?>
				<?php echo JHTML::link($item->venueurl, $item->venue_short);?>
			<?php else : ?>
					<?php echo $item->venue_short; ?>
			<?php	endif; ?>
		</td>
		<?php	endif; ?>
		
		<?php if ($params->get('show_picture', 1)):?>
		<td class="event-thumb">
				<?php $img = redEVENTImage::modalimage($item->datimage, $item->title_short, intval($params->get('picture_size', 30)));
							echo $img; ?>
		</td>
		<?php endif;?>
		
		<?php if ($params->get('show_price_column', 1)):?>
		<td class="event-price"><?php echo $item->price ? modRedEventAttendingHelper::printPrice($item->price, $item->currency) : '-'; ?></td>
		<?php	endif; ?>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php if ($params->get('show_price_total') && $total = modRedEventAttendingHelper::getTotal($list)): ?>
<div class="total-price">
	<span><?php echo JText::_('MOD_REDEVENT_ATTENDING_TOTAL_PRICE').': '; ?></span>
	<?php echo modRedEventAttendingHelper::printPrice($total, reset($list)->currency); ?>
</div>
<?php endif;?>

</div>