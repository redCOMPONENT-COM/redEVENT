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

/**
 * Override:
 *  - removed COM_REDEVENT_FRONTEND_ADMIN_MEMBER_INFO h2 title
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');

$fieldsOrdering = array(
	'rm_firstname',
	'rm_lastname',
	'email',
);

if ($this->uid)
{
	$fieldsOrdering[] = 'password';
	$fieldsOrdering[] = 'password2';
}

$fieldsOrdering[] = 'rm_birthday';
$fieldsOrdering[] = 'rm_note';
$fieldsOrdering[] = 'rm_mobile';
$fieldsOrdering[] = 'rm_certificate_email';
$fieldsOrdering[] = 'rm_invoice_email';
$fieldsOrdering[] = 'rm_invoice_contact';
?>
<?php if ($this->modal): ?>
	<script type="text/javascript">
		window.addEvent('domready', function() {
			document.id('cancel-employee').addEvent('click', function(){
				window.parent.SqueezeBox.close();
			});
		});
	</script>
<?php endif; ?>

<div>

<jdoc:include type="message" />

<div id="editmember-info">

	<?php if ($this->uid): ?>
		<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_TITLE_MEMBER_INFO'); ?></h2>
	<?php else: ?>
		<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_TITLE_CREATE_MEMBER'); ?></h2>
	<?php endif; ?>

	<form class="form-horizontal form-validate" id="member-update" method="post" action="index.php?option=com_redeventb2b&task=frontadmin.update_user&tmpl=component" enctype="multipart/form-data">

		<div id="employee-submit">
		<?php if (!$this->modal): ?>
			<button type="button" class="update-employee btn"><?php echo $this->uid ? JText::_('COM_REDEVENT_UPDATE') : JText::_('COM_REDEVENT_CREATE'); ?></button>
			<button type="button" id="closeeditmember" class="btn"><?php echo JText::_('COM_REDEVENT_CANCEL'); ?></button>
		<?php else: ?>
			<button type="submit" class="update-employee btn"><?php echo $this->uid ? JText::_('COM_REDEVENT_UPDATE') : JText::_('COM_REDEVENT_CREATE'); ?></button>
			<button type="button" id="cancel-employee" class="btn"><?php echo JText::_('COM_REDEVENT_CANCEL'); ?></button>
		<?php endif; ?>
		</div>

		<!-- Tab panes -->
		<div>
			<?php foreach ($fieldsOrdering as $lookup): ?>
				<?php foreach ($this->form->getFieldset('general') as $field): ?>
					<?php if ($field->fieldname !== $lookup) continue; ?>
					<?php if (!$field->hidden) : ?>
						<div class="control-group">
							<?php echo $field->label; ?>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php else: ?>
						<?php echo $field->input; ?>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php foreach ($this->tabs as $t): ?>
						<?php foreach ($t->fields as $field): ?>
							<?php if ($field->fieldcode !== $lookup) continue; ?>
							<?php if (!$field->hidden) : ?>
							<div class="control-group field-<?php echo $field->id; ?>">
								<?php echo $field->getLabel(array('class' => 'control-label')); ?>
								<div class="controls">
									<?php echo $field->getInput(); ?>
								</div>
							</div>
							<?php else: ?>
								<?php echo $field->getInput(); ?>
							<?php endif; ?>
						<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>

		<input type="hidden" name="modal" value="<?php echo $this->modal; ?>" />
		<input type="hidden" name="orgId" value="<?php echo $this->orgId; ?>" />
		<?php echo $this->form->getField('joomla_user_id')->input; ?>
		<?php echo $this->form->getField('id')->input; ?>
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
			$this->allow_edit_sessions = true;
			$this->show_action_column = true;
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
			$this->allow_edit_sessions = false;
			$this->show_action_column = false;
		?>
		<?php echo $this->loadTemplate('sessions'); ?>
	</form>
</div>
<?php endif; ?>
</div>
