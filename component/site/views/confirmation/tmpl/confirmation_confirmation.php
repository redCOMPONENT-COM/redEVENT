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
if ($this->registration) { 
	?>
	<div id="eventlist" class="event_id<?php echo $this->registration['event']->id; ?> el_details">
		<?php echo $this->tags->ReplaceTags($this->registration['event']->review_message); ?>
	</div>	
	<?php
	echo JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=details&xref='.JRequest::getInt('xref').'&id='.JRequest::getInt('id')), JText::_('RETURN_EVENT_DETAILS'));
}
else {
	echo $this->message;
}
if ($this->action == 'print') {
?>
<script type="text/javascript">
	window.open( window.location.protocol+"//"+window.location.host+'/index.php?view=confirmation&tmpl=component&page=print&xref=<?php echo JRequest::getVar('xref'); ?>&submit_key=<?php echo JRequest::getVar('submit_key'); ?>&option=com_redevent' );
</script>
<?php } ?>