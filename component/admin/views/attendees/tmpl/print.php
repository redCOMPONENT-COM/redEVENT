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
$colspan = 10;
?>

	<table class="adminlist">
		<tr>
		  	<td width="80%">
				<b><?php echo JText::_('COM_REDEVENT_DATE' ).':'; ?></b>&nbsp;<?php echo (redEVENTHelper::isValidDate($this->event->dates) ? $this->event->dates : JText::_('COM_REDEVENT_OPEN_DATE')); ?><br />
				<b><?php echo JText::_('COM_REDEVENT_EVENT_TITLE' ).':'; ?></b>&nbsp;<?php echo htmlspecialchars($this->event->title, ENT_QUOTES, 'UTF-8'); ?>
			</td>
		  </tr>
	</table>
	<br />
	
	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">#</th>
				<th class="title"><?php echo JText::_('COM_REDEVENT_REGDATE'); ?></th>
				<th class="title"><?php echo JText::_('COM_REDEVENT_ACTIVATIONDATE'); ?></th>
				<th class="title"><?php echo JText::_('COM_REDEVENT_UNIQUE_ID'); ?></th>
				<th class="title"><?php echo JText::_('COM_REDEVENT_USERNAME'); ?></th>
				<th class="title"><?php echo JText::_('COM_REDEVENT_ACTIVATED'); ?></th>
				<th class="title"><?php echo JText::_('COM_REDEVENT_WAITINGLIST'); ?></th>
				<?php foreach ((array) $this->rf_fields as $f):?>
					<?php $colspan++; ?>
					<th class="title"><?php echo $f->field_header; ?></th>
				<?php endforeach;?>
				<?php if ($this->form->activatepayment): ?>
	        <th class="title"><?php echo JText::_('COM_REDEVENT_PRICE' ); ?></th>
	        <th class="title"><?php echo JText::_( 'COM_REDEVENT_PRICEGROUP' ); ?></th>
					<th class="title"><?php echo JText::_('COM_REDEVENT_PAYMENT'); ?></th>
					<?php $colspan += 3; ?>
        <?php endif; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			$k = 0;
			for($i=0, $n=count( $this->rows ); $i < $n; $i++) 
			{
				$row = &$this->rows[$i];
   			?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $i+1; ?></td>
				<td>
					<?php echo JHTML::Date( $row->uregdate, JText::_('DATE_FORMAT_LC2' ) ); ?>
				</td>
				<td><?php echo ($row->confirmdate) ? JHTML::Date( $row->confirmdate, JText::_('DATE_FORMAT_LC2' ) ) : '-'; ?></td>
				<td><?php echo $row->course_code .'-'. $row->xref .'-'. $row->attendee_id; ?></td>
				<td><?php echo $row->name; ?></td>
				<td>
				  <?php 
				  //echo $row->confirmed == 0 ? JText::_('COM_REDEVENT_NO') : JText::_('COM_REDEVENT_YES'); 
				  if ($row->confirmed) {
            echo JText::_('COM_REDEVENT_Yes');
				  }
          else {
            echo JText::_('COM_REDEVENT_No');
          }
				  ?>
				</td>
				<td><?php // echo $row->waitinglist == 0 ? JText::_('COM_REDEVENT_NO') : JText::_('COM_REDEVENT_YES'); ?>
          <?php 
          //echo $row->confirmed == 0 ? JText::_('COM_REDEVENT_NO') : JText::_('COM_REDEVENT_YES'); 
          if ($row->waitinglist) {
            echo JText::_('COM_REDEVENT_Yes');
          }
          else {
            echo JText::_('COM_REDEVENT_No');
          }
          ?>
        </td>
				
        <?php foreach ((array) $this->rf_fields as $f):?>
					<?php $fname = 'field_'.$f->id; ?>
					<td><?php echo $row->$fname; ?></td>
				<?php endforeach;?>
        
				<?php if ($this->form->activatepayment): ?>
					<td>
						<?php echo $row->price; ?>
					</td>
					<td>
						<?php echo $row->pricegroup; ?>
					</td>
					<td class="price <?php echo ($row->paid ? 'paid' : 'unpaid'); ?>">
						<?php if (!$row->paid): ?>
            <?php echo JText::_('COM_REDEVENT_No'); ?>
						<?php else: ?>
            <?php echo JText::_('COM_REDEVENT_Yes'); ?>
						<?php endif; ?>						
					</td>
				<?php endif; ?>
			</tr>
			<?php $k = 1 - $k; } ?>
		</tbody>

	</table>
