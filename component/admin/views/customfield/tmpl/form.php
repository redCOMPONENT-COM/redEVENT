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

defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>
<?php JHTML::_('behavior.formvalidation'); ?>


<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
			
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-60">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_REDEVENT_Custom_field' ); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?></li>
				
				<li><?php echo $this->form->getLabel('tag'); ?>
				<?php echo $this->form->getInput('tag'); ?></li>
				
				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>
				
				<li><?php echo $this->form->getLabel('object_key'); ?>
				<?php echo $this->form->getInput('object_key'); ?></li>
				
				<li><?php echo $this->form->getLabel('type'); ?>
				<?php echo $this->form->getInput('type'); ?></li>
				
				<li><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>
				
				<li><?php echo $this->form->getLabel('tips'); ?>
				<?php echo $this->form->getInput('tips'); ?></li>
				
				<li><?php echo $this->form->getLabel('searchable'); ?>
				<?php echo $this->form->getInput('searchable'); ?></li>
				
				<li><?php echo $this->form->getLabel('in_lists'); ?>
				<?php echo $this->form->getInput('in_lists'); ?></li>
				
				<li><?php echo $this->form->getLabel('frontend_edit'); ?>
				<?php echo $this->form->getInput('frontend_edit'); ?></li>
				
				<li><?php echo $this->form->getLabel('required'); ?>
				<?php echo $this->form->getInput('required'); ?></li>
				
				<li><?php echo $this->form->getLabel('min'); ?>
				<?php echo $this->form->getInput('min'); ?></li>
				
				<li><?php echo $this->form->getLabel('max'); ?>
				<?php echo $this->form->getInput('max'); ?></li>
				
				<li><?php echo $this->form->getLabel('options'); ?>
				<?php echo $this->form->getInput('options'); ?></li>
				
				<li><?php echo $this->form->getLabel('default_value'); ?>
				<?php echo $this->form->getInput('default_value'); ?></li>
			</ul>
			<div class="clr"></div>
		</fieldset>
	</div>

	<input type="hidden" name="option" value="com_redevent" /> 
	<input type="hidden" name="controller" value="customfield" />
	<input type="hidden" name="cid[]" value="<?php echo $this->object->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>
