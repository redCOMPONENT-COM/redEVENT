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
<script type="text/javascript">
function tableOrdering( order, dir, view )
{
	var form = document.getElementById("venuesform");

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	form.submit( view );
}
</script>

<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="venuesform">
<div id="upcomingevents">
<table class="courseinfo_tabel">
<thead>
	<tr>
			<th class="courseinfo_titlename"><?php echo JText::_('COM_REDEVENT_EVENT_NAME'); ?></th>
			<th class="courseinfo_titledate"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_EVENT_DATE', 'x.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th class="courseinfo_titleduration"><?php echo JText::_('COM_REDEVENT_EVENT_DURATION'); ?></th>
			<th class="courseinfo_titlevenue" colspan="2"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_EVENT_VENUE', 'v.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th class="courseinfo_titleprice"><?php echo JText::_('COM_REDEVENT_EVENT_PRICE'); ?></th>
			<th class="courseinfo_titlecredit"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_EVENT_CREDITS', 'x.course_credit', $this->lists['order_Dir'], $this->lists['order'] );JText::_('COM_REDEVENT_EVENT_CREDITS'); ?></th>
			<th class="courseinfo_titlesignup"><?php echo JText::_('COM_REDEVENT_EVENT_SIGNUP'); ?></th>
	</tr>
</thead>
<tbody>
<?php
$elsettings = RedeventHelper::config();
$imagepath = JURI::base() . 'administrator/components/com_redevent/assets/images/';
foreach ($this->_eventlinks as $key => $event) {
	$event_url = JRoute::_(RedeventHelperRoute::getDetailsRoute($event->slug, $event->xslug));
	$venue_url = JRoute::_(RedeventHelperRoute::getUpcomingVenueEventsRoute($event->venueslug));
	?>
	<tr>
			<td class="courseinfo_name"><?php echo JHTML::_('link', $event_url, RedeventHelper::getSessionFullTitle($event)); ?></td>
			<td class="courseinfo_date"><?php echo RedeventHelperOutput::formatdate($event->dates, $event->times); ?></td>
			<td class="courseinfo_duration"><?php echo RedeventHelper::getEventDuration($event); ?></td>
			<td class="courseinfo_venue"><?php echo JHTML::_('link', $venue_url, $event->venue); ?></td>
			<td class="courseinfo_country"><?php echo RedeventHelperOutput::getFlag( $event->country ); ?></td>
			<td class="courseinfo_prices re-price"><?php echo RedeventHelperOutput::formatListPrices($event->prices); ?></td>
			<td class="courseinfo_credit"><?php echo $event->course_credit ?></td>
			<td class="courseinfo_signup">
				<?php $paid = ($event->custom8 == 'no') ? false : true; ?>
				<div class="session-<?php echo $paid ? 'paid' : 'free'; ?>">
					<?php if (!$paid): ?>
						<label><?php echo JText::_('COM_REDEVENT_TIMELINE_FREE'); ?></label>
					<?php endif; ?>

					<?php if ($event->external_registration_url): ?>
						<?php echo JHtml::link($event->external_registration_url, JText::_('COM_REDEVENT_TIMELINE_GET_TICKET'), array('class' => 'timeline-getticket')); ?>
					<?php endif; ?>
				</div>
			</td>
	</tr>
<?php }
?>
</tbody>
</table>
</div>

<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>
