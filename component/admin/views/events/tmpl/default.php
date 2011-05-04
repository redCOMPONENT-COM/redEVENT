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
$app = &JFactory::getApplication();
?>
<?php if (!count( $this->rows )):?>
<p><?php echo JHTML::link('index.php?option=com_redevent&task=sampledata', JText::_('Add sample data')); ?></p>
<?php endif;?>
<form action="index.php" method="post" name="adminForm">

	<table class="adminform">
		<tr>
			<td width="100%">
				<?php
				echo JText::_( 'SEARCH' );
				echo $this->lists['filter'];
				?>
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->lists['state'];	?>
			</td>
		</tr>
	</table>

	<?php
	//Twit redirect will only be used if the twit_id is set (when the autotweetredevent plugin is installed)
	if (JRequest::getVar('twit_id'))
	{
	?>
	<table class="adminlist">
		<tr>
			<td>
				<script>
				<?php
				echo "window.location.href='index.php?option=".JRequest::getVar('option')."&view=events&controller=events&task=twitRedirect&message=".JRequest::getVar('message')."';";
				?>
				</script>
			</td>
		</tr>
	</table>
	<?php
	}
	else
	{
	?>

		<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th width="5"><?php echo JText::_( 'Num' ); ?></th>
				<th width="5"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'TIMEDETAILS', 'x.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="title"><?php echo JHTML::_('grid.sort', 'EVENT TITLE', 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'CATEGORY', 'cat.catname', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			    <th width="1%" nowrap="nowrap"><?php echo JText::_( 'PUBLISHED' ); ?></th>
				<th class="title"><?php echo JText::_( 'CREATION' ); ?></th>
				<th width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'ID', 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pageNav->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>

		<tbody>
			<?php
			$k = 0;
			for($i=0, $n=count( $this->rows ); $i < $n; $i++) {
				$row = &$this->rows[$i];

				$link 			= 'index.php?option=com_redevent&amp;controller=events&amp;task=edit&amp;cid[]='.$row->id;

				$checked 	= JHTML::_('grid.checkedout', $row, $i );
				$published 	= JHTML::_('grid.published', $row, $i );
   			?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
				<td><?php echo $checked; ?></td>
				<td><div class="timedetails">
				<?php if (isset($this->eventvenues[$row->id])) { ?>
					<table class="adminlist">
					<colgroup class="colfeatured" width="5%"></colgroup>
					<colgroup class="colvenue" width="20%"></colgroup>
					<colgroup class="colcity" width="20%"></colgroup>
					<colgroup class="coldate" width="20%"></colgroup>
					<colgroup class="coltime" width="10%"></colgroup>
					<colgroup class="colnote" width="20%"></colgroup>
					<colgroup class="colpublished" width="5px"></colgroup>
					<colgroup class="colattendees" width="5px"></colgroup>
					<thead>
					 <tr>
					   <th>&nbsp;</th>
					   <th><?php echo JText::_('VENUE'); ?></th>
					   <th><?php echo JText::_('CITY'); ?></th>
					   <th><?php echo JText::_('DATE'); ?></th>
					   <th><?php echo JText::_('TIME'); ?></th>
					   <th><?php echo JText::_('NOTE'); ?></th>
					   <th><?php echo JText::_('STATE'); ?></th>
             <th><?php echo JText::_('ATTENDEES'); ?></th>
					 </tr>
				  </thead>
					<tbody>
					<?php
						foreach ($this->eventvenues[$row->id] as $key => $eventdetails) 
						{				
							/* Get the date */
							$date = (!redEVENTHelper::isValidDate($eventdetails->dates) ? Jtext::_('Open date') : strftime( $this->elsettings->formatdate, strtotime( $eventdetails->dates )));
							if (redEVENTHelper::isValidDate($eventdetails->enddates) && $eventdetails->enddates != $eventdetails->dates ) {
							  $enddate   = strftime( $this->elsettings->formatdate, strtotime( $eventdetails->enddates ));
							}
							else {
							 $enddate = null;
							}
							$displaydate = $date. ($enddate ? ' - '.$enddate: '');
							
              $displaytime = '';
							/* Get the time */
							if (isset($eventdetails->times)) 
							{
							 $displaytime = strftime( $this->elsettings->formattime, strtotime( $eventdetails->times )).' '.$this->elsettings->timename;
							 
                if (isset($eventdetails->endtimes)) {
                  $displaytime .= ' - '.strftime( $this->elsettings->formattime, strtotime( $eventdetails->endtimes )). ' '.$this->elsettings->timename;
                }
							}
							// published state
							if ($eventdetails->published == 1) {
								$img_published = JHTML::image('administrator/images/tick.png', JText::_('PUBLISHED'));
							}
							else if ($eventdetails->published == 0) {
								$img_published = JHTML::image('administrator/images/publish_x.png', JText::_('UNPUBLISHED'));
							}
							else {
								$img_published = JHTML::image('administrator/images/publish_y.png', JText::_('ARCHIVED'));
							}
							if ($eventdetails->featured == 1) {
								$img_featured = JHTML::image('administrator/components/com_redevent/assets/images/icon-16-featured.png', JText::_('COM_REDEVENT_SESSION_FEATURED'));
							}
							else {
								$img_featured = '&nbsp;';
							}
							?>
    					<tr class="eventdatetime<?php echo ($eventdetails->featured ? ' featured' : ''); ?>">
    						<td><?php echo $img_featured; ?></td>
    						<td><?php echo $eventdetails->venue; ?></td>
    						<td><?php echo $eventdetails->city; ?></td>
    						<td><?php echo $displaydate; ?></td>
    						<td><?php echo $displaytime; ?></td>
    						<td><?php echo $eventdetails->note; ?></td>
    						<td><?php echo $img_published; ?></td>
    						<td>
    						  <?php	if ($row->registra == 1):
      						  $linkreg  = 'index.php?option=com_redevent&amp;view=attendees&eventid='.$row->id.'&xref='.$eventdetails->id;
      						  ?>
      						  <a href="<?php echo $linkreg; ?>" title="Edit Users"><?php echo $eventdetails->regcount; ?></a>
      						<?php else: ?> 
      						  <img src="images/publish_x.png" width="16" height="16" border="0" alt="Registration disabled" />
      						<?php endif; ?>
    						</td>
    					</tr>
    					<?php
						  }
						?>
					</tbody>
					</table>
				<?php } ?>
				</div></td>
				<td>
					<div>
					<?php
					if ( $row->checked_out && ( $row->checked_out != $this->user->get('id') ) ) {
						echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
					} else {
						?>
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EDIT EVENT' );?>::<?php echo $row->title; ?>">
						<a href="<?php echo $link; ?>">
							<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8'); ?>
						</a></span>
						<?php
					}
					?>	 

					<br />

					<?php
					if (JString::strlen($row->alias) > 25) {
						echo JString::substr( htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8'), 0 , 25).'...';
					} else {
						echo htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8');
					}
					?>
					</div>
					<div class="linkfront"><?php echo JHTML::link($app->getSiteUrl().RedeventHelperRoute::getDetailsRoute($row->id), 
					                        JHTML::image('administrator/components/com_redevent/assets/images/linkfront.png', 
					                                     JText::_('COM_REDEVENT_EVENT_FRONTEND_LINK'))); ?>
					</div>
				</td>
				<td>
					<?php
					//$cats_html = array();
					foreach ((array) $row->categories as $k => $cat)
					{
						if ($cat->checked_out && ( $cat->checked_out != $this->user->get('id') ) ) {
              echo htmlspecialchars($cat->catname, ENT_QUOTES, 'UTF-8');
            } else {
              $catlink    = 'index.php?option=com_redevent&amp;controller=categories&amp;task=edit&amp;cid[]='.$cat->id;
		          ?>
		            <span class="editlinktip hasTip" title="<?php echo JText::_( 'EDIT CATEGORY' );?>::<?php echo $cat->catname; ?>">
		            <a href="<?php echo $catlink; ?>">
		              <?php echo htmlspecialchars($cat->catname, ENT_QUOTES, 'UTF-8'); ?>
		            </a></span>
		          <?php
		          if ($k < count($row->categories)-1) {
		            echo "<br/>";
		          }
            }
					}
					?>
				</td>
				<td align="center"><?php echo $published; ?></td>
				<td>
					<?php echo JText::_( 'AUTHOR' ).': '; ?><a href="<?php echo 'index.php?option=com_users&amp;task=edit&amp;hidemainmenu=1&amp;cid[]='.$row->created_by; ?>"><?php echo $row->author; ?></a><br />
					<?php echo JText::_( 'EMAIL' ).': '; ?><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a><br />
					<?php
					$created	 	= JHTML::Date( $row->created, JText::_( 'DATE_FORMAT_LC2' ) );
					$edited 		= JHTML::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
					$ip				= $row->author_ip == 'DISABLED' ? JText::_( 'DISABLED' ) : $row->author_ip;
					$image 			= JHTML::_('image', 'administrator/templates/'. $this->template .'/images/menu/icon-16-info.png', JText::_('NOTES') );
					$overlib 		= JText::_( 'CREATED AT' ).': '.$created.'<br />';
					$overlib		.= JText::_( 'WITH IP' ).': '.$ip.'<br />';
					if ($row->modified != '0000-00-00 00:00:00') {
						$overlib 	.= JText::_( 'EDITED AT' ).': '.$edited.'<br />';
						$overlib 	.= JText::_( 'EDITED FROM' ).': '.$row->editor.'<br />';
					}
					?>
					<span class="editlinktip hasTip" title="<?php echo JText::_('EVENT STATS'); ?>::<?php echo $overlib; ?>">
						<?php echo $image; ?>
					</span>
				</td>
				<td align="center"><?php echo $row->id; ?></td>
			</tr>
			<?php $k = 1 - $k;  } ?>

		</tbody>
	</table>

	<p class="copyright">
		<?php echo ELAdmin::footer( ); ?>
	</p>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="view" value="events" />
	<input type="hidden" name="controller" value="events" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php
	}
	?>
</form>