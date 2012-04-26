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
$this->infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) );
?>
<script language="javascript" type="text/javascript">

    window.addEvent('domready', function(){
      document.formvalidator.setHandler('categories',
        function (value) {
          if(value=="") {
            return false;
          } else {
            return true;
          }
        }
      );
      
			$('datimage').addEvent('change', function(){
				if (this.get('value')) {
					$('imagelib').empty().adopt(
							new Element('img', {
								src: '../'+this.get('value'),
								class: 're-image-preview',
								alt: 'preview'
							}));
				}
				else {
					$('imagelib').empty();
				}
			}).fireEvent('change');
    });
    
	Joomla.submitbutton = function(task)
	{
	
    if (task == 'cancel') {
    	Joomla.submitform( task );
      return true;
    }
    
      var form = document.getElementById('adminForm');
      var validator = document.formvalidator;
      var title = $(form.title).get('value');
      title.replace(/\s/g,'');
      
      if ( title.length==0 ) {
          alert("<?php echo JText::_('COM_REDEVENT_ADD_TITLE', true ); ?>");
          validator.handleResponse(false,form.title);
          return false;
      } else if ( validator.validate(form.categories) === false ) {
          alert("<?php echo JText::_('COM_REDEVENT_SELECT_CATEGORY', true ); ?>");
          validator.handleResponse(false,form.categories);
          return false;
      } else if ( $('activate1').getProperty('checked') &&  $('notify0').getProperty('checked')) {
          alert("<?php echo JText::_('COM_REDEVENT_EVENT_ACTIVATION_REQUIRES_NOTIFICATION_ENABLED', true ); ?>");
          validator.handleResponse(false,form.activate1);
          return false;
      } else if (document.formvalidator.isValid(form) === false) {
          var msg = '<?php echo JText::_('COM_REDEVENT_EVENT_FORM_INVALID'); ?>';
     
          alert(msg);
       } else {
      <?php
      echo $this->editor->save('datdescription');
      ?>
			$("meta_keywords").value = $keywords;
			$("meta_description").value = $description;
			// submit_unlimited();

			Joomla.submitform( task );
		}
	};
	
	// for xref update script
	var edittext = "<?php echo JText::_('COM_REDEVENT_EDIT'); ?>";
  var confirmremove = "<?php echo JText::_('COM_REDEVENT_REMOVE_DATE_TIME_BLOCK'); ?>";
  var textremove = "<?php echo JText::_('COM_REDEVENT_REMOVE'); ?>";
  var textyes = "<?php echo JText::_('COM_REDEVENT_YES'); ?>";
  var textno = "<?php echo JText::_('COM_REDEVENT_NO'); ?>";
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >
<?php
echo $this->pane->startPane("det-pane");

	echo $this->pane->startPanel( JText::_('COM_REDEVENT_EVENT'), 'event' );
	echo $this->loadTemplate('event');
	echo $this->pane->endPanel();

	if (!$this->row->id)
	{
		$title = JText::_( 'COM_REDEVENT_SESSION' );
		echo $this->pane->startPanel( $title, 'session' );
		echo $this->loadTemplate('session');
		echo $this->pane->endPanel();
	}

	if (count($this->customfields))
	{
		$title = JText::_('COM_REDEVENT_CUSTOM_FIELDS' );
	  echo $this->pane->startPanel( $title, 'customfields' );
	  echo $this->loadTemplate('customfields');
	  echo $this->pane->endPanel();
	}
	
	$title = JText::_('COM_REDEVENT_REGISTRATION' );
	echo $this->pane->startPanel( $title, 'registra' );
 	echo $this->loadTemplate('registration');
	echo $this->pane->endPanel();
	
	$title = JText::_('COM_REDEVENT_SUBMIT_TYPES' );
	echo $this->pane->startPanel( $title, 'submit_types' );
	echo $this->loadTemplate('submission_types');
	echo $this->pane->endPanel();
		
	$title = JText::_('COM_REDEVENT_ACTIVATION' );
	echo $this->pane->startPanel( $title, 'activation' );
	echo $this->loadTemplate('activation');
	echo $this->pane->endPanel();
	
	$title = JText::_('COM_REDEVENT_CONFIRMATION' );
	echo $this->pane->startPanel( $title, 'confirmation' );
	echo $this->loadTemplate('confirmation');
	echo $this->pane->endPanel();
	
	$title = JText::_('COM_REDEVENT_PAYMENT' );
	echo $this->pane->startPanel( $title, 'payment' );
 	echo $this->loadTemplate('payment');
	echo $this->pane->endPanel();
	
  $title = JText::_( 'COM_REDEVENT_EVENT_ATTACHMENTS_TAB' );
  echo $this->pane->startPanel( $title, 'attachments' );
  echo $this->loadTemplate('attachments');
  echo $this->pane->endPanel();
  
	$title = JText::_('COM_REDEVENT_METADATA_INFORMATION' );
	echo $this->pane->startPanel( $title, 'meta' );
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
<?php } else { ?>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="created" value="<?php echo $this->row->created; ?>" />
	<input type="hidden" name="author_ip" value="<?php echo $this->row->author_ip; ?>" />
<?php } ?>
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>