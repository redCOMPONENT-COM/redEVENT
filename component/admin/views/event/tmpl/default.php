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
JHTML::_('behavior.calendar');
JHTML::_('behavior.tooltip');
 
// for tooltips
$this->infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) );
?>
<script language="javascript" type="text/javascript">

    Window.onDomReady(function(){
      document.formvalidator.setHandler('categories',
        function (value) {
          if(value=="") {
            return false;
          } else {
            return true;
          }
        }
      );
    });
    
	function submitbutton(task)
	{
	
    if (task == 'cancel') {
      submitform( task );
      return;
    }
    
      var form = document.getElementById('adminForm');
      var validator = document.formvalidator;
      var title = $(form.title).getValue();
      title.replace(/\s/g,'');
      
      if ( title.length==0 ) {
          alert("<?php echo JText::_( 'ADD TITLE', true ); ?>");
          validator.handleResponse(false,form.title);
          return false;
      } else if ( validator.validate(form.categories) === false ) {
          alert("<?php echo JText::_( 'SELECT CATEGORY', true ); ?>");
          validator.handleResponse(false,form.categories);
          return false;
      } else {
      <?php
      echo $this->editor->save('datdescription');
      ?>
			$("meta_keywords").value = $keywords;
			$("meta_description").value = $description;
			// submit_unlimited();

			submitform( task );
		}
	}
	
	// for xref update script
	var edittext = "<?php echo JText::_('EDIT'); ?>";
  var confirmremove = "<?php echo JText::_('REMOVE_DATE_TIME_BLOCK'); ?>";
  var textremove = "<?php echo JText::_('REMOVE'); ?>";
  var textyes = "<?php echo JText::_('YES'); ?>";
  var textno = "<?php echo JText::_('NO'); ?>";
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php
echo $this->pane->startPane("det-pane");

	echo $this->pane->startPanel( JText::_('EVENT'), 'event' );
	echo $this->loadTemplate('event');
	echo $this->pane->endPanel();
	
	$title = JText::_( 'Sessions' );
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
	echo $this->pane->endPanel();
	
	$title = JText::_( 'PAYMENT' );
  $k = 0;
	echo $this->pane->startPanel( $title, 'payment' );
 	echo $this->loadTemplate('payment');
	echo $this->pane->endPanel();
	
	$title = JText::_( 'IMAGE' );
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
				if ($('a_imagename').value !=''){
					var imname = $('a_imagename').value;
					jsimg='../images/redevent/events/' + imname;
					$('imagelib').src= jsimg;
				}
				</script>

				<br />
			</td>
		</tr>
	</table>
	<?php
	echo $this->pane->endPanel();
	
  $title = JText::_( 'CUSTOM FIELDS' );
  echo $this->pane->startPanel( $title, 'customfields' );
  $k = 0;
  echo $this->loadTemplate('customfields');
  echo $this->pane->endPanel();
  
	$title = JText::_( 'METADATA INFORMATION' );
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