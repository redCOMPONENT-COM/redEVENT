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
?>

<script language="javascript" type="text/javascript">
	function submitbutton(task)
	{
		var form = document.adminForm;

		if (task == 'cancel') {
			submitform( task );
			return;
		} else {
			submitform( task );
		}
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<fieldset class="adminform"><legend><?php echo JText::_('COM_REDEVENT_Booking' ); ?></legend>

<table class="editevent">
<?php if ($this->row->id): ?>
	<tr>
		<td width="100" align="right" class="key"><label for="name"> <?php echo JText::_('COM_REDEVENT_UNIQUE_ID' ); ?>:
		</label></td>
		<td><?php echo $this->row->course_code .'-'. $this->row->xref .'-'. $this->row->id; ?></td>
	</tr>
<?php endif; ?>
	<tr>
		<td width="100" align="right" class="key"><label for="name"> <?php echo JText::_('COM_REDEVENT_USER' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['user']; ?>
		</td>
	</tr>
<?php if ($this->row->id): ?>
	<tr>
		<td width="100" align="right" class="key"><label for="name"> <?php echo JText::_('COM_REDEVENT_REGDATE' ); ?>:
		</label></td>
		<td>
			<?php echo $this->row->uregdate; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key"><label for="name"> <?php echo JText::_('COM_REDEVENT_IP_ADDRESS' ); ?>:
		</label></td>
		<td>
			<?php echo $this->row->uip; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key"><label for="name"> <?php echo JText::_('COM_REDEVENT_ACTIVATED' ); ?>:
		</label></td>
		<td>
			<?php echo ($this->row->confirmed ? $this->row->confirmdate : ''); ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key"><label for="name"> <?php echo JText::_('COM_REDEVENT_WAITINGLIST' ); ?>:
		</label></td>
		<td>
			<?php echo ($this->row->waitinglist ? JText::_('COM_REDEVENT_yes') : JText::_('COM_REDEVENT_no')); ?>
		</td>
	</tr>
<?php endif; ?>
</table>

</fieldset>

<fieldset class="adminform editevent"><legend><?php echo JText::_('COM_REDEVENT_Answers' ); ?></legend>
<?php $options = array('extrafields' => array(array('label' => JText::_('COM_REDEVENT_REGISTRATION_PRICE'), 'field' => $this->lists['pricegroup_id']))); ?>
<?php
	$rfcore = new RedformCore();
	echo $rfcore->getFormFields($this->row->form_id, ($this->row->sid ? array($this->row->sid) : null), 1, $options);
?>

</fieldset>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="attendees" />
<input type="hidden" name="view" value="attendee" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="xref" value="<?php echo $this->row->xref; ?>" />
<input type="hidden" name="sid" value="<?php echo $this->row->sid; ?>" />
<input type="hidden" name="submit_key" value="<?php echo $this->row->submit_key; ?>" />
<input type="hidden" name="task" value="" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>
