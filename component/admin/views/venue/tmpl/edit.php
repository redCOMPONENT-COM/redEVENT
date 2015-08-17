<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('rbootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('rjquery.chosen', 'select');

$fieldSets = $this->form->getFieldsets('params');
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

<form action="index.php?option=com_redevent&task=venue.edit&id=<?php echo $this->item->id ?>"
      method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate form-horizontal">

	<ul class="nav nav-tabs" id="venueTab">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_EVENT_INFO_TAB'); ?></strong>
			</a>
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

		<?php foreach ($fieldSets as $name => $fieldSet) : ?>
		<li>
			<?php $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_REDEVENT_VENUE_'.$name.'_FIELDSET_LABEL'; ?>
			<a href="#<?php echo $name; ?>" data-toggle="tab">
				<strong><?php echo JText::_($label); ?></strong>
			</a>
		</li>
		<?php endforeach; ?>

		<li>
			<a href="#rules" data-toggle="tab">
				<strong><?php echo JText::_('COM_REDEVENT_VENUE_FIELDSET_RULES'); ?></strong>
			</a>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<div class="row-fluid">
				<div class="span9">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('venue'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('venue'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('venue_code'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('venue_code'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('language'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('language'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('published'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('published'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('access'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('locimage'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('locimage'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('company'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('company'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('categories'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('categories'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('locdescription'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('locdescription'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('description'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('description'); ?>
						</div>
					</div>
				</div>
				<div class="span3">
					<?php echo RedeventLayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="address">
			<div class="row-fluid">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('street'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('street'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('plz'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('plz'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('city'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('city'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('state'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('country'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('country'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('url'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('url'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('email'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('email'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('map'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('map'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('latitude'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('latitude'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('longitude'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('longitude'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"></div>
					<div class="controls">
						<?php echo RedeventHelperOutput::pinpointicon($this->item); ?>
					</div>
				</div>

			</div>
		</div>

		<div class="tab-pane" id="attachments">
			<div class="row-fluid">
				<?php echo RedeventLayoutHelper::render('attachments.edit', $this, null, array('component' => 'com_redevent')); ?>
			</div>
		</div>

		<?php foreach ($fieldSets as $name => $fieldSet): ?>
		<div class="tab-pane" id="<?php echo $name; ?>">
			<div class="row-fluid">
			<?php
			if (isset($fieldSet->description) && trim($fieldSet->description)) :
				echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
			endif;
			?>
			<?php foreach ($this->form->getFieldset($name) as $field) : ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
		<?php endforeach; ?>

		<div class="tab-pane" id="extra">
			<div class="row-fluid">
				<?php echo RedeventLayoutHelper::render('attachments.edit', $this, null, array('component' => 'com_redevent')); ?>
			</div>
		</div>

		<div class="tab-pane" id="rules">
			<div class="row-fluid">
				<?php echo $this->form->getField('rules')->renderField(); ?>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
