<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.formvalidation');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'editevent.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . ($this->params->get('edit_description', 0) ? $this->form->getField('datdescription')->save() : '') . "
			" . ($this->params->get('edit_summary', 1) ? $this->form->getField('summary')->save() : '') . "
			Joomla.submitform(task);
		}
	};
");
$imageField = $this->customfields[6]; 

$short_intro=$this->customfields[1];
$date_time=$this->customfields[2];
$sted=$this->customfields[3];
$full_description=$this->customfields[4];
$note=$this->customfields[5];
$ved=$this->customfields[0];
$time = $this->customfields[7]; 

JFactory::getDocument()->addScriptDeclaration(<<<JS
        jQuery(document).ready(function($) {
                document.formvalidator.setHandler('futuredate', function(value) {
                        if (!value) {
                                return true;
                        }

                        var today = new Date();
                        today = new Date(today.toDateString());
                        var val = new Date(value);

                        return val >= today;
                });
        });
JS
);
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

<?php if ($this->params->def('show_page_title', 1)): ?>
	<h1 class='componentheading'>
		<?php echo $this->getTitle(); ?>
	</h1>
<?php endif; ?>

<div class="redevent-edit-form style-1">
	<form enctype="multipart/form-data"
	      action="<?php echo JRoute::_('index.php?option=com_redevent&view=editevent&e_id=' . $this->item->id); ?>"
	      method="post" name="adminForm" class="form-validate"
	      id="adminForm">

	<div class="short-description"><?php echo JText::_('JTEXT_CREATE_EVENT');?></div>	

		

		
				<div class="detail-edit-event">
					
						<div class="form edititem">
							
								
									<div class="control-group title-event form-group">
										<div class="control-label title-event">
											<?php echo $this->form->getLabel('title'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('title'); ?><span class="required">*</span>
										</div>
									</div>

									<div class="control-group customfield-short-intro form-group">
									<div class="control-label">
										<?php echo $short_intro->getLabel(); ?>
									</div>
									<div class="controls">
										<?php echo $short_intro->render(); ?><span class="required">*</span>
									</div>
								</div>
								<div class="control-group customfield-images form-group">
									<div class="control-label">
										<?php echo $imageField->getLabel(); ?>
									</div>
									<div class="controls">
										<?php echo $imageField->render(); ?>
									</div>
								</div>
								<div class="extra-wrapper">
									<div class="groupWrapper">
											<div class="control-group customfield-datetime form-group">
												<div class="control-label">
													<?php echo $date_time->getLabel(); ?>
												</div>
												<div class="controls">
													<?php echo $date_time->render(array('class' => 'validate-futuredate')); ?><span class="required">*</span>
												</div>

											</div>
											<!-- <div class="control-group customfield-time form-group">
												<div class="control-label">
													<?php //echo $date_time->getLabel(); ?>
												</div>
												<div class="controls">
													<?php //echo $time->render(); ?><span class="required">*</span>
												</div>
											
											</div> -->
											<div style="" class="customfield-time input-group clockpicker form-group" data-autoclose="true">
												<div class="control-label">
													<?php echo $date_time->getLabel(); ?>
												</div>
												<div class="controls">

												<?php echo $time->render(); ?><!-- 
												<span class="input-group-addon">
																							        <span class="glyphicon glyphicon-time"></span> -->
											    </span>
												<span class="required">*</span>
												</div>
												
											
											</div>
									</div>
									<?php if ($this->params->get('edit_categories', 0)): ?>
									<div class="control-group categories groupWrapper form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('categories'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('categories'); ?><span class="required">*</span>
										</div>
									</div>
									<?php endif; ?>
									<div class="control-group customfield-sted groupWrapper form-group">
										<div class="control-label">
											<?php echo $sted->getLabel(); ?>
										</div>
										<div class="controls">
											<?php echo $sted->render(); ?><span class="required">*</span>
										</div>

									</div>
									<div class="control-group customfield-ved groupWrapper form-group">
										<div class="control-label">
											<?php echo $ved->getLabel(); ?>
										</div>
										<div class="controls">
											<?php echo $ved->render(); ?><span class="required">*</span>
										</div>

									</div>

									

								</div>
									
							
							<?php if ($this->params->get('edit_description', 0)) :?>
									<div class="control-group  customfield-full-description form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('datdescription'); ?>
										</div>
										<div class="controls table-responsive">
											<?php echo $this->form->getInput('datdescription'); ?><span class="required">*</span>
										</div>
									</div>
								<?php endif; ?>
							<div class="control-group customfield-note form-group">
										<div class="control-label">
											<?php echo $note->getLabel(); ?>
										</div>
										<div class="controls">
											<?php echo $note->render(); ?>
										</div>

							</div>	
								
								
								
								
								

								

								<?php if ($this->params->get('edit_summary', 1)) :?>
									<div class="control-group form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('summary'); ?>
										</div>
										<div class="controls ">
											<?php echo $this->form->getInput('summary'); ?>
										</div>
									</div>
								<?php endif; ?>

								
							
						
						
					</div>
				</div>
			

		
		

		<?php echo $this->form->getInput('id'); ?>
		<input type="hidden" name="option" value="com_redevent" />
		<input type="hidden" name="task" value="" />
		<?php if ($this->return): ?>
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<?php endif; ?>
		<?php echo JHtml::_('form.token'); ?>
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary btn-save-event" onclick="Joomla.submitbutton('editevent.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn btn-cancel-event" onclick="Joomla.submitbutton('editevent.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
	</form>
</div>
