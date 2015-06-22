<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');


JHTML::_('behavior.formvalidation');

RHelperAsset::load('xref_prices.js');
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
	      action="<?php echo JRoute::_('index.php?option=com_redevent&view=editsession&s_id=' . $this->item->id); ?>"
	      method="post" name="adminForm" class="form-validate"
	      id="adminForm">
		<ul class="nav nav-tabs" id="userTab">
			<li class="active">
				<a href="#eventmain" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_MAIN'); ?></strong>
				</a>
			</li>

			<?php if ($this->params->get('edit_registration', 0)) :?>
			<li>
				<a href="#registration" data-toggle="tab">
					<strong><?php echo JText::_('COM_REDEVENT_SESSION_TAB_REGISTRATION'); ?></strong>
				</a>
			</li>
			<?php endif; ?>
		</ul>

		<div class="tab-content">
			<div class="tab-pane active" id="eventmain">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal"><div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('eventid'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('eventid'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('venueid'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('venueid'); ?>
								</div>
							</div>

							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('session_language'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('session_language'); ?>
								</div>
							</div>

							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('dates'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('dates'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('times'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('times'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('enddates'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('enddates'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('endtimes'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('endtimes'); ?>
								</div>
							</div>

							<?php if ($this->canpublish): ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('published'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('published'); ?>
									</div>
								</div>
							<?php endif; ?>
						</fieldset>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="registration">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('registrationend'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('registrationend'); ?>
								</div>
							</div>

							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('maxattendees'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('maxattendees'); ?>
								</div>
							</div>

							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('maxwaitinglist'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('maxwaitinglist'); ?>
								</div>
							</div>

							<table class="adminform" id="re-prices">
								<?php if ($this->prices): ?>
									<?php foreach ((array) $this->prices as $k => $r): ?>
										<tr>
											<td>
												<?php echo JHTML::_('select.genericlist', $this->pricegroupsoptions, 'jform[pricegroup][]', '', 'value', 'text', $r->pricegroup_id); ?>
											</td>
											<td>
												<input type="text" name="jform[price][]" class="price-val" value="<?php echo $r->price; ?>"/>
												<?php echo JHTML::_('select.genericlist', $this->currencyoptions, 'jform[currency][]', '', 'value', 'text', $r->currency); ?>
											</td>
											<td>
												<button type="button" class="btn price-button remove-price"><?php echo Jtext::_('COM_REDEVENT_REMOVE'); ?></button>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
								<tr id="trnewprice">
									<td>
										<?php echo JHTML::_('select.genericlist', $this->pricegroupsoptions, 'jform[pricegroup][]', array('id' => 'newprice', 'class' => 'price-group'), 'value', 'text'); ?>
									</td>
									<td>
										<input type="text" name="jform[price][]" class="price-val" value="0.00" size="10" />
										<?php echo JHTML::_('select.genericlist', $this->currencyoptions, 'jform[currency][]', array('class' => 'price-currency'), 'value', 'text'); ?>
									</td>
									<td>
										<button type="button" class="btn price-button" id="add-price"><?php echo JText::_('COM_REDEVENT_add'); ?></button>
									</td>
								</tr>
							</table>
						</fieldset>
				</div>
			</div>
		</div>

		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('editsession.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('editsession.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
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
