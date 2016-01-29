<?php
/**
 * @version 1.0 $Id: default.php 160 2009-05-29 16:16:39Z julien $
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

defined('_JEXEC') or die('Restricted access');

$options = array(
		'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
		'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
		'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
		'useCookie' => false, // this must not be a string. Don't use quotes.
);
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		<?php echo $this->form->getField('description')->save(); ?>
		Joomla.submitform(task, document.id('adminForm'));
	} else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}
</script>


<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="width-60 fltlft">
		<fieldset class="panelform">
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>
			</ul>
			<div class="clr"></div>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
		</fieldset>
	</div>

	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start', 'categories-sliders-'.$this->row->id, $options); ?>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_CATEGORIES'), 'categories'); ?>
			<fieldset class="panelform">
				<?php echo $this->form->getLabel('parent_id'); ?>
				<?php echo $this->form->getInput('parent_id'); ?>
			</fieldset>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_ACCESS'), 'access'); ?>
			<fieldset class="panelform">
				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('access'); ?>
					<?php echo $this->form->getInput('access'); ?></li>
				</ul>
			</fieldset>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_IMAGE'), 'vcimage'); ?>
			<fieldset class="panelform">
					<?php echo $this->form->getLabel('image'); ?>
					<?php echo $this->form->getInput('image'); ?>
			</fieldset>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_METADATA_INFORMATION'), 'metadata'); ?>
			<fieldset class="panelform">
				<table>
				<tr>
					<td>
						<label for="metadesc">
							<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION' ); ?>:
						</label>
						<br />
						<textarea class="inputbox" cols="40" rows="5" name="meta_description" id="metadesc" style="width:300px;"><?php echo str_replace('&','&amp;',$this->row->meta_description); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<label for="metakey">
							<?php echo JText::_('COM_REDEVENT_META_KEYWORDS' ); ?>:
						</label>
						<br />
						<textarea class="inputbox" cols="40" rows="5" name="meta_keywords" id="metakey" style="width:300px;"><?php echo str_replace('&','&amp;',$this->row->meta_keywords); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<input type="button" class="button" value="<?php echo JText::_('COM_REDEVENT_ADD_CATNAME' ); ?>" onclick="f=document.adminForm;f.metakey.value=f.name.value;" />
					</td>
				</tr>
				</table>
			</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>
	</div>

   <!-- begin ACL definition-->
   <div class="clr"></div>
   <?php if (1 or $this->canDo->get('core.admin')): ?>
	   <div class="width-100 fltlft">
		   <?php echo JHtml::_('sliders.start', 'permissions-sliders-'.$this->row->id, array('useCookie'=>1)); ?>
		   <?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_VENUECATEGORIE_FIELDSET_RULES'), 'access-rules'); ?>
		   <fieldset class="panelform">
			   <?php echo $this->form->getLabel('rules'); ?>
			   <?php echo $this->form->getInput('rules'); ?>
		   </fieldset>
		   <?php echo JHtml::_('sliders.end'); ?>
	   </div>
   <?php endif; ?>
   <!-- end ACL definition-->

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="controller" value="venuescategories" />
<input type="hidden" name="view" value="venuescategory" />
<input type="hidden" name="task" value="" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');