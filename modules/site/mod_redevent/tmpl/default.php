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
<ul class="redeventmod">
<?php foreach ($list as $item) :  ?>
	<?php $isover = (RedeventHelper::isOver($item) ? ' isover' : ''); ?>
	<li class="<?php echo $isover; ?>">
		<span class="event-title">
			<?php if ($params->get('linkdet', 2) == 2) : ?>
			<a href="<?php echo $item->link; ?>" class="redeventmod<?php echo $params->get('moduleclass_sfx'); ?>">
				<?php echo $item->title_short; ?>
			</a>
			<?php else : ?>
				<?php echo $item->title_short; ?>
			<?php endif; ?>
		</span>
		<div class="event-box">
			<?php if ($params->get('show_picture', 1)):?>
			<div class="event-thumb">
				<?php $img = RedeventImage::modalimage($item->datimage, $item->title_short, intval($params->get('picture_size', 30)));
							echo $img; ?>
			</div>
			<?php endif;?>
			<div class="event-details">
				<span class="event-dateinfo">
					<?php if ($params->get('linkdet', 2) == 1) : ?>
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->dateinfo; ?>
					</a>
					<?php else :
						echo $item->dateinfo;
					endif; ?>
				</span><br/>
				<span class="event-venue">
					<?php if ($params->get('showvenue', 1) == 1 && $params->get('linkloc', 1) == 1) : ?>
						<a href="<?php echo $item->venueurl; ?>" class="redeventmod<?php echo $params->get('moduleclass_sfx'); ?>">
							<?php echo $item->venue_short; ?>
						</a>
					<?php elseif ($params->get('showvenue', 1) == 1) : ?>
							<?php echo $item->venue_short; ?>
					<?php	endif; ?>
				</span>
			</div>
		</div>

	</li>
<?php endforeach; ?>
</ul>
