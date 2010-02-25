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
?>

<script type="text/javascript">
	Window.onDomReady(function()
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
        
    $('enddates').addEvent('click', 
    	function(){
    		if (this.value === "" || this.value === "0000-00-00") {
      		this.value = $('dates').value;
        }
       });
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
   				alert("<?php echo JText::_( 'ADD TITLE', true ); ?>");
   				validator.handleResponse(false,form.title);
   				return false;
			} else if ($(form.categories) && validator.validate(form.categories) === false ) {
    			alert("<?php echo JText::_( 'SELECT CATEGORY', true ); ?>");
    			validator.handleResponse(false,form.categories);
    			return false;
			} else if ( validator.validate(form.venueid) === false ) {
    			alert("<?php echo JText::_( 'SELECT VENUE', true ); ?>");
    			validator.handleResponse(false,form.venueid);
    			return false;
			} else if ( $(form.dates) && validator.validate(form.dates) === false ) {
    			alert("<?php echo JText::_( 'SELECT DATE', true ); ?>");
    			validator.handleResponse(false,form.dates);
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


		var tastendruck = false
		function rechne(restzeichen)
		{
			maximum = <?php echo $this->elsettings->datdesclimit; ?>

			if (restzeichen.datdescription.value.length > maximum) {
				restzeichen.datdescription.value = restzeichen.datdescription.value.substring(0, maximum)
				links = 0
			} else {
				links = maximum - restzeichen.datdescription.value.length
			}
			restzeichen.zeige.value = links
		}

		function berechne(restzeichen)
   		{
  			tastendruck = true
  			rechne(restzeichen)
   		}
   		
		function updateend(cal)
		{
			$('enddates').value = cal.date.print(cal.params.ifFormat);
		}
</script>

<div id="redevent" class="re_editevent">

	<?php if ($this->params->def( 'show_page_title', 1 )) : ?>
	<h1 class="componentheading"><?php echo $this->params->get('page_title'); ?></h1>
	<?php endif; ?>

	<form enctype="multipart/form-data" id="eventform" action="<?php echo JRoute::_('index.php') ?>" method="post" class="form-validate">

		<div class="re_save_buttons floattext">
			<button type="submit" class="submit"
				onclick="return submitbutton('saveevent')"><?php echo JText::_('SAVE') ?>
			</button>
			<button type="reset" class="button cancel"
				onclick="submitbutton('cancelevent')"><?php echo JText::_('CANCEL') ?>
			</button>
		</div>

		<p class="clear"></p>

		<fieldset class="re_fldst_details"><legend><?php echo JText::_('DETAILS'); ?></legend>

	<table class="fieldstable">
		<tbody>
			<tr>
				<td class="key hasTip" title="<?php echo JText::_(''); ?>">
					<label for="title"><?php echo JText::_( 'TITLE' ).':'; ?></label>
				</td>
				<td>
					<input class="inputbox required" type="text" id="title"
					name="title" value="<?php echo $this->escape($this->row->title); ?>" size="65" maxlength="60" />
				</td>
			</tr>
			<?php if ($this->params->get('edit_categories', 0)): ?>
			<tr>
				<td class="key hasTip" title="<?php echo JText::_(''); ?>">
					<label for="categories" class="catsid"> <?php echo JText::_( 'CATEGORY' ).':';?></label>
				</td>
				<td><?php	echo $this->lists['categories']; ?></td>
			</tr>
			<?php endif; ?>
			<?php if (!$this->row->id): // edit first xref only on initial event creation, afterwards use myevents ?>
			<tr>
				<td class="key hasTip" title="<?php echo JText::_(''); ?>">
					<label for="a_id"><?php echo JText::_( 'VENUE' ).':'; ?></label>
				</td>
				<td>
					<input type="text" id="a_name" value="<?php echo $this->row->venue; ?>" disabled="disabled" />
					<div class='re_buttons floattext'>
						<a class="re_venue_select modal" title="<?php echo JText::_('SELECT'); ?>" 
						   href="<?php echo JRoute::_('index.php?view=editevent&layout=selectvenue&tmpl=component'); ?>"
						   rel="{handler: 'iframe', size: {x: 650, y: 375}}">
						   	<span><?php echo JText::_('SELECT')?></span>
						</a> 
						<input class="inputbox required" type="hidden" id="a_id" name="venueid" value="<?php echo $this->row->locid; ?>" />
					</div>
				</td>
			</tr>
			<tr>
				<td class="key hasTip"
					title="<?php echo JText::_('EDIT XREF START DATE TIP'); ?>"><label
					for="dates"><?php echo JText::_('DATE') .': '; ?></label></td>
				<td><?php echo $this->calendar($this->row->dates, 'dates', 'dates', '%Y-%m-%d', 'updateend', 'class="inputbox validate-startdate required"'); ?>
				</td>
			</tr>
			<tr>
				<td class="key hasTip"
					title="<?php echo JText::_('EDIT XREF START TIME TIP'); ?>"><label
					for="times"><?php echo JText::_('TIME') .': '; ?></label></td>
				<td><input type="text" size="8" maxlength="8" name="times" id="times"
					value="<?php echo $this->row->times; ?>" /></td>
			</tr>
			<tr>
				<td class="key hasTip"
					title="<?php echo JText::_('EDIT XREF END DATE TIP'); ?>"><label
					for="enddates"><?php echo JText::_('ENDDATE') .': '; ?></label></td>
				<td><?php echo $this->calendar($this->row->enddates, 'enddates', 'enddates'); ?>
				</td>
			</tr>
			<tr>
				<td class="key hasTip"
					title="<?php echo JText::_('EDIT XREF END TIME TIP'); ?>"><label
					for="endtimes"><?php echo JText::_('ENDTIMES') .': '; ?></label></td>
				<td><input type="text" size="8" maxlength="8" name="endtimes"
					id="endtimes" value="<?php echo $this->row->endtimes; ?>" /></td>
			</tr>
			<tr>
				<td class="key hasTip"
					title="<?php echo JText::_('EDIT XREF REGISTRATION END TIP'); ?>"><label
					for="registrationend"><?php echo JText::_('EDIT XREF REGISTRATION END') .': '; ?></label>
				</td>
				<td><?php echo JHTML::calendar($this->row->registrationend, 'registrationend', 'registrationend', '%Y-%m-%d %H:%M'); ?>
				</td>
			</tr>
			<?php if ($this->params->get('edit_published', 0)): ?>
			<tr>
				<td class="key"><label for="published"><?php echo JText::_('PUBLISHED') .': '; ?></label>
				</td>
				<td><?php echo $this->lists['published']; ?></td>
			</tr>
			<?php endif; ?>
			<?php if ($this->params->get('edit_price', 0)): ?>
			<tr>
				<td class="key hasTip"
					title="<?php echo JText::_('EDIT XREF COURSE PRICE TIP'); ?>"><label
					for="course_price"><?php echo JText::_( 'EDIT XREF COURSE PRICE' ) .': '; ?></label>
				</td>
				<td><input type="text" size="8" maxlength="8" name="course_price"
					id="course_price" value="<?php echo $this->row->course_price; ?>" />
				</td>
			</tr>
			<?php endif; ?>
			<?php if ($this->params->get('edit_credits', 0)): ?>
			<tr>
				<td class="key hasTip"
					title="<?php echo JText::_('EDIT XREF COURSE CREDIT TIP'); ?>"><label
					for="course_credit"><?php echo JText::_( 'EDIT XREF COURSE CREDIT' ) .': '; ?></label>
				</td>
				<td><input type="text" size="8" maxlength="8" name="course_credit"
					id="course_credit" value="<?php echo $this->row->course_credit; ?>" />
				</td>
			</tr>
			<?php endif; ?>
			
			<?php if ($this->params->get('edit_customs', 0) && count($this->xcustoms)): ?>
	    <?php foreach ($this->xcustoms as $field): ?>
	    <tr>
	      <td class="key">
	        <label for="custom<?php echo $field->id; ?>" class="hasTip" title="<?php echo JText::_($field->get('name')).'::'. $field->get('tips'); ?>">
	          <?php echo JText::_( $field->name ); ?>:
	        </label>
	      </td>
	      <td>
	        <?php echo $field->render(); ?>
	      </td>   
	    </tr>
	    <?php endforeach; ?>
			<?php endif; ?>
	
			<?php endif; // xref details?>
			
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
	      </td>   
	    </tr>
	    <?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

</fieldset>


<?php if (( $this->elsettings->imageenabled == 2 ) || ($this->elsettings->imageenabled == 1)) : ?>
<fieldset class="re_fldst_image"><legend><?php echo JText::_('IMAGE'); ?></legend>
<?php
          if ($this->row->datimage) :
      		    echo ELOutput::flyer( $this->row, $this->dimage, 'event' );
      		else :
      		    echo JHTML::_('image', 'components/com_redevent/assets/images/noimage.png', JText::_('NO IMAGE'), array('class' => 'modal'));
      		endif;
        	?> <label for="userfile"><?php echo JText::_('IMAGE'); ?></label>
<input
	class="inputbox <?php echo $this->elsettings->imageenabled == 2 ? 'required' : ''; ?>"
	name="userfile" id="userfile" type="file" /> <small
	class="editlinktip hasTip"
	title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('MAX IMAGE FILE SIZE').' '.$this->elsettings->sizelimit.' kb'; ?>">
<?php echo $this->infoimage; ?> </small> <!--<div class="re_cur_image"><?php echo JText::_( 'CURRENT IMAGE' ); ?></div>
      		<div class="re_sre_image"><?php echo JText::_( 'SELECTED IMAGE' ); ?></div>-->
</fieldset>
<?php endif; ?>


<?php if ($this->params->get('edit_description', 0)): ?>
<fieldset class="description"><legend><?php echo JText::_('DESCRIPTION'); ?></legend>

<?php
      		//if usertyp min editor then editor else textfield
      		if ($this->editoruser) :
      			echo $this->editor->display('datdescription', $this->row->datdescription, '100%', '400', '70', '15', array('pagebreak', 'readmore') );
      		else :
      		?> <textarea style="width: 100%;" rows="10"
	name="datdescription" class="inputbox" wrap="virtual"
	onkeyup="berechne(this.form)"><?php echo $this->row->datdescription; ?></textarea><br />
<?php echo JText::_( 'NO HTML' ); ?><br />
<input disabled value="<?php echo $this->elsettings->datdesclimit; ?>"
	size="4" name="zeige" /><?php echo JText::_( 'AVAILABLE' ); ?><br />
<a href="javascript:rechne(document.eventform);"><?php echo JText::_( 'REFRESH' ); ?></a>
<?php endif; ?>
</fieldset>
<?php endif; ?>

<div class="re_save_buttons floattext">
<button type="submit" class="submit"
	onclick="return submitbutton('saveevent')"><?php echo JText::_('SAVE') ?>
</button>
<button type="reset" class="button cancel"
	onclick="submitbutton('cancelevent')"><?php echo JText::_('CANCEL') ?>
</button>
</div>

<p class="clear"><input type="hidden" name="id"
	value="<?php echo $this->row->id; ?>" /> <input type="hidden"
	name="returnid" value="<?php echo JRequest::getInt('returnid'); ?>" />
<input type="hidden" name="referer"
	value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" /> <input
	type="hidden" name="created" value="<?php echo $this->row->created; ?>" />
<input type="hidden" name="author_ip"
	value="<?php echo $this->row->author_ip; ?>" /> <input type="hidden"
	name="created_by" value="<?php echo $this->row->created_by; ?>" /> <input
	type="hidden" name="curimage"
	value="<?php echo $this->row->datimage; ?>" /> <?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="task" value="" /></p>
</form>

<p class="copyright">
    	<?php echo ELOutput::footer( ); ?>
    </p>

</div>
<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>