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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<ul class="rf_thumbevents vcalendar">
	<?php foreach ($this->rows as $row): ?>
	<?php $img = redEVENTImage::getThumbUrl('events', $row->datimage);
				$img = ($img ? JHTML::image($img, $row->title) : false);
				$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref) );
				$venuelink  = JRoute::_( RedeventHelperRoute::getVenueEventsRoute($row->venueslug) );
	?>
	<li class="rf_thumbevent vevent">
		<?php if ($img): ?>
		<?php echo JHTML::_('link', JRoute::_($detaillink), $img, array('class' => 'rf_img')); ?>
		<?php else: ?>
		<div class="rf_img"></div>
		<?php endif; ?>
		<p class="rf_thumbevent_title">
		<span class="summary"><?php echo JHTML::_('link', JRoute::_($detaillink), $row->title); ?></span> @ <span class="location"><?php echo JHTML::_('link', JRoute::_($venuelink), $row->venue); ?></span>
		</p>
		<p class="rf_thumbevent_date">
    	<span class="dtstart"><?php echo ELOutput::formatdate($row->dates, $row->times); ?></span>    	
    	<?php
    	if ($row->enddates && $row->enddates != '0000-00-00' && $row->enddates != $row->dates) :
    		echo ' - <span class="dtend">'.ELOutput::formatdate($row->enddates, $row->endtimes).'</span>';
    	endif;
    	?>
		</p>
	</li>
	<?php endforeach; ?>
</ul>
