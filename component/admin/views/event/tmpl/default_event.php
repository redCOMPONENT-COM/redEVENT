<fieldset class="adminform">
	<ul class="adminformlist">
		<li>
			<?php echo $this->form->getLabel('id').$this->form->getInput('id'); ?>
		</li>
		<li>
			<?php echo $this->form->getLabel('title').$this->form->getInput('title'); ?>
		</li>
		<li>
			<?php echo $this->form->getLabel('alias').$this->form->getInput('alias'); ?>
		</li>
		<li>
			<?php echo $this->form->getLabel('language').$this->form->getInput('language'); ?>
		</li>
		<li>
			<?php echo $this->form->getLabel('course_code').$this->form->getInput('course_code'); ?>
		</li>
		<li>
			<?php echo $this->form->getLabel('enable_ical').$this->form->getInput('enable_ical'); ?>
		</li>
		<li>
			<?php echo $this->form->getLabel('published').$this->form->getInput('published'); ?>
		</li>
		<li>
			<?php echo $this->form->getLabel('created_by').$this->form->getInput('created_by'); ?>
		</li>
		<li>
			<label for="catid">
				<?php echo JText::_('COM_REDEVENT_CATEGORY' ).':'; ?>
			</label>
			<?php	echo $this->lists['categories']; ?>
		</li>
		<li>
			<?php echo $this->form->getLabel('datimage').$this->form->getInput('datimage'); ?>
			<div class="clear"></div>
			<div id="imagelib"></div>
		</li>
	</ul>
</fieldset>

<fieldset class="adminform">
	<legend><?php echo JText::_('COM_REDEVENT_EVENT_DESCRIPTION'); ?></legend>
	<ul class="adminformlist">
	<li>
		<?php echo $this->form->getLabel('details_layout').$this->form->getInput('details_layout'); ?>
	</li>
	</ul>
	<div class="clr"></div>
	<span class="event-description-desc"><?php echo JText::_('COM_REDEVENT_EVENT_DESCRIPTION_DESC'); ?></span><br/>
	<?php echo $this->printTags('datdescription'); ?>
	<?php echo $this->form->getInput('datdescription'); ?>
</fieldset>

<fieldset class="adminform">
	<legend><?php echo JText::_('COM_REDEVENT_EVENT_SUMMARY'); ?></legend>
	<span class="event-summary-desc"><?php echo JText::_('COM_REDEVENT_EVENT_SUMMARY_DESC'); ?></span><br/>
	<?php echo $this->printTags('summary'); ?>
	<?php echo $this->form->getInput('summary'); ?>
</fieldset>
	