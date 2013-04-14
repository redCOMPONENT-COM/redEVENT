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
<form name="org-form" id="org-form" method="post" action="index.php?option=com_redevent&controller=frontadmin&task=getbookings">
<ul class="inline">
	<li>
		<?php echo JHtml::_('select.genericlist', $this->organizations_options, 'filter_organization', '', 'value', 'text', $this->state->get('filter_organization')); ?>
	</li>
	<li>
		<input name="filter_person" id="filter_person" type="text"
			class="input-medium" placeholder="<?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_PERSON'); ?>"
		/> <button type="button" id="reset_person" class="btn"><?php echo JText::_('COM_REDEVENT_RESET');?></button>
	</li>
	<li><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SEARCH_IN'); ?></li>
	<li><label class="checkbox"><input name="filter_person_active" id="filter_person_active" type="checkbox" value="1"
		<?php echo $this->state->get('filter_person_active') ? ' checked="checked"' : ''; ?>/> <?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ACTIVE_COURSES'); ?></label></li>
	<li><label class="checkbox"><input name="filter_person_archive" id="filter_person_archive" type="checkbox" value="1"
		<?php echo $this->state->get('filter_person_archive') ? ' checked="checked"' : ''; ?> /> <?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSES_HISTORY'); ?></label></li>
</ul>
	<input type="hidden" name="bookings_order" value="<?php echo $this->bookings_order; ?>"/>
	<input type="hidden" name="bookings_order_dir" value="<?php echo $this->bookings_order_dir; ?>"/>
	<input type="hidden" name="members_order" value="<?php echo $this->members_order; ?>"/>
	<input type="hidden" name="members_order_dir" value="<?php echo $this->members_order_dir; ?>"/>
<div class="clear"></div>
</form>