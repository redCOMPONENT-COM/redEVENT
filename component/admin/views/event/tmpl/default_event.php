<table class="adminform">
<tr>
	<td style="vertical-align: top;">
		<table>
			<tr>
				<td>
					<label for="title">
						<?php echo JText::_('COM_REDEVENT_EVENT_TITLE' ).':'; ?>
					</label>
				</td>
				<td>
					<input class="inputbox" name="title" value="<?php echo $this->row->title; ?>" size="50" maxlength="100" id="title" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="alias">
						<?php echo JText::_('COM_REDEVENT_Alias' ).':'; ?>
					</label>
				</td>
				<td colspan="3">
					<input class="inputbox" type="text" name="alias" id="alias" size="50" maxlength="100" value="<?php echo $this->row->alias; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="course_code">
						<?php echo JText::_('COM_REDEVENT_COURSE_CODE' ).':'; ?>
					</label>
				</td>
				<td>
					<input class="inputbox" name="course_code" value="<?php echo $this->row->course_code; ?>" size="50" maxlength="100" id="course_code" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="enable_ical">
						<?php echo JText::_( 'COM_REDEVENT_EVENT_ENABLE_ICAL_LABEL' ).':'; ?>
					</label>
				</td>
				<td>
					<?php	echo $this->lists['enable_ical'];	?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="published">
						<?php echo JText::_('COM_REDEVENT_PUBLISHED' ).':'; ?>
					</label>
				</td>
				<td>
					<?php
					$html = JHTML::_('select.booleanlist', 'published', '', $this->row->published );
					echo $html;
					?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="creator">
						<?php echo JText::_('COM_REDEVENT_CREATOR' ).':'; ?>
					</label>
				</td>
				<td>
					<?php echo redEVENTHelper::getUserSelector('created_by',$this->row->created_by); ?>
				</td>
			</tr>
		</table>
	</td>
	<td>
		<table>
		<tr>
			<td>
				<label for="catid">
					<?php echo JText::_('COM_REDEVENT_CATEGORY' ).':'; ?>
				</label>
			</td>
			<td>
				<?php
				echo $this->lists['categories']
				?>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<table class="adminform">
<tr>
	<td>
	<strong><?php echo JText::_('COM_REDEVENT_EVENT_DESCRIPTION'); ?></strong><br/>
	<span class="event-description-desc"><?php echo JText::_('COM_REDEVENT_EVENT_DESCRIPTION_DESC'); ?></span><br/>
	<?php echo $this->printTags('datdescription'); ?>
	</td>
</tr>
<tr>
	<td><label for="layout" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_EVENT_LAYOUT').'::'.JText::_('COM_REDEVENT_EVENT_LAYOUT_TIP'); ?>"><?php echo JText::_('COM_REDEVENT_EVENT_LAYOUT'); ?>:</label><?php echo $this->lists['details_layout']; ?></td>
</tr>
<tr>
	<td>
		<?php
		// parameters : areaname, content, hidden field, width, height, rows, cols, buttons
		echo $this->editor->display( 'datdescription',  $this->row->datdescription, '100%;', '550', '75', '20', array('pagebreak', 'readmore') ) ;
		?>
	</td>
</tr>

<tr>
	<td>
	<strong><?php echo JText::_('COM_REDEVENT_EVENT_SUMMARY'); ?></strong><br/>
	<span class="event-summary-desc"><?php echo JText::_('COM_REDEVENT_EVENT_SUMMARY_DESC'); ?></span>
	</td>
</tr>
<tr>
	<td>
		<?php
		// parameters : areaname, content, hidden field, width, height, rows, cols, buttons
		echo $this->editor->display( 'summary',  $this->row->summary, '100%;', '100', '75', '5', null ) ;
		?>
	</td>
</tr>
</table>