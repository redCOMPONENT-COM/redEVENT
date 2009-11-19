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

if ($this->manage_attendees) {
	?>
	<div id="eventlist" class="event_id<?php echo $this->row->did; ?> el_details">
		<h2 class="register"><?php echo JText::_( 'REGISTERED USERS' ).': '.$this->row->title; ?></h2>
		
		<?php echo JHTML::link('index.php?option=com_redevent&view=details&task=exportattendees&format=csv&xref='. $this->row->xref, JText::_('CSV export'));?>
		<div class="register">
			<?php	if (!empty($this->registers)):	?>
			<table class="registered">
			<thead>
  			<tr>
          <?php foreach ($this->registers[0]->fields as $f): ?>
  			  <th><?php echo $f; ?></th>
          <?php endforeach; ?>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
  			</tr>
			</thead>
			<tbody>
  			<?php 
    			//loop through attendees
    			foreach ($this->registers as $key => $register):
    				if ($register->submitter->waitinglist == 0): ?>
    				  <tr>
     				    <?php	foreach ($register->answers as $k => $name): ?>
      				  <td class='userfield <?php echo strtolower($k); ?>'>
      				    <?php 
      						if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
        						echo $name;
      				  ?>
      				  </td>
      				  <?php endforeach; ?>
      				  <?php $edit_url = JRoute::_('index.php?option=com_redevent&view=signup&task=edit&xref='. $this->row->xref .'&submitter_id='. $register->id); ?>
                <?php //$edit_url = JRoute::_('index.php?option=com_redevent&view=details&id='. $this->row->slug .'&task=editreguser&xref='. $this->row->xref .'&sid=' .$register->id); ?>
                <td class="edit">
                  <?php echo JHTML::link($edit_url, JText::_('edit'), array('class' => 'editlink')); ?>
                </td>
      				  <?php $unreg_url = JRoute::_('index.php?option=com_redevent&view=details&id='. $this->row->slug .'&task=managedelreguser&xref='. $this->row->xref .'&sid=' .$register->id); ?>
                <td class="attendee">
                  <?php echo JHTML::link($unreg_url, JText::_('cancel'), array('class' => 'unreglink')); ?>
                </td>
              </tr>
    				<?php else:	$waiting_count++; ?>
            <?php endif;?>
          <?php endforeach; ?>
			</tbody>
			</table>
			<?php endif; ?>
		</div>
		
		<?php if ($waiting_count): ?>
		<h2 class="register"><?php echo JText::_( 'WAITING LIST' ); ?></h2>
    
    <div class="register">
      <table class="registered">
      <thead>
        <tr>
          <?php foreach ($this->registers[0]->fields as $f): ?>
          <td><?php echo $f; ?></td>
          <?php endforeach; ?>
          <td>&nbsp;</td>
        </tr>
      </thead>
      <tbody>
        <?php 
          //loop through attendees
          foreach ($this->registers as $key => $register):
            if ($register->submitter->waitinglist == 1): ?>
              <tr>
                <?php foreach ($register->answers as $k => $name): ?>
                <td class='userfield <?php echo strtolower($k); ?>'>
                  <?php 
                  if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
                    echo $name;
                ?>
                </td>
                <?php endforeach; ?>
                
                <?php $edit_url = JRoute::_('index.php?option=com_redevent&view=details&id='. $this->row->slug .'&task=editreguser&xref='. $this->row->xref .'&sid=' .$register->id); ?>
                <td class="edit">
                  <?php echo JHTML::link($edit_url, JText::_('edit'), array('class' => 'editlink')); ?>
                </td>
                <?php $unreg_url = JRoute::_('index.php?option=com_redevent&view=details&id='. $this->row->slug .'&task=delreguser&xref='. $this->row->xref .'&sid=' .$register->id); ?>
                <td class="attendee">
                  <?php echo JHTML::link($unreg_url, JText::_('cancel'), array('class' => 'unreglink')); ?>
                </td>
              </tr>
            <?php endif;?>
          <?php endforeach; ?>
      </tbody>
      </table>
    </div>    
    <?php endif; ?>
	</div>
	<?php
}
echo JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=myevents'), JText::_('RETURN TO MY EVENTS'));
?>