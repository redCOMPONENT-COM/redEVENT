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

// no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.calendar');

jimport('joomla.html.pane');
$pane =& JPane::getInstance('tabs'); 
?>

<script type="text/javascript">
	window.addEvent('domready', function()
	{
		document.formvalidator.setHandler('categories',
			function (value) {
				if(value=="") {
					return false;
				} else {
				  return true;
				}
			}
		);
	        
    document.formvalidator.setHandler('startdate', function(value) {
    	return value.length > 0;
    });

    if ($('enddates')) {
	    $('enddates').addEvent('click', 
	    	function(){
	    		if (this.value === "" || this.value === "0000-00-00") {
	      		this.value = $('dates').value;
	        }
	       });
    }

	});

	
		function submitbutton( pressbutton ) 
		{
			if (pressbutton == 'cancelevent' || pressbutton == 'addvenue') {
				elsubmitform( pressbutton );
				return false;
			}

			var form = document.getElementById('eventform');
			var validator = document.formvalidator;

			if ( validator.validate(form.title) === false ) {
   				alert("<?php echo JText::_('COM_REDEVENT_ADD_TITLE', true ); ?>");
   				validator.handleResponse(false,form.title);
   				return false;
			} else if ($(form.categories) && validator.validate(form.categories) === false ) {
    			alert("<?php echo JText::_('COM_REDEVENT_SELECT_CATEGORY', true ); ?>");
    			validator.handleResponse(false,form.categories);
    			return false;
			} else if ( $(form.venueid) && validator.validate(form.venueid) === false ) {
    			alert("<?php echo JText::_('COM_REDEVENT_SELECT_VENUE', true ); ?>");
    			validator.handleResponse(false,form.venueid);
    			return false;
			} else if ( $(form.dates) && validator.validate(form.dates) === false ) {
    			alert("<?php echo JText::_('COM_REDEVENT_SELECT_DATE', true ); ?>");
    			validator.handleResponse(false,form.dates);
    			return false;
			} else if (validator.isValid(form) === false ) {
    			alert("<?php echo JText::_('COM_REDEVENT_Error_Please_check_required_fields', true ); ?>");
    			return false;
  			} else {
  			<?php
			// JavaScript for extracting editor text
				
      		if ($this->editoruser && $this->params->get('edit_description', 0)) {
						echo $this->editor->save( 'datdescription' );
      		}
			?>
				// submit_unlimited();
				elsubmitform(pressbutton);

				return false;
			}
			return false;
		}
		
		//joomla submitform needs form name
		function elsubmitform(pressbutton)
		{			
			var form = document.getElementById('eventform');
			if (pressbutton) {
				form.task.value=pressbutton;
			}
			if (typeof form.onsubmit == "function") {
				form.onsubmit();
			}
			form.submit();
		}


		var tastendruck = false;
		
		function rechne(restzeichen)
		{
			maximum = <?php echo $this->params->get('max_description', 1000); ?>;

			if (restzeichen.datdescription.value.length > maximum) {
				restzeichen.datdescription.value = restzeichen.datdescription.value.substring(0, maximum);
				links = 0;
			} else {
				links = maximum - restzeichen.datdescription.value.length;
			}
			restzeichen.zeige.value = links;
		}

		function berechne(restzeichen)
   		{
  			tastendruck = true;
  			rechne(restzeichen);
   		}
   		
		function updateend(cal)
		{
			$('enddates').value = cal.date.print(cal.params.ifFormat);
		}
</script>

<div id="redevent" class="re_editevent">

	<?php if ($this->params->def( 'show_page_title', 1 )) : ?>
	<h1 class="componentheading"><?php echo $this->title; ?></h1>
	<?php endif; ?>

	<form enctype="multipart/form-data" id="eventform" action="<?php echo JRoute::_('index.php?option=com_redevent'); ?>" method="post" class="form-validate">

		<div class="re_save_buttons floattext">
			<button type="submit" class="submit"
				onclick="return submitbutton('saveevent')"><?php echo JText::_('COM_REDEVENT_SAVE') ?>
			</button>
			<button type="reset" class="button cancel"
				onclick="submitbutton('cancelevent')"><?php echo JText::_('COM_REDEVENT_CANCEL') ?>
			</button>
		</div>

		<p class="clear"></p>
		
<?php echo $pane->startPane( 'pane' ); ?>
<?php echo $pane->startPanel( JText::_('COM_REDEVENT_DETAILS'), 'ev1' ); ?>

	<table class="fieldstable">
		<tbody>
			<tr>
				<td class="key">
					<label for="title"><?php echo JText::_('COM_REDEVENT_TITLE' ).':'; ?></label>
				</td>
				<td>
					<input class="inputbox required" type="text" id="title"
					name="title" value="<?php echo $this->row->title; ?>" size="65" maxlength="60" />
				</td>
			</tr>
			<?php if ($this->params->get('edit_categories', 0)): ?>
			<tr>
				<td class="key">
					<label for="categories" class="catsid"> <?php echo JText::_('COM_REDEVENT_CATEGORY' ).':';?></label>
				</td>
				<td><?php	echo $this->lists['categories']; ?></td>
			</tr>
			<?php endif; ?>
			<?php if ($this->canpublish): ?>
			<tr>
				<td class="key"><label for="published"><?php echo JText::_('COM_REDEVENT_PUBLISHED') .': '; ?></label>
				</td>
				<td><?php echo $this->lists['published']; ?></td>
			</tr>
			<?php endif; ?>
			
			<?php if ($this->params->get('edit_customs', 0) && count($this->customs)): ?>
	    <?php foreach ($this->customs as $field): ?>
	    <tr>
	      <td class="key">
	        <label for="custom<?php echo $field->id; ?>" class="hasTip" title="<?php echo JText::_($field->get('name')).'::'. $field->get('tips'); ?>">
	          <?php echo JText::_( $field->name ); ?>:
	        </label>
	      </td>
	      <td>
	        <?php echo $field->render(); ?>
	        <?php echo ($field->required? ' '.JText::_('COM_REDEVENT_Required') : '' ); ?>
	      </td>   
	    </tr>
	    <?php endforeach; ?>
			<?php endif; ?>
			
			<?php if ($this->params->get('edit_summary', 1)) :?>
		  <tr>
		    <td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_EVENT_SUMMARY'); ?>::<?php echo JText::_('COM_REDEVENT_EVENT_SUMMARY_DESC'); ?>">
		      <label for="summary"><?php echo JText::_( 'COM_REDEVENT_EVENT_SUMMARY' ) .': '; ?></label>
		    </td>
		    <td>
		      <?php echo $this->editor->display('summary', $this->row->summary, '100%', '100', '70', '5', array('pagebreak', 'readmore') ); ?>
		    </td>
		  </tr>
			<?php endif; ?>
			
		</tbody>
	</table>
