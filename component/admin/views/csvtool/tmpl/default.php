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

<form action="index.php?option=com_redevent&amp;view=csvtool" method="post" name="adminForm" id="export-form">

<table class="adminlist csvtool" cellspacing="1">
	<tbody>
	<tr id="export-form-row">
		<td class="label" width="150px"><?php echo JText::_('COM_REDEVENT_TOOLS_CSV_SELECT_FORM'); ?></td>
		<td><?php echo $this->lists['form_filter']; ?></td>
	</tr>
	<tr id="export-category-row">
		<td class="label" width="150px"><?php echo JText::_('COM_REDEVENT_TOOLS_CSV_SELECT_CATEGORY'); ?></td>
		<td><?php echo $this->lists['category_filter']; ?></td>
	</tr>
	<tr id="export-venue-row">
		<td class="label" width="150px"><?php echo JText::_('COM_REDEVENT_TOOLS_CSV_SELECT_VENUE'); ?></td>
		<td><?php echo $this->lists['venue_filter']; ?></td>
	</tr>
	<tr id="export-event-row">
		<td class="label" width="150px"><?php echo JText::_('COM_REDEVENT_TOOLS_CSV_SELECT_EVENTS'); ?></td>
		<td><span id="events-select">blalbalbal alal</span></td>
	</tr>
	<tr id="export-button-row">
		<td colspan="2"><button id="csv-export-button" type="button"><?php echo JText::_('COM_REDEVENT_TOOLS_CSV_BUTTON_EXPORT_LABEL'); ?></button></td>
	</tr>
	</tbody>

</table>

<p class="copyright">
	<?php echo ELAdmin::footer( ); ?>
</p>

<input type="hidden" name="task" id="exptask" value="" />
<input type="hidden" name="controller" value="csvtool" />
</form>