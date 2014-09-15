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

<div id="employees-header">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ORG_MEMBERS_TITLE')?></h2>
	<div id="search-member">
		<input name="filter_person" id="filter_person" type="text"
			class="input-medium form-control" placeholder="<?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_PERSON'); ?>"
			/>
	</div>
</div>

<div id="employees-result"></div>

<script type="application/javascript">
	<?php JHtml::script('com_redevent/autocompleter.js', false, true); ?>
	(function(){
		var url = '<?php echo JRoute::_('index.php?option=com_redevent&controller=frontadmin&task=personsuggestions&tmpl=component', false); ?>';
		var completer = new Autocompleter.Request.JSON(document.id('filter_person'), url, {'postVar': 'q', 'autoSubmit': true});

		completer.addEvent('onRequest', function(element, request, data){
			data['org'] = document.id('filter_organization').get('value');
		});

		completer.addEvent('onSelection', function(element, selected){
			document.id('filter_organization').fireEvent('change');
		});
	})();
</script>
