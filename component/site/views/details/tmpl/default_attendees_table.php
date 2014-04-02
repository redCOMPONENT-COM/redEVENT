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

if ($this->view_attendees_list) {
	?>
	<div id="redevent" class="event_id<?php echo $this->row->did; ?> el_details">

		<h2 class="register"><?php echo JText::_('COM_REDEVENT_REGISTERED_USERS' ).': '.RedeventHelper::getSessionFullTitle($this->row); ?>
		<?php if ($this->manage_attendees): ?>
    <?php echo REOutput::xrefattendeesbutton($this->row->xref); ?>
		<?php endif; ?></h2>

		<?php if (count($this->roles)): ?>
		<?php $this->showRoles(); ?>
		<?php endif; ?>

		<div class="register">
			<table class="registered">
			<thead>
  			<tr>
          <?php foreach ((array) $this->registersfields as $f): ?>
  			  <th><?php echo $f->field_header; ?></th>
          <?php endforeach; ?>
          <th>&nbsp;</th>
  			</tr>
			</thead>
			<tbody>
  			<?php
    			//loop through attendees
    			$waiting_count = 0;
    			$n = 0;
    			if ($this->registers):
	    			foreach ($this->registers as $key => $register):
	    				if ($register->submitter->waitinglist == 0): ?>
	    				  <tr class="<?php echo ($n ? 'd1' : 'd0'); ?><?php echo ($register->submitter->uid == $this->user->get('id')) ? ' myreg': ''; ?>">
	     				    <?php	foreach ($register->answers as $k => $name): ?>
	      				  <td class='userfield <?php echo strtolower($k); ?>'>
	      				    <?php
	      						if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
	        						echo $name;
	      				  ?>
	      				  </td>
	      				  <?php endforeach; ?>

	      				  <?php if (($this->unreg_check && $register->submitter->uid == $this->user->get('id')) || $this->candeleteattendees): ?>
	      				  <?php $unreg_url = JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug, $this->row->xslug) .'&task=delreguser&rid=' .$register->attendee_id); ?>
	                <td class="attendee">
	                  <?php echo JHTML::link($unreg_url, JText::_('COM_REDEVENT_UNREGISTER'), array('class' => 'unreglink')); ?>
	                </td>
	                <?php else: ?>
	                <td class="attendee"></td>
	                <?php endif;?>
	              </tr>
	    				<?php else:	$waiting_count++; ?>
	            <?php endif;?>
	            <?php $n = 1 - $n; ?>
	          <?php endforeach; ?>
          <?php endif;?>
			</tbody>
			</table>
		</div>

		<?php if ($waiting_count): ?>
		<h2 class="register"><?php echo JText::_('COM_REDEVENT_WAITING_LIST' ); ?></h2>

    <div class="register">
      <table class="registered">
      <thead>
        <tr>
          <?php foreach ((array) $this->registersfields as $f): ?>
  			  <th><?php echo $f->field; ?></th>
          <?php endforeach; ?>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php
          //loop through attendees
    			$n = 0;
          foreach ($this->registers as $key => $register):
            if ($register->submitter->waitinglist == 1): ?>
              <tr class="<?php echo ($n ? 'd1' : 'd0'); ?><?php echo ($this->unreg_check && $register->submitter->uid == $this->user->get('id')) ? 'myreg': ''; ?>">
                <?php foreach ($register->answers as $k => $name): ?>
                <td class='userfield <?php echo strtolower($k); ?>'>
                  <?php
                  if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
                    echo $name;
                ?>
                </td>
                <?php endforeach; ?>

      				  <?php if (($this->unreg_check && $register->submitter->uid == $this->user->get('id')) || $this->candeleteattendees): ?>
                <?php $unreg_url = JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug, $this->row->xslug).'&task=delreguser&rid=' .$register->attendee_id); ?>
                <td class="attendee">
                  <?php echo JHTML::link($unreg_url, JText::_('COM_REDEVENT_UNREGISTER'), array('class' => 'unreglink')); ?>
                </td>
                <?php else: ?>
                <td class="attendee"></td>
                <?php endif;?>
              </tr>
            <?php endif;?>
            <?php $n = 1 - $n; ?>
          <?php endforeach; ?>
      </tbody>
      </table>
    </div>
    <?php endif; ?>
	</div>
	<?php
}
echo JHTML::_('link', JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug, $this->row->xref)), JText::_('COM_REDEVENT_RETURN_EVENT_DETAILS'));
