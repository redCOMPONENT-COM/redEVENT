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

JHtml::_('script', 'media/jui/js/jquery.autocomplete.min.js', false, false, false, false, true);
?>

<div id="members-result-panel" class="panel-collapse collapse in">
	<div id="search-member">
		<input name="filter_person" id="filter_person" type="text"
		       class="input-medium form-control" placeholder="<?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_PERSON'); ?>"
			/><button class="btn" id="search_person" type="button"><i class="icon-search"></i></button>
		<button class="btn hide" id="reset_search_person" type="button"><i class="icon-remove"></i></button>
	</div>
	<button type="button" id="add-employee" class="btn"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ADD_EMPLOYEE'); ?></button>

	<div id="members-result"></div>
</div>

<script type="application/javascript">
	(function($){
		$('#filter_person').autocomplete({
			serviceUrl: 'index.php?option=com_redeventb2b&task=frontadmin.personsuggestions&tmpl=component',
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
				redb2b.filterPerson();
			}
		});

	})(jQuery);
</script>
