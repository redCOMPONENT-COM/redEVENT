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

<form action="index.php" method="post" name="adminForm" id="adminForm">
	
	<table class="adminform">
		<tr>
			<td style="text-align:left;">
				<?php echo $this->lists['filter_confirmed']; ?> <?php echo $this->lists['filter_waiting']; ?> <?php echo $this->lists['filter_cancelled']; ?>
			</td>
		</tr>
	</table>
	
	<?php if ($this->cancelled): ?>
	<div class="cancelled-notice"><?php echo ($this->cancelled == 1 ? JTExt::_('COM_REDEVENT_CANCELLED_REGISTRATIONS') : JTExt::_('COM_REDEVENT_ALL_REGISTRATIONS')); ?></div>
	<?php endif; ?>
	
	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">#</th>
				<th width="5"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_REGDATE', 'r.uregdate', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_SESSION', 'e.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_UNIQUE_ID', 'r.id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_USERNAME', 'u.username', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_ACTIVATED', 'r.confirmed', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_WAITINGLIST', 'r.waitinglist', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
        <th class="title"><?php echo JText::_( 'COM_REDEVENT_ANSWERS' ); ?></th>
        <th class="title"><?php echo JText::_('COM_REDEVENT_PRICE' ); ?></th>
        <th class="title"><?php echo JText::_( 'COM_REDEVENT_PRICEGROUP' ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_PAYMENT', 'p.paid', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
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
				
				$link 		= 'index.php?option=com_redevent&view=attendee&xref='. $row->xref.'&cid[]='.$row->id;
				$checked 	= JHTML::_('grid.checkedout', $row, $i );
				
				$eventdate = (!redEVENTHelper::isValidDate($row->dates) ? JText::_('COM_REDEVENT_Open_date') : strftime( $this->settings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $row->dates )));
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
							echo JHTML::Date( $row->uregdate, JText::_('DATE_FORMAT_LC2' ) );
						} else {
					?>
					<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_REGISTRATION' );?>::<?php echo $row->name; ?>">
					<a href="<?php echo $link; ?>">
					<?php echo JHTML::Date( $row->uregdate, JText::_('DATE_FORMAT_LC2' ) ); ?>
					</a></span>
					<?php } ?>
				</td>
				<td><?php echo $sessionlink; ?></td>
				<td><?php echo $row->course_code .'-'. $row->xref .'-'. $row->attendee_id; ?></td>
				<td><?php echo $row->name; ?></td>
				<td>
				  <?php 
				  if (!$row->confirmed) 
				  {
            echo JHTML::link('javascript: void(0);', 
            		             JHTML::_('image', 'admin/publish_x.png', JText::_('JNO'), null, true), 
            		             array('onclick' => 'return listItemTask(\'cb'.$i.'\', \'confirmattendees\');',
            		                   'class' => 'hasTip',
            		             		   'title' => Jtext::_('COM_REDEVENT_REGISTRATION_NOT_ACTIVATED')
            		             		              .'::'.Jtext::_('COM_REDEVENT_CLICK_TO_ACTIVATE'))
            		);
            
				  }
          else 
          {
          	$tip = Jtext::_('COM_REDEVENT_REGISTRATION_ACTIVATED')
            		   .'::'.Jtext::sprintf('COM_REDEVENT_REGISTRATION_ACTIVATED_ON_S'
            		   		                  ,JHTML::Date( $row->confirmdate, JText::_('DATE_FORMAT_LC2' )));
          	echo JHTML::link('javascript: void(0);', 
          			             JHTML::_('image', 'admin/tick.png', JText::_('JYES'), null, true), 
          			             array('onclick' => 'return listItemTask(\'cb'.$i.'\', \'unconfirmattendees\');',
            		                   'class' => 'hasTip',
            		             		   'title' => $tip
          			             		));
          }
				  ?>
				</td>
				<td>
					<?php if (!$row->maxattendees): // no waiting list ?>
					<?php echo '-'; ?>					
          <?php else:
          if (!$row->waitinglist) // attending
          {
          	$tip = Jtext::_('COM_REDEVENT_REGISTRATION_CURRENTLY_ATTENDING')
            		   .'::'.Jtext::_('COM_REDEVENT_REGISTRATION_CLICK_TO_PUT_ON_WAITING_LIST');
            echo JHTML::link('javascript: void(0);', 
                              JHTML::_('image', 'administrator/components/com_redevent/assets/images/attending-16.png', JText::_('COM_REDEVENT_REGISTRATION_CURRENTLY_ATTENDING'), null, false), 
                              array('onclick' => 'return listItemTask(\'cb'.$i.'\', \'onwaiting\');',
            		                   'class' => 'hasTip',
            		             		   'title' => $tip
          			             		));
          }
          else // waiting
          {
          	$tip = Jtext::_('COM_REDEVENT_REGISTRATION_CURRENTLY_ON_WAITING_LIST')
            		   .'::'.Jtext::_('COM_REDEVENT_REGISTRATION_CLICK_TO_TAKE_OFF_WAITING_LIST');
            echo JHTML::link( 'javascript: void(0);', 
                              JHTML::_('image', 'administrator/components/com_redevent/assets/images/enumList.png', JText::_('COM_REDEVENT_REGISTRATION_CURRENTLY_ON_WAITING_LIST'), null, false), 
                              array('onclick' => 'return listItemTask(\'cb'.$i.'\', \'offwaiting\');',
            		                   'class' => 'hasTip',
            		             		   'title' => $tip
          			             		));
          }
          endif; ?>
        </td>
        
        <td><a href="<?php echo JRoute::_('index.php?option=com_redevent&view=attendeeanswers&tmpl=component&submitter_id='. $row->submitter_id); ?>" class="answersmodal" rel="{handler: 'iframe'}"><?php echo JText::_('COM_REDEVENT_view')?></a></td>
				
					<td>
						<?php echo $row->price; ?>
					</td>
					<td>
						<?php echo $row->pricegroup; ?>
					</td>
					<td class="price <?php echo ($row->paid ? 'paid' : 'unpaid'); ?>">
						<?php $link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&submit_key='.$row->submit_key), JText::_('COM_REDEVENT_history')); ?>
						<?php if (!$row->paid): ?>
						<span class="hasTip" title="<?php echo JText::_('COM_REDEVENT_REGISTRATION_NOT_PAID').'::'.$row->status; ?>"><?php echo JHTML::_('image', 'admin/publish_x.png', 'Not Paid', null, true); ?><?php echo $link; ?></span>
						<?php echo ' '.JHTML::link(JURI::root().'/index.php?option=com_redform&controller=payment&task=select&key='.$row->submit_key, JText::_('COM_REDEVENT_link')); ?>
						<?php else: ?>
						<span class="hasTip" title="<?php echo JText::_('COM_REDEVENT_REGISTRATION_PAID').'::'.$row->status; ?>"><?php echo JHTML::_('image', 'admin/tick.png', 'Paid', null, true); ?><?php echo $link; ?></span>
						<?php endif; ?>						
					</td>
			</tr>
			<?php $k = 1 - $k; } ?>
		</tbody>

	</table>

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_redevent" />
		<input type="hidden" name="controller" value="registrations" />
		<input type="hidden" name="view" value="registrations" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />

</form>
