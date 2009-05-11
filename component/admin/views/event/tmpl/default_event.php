<table class="adminform">
<tr>
	<td style="vertical-align: top;">
		<table>
			<tr>
				<td>
					<label for="title">
						<?php echo JText::_( 'EVENT TITLE' ).':'; ?>
					</label>
				</td>
				<td>
					<input class="inputbox" name="title" value="<?php echo $this->row->title; ?>" size="50" maxlength="100" id="title" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="alias">
						<?php echo JText::_( 'Alias' ).':'; ?>
					</label>
				</td>
				<td colspan="3">
					<input class="inputbox" type="text" name="alias" id="alias" size="50" maxlength="100" value="<?php echo $this->row->alias; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="course_code">
						<?php echo JText::_( 'COURSE_CODE' ).':'; ?>
					</label>
				</td>
				<td>
					<input class="inputbox" name="course_code" value="<?php echo $this->row->course_code; ?>" size="50" maxlength="100" id="course_code" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="published">
						<?php echo JText::_( 'PUBLISHED' ).':'; ?>
					</label>
				</td>
				<td>
					<?php
					$html = JHTML::_('select.booleanlist', 'published', '', $this->row->published );
					echo $html;
					?>
				</td>
			</tr>
		</table>
	<td>
		<table>
		<tr>
			<td>
				<label for="catid">
					<?php echo JText::_( 'CATEGORY' ).':'; ?>
				</label>
			</td>
			<td>
				<?php
				echo $this->Lists['category']
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
	<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#details_tags\").toggle(\"slideUp\");'"); ?>
		<div id="details_tags" style="display: none;">
			[venues] = <?php echo JText::_('SUBMISSION_VENUES');?><br />
			[price] = <?php echo JText::_('SUBMISSION_EVENT_PRICE');?><br />
			[credits] = <?php echo JText::_('SUBMISSION_EVENT_CREDITS');?><br />
			[code] = <?php echo JText::_('SUBMISSION_EVENT_CODE');?><br />
			[event_title]  = <?php echo JText::_('SUBMISSION_EVENT_TITLE');?><br />
			[eventplaces] = <?php echo JText::_('SUBMISSION_EVENTPLACES');?><br />
			[waitinglistplaces] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES');?><br />
			[eventplacesleft] = <?php echo JText::_('SUBMISSION_EVENTPLACES_LEFT');?><br />
			[waitinglistplacesleft] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES_LEFT');?>
		</div>
	</td>
</tr>
<tr>
	<td>
		<?php
		// parameters : areaname, content, hidden field, width, height, rows, cols, buttons
		echo $this->editor->display( 'datdescription',  $this->row->datdescription, '100%;', '550', '75', '20', array('pagebreak', 'readmore') ) ;
		?>
	</td>
</tr>
</table>
