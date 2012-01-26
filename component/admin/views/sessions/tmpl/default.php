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

defined('_JEXEC') or die('Restricted access');

$app = &JFactory::getApplication();
$colspan = 9;
if (!$this->event) $colspan++;
if (!$this->event || $this->event->registra) $colspan += 2;
?>
<script type="text/javascript">
 /**
  * Overrides default function.
  */
  function submitbutton(pressbutton) {
    submitform(pressbutton);
  }
</script>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
<table>
<tr>
	<td align="left" width="100%">
		<div>
			<?php echo JText::_('COM_REDEVENT_Filter' ); ?>:
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
			<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
		</div>
		<div class="sessions-filter">		
			<label for="eventid" class="hasTip" title="<?php echo JText::_( 'COM_REDEVENT_SESSIONS_EVENT_FILTER' ).'::'.JText::_( 'COM_REDEVENT_SESSIONS_EVENT_FILTER_TIP' ); ?>">
				<?php echo JText::_( 'COM_REDEVENT_SESSIONS_EVENT_FILTER' ).':'; ?>
			</label>
			<?php	$link = 'index.php?option=com_redevent&amp;view=eventelement&amp;tmpl=component&amp;function=elSelectEvent'; ?>
			<input style="background: #ffffff;" type="text" id="eventid_name" value="<?php echo ($this->eventid ? $this->event->title : JText::_('COM_REDEVENT_SESSIONS_EVENT_FILTER_ALL')); ?>" disabled="disabled" />
			<a class="modal" title="<?php JText::_('COM_REDEVENT_Select'); ?>"  href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}"><?php echo JText::_('COM_REDEVENT_Select'); ?></a>
			<a title="<?php JText::_('COM_REDEVENT_Reset'); ?>" id="ev-reset-button"><?php echo JText::_('COM_REDEVENT_Reset'); ?></a>
			<input type="hidden" id="eventid" name="eventid" value="<?php echo $this->eventid; ?>" />
		</div>
		<div class="sessions-filter">
			<label for="venueid" class="hasTip" title="<?php echo JText::_( 'COM_REDEVENT_SESSIONS_VENUE_FILTER' ).'::'.JText::_( 'COM_REDEVENT_SESSIONS_VENUE_FILTER_TIP' ); ?>">
				<?php echo JText::_( 'COM_REDEVENT_SESSIONS_VENUE_FILTER' ).':'; ?>
			</label>
			<?php	$link = 'index.php?option=com_redevent&amp;view=venueelement&amp;tmpl=component&amp;function=elSelectVenue'; ?>
			<input style="background: #ffffff;" type="text" id="venueid_name" value="<?php echo ($this->venue ? $this->venue->venue : JText::_('COM_REDEVENT_SESSIONS_VENUE_FILTER_ALL')); ?>" disabled="disabled" />
			<a class="modal" title="<?php JText::_('COM_REDEVENT_Select'); ?>"  href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}"><?php echo JText::_('COM_REDEVENT_Select'); ?></a>
			<a title="<?php JText::_('COM_REDEVENT_Reset'); ?>" id="venue-reset-button"><?php echo JText::_('COM_REDEVENT_Reset'); ?></a>
			<input type="hidden" id="venueid" name="venueid" value="<?php echo $this->venueid; ?>" />
		</div>
	</td>
		<td nowrap="nowrap">
			<div>
			<?php echo $this->lists['state'];	?>
			<?php echo $this->lists['featured'];	?>
			</div>
			<div>
			<label for="filter_group"><?php echo JText::_('COM_REDEVENT_SESSIONS_FILTER_GROUP_LABEL'); ?></label> 
			<?php echo $this->lists['filter_group'];	?>		
			<?php echo $this->lists['filter_group_manage'];	?>			
			</div>
		</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				#
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th><?php echo JHTML::_('grid.sort',  JText::_('COM_REDEVENT_DATE'), 'obj.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_TIME'); ?></th>
			<?php if (!$this->event): ?>
			<th><?php echo JHTML::_('grid.sort',  JText::_('COM_REDEVENT_EVENT'), 'e.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<?php endif; ?>
			<th><?php echo JHTML::_('grid.sort',  JText::_('COM_REDEVENT_VENUE'), 'v.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th><?php echo JHTML::_('grid.sort',  JText::_('COM_REDEVENT_TITLE'), 'obj.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th><?php echo JHTML::_('grid.sort',  JText::_('COM_REDEVENT_NOTE'), 'obj.note', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th width="5"><?php echo JHTML::_('grid.sort',  JText::_('COM_REDEVENT_PUBLISHED'), 'obj.published', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th width="5"><?php echo JHTML::_('grid.sort',  JText::_('COM_REDEVENT_SESSION_FEATURED'), 'obj.featured', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <?php if (!$this->event || $this->event->registra): ?>
			<th><?php echo JHTML::_('grid.sort',  JText::_('COM_REDEVENT_REGISTRATION_END'), 'obj.registrationend', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th width="5"><?php echo JText::_('COM_REDEVENT_SESSION_TABLE_HEADER_ATTENDEES'); ?></th>
      <?php endif; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="<?php echo $colspan; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php
		$k = 0;		
		if ($this->items && count($this->items)):
		foreach ((array) $this->items as $i => $row) 
		{
			/* Get the date */
			$date = (!redEVENTHelper::isValidDate($row->dates) ? JText::_('COM_REDEVENT_Open_date') : strftime( $this->params->get('formatdate', '%d.%m.%Y'), strtotime( $row->dates )));
			$enddate  = (!redEVENTHelper::isValidDate($row->enddates) || $row->enddates == $row->dates) ? '' : strftime( $this->params->get('formatdate', '%d.%m.%Y'), strtotime( $row->enddates ));
			$displaydate = $date. ($enddate ? ' - '.$enddate: '');
			$endreg = (!redEVENTHelper::isValidDate($row->registrationend) ? '-' : strftime( $this->params->get('formatdate', '%d.%m.%Y'), strtotime( $row->registrationend )));
	
			$displaytime = '';
			/* Get the time */
			if (isset($row->times) && $row->times != '00:00:00') {
				$displaytime = strftime( $this->params->get('formattime', '%H:%M'), strtotime( $row->times ));
	
				if (isset($row->endtimes) && $row->endtimes != '00:00:00') {
					$displaytime .= ' - '.strftime( $this->params->get('formattime', '%H:%M'), strtotime( $row->endtimes ));
				}
			}
			$checked 	= JHTML::_('grid.checkedout',   $row, $i );
			$published 	= JHTML::_('grid.published',   $row, $i );
			$featured = $this->featured($row, $i);
			
			$sessionlink = JRoute::_( 'index.php?option=com_redevent&controller=sessions&task=edit&cid[]='. $row->id );
			$venuelink = JRoute::_( 'index.php?option=com_redevent&controller=venues&task=edit&cid[]='. $row->venueid );
			$eventlink = JRoute::_( 'index.php?option=com_redevent&controller=events&task=edit&cid[]='. $row->eventid );
			
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $this->pagination->getRowOffset( $i ); ?></td>
				<td><?php echo $checked; ?></td>
	      <td>
					<?php
					if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) {
						echo $displaydate;
					} else {
					?>
						<a href="<?php echo $sessionlink; ?>" title="<?php echo JText::_( 'COM_REDEVENT_SESSIONS_EDIT_SESSION' ); ?>">
							<?php echo $displaydate; ?></a>
					<?php
					}
					?>
					<span class="linkfront hasTip" title="<?php echo JText::_('COM_REDEVENT_EVENT_FRONTEND_LINK')?>">
						<?php echo JHTML::link(JURI::root().RedeventHelperRoute::getDetailsRoute($row->eventid, $row->id), 
					                         JHTML::image('administrator/components/com_redevent/assets/images/linkfront.png', 
					                         JText::_('COM_REDEVENT_EVENT_FRONTEND_LINK'))); ?>
					</span>
				</td>
	      <td><?php echo $displaytime; ?></td>
				
			<?php if (!$this->event): ?>
				<td>
					<?php
					if (  JTable::isCheckedOut($this->user->get ('id'), $row->event_checked_out ) ) {
						echo $row->event_title;
					} else {
					?>
						<a href="<?php echo $eventlink; ?>" title="<?php echo JText::_('COM_REDEVENT_EDIT_EVENT' ); ?>">
							<?php echo $row->event_title; ?></a>
					<?php
					}
					?>
				</td>
				<?php endif; ?>
			  
			  <td>
					<?php
					if (  JTable::isCheckedOut($this->user->get ('id'), $row->venue_checked_out ) ) {
						echo $row->venue;
					} else {
					?>
						<a href="<?php echo $venuelink; ?>" title="<?php echo JText::_('COM_REDEVENT_EDIT_VENUE' ); ?>">
							<?php echo $row->venue; ?></a>
					<?php
					}
					?>
				</td>
				
	      <td><?php echo $row->title; ?></td>
	      <td><?php echo $row->note; ?></td>
        <td align="center">
        	<?php if ($row->published >= 0): ?>
	        <?php echo $published; ?>
	        <?php else: ?>
	        <?php echo JHTML::image('administrator/images/publish_y.png', JText::_('COM_REDEVENT_ARCHIVED')); ?>
	        <?php endif; ?>
				</td>
	      <td align="center"><?php echo $featured ?></td>
	      
	      <?php if (!$this->event || $row->registra): ?>
	      <td><?php echo $endreg; ?></td>
	      <td><?php echo ($row->registra ? 
	                      JHTML::link('index.php?option=com_redevent&view=attendees&xref='.$row->id, intval($row->attendees->attending). ' / '. intval($row->attendees->waiting)) : '-'); ?></td>
	      <?php endif; ?>
	    </tr>
	    <?php
	    $k = 1 - $k;
		}
		?>
		<?php endif; ?>
	</tbody>
	</table>
</div>

<input type="hidden" name="controller" value="sessions" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
