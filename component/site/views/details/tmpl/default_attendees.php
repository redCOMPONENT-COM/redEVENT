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

if ($this->row->show_names && $this->registers) {
	?>
	<div id="eventlist" class="event_id<?php echo $this->row->did; ?> el_details">
		<h2 class="register"><?php echo JText::_( 'REGISTERED USERS' ).': '.$this->row->title; ?></h2>
		
		<div class="register">
			<ul class="user floattext">
			<?php
			//loop through attendees
			foreach ($this->registers as $key => $register) {
//				//if CB
//				if ($this->elsettings->comunsolution == 1) :
//					$thumb_path = 'images/comprofiler/tn';
//					$no_photo 	= ' alt="'.$register->name.'"';
//					if ($this->elsettings->comunoption == 1) :
//						//User has avatar
//						if(!empty($register->avatar)) :
//							echo "<li><a href='".JRoute::_('index.php?option=com_comprofiler&task=userProfile&user='.$register->uid )."'><img src=".$thumb_path.$register->avatar.$no_photo." alt='no photo' /><span class='username'>".$register->name."</span></a></li>";
//						//User has no avatar
//						else :
//							echo "<li><a href='".JRoute::_( 'index.php?option=com_comprofiler&task=userProfile&user='.$register->uid )."'><img src=\"components/com_comprofiler/images/english/tnnophoto.jpg\" alt=\"no photo\" /><span class='username'>".$register->name."</span></a></li>";
//						endif;
//					endif;
//			
//					//only show the username with link to profile
//					if ($this->elsettings->comunoption == 0) :
//						echo "<li><span class='username'><a href='".JRoute::_( 'index.php?option=com_comprofiler&amp;task=userProfile&amp;user='.$register->uid )."'>".$register->name." </a></span></li>";
//					endif;
//			
//				//if CB end - if not CB than only name
//				endif;
			
				//no communitycomponent is set so only show the username
				// if ($this->elsettings->comunsolution == 0) :
				if ($register->waitinglist == 0)
				{
					echo '<li><ul class="attendee">';
					foreach ($register->answers as $key => $name) {
						if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
						echo "<li class='userfield ".strtolower($key)."'>".$name."</li>";
					}
					echo '</ul></li>';
				}
			} ?>
		</ul>
		</div>
		
		<h2 class="register"><?php echo JText::_( 'WAITING LIST' ); ?></h2>
    
    <div class="register">
      <ul class="user floattext">
      <?php
      //loop through attendees
      foreach ($this->registers as $key => $register) {
//        //if CB
//        if ($this->elsettings->comunsolution == 1) :
//          $thumb_path = 'images/comprofiler/tn';
//          $no_photo   = ' alt="'.$register->name.'"';
//          if ($this->elsettings->comunoption == 1) :
//            //User has avatar
//            if(!empty($register->avatar)) :
//              echo "<li><a href='".JRoute::_('index.php?option=com_comprofiler&task=userProfile&user='.$register->uid )."'><img src=".$thumb_path.$register->avatar.$no_photo." alt='no photo' /><span class='username'>".$register->name."</span></a></li>";
//            //User has no avatar
//            else :
//              echo "<li><a href='".JRoute::_( 'index.php?option=com_comprofiler&task=userProfile&user='.$register->uid )."'><img src=\"components/com_comprofiler/images/english/tnnophoto.jpg\" alt=\"no photo\" /><span class='username'>".$register->name."</span></a></li>";
//            endif;
//          endif;
//      
//          //only show the username with link to profile
//          if ($this->elsettings->comunoption == 0) :
//            echo "<li><span class='username'><a href='".JRoute::_( 'index.php?option=com_comprofiler&amp;task=userProfile&amp;user='.$register->uid )."'>".$register->name." </a></span></li>";
//          endif;
//      
//        //if CB end - if not CB than only name
//        endif;
      
        //no communitycomponent is set so only show the username
        // if ($this->elsettings->comunsolution == 0) :
        if ($register->waitinglist == 1)
        {
          echo '<li><ul class="attendee">';
          foreach ($register->answers as $key => $name) {
            if (stristr($name, '~~~')) $name = str_replace('~~~', '<br />', $name).'<br />';
            echo "<li class='userfield ".strtolower($key)."'>".$name."</li>";
          }
          echo '</ul></li>';
        }
      } ?>
    </ul>
    </div>
	</div>
	<?php
	if ($this->formhandler == 3) {
		//the user is allready registered. Let's check if he can unregister from the event
				if ($this->row->unregistra == 0) :
		
					//no he is not allowed to unregister
					echo JText::_( 'ALLREADY REGISTERED' );
		
				else:
		
					//he is allowed to unregister -> display form
					?>
		<form id="Eventlist" action="<?php echo JRoute::_('index.php'); ?>" method="post">
			<p>
				<?php echo JText::_( 'UNREGISTER BOX' ).': '; ?>
				<input type="checkbox" name="reg_check" onclick="check(this, document.getElementById('el_send_attend'))" />
			</p>
			<p>
				<input type="submit" id="el_send_attend" name="el_send_attend" value="<?php echo JText::_( 'UNREGISTER' ); ?>" disabled="disabled" />
			</p>
			<p>
				<input type="hidden" name="xref" value="<?php echo $this->row->xref; ?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
				<input type="hidden" name="task" value="delreguser" />
			</p>
		</form>
		<?php
		endif;
	}
}
echo JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=details&xref='.JRequest::getInt('xref').'&id='.JRequest::getInt('id')), JText::_('RETURN_EVENT_DETAILS'));
?>