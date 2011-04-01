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
Window.onDomReady(function() {
        document.formvalidator.setHandler('venue', function(value) {
                return value != 0;
        });

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

function validateForm(f) {
        if (document.formvalidator.isValid(f)) {
                //f.check.value='<?php echo JUtility::getToken(); ?>';//send token
                return true; 
        }
        else {
                alert('Some values are not acceptable.  Please retry.');
        }
        return false;
}

function updateend(cal)
{
	$('enddates').value = cal.date.print(cal.params.ifFormat);
}
</script>

<form name="formxref" action="index.php" method="post" onSubmit="return validateForm(this);">
<?php echo $this->pane->startPane("det-pane");
			echo $this->pane->startPanel( JText::_('Details'), 'tdetails' );
?>
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
    <td class="key hasTip" title="<?php echo JText::_('XREF START DATE TIP'); ?>">
      <label for="dates"><?php echo JText::_('DATE') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->calendar($this->xref->dates, 'dates', 'dates', '%Y-%m-%d', 'updateend'); ?>
    </td>
	</tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF START TIME TIP'); ?>">
      <label for="times"><?php echo JText::_('TIME') .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="times" id="times" value="<?php echo $this->xref->times; ?>" />      
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF END DATE TIP'); ?>">
      <label for="enddates"><?php echo JText::_('ENDDATE') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->calendar($this->xref->enddates, 'enddates', 'enddates'); ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF END TIME TIP'); ?>">
      <label for="endtimes"><?php echo JText::_('ENDTIMES') .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="endtimes" id="endtimes" value="<?php echo $this->xref->endtimes; ?>" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF REGISTRATION END TIP'); ?>">
      <label for="registrationend"><?php echo JText::_('XREF REGISTRATION END') .': '; ?></label>
    </td>
    <td>
      <?php echo JHTML::calendar($this->xref->registrationend, 'registrationend', 'registrationend', '%Y-%m-%d %H:%M'); ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF NOTE TIP'); ?>">
      <label for="note"><?php echo JText::_( 'XREF NOTE' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="50" maxlength="50" name="note" id="note" value="<?php echo $this->xref->note; ?>" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('REDEVENT_XREF_EXTERNAL_REGISTRATION_TIP'); ?>">
      <label for="external_registration_url"><?php echo JText::_( 'REDEVENT_XREF_EXTERNAL_REGISTRATION' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="50" maxlength="255" name="external_registration_url" id="external_registration_url" value="<?php echo $this->xref->external_registration_url; ?>" /> 
    </td>
  </tr>
  <tr>
    <td class="key">
      <label for="published"><?php echo JText::_('PUBLISHED') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->lists['published']; ?>
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
    <?php foreach ($this->customfields as $field): ?>
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
<?php echo $this->editor->display('details', $this->xref->details, '100%;', '300', '100', '20', array('pagebreak', 'readmore')); ?>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('REDEVENT_XREF_ICALDETAILS_FIELDSET'); ?></legend>
<?php echo JText::_('REDEVENT_XREF_ICALDETAILS_INFO'); ?>
<textarea name="icaldetails" rows="10" cols="50"><?php echo $this->xref->icaldetails; ?></textarea>
</fieldset>

<?php echo $this->pane->endPanel();

			echo $this->pane->startPanel(JText::_('Registration'), 'registration');
?>
<fieldset class="adminform">
<legend><?php echo JText::_('Registration'); ?></legend>

<table class="admintable">
<tbody>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF MAX ATTENDEES TIP'); ?>">
      <label for="maxattendees"><?php echo JText::_( 'MAXIMUM_ATTENDEES' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="maxattendees" id="maxattendees" value="<?php echo $this->xref->maxattendees; ?>" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF MAX WAITING TIP'); ?>">
      <label for="maxwaitinglist"><?php echo JText::_( 'MAXIMUM_WAITINGLIST' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="maxwaitinglist" id="maxwaitinglist" value="<?php echo $this->xref->maxwaitinglist; ?>" /> 
    </td>
  </tr>

  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF COURSE CREDIT TIP'); ?>">
      <label for="course_credit"><?php echo JText::_( 'COURSE_CREDIT' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="course_credit" id="course_credit" value="<?php echo $this->xref->course_credit; ?>" /> 
    </td>
  </tr>
    
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('XREF COURSE PRICE TIP'); ?>">
      <label for="course_price"><?php echo JText::_( 'COURSE_PRICE' ) .': '; ?></label>
    </td>
    <td>
	    <table>
				<?php foreach ((array)$this->prices as $k => $r): ?>
			  <tr>
			  	<td><?php echo JHTML::_('select.genericlist', $this->pricegroupsoptions, 'pricegroup[]', '', 'value', 'text', $r->pricegroup_id); ?></td>
			  	<td><input type="text" name="price[]" class="price-val" value="<?php echo $r->price; ?>"/> <button type="button" class="price-button remove-price"><?php echo Jtext::_('COM_REDEVENT_REMOVE'); ?></button></td>
			  </tr>
			  <?php endforeach; ?>
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


<?php echo $this->pane->endPanel();

			echo $this->pane->startPanel(JText::_('Recurrence'), 'recurrence');
			echo $this->loadTemplate('recurrence'); 
			echo $this->pane->endPanel();

			echo $this->pane->startPanel(JText::_('COM_REDEVENT_MENU_ROLES'), 'roles');
			echo $this->loadTemplate('roles'); 
			echo $this->pane->endPanel();
			
			echo $this->pane->endPane();
?> 

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="sessions"/>
<input type="hidden" name="task" value="savexref"/>
<input type="hidden" name="id" value="<?php echo $this->xref->id; ?>"/>
<input type="hidden" name="eventid" value="<?php echo $this->xref->eventid; ?>"/>
<input type="hidden" name="recurrenceid" value="<?php echo (isset($this->xref->recurrence_id) ? $this->xref->recurrence_id : ''); ?>"/>
<input type="hidden" name="repeat" id="repeat" value="<?php echo (isset($this->xref->recurrence_id) ? $this->xref->count : ''); ?>"/>

<input type="submit" name="submitbutton" value="submit"/> 
</form>