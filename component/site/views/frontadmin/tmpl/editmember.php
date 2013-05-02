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

<div id="editmember-menu">
	<div class="editmember-breadcrumbs"></div>
	<?php if ($this->uid): ?>
	<div class="editmember-addnew">
		<button type="button" class="add-employee btn"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ADD_EMPLOYEE'); ?></button>
	</div>
	<?php endif; ?>
</div>

<div id="editmember-info">
    <h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_INFO'); ?></h2>
    <form class="form-horizontal">
        <div class="control-group">
            <label class="control-label" for="member_name"><?php echo JText::_('COM_REDEVENT_NAME'); ?></label>
            <div class="controls">
                <input id="member_name" type="text" placeholder="<?php echo JText::_('COM_REDEVENT_NAME'); ?>" value="<?php echo $this->member->name; ?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="member_username"><?php echo JText::_('COM_REDEVENT_USERNAME'); ?></label>
            <div class="controls">
                <input id="member_username" type="text" placeholder="<?php echo JText::_('COM_REDEVENT_USERNAME'); ?>" value="<?php echo $this->member->username; ?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="member_password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
            <div class="controls">
                <input id="member_password" type="password" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" value="">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="member_password2"><?php echo JText::_('COM_REDEVENT_PASSWORD_REPEAT'); ?></label>
            <div class="controls">
                <input id="member_password2" type="text" placeholder="<?php echo JText::_('COM_REDEVENT_PASSWORD_REPEAT'); ?>" value="">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="member_email"><?php echo JText::_('COM_REDEVENT_EMAIL'); ?></label>
            <div class="controls">
                <input id="member_email" type="text" placeholder="<?php echo JText::_('COM_REDEVENT_EMAIL'); ?>" value="<?php echo $this->member->email; ?>">
            </div>
        </div>
        <input id="member_id" name="id" type="hidden" value="<?php echo $this->member->id; ?>"/>
        <button type="button" class="update-employee btn"><?php echo $this->uid ? JText::_('COM_REDEVENT_UPDATE') : JText::_('COM_REDEVENT_CREATE'); ?></button>
    </form>
</div>

<?php if ($this->uid): ?>
<div id="editmember-booked">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_BOOKED'); ?></h2>
	<form class="ajaxlist">
		<?php
			$this->sessions = $this->booked;
			$this->order_input = "booked_order";
			$this->order_dir_input = "booked_order_dir";
			$this->order = $this->booked_order;
			$this->order_dir = $this->booked_order_dir;
			$this->task = "getmemberbooked";
			$this->pagination = $this->booked_pagination;
			$this->limitstart_name = "booked_limitstart";
			$this->limitstart = $this->booked_limitstart;
		?>
		<?php echo $this->loadTemplate('sessions'); ?>
	</form>
</div>

<div id="editmember-previous">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_PREVIOUS'); ?></h2>
	<form class="ajaxlist">
		<?php
			$this->sessions = $this->previous;
			$this->order_input = "previous_order";
			$this->order_dir_input = "previous_order_dir";
			$this->order = $this->previous_order;
			$this->order_dir = $this->previous_order_dir;
			$this->task = "getmemberprevious";
			$this->pagination = $this->previous_pagination;
			$this->limitstart_name = "previous_limitstart";
			$this->limitstart = $this->previous_limitstart;
		?>
		<?php echo $this->loadTemplate('sessions'); ?>
	</form>
</div>
<?php endif; ?>