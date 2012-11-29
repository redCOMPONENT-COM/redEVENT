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
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table class="adminform">
		<tr>
			<td width="100%">
				<?php
				echo JText::_('COM_REDEVENT_SEARCH' );
				echo $this->lists['filter'];
				?>
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
				<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th width="5">#</th>
				<th width="5"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
				<th class="title"><?php echo JText::_('COM_REDEVENT_TIMEDETAILS' ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_EVENT_TITLE', 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_CATEGORY', 'cat.catname', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JText::_('COM_REDEVENT_CREATION' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<td colspan="12">
					<?php echo $this->pageNav->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>

		<tbody>
			<?php
			$k = 0;
			for($i=0, $n=count( $this->rows ); $i < $n; $i++) {
				$row = $this->rows[$i];
				$link 			= 'index.php?option=com_redevent&amp;controller=events&amp;task=edit&amp;cid[]='.$row->id;
	
				$checked 	= JHTML::_('grid.checkedout', $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
					<td><?php echo $checked; ?></td>
					<td><div id="timedetails">
					<?php if (isset($this->eventvenues[$row->id])) { ?>
						<table class="adminlist">
						<thead>
							<tr>
								<th><?php echo JText::_('COM_REDEVENT_VENUE'); ?></th>
								<th><?php echo JText::_('COM_REDEVENT_CITY'); ?></th>
								<th><?php echo JText::_('COM_REDEVENT_DATE'); ?></th>
								<th><?php echo JText::_('COM_REDEVENT_TIME'); ?></th>
             		<th><?php echo JText::_('COM_REDEVENT_ATTENDEES'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
							foreach ($this->eventvenues[$row->id] as $key => $eventdetails) {
								/* Get the date */
								if (redEVENTHelper::isValidDate($eventdetails->dates)) 
								{
									$date = strftime( $this->elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $eventdetails->dates )); 
									$enddate 	= strftime( $this->elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $eventdetails->enddates ));
									$displaydate = $date.' - '.$enddate;
								}
								else {
									$displaydate = JText::_('COM_REDEVENT_OPEN_DATE');
								}
									
								/* Get the time */
								$time = strftime( $this->elsettings->get('formattime', '%H:%M'), strtotime( $eventdetails->times ));
								$endtimes = strftime( $this->elsettings->get('formattime', '%H:%M'), strtotime( $eventdetails->endtimes ));
								$displaytime = $time.' - '.$endtimes;
								?>
								<tr class="eventdatetime">
									<td><?php echo $eventdetails->venue; ?></td>
									<td><?php echo $eventdetails->city; ?></td>
									<td><?php echo $displaydate; ?></td>
									<td><?php echo $displaytime; ?></td>
	    						<td>
	    						  <?php	if ($row->registra == 1):
	      						  $linkreg  = 'index.php?option=com_redevent&amp;view=attendees&eventid='.$row->id.'&xref='.$eventdetails->id;
	      						  ?>
	      						  <a href="<?php echo $linkreg; ?>" title="Edit Users"><?php echo $eventdetails->regcount; ?></a>
	      						<?php else: ?> 
	      						  -
	      						<?php endif; ?>
	    						</td>
								</tr>
								<?php 
							}
							?>
						</tbody>
						</table>
					<?php } ?>
					</div></td>
					<td>
						<?php
						if ( $row->checked_out && ( $row->checked_out != $this->user->get('id') ) ) {
							echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
						} else {
							?>
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_EVENT' );?>::<?php echo $row->title; ?>">
							<a href="<?php echo $link; ?>">
								<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8'); ?>
							</a></span>
							<?php
						}
						?>
	
						<br />
	
						<?php
						if (JString::strlen($row->alias) > 25) {
							echo JString::substr( htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8'), 0 , 25).'...';
						} else {
							echo htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8');
						}
						?>
					</td>
					<td>
						<?php
						if ($row->catname) {
								echo htmlspecialchars($row->catname, ENT_QUOTES, 'UTF-8');
							} 
						else {
							echo '-';
						}
						?>
					</td>
					<td>
						<?php echo JText::_('COM_REDEVENT_AUTHOR' ).': '; ?><a href="<?php echo 'index.php?option=com_users&amp;task=edit&amp;hidemainmenu=1&amp;cid[]='.$row->created_by; ?>"><?php echo $row->author; ?></a><br />
						<?php echo JText::_('COM_REDEVENT_EMAIL' ).': '; ?><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a><br />
						<?php
						$created	 	= JHTML::Date( $row->created, JText::_('DATE_FORMAT_LC2' ) );
						$edited 		= JHTML::Date( $row->modified, JText::_('DATE_FORMAT_LC2' ) );
						$ip				= $row->author_ip == 'DISABLED' ? JText::_('COM_REDEVENT_DISABLED' ) : $row->author_ip;
						$image 			= JHTML::_('image', 'administrator/templates/'. $this->template .'/images/menu/icon-16-info.png', JText::_('COM_REDEVENT_NOTES') );
						$overlib 		= JText::_('COM_REDEVENT_CREATED_AT' ).': '.$created.'<br />';
						$overlib		.= JText::_('COM_REDEVENT_WITH_IP' ).': '.$ip.'<br />';
						if ($row->modified != '0000-00-00 00:00:00') {
							$overlib 	.= JText::_('COM_REDEVENT_EDITED_AT' ).': '.$edited.'<br />';
							$overlib 	.= JText::_('COM_REDEVENT_EDITED_FROM' ).': '.$row->editor.'<br />';
						}
						?>
						<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EVENT_STATS'); ?>::<?php echo $overlib; ?>">
							<?php echo $image; ?>
						</span>
					</td>
				</tr>
				<?php $k = 1 - $k;
			} ?>

		</tbody>
	</table>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="view" value="archive" />
	<input type="hidden" name="controller" value="archive" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>