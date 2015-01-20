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

<div class="redevent-edit-form">
	<form enctype="multipart/form-data"
	      action="<?php echo JRoute::_('index.php?option=com_redevent&view=editvenue&id=' . $this->item->id); ?>"
	      method="post" name="adminForm" class="form-validate"
	      id="adminForm">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('editvenue.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('editvenue.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<ul class="nav nav-tabs" id="userTab">
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
		<div class="tab-content">
			<div class="tab-pane active" id="general">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php foreach ($this->form->getFieldset('venue') as $field) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="address">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php foreach ($this->form->getFieldset('address') as $field) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
							<div class="control-group">
								<div class="control-label"></div>
								<div class="controls">
									<?php echo RedeventHelperOutput::pinpointicon($this->item); ?>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="attachments">
				<div class="row-fluid">
					<div class="span12">
						<?php echo RLayoutHelper::render('attachments.edit', $this, null, array('component' => 'com_redevent', 'client' => 1)); ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->form->getInput('id'); ?>
		<input type="hidden" name="option" value="com_redevent" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
