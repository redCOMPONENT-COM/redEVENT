<?php
/**
 * @version 1.0 $Id: default.php 1207 2009-10-17 16:21:37Z julien $
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
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.calendar');
JHTML::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');
?>
<script language="javascript" type="text/javascript">
Window.onDomReady(function() {
        document.formvalidator.setHandler('event', function(value) {
                return value != 0;
        });

        document.formvalidator.setHandler('venue', function(value) {
                return value != 0;
        });
        
        document.formvalidator.setHandler('startdate', function(value) {
            return strlen(value) > 0;
        });
        
        $('enddates').addEvent('click', function(){
        	 if (this.value === "" || this.value === "0000-00-00") {
        		  this.value = $('dates').value;
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

<fieldset class="adminform">
<legend><?php echo JText::_('Session'); ?></legend>

<table class="admintable">
<tbody>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF SELECT EVENT TIP'); ?>">
      <label for="eventid"><?php echo JText::_('EDIT XREF SELECT EVENT') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->lists['event']; ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF VENUE TIP'); ?>">
      <label for="venueid"><?php echo JText::_('Venue') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->lists['venue']; ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF GROUP TIP'); ?>">
      <label for="groupid"><?php echo JText::_('Group') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->lists['group']; ?>
    </td>
  </tr>
	<tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF START DATE TIP'); ?>">
      <label for="dates"><?php echo JText::_('DATE') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->calendar($this->xref->dates, 'dates', 'dates', '%Y-%m-%d', 'updateend', 'class="inputbox validate-startdate"'); ?>
    </td>
	</tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF START TIME TIP'); ?>">
      <label for="times"><?php echo JText::_('TIME') .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="times" id="times" value="<?php echo $this->xref->times; ?>" />      
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF END DATE TIP'); ?>">
      <label for="enddates"><?php echo JText::_('ENDDATE') .': '; ?></label>
    </td>
    <td>
      <?php echo $this->calendar($this->xref->enddates, 'enddates', 'enddates'); ?>
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF END TIME TIP'); ?>">
      <label for="endtimes"><?php echo JText::_('ENDTIMES') .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="endtimes" id="endtimes" value="<?php echo $this->xref->endtimes; ?>" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF REGISTRATION END TIP'); ?>">
      <label for="registrationend"><?php echo JText::_('EDIT XREF REGISTRATION END') .': '; ?></label>
    </td>
    <td>
      <?php echo JHTML::calendar($this->xref->registrationend, 'registrationend', 'registrationend', '%Y-%m-%d %H:%M'); ?>
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
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF COURSE PRICE TIP'); ?>">
      <label for="course_price"><?php echo JText::_( 'EDIT XREF COURSE PRICE' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="course_price" id="course_price" value="<?php echo $this->xref->course_price; ?>" /> 
    </td>
  </tr>
  <tr>
    <td class="key hasTip" title="<?php echo JText::_('EDIT XREF COURSE CREDIT TIP'); ?>">
      <label for="course_credit"><?php echo JText::_( 'EDIT XREF COURSE CREDIT' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="course_credit" id="course_credit" value="<?php echo $this->xref->course_credit; ?>" /> 
    </td>
  </tr>
</tbody>
</table>

</fieldset>

<?php if (count($this->customfields)): ?>
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
<?php endif;?>

<?php if ($this->params->get('allow_edit_registration', 0)) :?>
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
</tbody>
</table>

</fieldset>
<?php endif; ?>

<fieldset class="adminform">
<legend><?php echo JText::_('Details'); ?></legend>
<?php echo $this->editor->display('details', $this->xref->details, '100%;', '300', '100', '20', array('pagebreak', 'readmore')); ?>
</fieldset>

<?php if ($this->params->get('allow_edit_recurrence', 0)) :?>
<div id="recurrence">
<fieldset class="adminform">
<legend><?php echo JText::_('RECURRENCE TYPE'); ?></legend>
<?php echo $this->lists['recurrence_type']; ?>
</fieldset>

<div id="xref_recurrence_repeat_common">

<fieldset class="adminform">
<legend><?php echo JText::_('REPEAT INTERVAL'); ?></legend>
<input type="text" name="recurrence_interval" value="<?php echo $this->xref->rrules->interval; ?>" class="hasTip" title="<?php echo JText::_('REPEAT INTERVAL TIP'); ?>"/><span id="repeat_object"></span>
</fieldset>

<fieldset class="adminform">
<legend><input id="rcount" type="radio" name="rutype" value="count" <?php echo ($this->xref->rrules->until_type == 'count') ? ' checked="checked"' : ''; ?>/><?php echo JText::_('REPEAT COUNT'); ?></legend>
<input type="text" name="recurrence_repeat_count" value="<?php echo $this->xref->rrules->count; ?>" class="hasTip" title="<?php echo JText::_('REPEAT COUNT TIP'); ?>"/>
</fieldset>

<fieldset class="adminform">
<legend><input id="runtil" type="radio" name="rutype" value="until" <?php echo ($this->xref->rrules->until_type == 'until') ? ' checked="checked"' : ''; ?>/><?php echo JText::_('REPEAT UNTIL'); ?></legend>

<table class="admintable">
<tbody>
  <tr>
    <td class="hasTip" title="<?php echo JText::_('REPEAT UNTIL TIP'); ?>">
      <?php echo JHTML::calendar($this->xref->rrules->until, 'recurrence_repeat_until', 'recurrence_repeat_until'); ?>
    </td>
  </tr>
</tbody>
</table>
</fieldset>

</div>

<div id="recurrence_repeat_weekly">
<fieldset class="adminform">
<legend><?php echo JText::_('RECURRENCE WEEK BY DAY'); ?></legend>

      <input type="checkbox" id="recurrence_week_byday0" name="wweekdays[]" value="SU" <?php echo (in_array('SU', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_week_byday0"><?php echo JText::_('SUNDAY_S'); ?></label> 
      <input type="checkbox" id="recurrence_week_byday1" name="wweekdays[]" value="MO" <?php echo (in_array('MO', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_week_byday1"><?php echo JText::_('MONDAY_M'); ?></label> 
      <input type="checkbox" id="recurrence_week_byday2" name="wweekdays[]" value="TU" <?php echo (in_array('TU', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_week_byday2"><?php echo JText::_('TUESDAY_T'); ?></label> 
      <input type="checkbox" id="recurrence_week_byday3" name="wweekdays[]" value="WE" <?php echo (in_array('WE', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_week_byday3"><?php echo JText::_('WEDNESDAY_W'); ?></label> 
      <input type="checkbox" id="recurrence_week_byday4" name="wweekdays[]" value="TH" <?php echo (in_array('TH', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_week_byday4"><?php echo JText::_('THURSDAY_T'); ?></label> 
      <input type="checkbox" id="recurrence_week_byday5" name="wweekdays[]" value="FR" <?php echo (in_array('FR', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_week_byday5"><?php echo JText::_('FRIDAY_F'); ?></label> 
      <input type="checkbox" id="recurrence_week_byday6" name="wweekdays[]" value="SA" <?php echo (in_array('SA', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_week_byday6"><?php echo JText::_('SATURDAY_S'); ?></label> 

</fieldset>
</div>

<div id="recurrence_repeat_monthly">

<fieldset class="adminform">
<legend><input id="monthtypebmd" type="radio" name="monthtype" value="bymonthday" <?php echo ($this->xref->rrules->monthtype == 'bymonthdays') ? ' checked="checked"' : ''; ?>/><?php echo JText::_('RECURRENCE MONTH BY MONTHDAY'); ?></legend>

<input type="text" name="bymonthdays" value="<?php echo implode(', ', $this->xref->rrules->bydays); ?>"/>
<label for="bymonthdays"><?php echo JText::_('BY MONTH DAY COMMA LIST')?></label>
<br>
<input type="checkbox" id="reverse_bymonthday" name="reverse_bymonthday" <?php echo ($this->xref->rrules->reverse_bydays) ? ' checked="checked"' : ''; ?>/><label for="reverse_bymonthday"><?php echo JText::_('REVERSE BY MONTH DAY'); ?></label> 
</fieldset>

<fieldset class="adminform">
<legend><input id="monthtypebd" type="radio" name="monthtype" value="byday" <?php echo ($this->xref->rrules->monthtype == 'bymonthdays') ? '' : ' checked="checked"'; ?>/><?php echo JText::_('RECURRENCE MONTH BY DAY'); ?></legend>

<table class="admintable">
<tbody>
  <tr>
    <td>
      <input type="checkbox" id="recurrence_month_week0" name="mweeks[]" value="1" <?php echo (in_array('1', $this->xref->rrules->weeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_week0"><?php echo JText::_('WEEK 1'); ?></label> 
      <input type="checkbox" id="recurrence_month_week1" name="mweeks[]" value="2" <?php echo (in_array('2', $this->xref->rrules->weeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_week1"><?php echo JText::_('WEEK 2'); ?></label> 
      <input type="checkbox" id="recurrence_month_week2" name="mweeks[]" value="3" <?php echo (in_array('3', $this->xref->rrules->weeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_week2"><?php echo JText::_('WEEK 3'); ?></label> 
      <input type="checkbox" id="recurrence_month_week3" name="mweeks[]" value="4" <?php echo (in_array('4', $this->xref->rrules->weeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_week3"><?php echo JText::_('WEEK 4'); ?></label> 
      <input type="checkbox" id="recurrence_month_week4" name="mweeks[]" value="5" <?php echo (in_array('5', $this->xref->rrules->weeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_week4"><?php echo JText::_('WEEK 5'); ?></label> 
    </td>
  </tr>
  <tr>
    <td>
      <input type="checkbox" id="recurrence_month_byday0" name="mweekdays[]" value="SU" <?php echo (in_array('SU', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_byday0"><?php echo JText::_('SUNDAY_S'); ?></label> 
      <input type="checkbox" id="recurrence_month_byday1" name="mweekdays[]" value="MO" <?php echo (in_array('MO', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_byday1"><?php echo JText::_('MONDAY_M'); ?></label> 
      <input type="checkbox" id="recurrence_month_byday2" name="mweekdays[]" value="TU" <?php echo (in_array('TU', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_byday2"><?php echo JText::_('TUESDAY_T'); ?></label> 
      <input type="checkbox" id="recurrence_month_byday3" name="mweekdays[]" value="WE" <?php echo (in_array('WE', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_byday3"><?php echo JText::_('WEDNESDAY_W'); ?></label> 
      <input type="checkbox" id="recurrence_month_byday4" name="mweekdays[]" value="TH" <?php echo (in_array('TH', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_byday4"><?php echo JText::_('THURSDAY_T'); ?></label> 
      <input type="checkbox" id="recurrence_month_byday5" name="mweekdays[]" value="FR" <?php echo (in_array('FR', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_byday5"><?php echo JText::_('FRIDAY_F'); ?></label> 
      <input type="checkbox" id="recurrence_month_byday6" name="mweekdays[]" value="SA" <?php echo (in_array('SA', $this->xref->rrules->weekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_byday6"><?php echo JText::_('SATURDAY_S'); ?></label> 
    </td>
  </tr>
  <tr>
    <td><hr/><?php echo JText::_('REVERSE BY MONTH DAY'); ?></td>
  </tr>
  <tr>
    <td>
      <input type="checkbox" id="recurrence_month_rweek0" name="mrweeks[]" value="1" <?php echo (in_array('1', $this->xref->rrules->rweeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rweek0"><?php echo JText::_('WEEK 1'); ?></label> 
      <input type="checkbox" id="recurrence_month_rweek0" name="mrweeks[]" value="2" <?php echo (in_array('2', $this->xref->rrules->rweeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rweek1"><?php echo JText::_('WEEK 2'); ?></label> 
      <input type="checkbox" id="recurrence_month_rweek0" name="mrweeks[]" value="3" <?php echo (in_array('3', $this->xref->rrules->rweeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rweek2"><?php echo JText::_('WEEK 3'); ?></label> 
      <input type="checkbox" id="recurrence_month_rweek0" name="mrweeks[]" value="4" <?php echo (in_array('4', $this->xref->rrules->rweeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rweek3"><?php echo JText::_('WEEK 4'); ?></label> 
      <input type="checkbox" id="recurrence_month_rweek0" name="mrweeks[]" value="5" <?php echo (in_array('5', $this->xref->rrules->rweeks)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rweek4"><?php echo JText::_('WEEK 5'); ?></label> 
    </td>
  </tr>
  <tr>
    <td>
      <input type="checkbox" id="recurrence_month_rbyday0" name="mrweekdays[]" value="SU" <?php echo (in_array('SU', $this->xref->rrules->rweekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rbyday0"><?php echo JText::_('SUNDAY_S'); ?></label> 
      <input type="checkbox" id="recurrence_month_rbyday1" name="mrweekdays[]" value="MO" <?php echo (in_array('MO', $this->xref->rrules->rweekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rbyday0"><?php echo JText::_('MONDAY_M'); ?></label> 
      <input type="checkbox" id="recurrence_month_rbyday2" name="mrweekdays[]" value="TU" <?php echo (in_array('TU', $this->xref->rrules->rweekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rbyday0"><?php echo JText::_('TUESDAY_T'); ?></label> 
      <input type="checkbox" id="recurrence_month_rbyday3" name="mrweekdays[]" value="WE" <?php echo (in_array('WE', $this->xref->rrules->rweekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rbyday0"><?php echo JText::_('WEDNESDAY_W'); ?></label> 
      <input type="checkbox" id="recurrence_month_rbyday4" name="mrweekdays[]" value="TH" <?php echo (in_array('TH', $this->xref->rrules->rweekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rbyday0"><?php echo JText::_('THURSDAY_T'); ?></label> 
      <input type="checkbox" id="recurrence_month_rbyday5" name="mrweekdays[]" value="FR" <?php echo (in_array('FR', $this->xref->rrules->rweekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rbyday0"><?php echo JText::_('FRIDAY_F'); ?></label> 
      <input type="checkbox" id="recurrence_month_rbyday6" name="mrweekdays[]" value="SA" <?php echo (in_array('SA', $this->xref->rrules->rweekdays)) ? 'checked="checked"': '';?>/><label for="recurrence_month_rbyday0"><?php echo JText::_('SATURDAY_S'); ?></label> 
    </td>
  </tr>
</tbody>
</table>

</fieldset>
</div>

<div id="recurrence_repeat_yearly">

<fieldset class="adminform">
<legend><?php echo JText::_('RECURRENCE YEAR BY YEARDAY'); ?></legend>

<input type="text" name="byyeardays" value="<?php echo implode(', ', $this->xref->rrules->bydays); ?>"/>
<label for="byyeardays"><?php echo JText::_('BY YEAR DAY COMMA LIST')?></label>
<br>
<input type="checkbox" id="reverse_byyearday" name="reverse_byyearday" <?php echo ($this->xref->rrules->reverse_bydays) ? ' checked="checked"' : ''; ?>/><label for="reverse_byyearday"><?php echo JText::_('REVERSE BY YEAR DAY'); ?></label> 

</fieldset>
</div>
</div>
<?php endif; ?>


<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value=""/>
<input type="hidden" name="task" value="savexref"/>
<input type="hidden" name="id" value="<?php echo $this->xref->id; ?>"/>
<input type="hidden" name="recurrenceid" value="<?php echo (isset($this->xref->recurrence_id) ? $this->xref->recurrence_id : ''); ?>"/>
<input type="hidden" name="repeat" id="repeat" value="<?php echo (isset($this->xref->recurrence_id) ? $this->xref->count : ''); ?>"/>

<input type="submit" name="submitbutton" value="<?php echo JText::_('Submit'); ?>"/> 
<input type="button" name="cancelbutton" value="<?php echo JText::_('Cancel'); ?>" onclick="javascript:history.go(-1);"/> 
</form>