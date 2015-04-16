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
JHTML::_('behavior.modal', 'a.xrefmodal');
JHTML::_('behavior.formvalidation');

$selectxref_link = JRoute::_('index.php?option=com_redevent&controller=attendees&task=selectxref&tmpl=component&form_id=' . $this->form_id . '&function=selectXref');
?>
<script language="javascript" type="text/javascript">

	function selectXref(xref, title, event)
	{
		$('dest').value = xref;
		$('dest_name').value = title;
		SqueezeBox.close();
	}

	function submitbutton(pressbutton)
	{
		var form = document.getElementById('adminForm');
		var validator = document.formvalidator;

		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

		if ( validator.validate(form.dest) === false ) {
   			alert("<?php echo JText::_( 'COM_REDEVENT_ATTENDEES_MOVE_SELECT_DESTINATION', true ); ?>");
   			return false;
   		} else {
			submitform( pressbutton );
   		}

	}

</script>

<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
	<table  class="admintable">
		<tr>
			<td class="key" width="150">
				<label for="from">
					<?php echo JText::_( 'COM_REDEVENT_ATTENDEES_MOVE_SELECT_FROM' ).':'; ?>
				</label>
			</td>
			<td>
				<?php echo $this->session->title; ?>
			</td>
		<tr>
			<td class="key" width="150">
				<label for="dest">
					<?php echo JText::_( 'COM_REDEVENT_ATTENDEES_MOVE_SELECT_DESTINATION' ).':'; ?>
				</label>
			</td>
			<td>
				<input type="text" name="dest_name" id="dest_name" readonly="readonly" value="<?php echo $this->session->title; ?>" />
				<input type="hidden" name="dest" id="dest" value="<?php echo $this->session->xref; ?>" />
				<a class="xrefmodal" title="<?php echo JText::_('COM_REDEVENT_ATTENDEES_MOVE_SELECT_DESTINATION'); ?>" href="<?php echo $selectxref_link; ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}">
					<span><?php echo JText::_('COM_REDEVENT_ATTENDEES_MOVE_SELECT_DESTINATION')?></span>
        </a>
			</td>
		</tr>
	</table>

	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="controller" value="attendees" />
	<input type="hidden" name="view" value="attendees" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="xref" value="<?php echo $this->session->xref; ?>" />
	<?php foreach ($this->cid as $attendee_id): ?>
	<input type="hidden" name="cid[]" value="<?php echo $attendee_id; ?>" />
	<?php endforeach; ?>

</form>
