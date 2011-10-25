<?php
/**
 * @version 1.0 $Id: default_attendees.php 299 2009-06-24 08:20:04Z julien $
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

$waiting_count = 0;

$edit_image   = JHTML::_('image.site', 'calendar_edit.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_('COM_REDEVENT_Edit' ), 'class="hasTip" title="'.JText::_('COM_REDEVENT_Edit' ).'::"');
$remove_image = JHTML::_('image.site', 'no.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_('COM_REDEVENT_Delete' ), 'class="hasTip" title="'.JText::_('COM_REDEVENT_Delete' ).'::"');

if ($this->manage_attendees) {
	?>	
	<script language="javascript" type="text/javascript">
	function tableOrdering( order, dir, task )
	{
	        var form = document.manageform;
	 
	        form.filter_order.value = order;
	        form.filter_order_Dir.value = dir;
	        form.submit( task );
	}
	</script>
	
	
	<form action="<?php echo $this->action; ?>" method="post" name="manageform">
	<div id="redevent" class="event_id<?php echo $this->row->eventid; ?> el_details">
		<h2 class="register"><?php echo JText::_('COM_REDEVENT_REGISTERED_USERS' ).': '.$this->row->full_title; ?></h2>
		
		<?php echo JHTML::link('index.php?option=com_redevent&controller=attendees&task=exportattendees&format=csv&xref='. $this->row->xref, JText::_('COM_REDEVENT_CSV_export'));?>
						
		<?php if (count($this->roles)): ?>
		<?php $this->showRoles(); ?>
		<?php endif; ?>
		
		<div class="register">
			<?php	if (!empty($this->registers)):	?>
			<table class="registered">
			<thead>
  			<tr>
  				<th>#</th>
          <?php foreach ($this->registers[0]->fields as $k => $f): ?>
  			  <th><?php echo JHTML::_('grid.sort', $this->escape($f), 'a.'.$k, $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <?php endforeach; ?>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
  			  <th><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_Registration_id'), 'r.id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
  			</tr>
			</thead>
			<tbody>
  			<?php 
    			//loop through attendees
   				$n = 1;
    			foreach ($this->registers as $key => $register):
    				if ($register->submitter->waitinglist == 0): ?>
    				  <tr>
    				  	<td><?php echo $n++; ?></td>
     				    <?php	foreach ($register->answers as $k => $name): ?>
      				  <td class='userfield <?php echo strtolower($k); ?>'>
      				    <?php 
      						if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
        						echo $name;
      				  ?>
      				  </td>
      				  <?php endforeach; ?>
      				  
      				  <?php $edit_url = JRoute::_('index.php?option=com_redevent&controller=registration&task=manageredit&xref='. $this->row->xref .'&submitter_id='. $register->id); ?>
                <td class="edit">
                  <?php echo JHTML::link($edit_url, $edit_image, array('class' => 'editlink')); ?>
                </td>
      				  <?php $unreg_url = JRoute::_(RedeventHelperRoute::getManageAttendees($this->row->xref, 'managedelreguser').'&rid=' .$register->attendee_id); ?>
                <td class="attendee">
                  <?php echo JHTML::link($unreg_url, $remove_image, array('class' => 'unreglink')); ?>
                </td>
                <td><?php echo $this->row->course_code .'-'. $this->row->xref .'-'. $register->attendee_id; ?></td>
              </tr>
    				<?php else:	$waiting_count++; ?>
            <?php endif;?>
          <?php endforeach; ?>
			</tbody>
			</table>
			<?php endif; ?>
		</div>
		
		<?php if ($waiting_count): ?>
		<h2 class="register"><?php echo JText::_('COM_REDEVENT_WAITING_LIST' ); ?></h2>
    
    <div class="register">
      <table class="registered">
      <thead>
        <tr>
  				<th>#</th>
          <?php foreach ($this->registers[0]->fields as $k => $f): ?>
  			  <th><?php echo JHTML::_('grid.sort', $this->escape($f), 'a.'.$k, $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <?php endforeach; ?>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
  			  <th><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_Registration_id'), 'r.id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php 
          //loop through attendees
   				$n = 1;
          foreach ($this->registers as $key => $register):
            if ($register->submitter->waitinglist == 1): ?>
              <tr>
    				  	<td><?php echo $n++; ?></td>
                <?php foreach ($register->answers as $k => $name): ?>
                <td class='userfield <?php echo strtolower($k); ?>'>
                  <?php 
                  if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
                    echo $name;
                ?>
                </td>
                <?php endforeach; ?>
                
      				  <?php $edit_url = JRoute::_('index.php?option=com_redevent&view=signup&task=manageredit&xref='. $this->row->xref .'&submitter_id='. $register->id); ?>
                <td class="edit">
                  <?php echo JHTML::link($edit_url, $edit_image, array('class' => 'editlink')); ?>
                </td>
      				  <?php $unreg_url = JRoute::_('index.php?option=com_redevent&view=details&id='. $this->row->slug .'&task=managedelreguser&xref='. $this->row->xref .'&rid=' .$register->attendee_id); ?>
                <td class="attendee">
                  <?php echo JHTML::link($unreg_url, $remove_image, array('class' => 'unreglink')); ?>
                </td>
                <td><?php echo $this->row->course_code .'-'. $this->row->xref .'-'. $register->attendee_id; ?></td>
              </tr>
            <?php endif;?>
          <?php endforeach; ?>
      </tbody>
      </table>
    </div>    
    <?php endif; ?>
	</div>
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	</form>
	<?php
}
echo JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=myevents'), JText::_('COM_REDEVENT_RETURN_TO_MY_EVENTS'));
?>