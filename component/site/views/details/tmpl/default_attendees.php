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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if ($this->view_attendees_list && $this->registers) {
	?>
	<div id="redevent" class="event_id<?php echo $this->row->did; ?> el_details">
		<h2 class="register"><?php echo JText::_('COM_REDEVENT_REGISTERED_USERS' ).': '.redEVENTHelper::getSessionFullTitle($this->row); ?>
		<?php if ($this->manage_attendees): ?>
    <?php echo REOutput::xrefattendeesbutton($this->row->xref); ?>
		<?php endif; ?></h2>

		<?php if (count($this->roles)): ?>
		<?php $this->showRoles(); ?>
		<?php endif; ?>

		<div class="register">
			<ul class="user floattext">
			<?php
			//loop through attendees
			$waiting_count = 0;
			foreach ($this->registers as $key => $register)
			{
				if ($register->submitter->waitinglist == 0)
				{
					if ($register->submitter->uid == $this->user->get('id')) {
						echo '<li><ul class="attendee myreg">';
					}
					else {
					  echo '<li><ul class="attendee">';
					}
					foreach ($register->answers as $k => $name) {
						if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
						echo "<li class='userfield ".strtolower($k)."'>".$name."</li>";
					}
	      	if (($this->unreg_check && $register->submitter->uid == $this->user->get('id')) || $this->candeleteattendees) {
					  $unreg_url = JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug, $this->row->xslug). '&task=delreguser&rid=' .$register->attendee_id);
            echo '<li>'. JHTML::link($unreg_url, JText::_('COM_REDEVENT_UNREGISTER'), array('class' => 'unreglink')) .'</li>';
          }
					echo '</ul></li>';
				}
				else {
					$waiting_count++;
				}
			} ?>
		</ul>
		</div>

		<?php if ($waiting_count): ?>
		<h2 class="register"><?php echo JText::_('COM_REDEVENT_WAITING_LIST' ); ?></h2>

    <div class="register">
      <ul class="user floattext">
      <?php
      //loop through attendees
      foreach ($this->registers as $key => $register) {
        if ($register->submitter->waitinglist == 1)
        {
	      	if (($register->submitter->uid == $this->user->get('id')) || $this->candeleteattendees) {
            echo '<li><ul class="attendee myreg">';
          }
          else {
            echo '<li><ul class="attendee">';
          }
          foreach ($register->answers as $k => $name) {
            if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
            echo "<li class='userfield ".strtolower($k)."'>".$name."</li>";
          }
	      	if (($this->unreg_check && $register->submitter->uid == $this->user->get('id')) || $this->candeleteattendees) {
            $unreg_url = JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug, $this->row->xslug).'&task=delreguser&rid=' .$register->attendee_id);
            echo '<li>'. JHTML::link($unreg_url, JText::_('COM_REDEVENT_UNREGISTER'), array('class' => 'unreglink')) .'</li>';
          }
          echo '</ul></li>';
        }
      } ?>
    </ul>
    </div>
    <?php endif; ?>
	</div>
	<?php
}
echo JHTML::_('link', JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug, $this->row->xslug)), JText::_('COM_REDEVENT_RETURN_EVENT_DETAILS'));
