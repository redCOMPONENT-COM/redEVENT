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
JHTML::_('behavior.tooltip');
$colspan = 13;
?>

<form action="index.php" method="post" name="adminForm">
	
	<table class="adminform">
		<tr>
			 <td width="100%">
				<!-- <input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" /> -->
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="this.form.getElementById('filter').value='0';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td style="text-align:right;">
				<?php echo $this->lists['filter_confirmed']; ?> <?php echo $this->lists['filter_waiting']; ?> <?php echo $this->lists['filter_cancelled']; ?>
			</td>
		</tr>
	</table>
	
	<table class="adminlist">
		<thead>
			<tr>
				<th width="5"><?php echo JText::_( 'Num' ); ?></th>
				<th width="5"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'REGDATE', 'r.uregdate', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_SESSION', 'e.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'UNIQUE ID', 'r.id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'USERNAME', 'u.username', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'CONFIRMED', 'r.confirmed', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'WAITINGLIST', 'r.waitinglist', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
        <th class="title"><?php echo JText::_( 'COM_REDEVENT_ANSWERS' ); ?></th>
        <th class="title"><?php echo JText::_( 'PRICE' ); ?></th>
        <th class="title"><?php echo JText::_( 'COM_REDEVENT_PRICEGROUP' ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'PAYMENT', 'p.paid', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<td colspan="<?php echo $colspan; ?>"><?php echo $this->pageNav->getListFooter(); ?></td>
			</tr>
		</tfoot>

		<tbody>
			<?php
			$k = 0;
			for($i=0, $n=count( $this->rows ); $i < $n; $i++) 
			{
				$row = &$this->rows[$i];
				
				$link 		= 'index.php?option=com_redevent&controller=attendees&task=edit&xref='. $row->xref.'&cid[]='.$row->id;
				$checked 	= JHTML::_('grid.checkedout', $row, $i );
				
				$eventdate = (!redEVENTHelper::isValidDate($row->dates) ? Jtext::_('Open date') : strftime( $this->settings->formatdate, strtotime( $row->dates )));
				$sessionlink = JHTML::link('index.php?option=com_redevent&view=attendees&xref='.$row->xref, 
				                           $row->title . '<br/>'.$eventdate, 
				                           'class="hasTip" title="'.JText::_('COM_REDEVENT_VIEW_REGISTRATIONS_CLICK_TO_MANAGE').'::"').'<br/>@'.$row->venue.'</br>'.JText::_('COM_REDEVENT_AUTHOR').': '.$row->creator;
   			?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
				<td><?php echo $checked; ?></td>
				<td>
					<?php
						if ( $row->checked_out && ( $row->checked_out != $this->user->get('id') ) ) {
							echo JHTML::Date( $row->uregdate, JText::_( 'DATE_FORMAT_LC2' ) );
						} else {
					?>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'EDIT MEMBER' );?>::<?php echo $row->name; ?>">
					<a href="<?php echo $link; ?>">
					<?php echo JHTML::Date( $row->uregdate, JText::_( 'DATE_FORMAT_LC2' ) ); ?>
					</a></span>
					<?php } ?>
				</td>
				<td><?php echo $sessionlink; ?></td>
				<td><?php echo $row->course_code .'-'. $row->xref .'-'. $row->attendee_id; ?></td>
				<td><?php echo $row->name; ?></td>
				<td>
				  <?php 
				  //echo $row->confirmed == 0 ? JText::_('NO') : JText::_('YES'); 
				  if (!$row->confirmed) {
            echo JHTML::_('image.administrator', 'publish_x.png');
				  }
          else {?>
            <span class="hasTip" title="<?php echo JHTML::Date( $row->confirmdate, JText::_( 'DATE_FORMAT_LC2' )); ?>">
            <?php echo JHTML::_('image.administrator', 'tick.png'); ?>
            </span>
            <?php 
          }
				  ?>
				</td>
				<td>
          <?php 
          if (!$row->waitinglist) {
          	echo JHTML::_('image.administrator', 'publish_x.png');
          }
          else {
            echo JHTML::_('image.administrator', 'tick.png');
          }
          ?>
        </td>
        
        <td><a href="<?php echo JRoute::_('index.php?option=com_redevent&view=attendeeanswers&tmpl=component&submitter_id='. $row->submitter_id); ?>" class="answersmodal"><?php echo JText::_('view')?></a></td>
				
					<td>
						<?php echo $row->price; ?>
					</td>
					<td>
						<?php echo $row->pricegroup; ?>
					</td>
					<td class="price <?php echo ($row->paid ? 'paid' : 'unpaid'); ?>">
						<?php $link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&submit_key='.$row->submit_key), JText::_('history')); ?>
						<?php if (!$row->paid): ?>
						<span class="hasTip" title="<?php echo JText::_('REGISTRATION_NOT_PAID').'::'.$row->status; ?>"><?php echo JHTML::_('image.administrator', 'publish_x.png'); ?><?php echo $link; ?></span>
						<?php echo ' '.JHTML::link(JURI::root().'/index.php?option=com_redform&controller=payment&task=select&key='.$row->submit_key, JText::_('link')); ?>
						<?php else: ?>
						<span class="hasTip" title="<?php echo JText::_('REGISTRATION_PAID').'::'.$row->status; ?>"><?php echo JHTML::_('image.administrator', 'tick.png'); ?><?php echo $link; ?></span>
						<?php endif; ?>						
					</td>
			</tr>
			<?php $k = 1 - $k; } ?>
		</tbody>

	</table>

	<p class="copyright">
		<?php echo ELAdmin::footer( ); ?>

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_redevent" />
		<input type="hidden" name="controller" value="registrations" />
		<input type="hidden" name="view" value="registrations" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
	</p>
</form>