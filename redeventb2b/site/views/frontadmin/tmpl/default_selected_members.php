<?php
/**
 * @package    Redevent
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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
<div id="selected_members">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECTED_USERS'); ?></h2>

	<div id="booking_member_search">
		<input name="filter_member" id="booking_filter_member" type="text"
		       class="input-medium form-control" placeholder="<?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_BOOKING_ADD_MEMBER'); ?>"
		/>
	</div>

	<table id="newbooking-members-tbl" class="table">
		<thead>
		<tr>
			<th><?= JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_NAME'); ?></th>
			<th><?= JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_EMAIL'); ?></th>
			<th><?= JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_PO_NUMBER'); ?></th>
			<th><?= JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_COMMENTS'); ?></th>
			<th><?= JText::_('COM_REDEVENT_FRONTEND_ADMIN_REMOVE'); ?></th>
		</tr>
		</thead>
		<tbody>
		</tbody>
	</table>

	<div id="selected-sessions" class="panel panel-default">
		<?php echo $this->loadTemplate('selected_sessions'); ?>
	</div>

	<button type="button" id="book-course" disabled="disabled" class="btn"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_BUTTON_BOOK'); ?></button>
</div>

<script type="application/javascript">
	(function($){
		$('#booking_filter_member').autocomplete({
			serviceUrl: 'index.php?option=com_redeventb2b&task=frontadmin.personsuggestions&format=json',
			paramName: 'q',
			minChars: 1,
			maxHeight: 400,
			width: 300,
			zIndex: 9999,
			deferRequestBy: 500,
			onSearchStart: function(query) {
				query.org = $('#filter_organization').val();
			},
			onSelect: function(suggestion) {
				redb2b.addSelectedMember(suggestion);
			},
			transformResult: function(response) {
				var json = JSON.parse(response);
				return {
					suggestions: $.map(json.data.suggestions, function(dataItem) {
						return { value: dataItem.name, data: dataItem };
					})
				};
			}
		});
	})(jQuery);
</script>
