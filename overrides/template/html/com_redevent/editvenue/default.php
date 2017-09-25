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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


JHTML::_('behavior.formvalidation');
JHtml::_('rjquery.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'editvenue.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . $this->form->getField('locdescription')->save() . "
			Joomla.submitform(task);
		}
	};
");

$document = &JFactory::getDocument();
$renderer   = $document->loadRenderer('modules');
$position_breadcrumbs   = 'breadcrumbs';
?>

<script type="text/javascript">
	jQuery(document).ready(function()
	{
		// Disable click function on btn-group
		jQuery(".btn-group").each(function(index){
			if (jQuery(this).hasClass('disabled'))
			{
				jQuery(this).find("label").off('click');
			}
		});
	});
</script>

<div class="container myevents venue-create control-create">
	<?php echo $renderer->render($position_breadcrumbs, $options, null); ?>
	<div class="aesir-container user-management-cotaniner">
		<div class="aesir-item-header reditem-content-header text-center">
			<h1 class="header-title"><?php echo $this->getTitle(); ?></h1>
		</div>
		<div class="redevent-edit-form<?= $this->params->get('pageclass_sfx') ?>">
			<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_redevent&view=editvenue&id=' . $this->item->id); ?>" method="post" name="adminForm" class="form-validate form-control-custom" id="adminForm">
				<div class="row">
					<div class="left-sidebar col-md-5 col-lg-3">
						<div class="white-block-border">
							<div class="tab-header"><?php echo JText::_('COM_REDITEM_ITEM_LABEL_FIELD_GROUPS'); ?></div>
							<ul class="nav menu navbar-nav js-aesir-tabs-container" id="userTab">
								<li class="active">
									<a href="#general" data-toggle="tab"><strong><?php echo JText::_('COM_REDEVENT_EVENT_INFO_TAB'); ?></strong></a>
								</li>
								<li>
									<a href="#address" data-toggle="tab">
										<strong><?php echo JText::_('COM_REDEVENT_ADDRESS'); ?></strong>
									</a>
								</li>
								<li>
									<a href="#attachments" data-toggle="tab">
										<strong><?php echo JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'); ?></strong>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="col-md-7 col-lg-9">
						<div class="white-block-border">
							<div class="tab-content">
								<div class="tab-pane active" id="general">
									<div class="tab-header">
										<?php echo JText::_('COM_REDEVENT_EVENT_INFO_TAB'); ?>
									</div>
									<fieldset>
										<?php foreach ($this->form->getFieldset('venue') as $field) : ?>
											<?php
												$hidden= "";
												if (strtolower(trim(strip_tags($field->label))) == 'image'){
													$hidden = 'hidden';
												}
											?>
											<div class="form-group <?php echo $hidden ?> a">
												<?php echo $field->label; ?>
												<?php echo $field->input; ?>
											</div>
										<?php endforeach; ?>
									</fieldset>
								</div>
								<div class="tab-pane" id="address">
									<div class="tab-header">
										<?php echo JText::_('COM_REDEVENT_ADDRESS'); ?>
									</div>
									<fieldset>
										<?php foreach ($this->form->getFieldset('address') as $field) : ?>
											<div class="form-group">
												<?php echo $field->label; ?>
												<?php echo $field->input; ?>
											</div>
										<?php endforeach; ?>
										<div class="form-group">
											<div class="control-label"></div>
											<div class="controls">
												<?php echo RedeventHelperOutput::pinpointicon($this->item); ?>
											</div>
										</div>
									</fieldset>
								</div>
								<div class="tab-pane" id="attachments">
									<div class="tab-header">
										<?php echo JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'); ?>
									</div>
									<fieldset>
										<?php echo RedeventLayoutHelper::render('attachments.edit', $this, null, array('component' => 'com_redevent', 'client' => 1)); ?>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="actions-btn-group">
					<button type="button" class="btn btn-default" onclick="Joomla.submitbutton('editvenue.cancel')"><?php echo JText::_('JCANCEL') ?></button>
					<button type="button" class="btn btn-success" onclick="Joomla.submitbutton('editvenue.save')"><?php echo JText::_('JSAVE') ?></button>
				</div>
				<?php echo $this->form->getInput('id'); ?>
				<input type="hidden" name="option" value="com_redevent" />
				<input type="hidden" name="task" value="" />
				<?php if ($this->return): ?>
					<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
				<?php endif; ?>
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