<?php echo $pane->endPanel(); ?>

<?php if ($this->params->get('create_session', 1) && !($this->row->id && !JRequest::getInt('xref'))): // edit/create xref ?>
<?php echo $pane->startPanel( JText::_('COM_REDEVENT_SESSION'), 'ev-session' ); ?>
<?php echo $this->loadTemplate('session'); ?>
<?php echo $pane->endPanel(); ?>

<?php endif; ?>

<?php if (( $this->params->get('edit_image', 1) == 2 ) || ($this->params->get('edit_image', 1) == 1)) : ?>
<?php echo $pane->startPanel( JText::_('COM_REDEVENT_IMAGE'), 'ev-image' ); ?>
<div class="editevent-image">
<?php if ($this->row->datimage) :
				echo ELOutput::flyer( $this->row, $this->dimage, 'event' );
			else :
				echo JHTML::_('image', 'components/com_redevent/assets/images/noimage.png', JText::_('COM_REDEVENT_NO_IMAGE'), array('class' => 'modal'));
			endif;?> <label for="userfile"><?php echo JText::_('COM_REDEVENT_IMAGE'); ?></label>
<input class="inputbox <?php echo $this->params->get('edit_image', 1) == 2 ? 'required' : ''; ?>"	name="userfile" id="userfile" type="file" /> 
<small class="editlinktip hasTip"	title="<?php echo JText::_('COM_REDEVENT_NOTES' ); ?>::<?php echo JText::_('COM_REDEVENT_MAX_IMAGE_FILE_SIZE').' '.$this->elsettings->sizelimit.' kb'; ?>"><?php echo $this->infoimage; ?> </small>
</div>
<?php echo $pane->endPanel(); ?>
<?php endif; ?>


<?php if ($this->params->get('edit_description', 0)): ?>
<?php echo $pane->startPanel( JText::_('COM_REDEVENT_DESCRIPTION'), 'ev3' ); ?>
<fieldset class="description"><legend><?php echo JText::_('COM_REDEVENT_DESCRIPTION'); ?></legend>

<?php
      		//if usertyp min editor then editor else textfield
      		if ($this->editoruser) :
      			echo $this->editor->display('datdescription', $this->row->datdescription, '100%', '400', '70', '15', array('pagebreak', 'readmore') );
      		else :
      		?> <textarea style="width: 100%;" rows="10"
	name="datdescription" class="inputbox" wrap="virtual"
	onkeyup="berechne(this.form)"><?php echo $this->row->datdescription; ?></textarea><br />
<?php echo JText::_('COM_REDEVENT_NO_HTML' ); ?><br />
<input disabled value="<?php echo $this->params->get('max_description', 1000); ?>"
	size="4" name="zeige" /><?php echo JText::_('COM_REDEVENT_AVAILABLE' ); ?><br />
<a href="javascript:rechne(document.eventform);"><?php echo JText::_('COM_REDEVENT_REFRESH' ); ?></a>
<?php endif; ?>
</fieldset>
<?php echo $pane->endPanel(); ?>
<?php endif; ?>

<?php if ($this->params->get('allow_attachments', 1)): ?>
<?php echo $pane->startPanel( JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'), 'ev4' ); ?>
<?php echo $this->loadTemplate('attachments'); ?>
<?php echo $pane->endPanel(); ?>
<?php endif; ?>
<?php echo $pane->endPane(); ?>

<div class="re_save_buttons floattext">
<button type="submit" class="submit"
	onclick="return submitbutton('saveevent')"><?php echo JText::_('COM_REDEVENT_SAVE') ?>
</button>
<button type="reset" class="button cancel"
	onclick="submitbutton('cancelevent')"><?php echo JText::_('COM_REDEVENT_CANCEL') ?>
</button>
</div>

<p class="clear">
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" /> 
<input type="hidden" name="xref" value="<?php echo $this->row->xref; ?>" /> 
<input type="hidden" name="returnid" value="<?php echo JRequest::getInt('returnid'); ?>" />
<input type="hidden" name="referer"	value="<?php echo $this->referer; ?>" /> 
<input type="hidden" name="created" value="<?php echo $this->row->created; ?>" />
<input type="hidden" name="author_ip"	value="<?php echo $this->row->author_ip; ?>" /> 
<input type="hidden" name="created_by" value="<?php echo $this->row->created_by; ?>" /> 
<input type="hidden" name="curimage" value="<?php echo $this->row->datimage; ?>" /> 
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_redevent" />
</p>
</form>

</div>
<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>