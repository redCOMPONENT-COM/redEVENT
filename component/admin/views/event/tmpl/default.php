<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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
JHTML::_('behavior.calendar');
?>
<script language="javascript" type="text/javascript">
	function submitbutton(task)
	{

		var form = document.adminForm;
		var datdescription = <?php echo $this->editor->getContent( 'datdescription' ); ?>

		if (task == 'cancel') {
			submitform( task );
		} else if (form.title.value == ""){
			alert( "<?php echo JText::_( 'ADD TITLE'); ?>" );
			form.title.focus();
		} else if (form.catsid.value == "0"){
			alert( "<?php echo JText::_( 'CHOOSE CATEGORY'); ?>" );
		} else {
			<?php
			echo $this->editor->save( 'datdescription' );
			?>
			$("meta_keywords").value = $keywords;
			$("meta_description").value = $description;
			// submit_unlimited();

			submitform( task );
		}
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php
echo $this->pane->startPane("det-pane");
echo $this->pane->startPanel( JText::_('EVENT'), 'event' );
	echo $this->loadTemplate('event');
	echo $this->pane->endPanel();
	$title = JText::_( 'VENUES' );
	echo $this->pane->startPanel( $title, 'venues' );
		echo $this->loadTemplate('venues');
	echo $this->pane->endPanel();
	$title = JText::_( 'SUBMIT_TYPES' );
	echo $this->pane->startPanel( $title, 'submit_types' );
		echo $this->loadTemplate('submission_types');
	echo $this->pane->endPanel();
	$title = JText::_( 'EMAILS' );
	echo $this->pane->startPanel( $title, 'emails' );
		echo $this->loadTemplate('emails');
	echo $this->pane->endPanel();
	$title = JText::_( 'FORM' );
	echo $this->pane->startPanel( $title, 'form' );
		if ($this->redform_install) {
			echo $this->loadTemplate('form');
		}
		else echo JText::_('REDFORM_NOT_INSTALLED');
	echo $this->pane->endPanel();
	$title = JText::_( 'SUBMISSION' );
	echo $this->pane->startPanel( $title, 'submission' );
		$k = 0;
		echo $this->loadTemplate('submission');
	echo $this->pane->endPanel();
	$title = JText::_( 'WAITINGLIST' );
	echo $this->pane->startPanel( $title, 'waitinglist' );
		$k = 0;
		echo $this->loadTemplate('waitinglist');
	echo $this->pane->endPanel();
	$title = JText::_( 'CONFIRMATION' );
	echo $this->pane->startPanel( $title, 'confirmation' );
		echo $this->loadTemplate('confirmation');
	echo $this->pane->endPanel();
	$title = JText::_( 'REGISTRATION' );
	echo $this->pane->startPanel( $title, 'registra' );
	$k = 0;
		echo $this->loadTemplate('registration');
	$title = JText::_( 'IMAGE' );
	echo $this->pane->endPanel();
	echo $this->pane->startPanel( $title, 'image' );
	$k = 0;
	?>
	<table class="adminform">
		<tr class="row<?php echo $k = 1 - $k; ?>">
			<td class="redevent_settings">
				<label for="image">
					<?php echo JText::_( 'CHOOSE IMAGE' ).':'; ?>
				</label>
			</td>
			<td>
				<?php echo $this->imageselect; ?>
			</td>
		</tr>
		<tr class="row<?php echo $k = 1 - $k; ?>">
			<td>&nbsp;</td>
			<td>
				<img src="../images/M_images/blank.png" name="imagelib" id="imagelib" width="80" height="80" border="2" alt="Preview" />
				<script language="javascript" type="text/javascript">
				if (document.forms[0].a_imagename.value!=''){
					var imname = document.forms[0].a_imagename.value;
					jsimg='../images/redevent/events/' + imname;
					document.getElementById('imagelib').src= jsimg;
				}
				</script>

				<br />
			</td>
		</tr>
	</table>
	<?php
	if (0) {
		$title = JText::_( 'RECURRING EVENTS' );
		echo $this->pane->endPanel();
		echo $this->pane->startPanel( $title, 'recurrence' );
		$k = 0;
		?>
			<table class="adminform">
				<tr class="row<?php echo $k = 1 - $k; ?>">
					<td class="redevent_settings_repeat"><?php echo JText::_( 'RECURRENCE' ); ?>:</td>
					<td>
					  <select id="recurrence_select" name="recurrence_select" size="1">
						<option value="0"><?php echo JText::_( 'NOTHING' ); ?></option>
						<option value="1"><?php echo JText::_( 'DAYLY' ); ?></option>
						<option value="2"><?php echo JText::_( 'WEEKLY' ); ?></option>
						<option value="3"><?php echo JText::_( 'MONTHLY' ); ?></option>
						<option value="4"><?php echo JText::_( 'WEEKDAY' ); ?></option>
					  </select>
					</td>
				</tr>
				<tr class="row<?php echo $k = 1 - $k; ?>">
					<td colspan="2" id="recurrence_output">&nbsp;</td>
				</tr>
				<tr id="counter_row" style="display:none;" class="row<?php echo $k = 1 - $k; ?>">
					<td><?php echo JText::_( 'RECURRENCE COUNTER' ); ?>:</td>
					<td>
						<?php echo JHTML::_('calendar', ($this->row->recurrence_counter <> 0000-00-00)? $this->row->recurrence_counter: JText::_( 'UNLIMITED' ), "recurrence_counter", "recurrence_counter"); ?><a href="#" onclick="include_unlimited('<?php echo JText::_( 'UNLIMITED' ); ?>'); return false;"><img src="../components/com_redevent/assets/images/unlimited.png" width="16" height="16" alt="<?php echo JText::_( 'UNLIMITED' ); ?>" /></a>
					</td>
				<tr>
			</table>
		<br/>
		<input type="hidden" name="recurrence_number" id="recurrence_number" value="<?php echo $this->row->recurrence_number; ?>" />
		<input type="hidden" name="recurrence_type" id="recurrence_type" value="<?php echo $this->row->recurrence_type; ?>" />
		<script type="text/javascript">
		<!--
			var $select_output = new Array();
			$select_output[1] = "<?php echo JText::_( 'OUTPUT DAY' ); ?>";
			$select_output[2] = "<?php echo JText::_( 'OUTPUT WEEK' ); ?>";
			$select_output[3] = "<?php echo JText::_( 'OUTPUT MONTH' ); ?>";
			$select_output[4] = "<?php echo JText::_( 'OUTPUT WEEKDAY' ); ?>";

			var $weekday = new Array();
			$weekday[0] = "<?php echo JText::_( 'MONDAY' ); ?>";
			$weekday[1] = "<?php echo JText::_( 'TUESDAY' ); ?>";
			$weekday[2] = "<?php echo JText::_( 'WEDNESDAY' ); ?>";
			$weekday[3] = "<?php echo JText::_( 'THURSDAY' ); ?>";
			$weekday[4] = "<?php echo JText::_( 'FRIDAY' ); ?>";
			$weekday[5] = "<?php echo JText::_( 'SATURDAY' ); ?>";
			$weekday[6] = "<?php echo JText::_( 'SUNDAY' ); ?>";

			var $before_last = "<?php echo JText::_( 'BEFORE LAST' ); ?>";
			var $last = "<?php echo JText::_( 'LAST' ); ?>";

			start_recurrencescript();
		-->
		</script>
		<?php
	}
	$title = JText::_( 'METADATA INFORMATION' );
	echo $this->pane->endPanel();
	echo $this->pane->startPanel( $title, 'meta' );
		$k = 0;
		echo $this->loadTemplate('metadata');
	echo $this->pane->endPanel();
echo $this->pane->endPane(); ?>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="events" />
<input type="hidden" name="view" value="event" />
<input type="hidden" name="task" value="" />
<?php if ($this->task == 'copy') { ?>
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="created" value="" />
	<input type="hidden" name="author_ip" value="" />
	<input type="hidden" name="created_by" value="" />
<?php } else { ?>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="created" value="<?php echo $this->row->created; ?>" />
	<input type="hidden" name="author_ip" value="<?php echo $this->row->author_ip; ?>" />
	<input type="hidden" name="created_by" value="<?php echo $this->row->created_by; ?>" />
<?php } ?>
</form>
<?php echo $this->loadTemplate('jsscript'); ?>

<p class="copyright">
	<?php echo ELAdmin::footer( ); ?>
</p>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>