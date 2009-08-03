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
        })
})

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
</script>

<form name="formxref" action="index.php" method="post" onSubmit="return validateForm(this);">

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
    <td class="key hasTip" title="<?php echo JText::_('XREF START DATE TIP'); ?>">
      <label for="dates"><?php echo JText::_('DATE') .': '; ?></label>
    </td>
    <td>
      <?php echo JHTML::calendar($this->xref->dates, 'dates', 'dates'); ?>
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
      <?php echo JHTML::calendar($this->xref->enddates, 'enddates', 'enddates'); ?>
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
    <td class="key">
      <label for="published"><?php echo JText::_('PUBLISHED') .': '; ?></label>
    </td>
    <td>
      <?php echo JHTML::_('select.booleanlist', 'published', '', $this->xref->published); ?> 
    </td>
  </tr>
</tbody>
</table>

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
    <td class="key hasTip" title="<?php echo JText::_('XREF COURSE PRICE TIP'); ?>">
      <label for="course_price"><?php echo JText::_( 'COURSE_PRICE' ) .': '; ?></label>
    </td>
    <td>
      <input type="text" size="8" maxlength="8" name="course_price" id="course_price" value="<?php echo $this->xref->course_price; ?>" /> 
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
</tbody>
</table>

</fieldset>


<fieldset class="adminform">
<legend><?php echo JText::_('Details'); ?></legend>

<table class="admintable">
<tbody>
  <tr>
    <td>
      <?php echo $this->editor->display('details', $this->xref->details, '100%;', '300', '100', '20', array('pagebreak', 'readmore')); ?>
    </td>
  </tr>
</tbody>
</table>

</fieldset>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="events"/>
<input type="hidden" name="task" value="savexref"/>
<input type="hidden" name="id" value="<?php echo $this->xref->id; ?>"/>
<input type="hidden" name="eventid" value="<?php echo $this->xref->eventid; ?>"/>

<input type="submit" name="submitbutton" value="submit"/> 
</form>