<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$colnames = explode(",", $this->params->get('lists_columns_names', 'date, title, venue, city, category'));
$colnames = array_map('trim', $colnames);
?>
<div class="featured-events<?php echo $this->params->get( 'pageclass_sfx' ); ?>" summary="eventlist">
<?php
	$k = 0;
	foreach ($this->rows as $row) :
		//Link to details
		$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug) );
		if (RedeventHelper::isValidDate($row->dates))
		{
			$date = JFactory::getDate($row->times ? $row->dates.' '.$row->times : $row->dates);
		}
		else
		{
			$date = false;
		}
		$img = redEVENTImage::getThumbUrl($row->datimage, 150);
		$img = ($img ? JHTML::image($img, RedeventHelper::getSessionFullTitle($row)) : false);
		?>
  	<div class="event row<?php echo ($k + 1); ?>"
  	    itemscope itemtype="http://schema.org/Event">

			<?php if ($img): ?>
			<div class="event-image" itemprop="image">
				<?php echo $img; ?>
			</div>
  	  <?php endif; ?>

			<div class="when-where">
				<div class="date">
					<div class="day">
					<?php if (!$date): ?>
						<div class="open">
						<?php echo JText::_('COM_REDEVENT_OPEN_DATE'); ?>
						</div>
					<?php else: ?>
						<meta itemprop="startDate" content="<?php echo REOutput::getIsoDate($row->dates, $row->times); ?>">
						<div class="month"><?php echo $date->format('M'); ?></div>
						<div class="daynumber"><?php echo $date->format('d'); ?></div>
						<div class="weekday"><?php echo $date->format('D'); ?></div>
					<?php endif; ?>
					</div>
					<?php if ($row->times): ?>
						<div class="event-time">
							<?php echo $date->format($this->params->get('formattime_date', 'H:i')); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="description">
				<div class="event-title" itemprop="name">
					<a href="<?php echo $detaillink ; ?>" itemprop="url"><?php echo $this->escape(RedeventHelper::getSessionFullTitle($row)); ?></a>
				</div>

				<div class="event-venue" itemprop="location" itemscope itemtype="http://schema.org/Place">
						<?php
						if ($this->params->get('showlinkvenue',1) == 1 ) :
							echo $row->xref != 0 ? JHTML::link(JRoute::_( RedeventHelperRoute::getVenueEventsRoute($row->venueslug) ), '@ '.$this->escape($row->venue), 'itemprop="url"') : '-';
						else :
							echo $row->xref ? $this->escape($row->venue) : '-';
						endif;
						?>
				</div>
				<div class="summary"><?php echo $row->summary; ?></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
