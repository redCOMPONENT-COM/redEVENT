<?php defined('_JEXEC') or die('Restricted access'); ?>

<fieldset class="adminform">
<legend><?php echo JText::_('REDEVENT_XREF_ICALDETAILS_FIELDSET'); ?></legend>

<table class="admintable">
	<tbody>
    <tr>
      <td class="key">
        <label for="icaldetails" class="hasTip" title="<?php echo JText::_('REDEVENT_XREF_ICALDETAILS_INFO').'::'.JText::_('REDEVENT_XREF_ICALDETAILS_INFO_TIP'); ?>">
          <?php echo JText::_('REDEVENT_XREF_ICALDETAILS_INFO'); ?>:
        </label>
      </td>
      <td>
				<textarea name="icaldetails" rows="10" cols="50"></textarea>
      </td>   
    </tr>
    <tr>
      <td class="key">
        <label for="icalvenue" class="hasTip" title="<?php echo JText::_('REDEVENT_XREF_ICALDETAILS_VENUE').'::'.JText::_('REDEVENT_XREF_ICALDETAILS_VENUE_TIP'); ?>">
          <?php echo JText::_('REDEVENT_XREF_ICALDETAILS_VENUE'); ?>:
        </label>
      </td>
      <td>
      	<input type="text" size="50" maxlength="50" name="icalvenue" id="icalvenue" value="" /> 
      </td>   
    </tr>
	</tbody>
</table>

</fieldset>

<?php
