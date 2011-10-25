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
function submitbutton(pressbutton)
{
	var form = document.adminForm;
	var catdescription = <?php echo $this->editor->getContent( 'catdescription' ); ?>
	
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	// do field validation
	if (form.catname.value == ""){
		alert( "<?php echo JText::_('COM_REDEVENT_ADD_NAME_CATEGORY' ); ?>" );
	} else {
		<?php echo $this->editor->save( 'catdescription' ); ?>
		submitform( pressbutton );
	}
}
</script>


<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >

	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
			<?php echo $this->tabs->startPane("det-pane"); ?>
			
			<?php	echo $this->tabs->startPanel( JText::_('COM_REDEVENT_EVENT_INFO_TAB'), 'info' ); ?>
				<table  class="adminform">
					<tr>
						<td>
							<label for="catname">
								<?php echo JText::_('COM_REDEVENT_CATEGORY' ).':'; ?>
							</label>
						</td>
						<td>
							<input name="catname" value="<?php echo $this->row->catname; ?>" size="50" maxlength="100" />
						</td>
						<td>
							<label for="published">
								<?php echo JText::_('COM_REDEVENT_PUBLISHED' ).':'; ?>
							</label>
						</td>
						<td>
							<?php
							$html = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $this->row->published );
							echo $html;
							?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="alias">
								<?php echo JText::_('COM_REDEVENT_Alias' ).':'; ?>
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="alias" id="alias" size="50" maxlength="100" value="<?php echo $this->row->alias; ?>" />
						</td>
            <td>
              <label for="color">
                <?php echo JText::_('COM_REDEVENT_COLOR' ).':'; ?>
              </label>
            </td>
            <td>
              <input class="inputbox" type="text" style="background: <?php echo ( $this->row->color == '' )?"transparent":$this->row->color; ?>;"
                     name="color" id="color" size="10" maxlength="20" value="<?php echo $this->row->color; ?>" />                   
              <input type="button" class="button" value="<?php echo JText::_('COM_REDEVENT_PICK' ); ?>" onclick="openPicker('color', -200, 20);" /> 
            </td>
					</tr>
				</table>

			<table class="adminform">
				<tr>
					<td>
						<?php
						// parameters : areaname, content, hidden field, width, height, rows, cols
						echo $this->editor->display( 'catdescription',  $this->row->catdescription, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ;
						?>
					</td>
				</tr>
			</table>
				<?php echo $this->tabs->endPanel(); ?>
				
				<?php echo $this->tabs->startPanel( JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'), 'attachments' ); ?>
				<?php echo $this->loadTemplate('attachments'); ?>
				<?php echo $this->tabs->endPanel(); ?>
				
				<?php echo $this->tabs->endPane(); ?>
				
			</td>
			<td valign="top" width="320px" style="padding: 7px 0 0 5px">
			<?php
			echo $this->pane->startPane( 'det-pane' );
			$title = JText::_('COM_REDEVENT_CATEGORIES' );
			echo $this->pane->startPanel( $title, 'categories' );
			?>
			<table>
				<tr>
					<td>
						<label for="categories">
							<?php echo JText::_('COM_REDEVENT_PARENT_CATEGORY' ).':'; ?>
						</label>
					</td>
					<td>
						<?php
						echo $this->lists['categories'];
						?>
					</td>
				</tr>
			</table>
			<?php
			echo $this->pane->endPanel();
			$title = JText::_('COM_REDEVENT_ACCESS' );
			echo $this->pane->startPanel( $title, 'access' );
			?>
			<table>
				<tr>
					<td>
						<label for="access">
							<?php echo JText::_('COM_REDEVENT_ACCESS' ).':'; ?>
						</label>
					</td>
					<td>
						<?php
						echo $this->lists['access'];
						?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="private" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_CATEGORY_PRIVATE_LABEL').'::'.JText::_('COM_REDEVENT_CATEGORY_PRIVATE_TIP'); ?>">
							<?php echo JText::_( 'COM_REDEVENT_CATEGORY_PRIVATE_LABEL' ).':'; ?>
						</label>
					</td>
					<td>
						<?php
						echo JHTML::_('select.booleanlist', 'private', '', $this->row->private);
						?>
					</td>
				</tr>
			</table>
			<?php
			echo $this->pane->endPanel();
			echo $this->pane->startPanel( JText::_('COM_REDEVENT_Frontend_event_submission'), 'access' );
			?>
			<table>
				<tr>
					<td>
						<label for="event_template" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_Category_Event_template' ).'::'.JText::_('COM_REDEVENT_Category_Event_template_tip' ); ?>">
							<?php echo JText::_('COM_REDEVENT_Category_Event_template' ).':'; ?>
						</label>
					</td>
					<td>
						<?php	$link = 'index.php?option=com_redevent&amp;view=xrefelement&amp;tmpl=component&amp;field=event_template'; ?>
						<div style="float: left;"><input style="background: #ffffff;" type="text" id="event_template_name" value="<?php echo ($this->row->event_template_name ? $this->row->event_template_name : JText::_('COM_REDEVENT_Default')); ?>" disabled="disabled" /></div>
						<div class="button2-left"><div class="blank">
							<a class="modal" title="<?php JText::_('COM_REDEVENT_Select'); ?>"  href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}"><?php echo JText::_('COM_REDEVENT_Select'); ?></a>
						</div></div>
						<div class="button2-left"><div class="blank">
							<a title="<?php JText::_('COM_REDEVENT_Reset'); ?>" id="ev-reset-button"><?php echo JText::_('COM_REDEVENT_Reset'); ?></a>
						</div></div>
						<input type="hidden" id="event_template" name="event_template" value="<?php echo $this->row->event_template; ?>" />
					</td>
				</tr>
			</table>
			<?php
			echo $this->pane->endPanel();
			$title = JText::_('COM_REDEVENT_GROUP' );
			echo $this->pane->startPanel( $title, 'group' );
			?>
			<table>
				<tr>
					<td>
						<label for="groups">
							<?php echo JText::_('COM_REDEVENT_GROUP' ).':'; ?>
						</label>
					</td>
					<td>
						<?php echo $this->lists['groups']; ?>
					</td>
				</tr>
			</table>
			<?php
			$title = JText::_('COM_REDEVENT_IMAGE' );
			echo $this->pane->endPanel();
			echo $this->pane->startPanel( $title, 'catimage' );
			?>
			<table>
				<tr>
					<td>
						<label for="image">
							<?php echo JText::_('COM_REDEVENT_CHOOSE_IMAGE' ).':'; ?>
						</label>
					</td>
					<td>
						<?php
							echo $this->imageselect;
						?>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<img src="../images/M_images/blank.png" name="imagelib" id="imagelib" width="80" height="80" border="2" alt="Preview" />
						<script language="javascript" type="text/javascript">
						if ($('a_imagename').value !=''){
							var imname = $('a_imagename').value;
							jsimg='../images/redevent/categories/' + imname;
							$('imagelib').src= jsimg;
						}
						</script>
						<br />
						<br />
					</td>
				</tr>
			</table>
			<?php
			$title = JText::_('COM_REDEVENT_METADATA_INFORMATION' );
			echo $this->pane->endPanel();
			echo $this->pane->startPanel( $title, 'metadata' );
			?>
		<table>
		<tr>
			<td>
				<label for="metadesc">
					<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION' ); ?>:
				</label>
				<br />
				<textarea class="inputbox" cols="40" rows="5" name="meta_description" id="metadesc" style="width:300px;"><?php echo str_replace('&','&amp;',$this->row->meta_description); ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<label for="metakey">
					<?php echo JText::_('COM_REDEVENT_META_KEYWORDS' ); ?>:
				</label>
				<br />
				<textarea class="inputbox" cols="40" rows="5" name="meta_keywords" id="metakey" style="width:300px;"><?php echo str_replace('&','&amp;',$this->row->meta_keywords); ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" class="button" value="<?php echo JText::_('COM_REDEVENT_ADD_CATNAME' ); ?>" onclick="f=document.adminForm;f.metakey.value=f.catname.value;" />
			</td>
		</tr>
		</table>

		<?php
		echo $this->pane->endPanel();
		echo $this->pane->endPane();
		?>
		</td>
	</tr>
</table>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="controller" value="categories" />
<input type="hidden" name="view" value="category" />
<input type="hidden" name="task" value="" />
</form>

<p class="copyright">
	<?php echo ELAdmin::footer( ); ?>
</p>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>