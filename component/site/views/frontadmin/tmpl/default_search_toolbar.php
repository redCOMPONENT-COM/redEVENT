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
<script type="application/javascript">
	<?php JHtml::script('com_redevent/autocompleter.js', false, true); ?>
	window.addEvent('domready', function(){
		var url = '<?php echo JRoute::_('index.php?option=com_redevent&controller=frontadmin&task=personsuggestions&tmpl=component', false); ?>';
		var completer = new Autocompleter.Request.JSON(document.id('filter_person'), url, {'postVar': 'q', 'autoSubmit': true});

		completer.addEvent('onRequest', function(element, request, data){
			data['org'] = document.id('filter_organization').get('value');
		});

		completer.addEvent('onSelection', function(element, selected){
			document.id('filter_organization').fireEvent('change');
		});
	});
</script>

<div class="search-toolbar">
	<form name="org-form" id="org-form" method="post">
		<ul class="inline">
			<li>
				<?php echo JHtml::_('select.genericlist', $this->organizations_options, 'filter_organization', '', 'value', 'text', $this->state->get('filter_organization')); ?>
			</li>
			<li>
				<input name="filter_person" id="filter_person" type="text"
					class="input-medium" placeholder="<?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_PERSON'); ?>"
				/>
				<button type="button" id="search_person" class="btn"><?php echo JText::_('COM_REDEVENT_SEARCH');?></button>
				<button type="button" id="reset_person" class="btn"><?php echo JText::_('COM_REDEVENT_RESET');?></button>
			</li>
			<li><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SEARCH_IN'); ?></li>
			<li><label class="checkbox"><input name="filter_person_active" id="filter_person_active0" type="radio" value="1"
				<?php echo $this->state->get('filter_person_active') == 1 ? ' checked="checked"' : ''; ?>/> <?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ACTIVE_COURSES'); ?></label></li>
			<li><label class="checkbox"><input name="filter_person_active" id="filter_person_active1" type="radio" value="0"
				<?php echo $this->state->get('filter_person_active' == 0) ? ' checked="checked"' : ''; ?> /> <?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSES_HISTORY'); ?></label></li>
		</ul>

		<input type="hidden" name="bookings_order" value="<?php echo $this->bookings_order; ?>"/>
		<input type="hidden" name="bookings_order_dir" value="<?php echo $this->bookings_order_dir; ?>"/>
		<input type="hidden" name="bookings_limitstart" value="<?php echo $this->bookings_limitstart; ?>"/>

		<input type="hidden" name="members_order" value="<?php echo $this->members_order; ?>"/>
		<input type="hidden" name="members_order_dir" value="<?php echo $this->members_order_dir; ?>"/>
		<input type="hidden" name="members_limitstart" value="<?php echo $this->members_limitstart; ?>"/>
	</form>
</div>

<div class="clear"></div>
