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
<div id="closeeditmember"><?php echo "< " . JText::_('COM_REDEVENT_BACK'); ?></div>

<div class="editmember-menu">
	<div class="editmember-breadcrumbs"></div>
	<div class="editmember-addnew">
		<button type="button" id="add-employee" class="btn"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ADD_EMPLOYEE'); ?></button>
	</div>
</div>

<div class="editmember-info">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_INFO'); ?></h2>
	<form class="form-horizontal">
		<div class="control-group">
			<label class="control-label" for="name"><?php echo JText::_('COM_REDEVENT_NAME'); ?></label>
			<div class="controls">
				<input type="text" placeholder="<?php echo JText::_('COM_REDEVENT_NAME'); ?>" value="<?php echo $this->member->name; ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="username"><?php echo JText::_('COM_REDEVENT_USERNAME'); ?></label>
			<div class="controls">
				<input type="text" placeholder="<?php echo JText::_('COM_REDEVENT_USERNAME'); ?>" value="<?php echo $this->member->username; ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="email"><?php echo JText::_('COM_REDEVENT_email'); ?></label>
			<div class="controls">
				<input type="text" placeholder="<?php echo JText::_('COM_REDEVENT_email'); ?>" value="<?php echo $this->member->email; ?>">
			</div>
		</div>
		<input name="id" type="hidden" value="<?php echo $this->member->id; ?>"/>
		<button type="button" id="update-employee" class="btn"><?php echo JText::_('COM_REDEVENT_UPDATE'); ?></button>
	</form>
</div>

<div class="editmember-booked">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_BOOKED'); ?></h2>

</div>

<div class="editmember-previous">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_PREVIOUS'); ?></h2>

</div>