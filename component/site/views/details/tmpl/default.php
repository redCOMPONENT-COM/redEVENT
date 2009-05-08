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
?>
<div id="eventlist" class="event_id<?php echo $this->row->did; ?> el_details">
	<p class="buttons">
			<?php echo ELOutput::mailbutton( $this->row->slug, 'details', $this->params ); ?>
			<?php echo ELOutput::printbutton( $this->print_link, $this->params ); ?>
	</p>

<?php if ($this->params->def( 'show_page_title', 1 )) : ?>
	<h1 class="componentheading">
		<?php echo $this->params->get('page_title'); ?>
	</h1>
<?php endif; ?>

<!-- Details EVENT -->
	<h2 class="eventlist">
		<?php
    	echo JText::_( 'EVENT' );
    	echo '&nbsp;'.ELOutput::editbutton($this->item->id, $this->row->did, $this->params, $this->allowedtoeditevent, 'editevent' );
    	?>
	</h2>
	<?php //flyer
	echo ELOutput::flyer( $this->row, $this->dimage, 'event' );
	?> 
	<dl class="event_info floattext"> 
	<dt class="category"><?php echo JText::_( 'CATEGORY' ).':'; ?></dt>
    		<dd class="category">
				<?php echo "<a href='".JRoute::_( 'index.php?option=com_redevent&view=categoryevents&id='.$this->row->categoryslug )."'>".$this->escape($this->row->catname)."</a>";?>
			</dd>
	</dl>
	<?php
		echo $this->tags->ReplaceTags($this->row->datdescription);
	?>
	<?php if ($this->row->show_attendants || $this->row->show_waitinglist) { ?>
		<h2 class="eventlist">
			<?php
			echo JText::_( 'EVENT_AVAILABILITY' );
			?>
		</h2>
		<dl class="event_availability floattext">
			<?php 
			if ($this->row->show_attendants) { ?>
			<dt class="max_attendants"><?php echo JText::_( 'MAX_ATTENDANTS' ).':'; ?></dt>
				<dd class="max_attendants">
					<?php echo $this->maxattendance; ?>
				</dd>
			<dt class="show_attendants"><?php echo JText::_( 'SHOW_ATTENDANTS' ).':'; ?></dt>
				<dd class="show_attendants">
					<?php echo $this->attendancelist; ?>
				</dd>
			<?php }
			
			if ($this->row->show_waitinglist) { ?>
			<dt class="max_waitinglist"><?php echo JText::_( 'MAX_WAITINGLIST' ).':'; ?></dt>
				<dd class="max_waitinglist">
					<?php echo $this->maxwaitinglist; ?>
				</dd>
			<dt class="show_waitinglist"><?php echo JText::_( 'SHOW_WAITINGLIST' ).':'; ?></dt>
				<dd class="show_waitinglist">
					<?php echo $this->waitinglist; ?>
				</dd>
			<?php } ?>
		</dl>
	<?php } 
/* If registration is enabled */
if ($this->row->registra == 1) : ?>
		<!-- Registration -->
		<h2 class="register"><?php echo JText::_( 'REGISTERED USERS' ).':'; ?></h2>
		<?php 
			echo JHTML::_('link', JRoute::_('index.php?view=details&tpl=attendees&xref='.JRequest::getInt('xref').'&id='.JRequest::getInt('id')), JText::_('SHOW_REGISTERED_USERS'));
		?>
	<?php endif; ?>
	
	<?php if ($this->elsettings->commentsystem != 0) :	?>
	
		<!-- Comments -->
		<?php echo $this->loadTemplate('comments'); ?>
		
  	<?php endif; ?>
<p class="copyright">
	<?php echo ELOutput::footer( ); ?>
</p>
</div>