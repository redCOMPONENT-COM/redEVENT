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

defined('_JEXEC') or die('Restricted access');
?>

<script language="javascript" type="text/javascript">
window.addEvent('domready', function() {

        $('enddates').addEvent('click', function(){           
        	 if (this.value === "" || this.value === "0000-00-00") {
        		  this.value = $('dates').value;
         	 }
        });

        $('times').addEvent('change', function(){
					if ($('dates').value !== "" && $('dates').value !== "0000-00-00" && $('dates').value !== $('enddates').value) {						
						 $('endtimes').value = this.value;
					}
        });
});

function updateend(cal)
{
	$('enddates').value = cal.date.print(cal.params.ifFormat);
}
</script>

<fieldset class="adminform">
<legend><?php echo JText::_('Dates'); ?></legend>

<table class="admintable">
<tbody>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF VENUE TIP'); ?>">
      <label for="venueid"><?php echo JText::_('Venue') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->lists['venue']; ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF GROUP TIP'); ?>">
      <label for="groupid"><?php echo JText::_('Group') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->lists['group']; ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_SESSION_TITLE_TIP'); ?>">
      <label for="session_title"><?php echo JText::_( 'COM_REDEVENT_SESSION_TITLE_LABEL' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="20" maxlength="255" name="session_title" id="session_title" value="" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_SESSION_ALIAS_TIP'); ?>">
      <label for="session_alias"><?php echo JText::_( 'COM_REDEVENT_SESSION_ALIAS_LABEL' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="20" maxlength="255" name="session_alias" id="session_alias" value="" /> 
    </td>
  </tr>
	<tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF START DATE TIP'); ?>">
      <label for="dates"><?php echo JText::_('DATE') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->calendar(null, 'dates', 'dates', '%Y-%m-%d', 'updateend'); ?>
    </td>
	</tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF START TIME TIP'); ?>">
      <label for="times"><?php echo JText::_('TIME') .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="times" id="times" value="" />      
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF END DATE TIP'); ?>">
      <label for="enddates"><?php echo JText::_('ENDDATE') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->calendar(null, 'enddates', 'enddates'); ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF END TIME TIP'); ?>">
      <label for="endtimes"><?php echo JText::_('ENDTIMES') .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="endtimes" id="endtimes" value="" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF REGISTRATION END TIP'); ?>">
      <label for="registrationend"><?php echo JText::_('XREF REGISTRATION END') .': '; ?></label>
    </td>
    <td>
      <?php echo JHTML::calendar(null, 'registrationend', 'registrationend', '%Y-%m-%d %H:%M'); ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF NOTE TIP'); ?>">
      <label for="session_note"><?php echo JText::_( 'XREF NOTE' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="50" maxlength="50" name="session_note" id="note" value="" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('REDEVENT_XREF_EXTERNAL_REGISTRATION_TIP'); ?>">
      <label for="session_external_registration_url"><?php echo JText::_( 'REDEVENT_XREF_EXTERNAL_REGISTRATION' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="50" maxlength="255" name="session_external_registration_url" id="session_external_registration_url" value="" /> 
    </td>
  </tr>
  <tr>
    <td class="key">
      <label for="session_published"><?php echo JText::_('PUBLISHED') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->lists['session_published']; ?>
    </td>
  </tr>
  <tr>
    <td class="key">
      <label for="featured"><?php echo JText::_('COM_REDEVENT_SESSION_FEATURED') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->lists['featured']; ?>
    </td>
  </tr>
</tbody>
</table>

</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('Custom fields'); ?></legend>
<table class="admintable">
	<tbody>
    <?php foreach ($this->xrefcustomfields as $field): ?>
    <tr>
      <td class="key">
        <label for="custom" class="hasTip" title="<?php echo JText::_($field->get('name')).'::'.JText::_('USE TAG') .': ['. $field->get('tag') .']'; ?>">
          <?php echo JText::_( $field->name ); ?>:
        </label>
      </td>
      <td>
        <?php echo $field->render(); ?>
      </td>   
    </tr>
    <?php endforeach; ?>
	</tbody>
</table>

</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('Details'); ?></legend>
<?php echo JText::_('REDEVENT_XREF_DETAILS_INFO'); ?>
<?php echo $this->editor->display('session_details', null, '100%;', '300', '100', '20', array('pagebreak', 'readmore')); ?>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('Registration'); ?></legend>

<table class="admintable">
<tbody>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF MAX ATTENDEES TIP'); ?>">
      <label for="maxattendees"><?php echo JText::_( 'MAXIMUM_ATTENDEES' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="maxattendees" id="maxattendees" value="" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF MAX WAITING TIP'); ?>">
      <label for="maxwaitinglist"><?php echo JText::_( 'MAXIMUM_WAITINGLIST' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="maxwaitinglist" id="maxwaitinglist" value="" /> 
    </td>
  </tr>

  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF COURSE CREDIT TIP'); ?>">
      <label for="course_credit"><?php echo JText::_( 'COURSE_CREDIT' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="course_credit" id="course_credit" value="" /> 
    </td>
  </tr>
    
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF COURSE PRICE TIP'); ?>">
      <label for="course_price"><?php echo JText::_( 'COURSE_PRICE' ) .': '; ?></label>
    </td>
    <td>
	    <table>
			  <tr id="trnewprice">
			  	<td><?php echo JHTML::_('select.genericlist', $this->pricegroupsoptions, 'pricegroup[]', array('id' => 'newprice', 'class' => 'newprice')); ?></td>
			  	<td><input type="text" name="price[]" class="price-val" value="0.00" size="10" /> <button type="button" class="price-button" id="add-price"><?php echo Jtext::_('add'); ?></button></td>  	
			  </tr>
	    </table>
    </td>
  </tr>
  
</tbody>
</table>

</fieldset>


<?php 
	echo $this->loadTemplate('session_roles'); 
	echo $this->loadTemplate('session_ical'); 
	echo $this->loadTemplate('session_recurrence'); 
?> 

<input type="hidden" name="recurrenceid" value=""/>
<input type="hidden" name="repeat" id="repeat" value=""/>
