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
<?php if (!$this->modal): ?>
<div id="closeeditmember"><?php echo "< " . JText::_('COM_REDEVENT_BACK'); ?></div>
<?php endif; ?>

<div id="editmember-menu">
	<div class="editmember-breadcrumbs">
		<ul class="breadcrumb">
			<li><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_YOU_ARE_HERE'); ?> <span class="divider">></span></li>
			<?php if ($this->uid): ?>
				<li><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_EDIT_MEMBER'); ?></li>
			<?php else: ?>
				<li><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_ADD_MEMBER'); ?></li>
			<?php endif; ?>
		</ul>
	</div>
	<?php if ($this->uid): ?>
	<div class="editmember-addnew">
		<button type="button" class="add-employee btn"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ADD_EMPLOYEE'); ?></button>
	</div>
	<?php endif; ?>
</div>

<div id="editmember-info">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_INFO'); ?></h2>

	<form class="form-horizontal" id="member-update">
		<?php foreach ($this->tabs as $t): ?>
			<fieldset>
				<legend><?php echo $t->tab_name; ?></legend>
				<?php foreach ($t->fields as $field): ?>
					<?php if (!$field->hidden) : ?>
					<div class="control-group">
						<?php echo $field->getLabel(array('class' => 'control-label')); ?>
						<div class="controls">
							<?php echo $field->getInput(); ?>
						</div>
					</div>
					<?php else: ?>
						<?php echo $field->getInput(); ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</fieldset>
		<?php endforeach; ?>
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
