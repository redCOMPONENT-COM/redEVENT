<?php
/**
* @version 1.0 $Id: default.php 30 2009-05-08 10:22:21Z roland $
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
defined( '_JEXEC' ) or die( 'Restricted access' );
$jinput = JFactory::getApplication()->input;
$itemId = $jinput->get('Itemid', 'default_value', 'filter');
?>
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="my-managed-venues" class="table-responsive redevent-ajaxnav">
	<div class="table-header">
		<div class="table-header-title"><?php echo JText::_('COM_REDEVENT_MYEVENTS_VENUES'); ?></div>
		<?php if ($this->canAddVenue): ?>
		<div><?php echo JHTML::link('index.php?option=com_redevent&task=editvenue.add' . $this->returnAppend . '&Itemid=' . $itemId, JText::_('COM_REDEVENT_MYEVENTS_ADD_NEW_VENUE')); ?></div>
		<?php endif; ?>
	</div>
	<?php if (count((array) $this->venues) == 0): ?>
		<div class="alert alert-info dark-blue">
			<div class="pagination-centered">
				<h3><?php echo JText::_('COM_REDITEM_NOTHING_TO_DISPLAY' ); ?></h3>
			</div>
		</div>
	<?php else: ?>
	<table class="eventtable table clean-table" summary="venues list">
		<thead>
			<tr>
				<th id="el_title" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_VENUE'); ?></th>
				<th id="el_city" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_CITY'); ?></th>
				<th id="el_published" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_PUBLISHED'); ?></th>
				<th id="el_action" class="sectiontableheader" align="left"></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 0;
			foreach ((array) $this->venues as $row) :
			?>
			<?php $link = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug)); ?>
			<tr class="sectiontableentry<?php echo $i + 1 . $this->params->get( 'pageclass_sfx' ); ?>" >
				<!-- <td headers="el_title" align="left" valign="top"><?php echo JHTML::link($link, $row->venue); ?></td> -->
				<td headers="el_title" align="left" valign="top"><div><?php echo $row->venue ?></div></td>
				<td headers="el_city" align="left" valign="top"><div><?php echo $row->city ? $row->city : '-'; ?></div></td>
				<td headers="el_published" align="center" valign="middle">
					<?php if ($row->published == '1'): ?>
						<div class="el_publish el_icon">
						<?php if ($this->acl->canPublishVenue($row->id)): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&task=myevents.unpublishvenue&id='. $row->id, JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' ))); ?>
						<?php else: ?>
							<?php echo JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' )); ?>
						<?php endif; ?>
						</div>
					<?php elseif ($row->published == '0'):?>
						<div class="el_unpublish el_icon">
						<?php if ($this->acl->canPublishVenue($row->id)): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&task=myevents.publishvenue&id='. $row->id, JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' ))); ?>
						<?php else: ?>
							<?php echo JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' )); ?>
						<?php endif; ?>
						</div>
					<?php endif;?>
				</td>
				<td headers="el_action" align="left" valign="middle">
					<?php if ($this->acl->canEditVenue($row->id)): ?>
						<div class="el_action">
								<div class="el_edit el_icon">
									<?php echo $this->venueeditbutton($row->id); ?>
								</div>
								<div class="el_delete el_icon">
									<?php echo JHTML::link('index.php?option=com_redevent&task=myevents.deletevenue&id='. $row->id, RHelperAsset::load('no.png', null, array('alt' => JText::_('COM_REDEVENT_DELETE')))); ?>
								</div>
						</div>
					<?php endif; ?>
				</td>
			</tr>
			<?php
			$i = 1 - $i;
			endforeach;
			?>
		</tbody>
	</table>
	<!--pagination-->
	<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->venues_pageNav->get('pages.total') > 1)) : ?>
	<div class="pagination">
		<?php echo $this->venues_pageNav->getPagesLinks(); ?>
	</div>
	<?php  endif; ?>
	<!-- pagination end -->
	<?php endif; ?>
	<input type="hidden" name="limitstart" value="<?php echo $this->lists['limitstart']; ?>" class="redajax_limitstart" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" class="redajax_order"/>
	<input type="hidden" name="filter_order_Dir" value="" class="redajax_order_dir"/>
	<input type="hidden" name="task" value="myevents.managedvenues" />
</form>