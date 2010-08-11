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

JHTML::_('behavior.formvalidation');

?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;
	
	if (pressbutton == 'cancelemail') {
		submitform( pressbutton );
		return;
	}
	
	if (document.formvalidator.isValid(form)) {
		<?php echo $this->editor->save( 'body' ); ?>
		submitform( pressbutton );
	  return true; 
	}
	else {
	  var msg = "<?php echo JText::_('COM_REDEVENT_EMAIL_ATTENDEES_VAILDATION_FAILED'); ?>";
	 
	  //Example on how to test specific fields
	   if($('subject').hasClass('invalid')){msg += '\n\n\t* <?php echo JText::_('COM_REDEVENT_EMAIL_ATTENDEES_SUBJECT_REQUIRED'); ?>';}
	 
	   alert(msg);
	 }
	 return false;

}
</script>

<h2><?php echo $this->event->title. '@'. $this->event->venue. ' ' . strftime($this->settings->formatdate, strtotime($this->event->dates)); ?></h2>
<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td>
								<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_TO' ).':'; ?>
						</td>
						<td>
							<?php foreach ($this->emails as $email): ?>
							<?php echo (isset($email['fullname']) ? $email['fullname']. ' ' : '').htmlspecialchars('<').$email['email'].htmlspecialchars('>').'<br/>'; ?>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="subject">
								<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_SUBJECT' ).':'; ?>
							</label>
						</td>
						<td>
							<input name="subject" id="subject" value="" class="required" size="50" maxlength="100" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="body">
								<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_BODY' ).':'; ?>
							</label>
						</td>
						<td>
							<?php echo $this->editor->display('body', '', '100%', '350', '75', '20'); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php foreach ($this->cids as $cid) :?>
	<input type="hidden" name="cid[]" value="<?php echo $cid; ?>"/>
	<?php endforeach; ?>
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="controller" value="attendees" />
	<input type="hidden" name="xref" value="<?php echo $this->xref; ?>" />
	<input type="hidden" name="view" value="attendees" />
	<input type="hidden" name="task" value="" />
</form>