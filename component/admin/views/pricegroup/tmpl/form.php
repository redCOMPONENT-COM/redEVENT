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

<?php
$imagepath = '/administrator/components/com_redevent/assets/images/';
?>

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
			<legend><?php echo JText::_( 'COM_REDEVENT_PRICEGROUPS_PRICEGROUP' ); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?></li>
				
				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>
				
				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>
				
				<li><?php echo $this->form->getLabel('tooltip'); ?>
				<?php echo $this->form->getInput('tooltip'); ?></li>
				
				<li><?php echo $this->form->getLabel('adminonly'); ?>
				<?php echo $this->form->getInput('adminonly'); ?></li>
				
				<li><?php echo $this->form->getLabel('image'); ?>
				<?php echo $this->form->getInput('image'); ?></li>
			</ul>
			<div class="clr"></div>
		</fieldset>
	</div>

	<input type="hidden" name="option" value="com_redevent" /> 
	<input type="hidden" name="controller" value="pricegroups" />
	<input type="hidden" name="cid[]" value="<?php echo $this->object->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>