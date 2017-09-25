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

$helper = new PlgSystemAesir_Redevent_SyncSyncSessions;
?>
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="my-managed-sessions" class="redevent-ajaxnav">
	<?php if ($this->params->get('filter_text',1) || $this->params->get('display_limit_select') || $this->params->get('showeventfilter')) : ?>
	<div id="el_filter" class="floattext row">
		<?php if ($this->params->get('showeventfilter', 1)) : ?>
		<div class="col-xs-6 col-lg-5">
			<?php echo $this->lists['filter_event']; ?>
		</div>
		<?php endif; ?>
		<?php if ($this->params->get('filter_text',1)) : ?>
		<div class="col-xs-6 col-lg-5 col-lg-push-2">
			<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="input" 
			placeholder="<?php echo JText::_('COM_AESIR_SEARCH' ); ?>..."/>
			<button type="button" id="filter-go"><?php echo JText::_('COM_REDEVENT_GO' ); ?></button>
			<button type="reset" class="hidden" id="filter-reset"><?php echo JText::_('COM_REDEVENT_RESET' ); ?></button>
		</div>
		<?php endif; ?>
		<?php if ($this->params->get('display_limit_select')) : ?>
		<div class="hidden">
			<?php
			echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
			echo $this->sessions_pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<div class="table-responsive el_content">
		<div class="table-header">
			<div class="table-header-title"><?php echo JText::_('COM_REDEVENT_MYEVENTS_SESSIONS'); ?></div>
			<?php if ($this->canAddXref): ?>
			<?php echo JHTML::link('index.php?option=com_redevent&task=editsession.add' . $this->returnAppend . '&Itemid=' . $itemId, JText::_('COM_REDEVENT_MYEVENTS_ADD_NEW_EVENT_SESSION')); ?>
			<?php endif; ?>
		</div>
		<?php if (empty($this->sessions)): ?>
			<div class="alert alert-info dark-blue">
				<div class="pagination-centered">
					<h3><?php echo JText::_('COM_REDITEM_NOTHING_TO_DISPLAY' ); ?></h3>
				</div>
			</div>
		<?php else: ?>
		<table class="eventtable table clean-table" summary="eventlist">
			<colgroup>
			<col class="el_col_date" />
			<?php if ($this->params->get('showtitle', 1)) : ?>
			<col class="el_col_title" />
			<?php endif; ?>
			<?php if ($this->params->get('showlocate', 1)) : ?>
			<col class="el_col_venue" />
			<?php endif; ?>
			<?php if ($this->params->get('showcity', 0)) : ?>
			<col class="el_col_city" />
			<?php endif; ?>
			<?php if ($this->params->get('showstate', 0)) : ?>
			<col class="el_col_state" />
			<?php endif; ?>
			<?php if ($this->params->get('showcat', 1)) : ?>
			<col class="el_col_category" />
			<?php endif; ?>
			<?php if ($this->params->get('showcode', 1)): ?>
			<col width="10" class="el_col_code" />
			<?php endif; ?>
			<col width="5" class="el_col_attendees" />
			<col width="5" class="el_col_edit" />
			<col width="5" class="el_col_published" />
			<col width="5" class="el_col_delete" />
			</colgroup>
			<thead>
				<tr>
					<th id="el_date" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_DATE'), 'x.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					<?php
					if ($this->params->get('showtitle', 1)) :
					?>
					<th id="el_title" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_TITLE'), 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					<?php
					endif;
					if ($this->params->get('showlocate', 1)) :
					?>
					<th id="el_location" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_VENUE'), 'l.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					<?php
					endif;
					if ($this->params->get('showcity', 0)) :
					?>
					<th id="el_city" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_CITY'), 'l.city', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					<?php
					endif;
					if ($this->params->get('showstate', 0)) :
					?>
					<th id="el_state" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_STATE'), 'l.state', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					<?php
					endif;
					if ($this->params->get('showcat', 1)) :
					?>
					<th id="el_category" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_CATEGORY'), 'c.name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					<?php
					endif;
					?>
					<?php if ($this->params->get('showcode', 1)): ?>
					<th id="el_code" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Code'); ?></th>
					<?php endif; ?>
					<th id="el_attendees" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Booked'); ?></th>
					<!-- <th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Edit'); ?></th> -->
					<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Published'); ?></th>
					<th id="el_action" class="sectiontableheader empty" align="left">&nbsp;</th>
					<!-- <th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Delete'); ?></th> -->
                    <th id="el_aesir_item" class="sectiontableheader" align="left"><?= JText::_('COM_REDEVENT_AESIR_SYNC_LABEL_ITEM') ?></th>
                </tr>
			</thead>
			<tbody>
				<?php
				if (count((array)$this->sessions) == 0) :
				?>
				<tr align="center"><td><?php echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?></td></tr>
				<?php
				else :
				$i = 0;
				foreach ((array) $this->sessions as $row) :
				    $editsessionLink = RedeventHelperRoute::getEditSessionTaskRoute($row->slug, $row->xref);
					$itemEntity = $helper->getAesirSessionItem($row->id);
				?>
				<tr class="sectiontableentry<?php echo $i +1 . $this->params->get( 'pageclass_sfx' ); ?>" >
					<td headers="el_date" align="left">
						<?php if ($this->acl->canEditXref($row->xref)): ?>
						<?php echo JHTML::link($editsessionLink,
											RedeventHelperDate::formatEventDateTime($row),
											array('class' => 'hasTooltip',
						'title' => JText::_('COM_REDEVENT_EDIT_XREF' ).'<br/>'.JText::_('COM_REDEVENT_EDIT_XREF_TIP' )));	?>
						<?php else: ?>
						<?php echo RedeventHelperDate::formatEventDateTime($row);	?>
						<?php endif; ?>
					</td>
					<?php
					//Link to details
					$detaillink = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug));
					//title
					?>
					<td headers="el_title" align="left" valign="top">
						<!-- <a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape(RedeventHelper::getSessionFullTitle($row)); ?></a> -->
						<div><?php echo $this->escape(RedeventHelper::getSessionFullTitle($row)); ?></div>
					</td>
					<?php if ($this->params->get('showlocate', 1)) :	?>
					<td headers="el_location" align="left" valign="top">
						<div>
						<?php
						if ($this->params->get('showlinkvenue',1) == 1 ) :
							echo $row->locid != 0 ? "<a href='".JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>" : '-';
						else :
							echo $row->locid ? $this->escape($row->venue) : '-';
						endif;
						?>
						</div>
					</td>
					<?php
					endif;
					if ($this->params->get('showcity', 0)) :
					?>
					<td headers="el_city" align="left" valign="top"><div><?php echo $row->city ? $this->escape($row->city) : '-'; ?></div></td>
					<?php
					endif;
					if ($this->params->get('showstate', 0)) :
					?>
					<td headers="el_state" align="left" valign="top"><div><?php echo $row->state ? $this->escape($row->state) : '-'; ?></div></td>
					<?php
					endif;
					if ($this->params->get('showcat', 1)) : ?>
					<td headers="el_category" align="left" valign="top">
						<div>
						<?php foreach ($row->categories as $k => $cat): ?>
								<?php if ($this->params->get('catlinklist', 1) == 1) : ?>
								<a href="<?php echo JRoute::_(RedeventHelperRoute::getCategoryEventsRoute($cat->slug)); ?>">
									<?php echo $cat->name ? $this->escape($cat->name) : '-' ; ?>
								</a>
								<?php else: ?>
								<?php echo $cat->name ? $this->escape($cat->name) : '-'; ?>
								<?php endif; ?>
								<?php echo ($k < count($row->categories)) ? '<br/>' : '' ; ?>
						<?php endforeach; ?>
						</div>
					</td>
					<?php endif; ?>
					<?php if ($this->params->get('showcode', 1)): ?>
					<td headers="el_code" align="left" valign="top"><div><?php echo $this->escape(RedeventHelper::getSessionCode($row)); ?></div></td>
					<?php endif; ?>
					<td headers="el_edit" align="left" valign="top"><div><?php echo $row->registered.($row->maxattendees ? '/'.$row->maxattendees : ''); ?></div></td>
					<!-- <td headers="el_edit" align="left" valign="top"><?php echo $this->eventeditbutton($row->slug, $row->xref); ?></td> -->
					<td headers="el_edit" align="left" valign="middle">
						<?php if ($row->published == '1'): ?>
						<div class="el_publish el_icon">
							<?php if ($this->acl->canEditXref($row->xref)): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&task=unpublishxref&xref='. $row->xref, JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' ))); ?>
							<?php else: ?>
							<?php echo JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' )); ?>
							<?php endif; ?>
						</div>
						<?php elseif ($row->published == '0'):?>
						<div class="el_unpublish el_icon">
							<?php if ($this->acl->canEditXref($row->xref)): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&task=publishxref&xref='. $row->xref, JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' ))); ?>
							<?php else: ?>
							<?php echo JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' )); ?>
							<?php endif; ?>
						</div>
						<?php endif;?>
					</td>
					<!-- <td headers="el_delete" align="left" valign="top">
						<?php if ($this->acl->canEditXref($row->xref)): ?>
						<?php echo $this->xrefdeletebutton($row->xref); ?>
						<?php endif; ?>
					</td> -->
					<td headers="el_action" align="left" valign="middle">
						<div class="el_action">
							<div class="el_edit el_icon">
								<?php echo $this->eventeditbutton($row->slug, $row->xref); ?>
							</div>
							<div class="el_delete el_icon">
								<?php if ($this->acl->canEditXref($row->xref)): ?>
								<?php echo $this->xrefdeletebutton($row->xref); ?>
								<?php endif; ?>
							</div>
						</div>
					</td>
                    <td headers="el_aesir_item">
						<?php if ($itemEntity->isValid()): ?>
							<?php if ($itemEntity->canEdit()): ?>
                                <a href="<?= $itemEntity->getEditLink() ?>"><span class="fa fa-edit"/>edit</a>
							<?php else: ?>
                                <a href="<?= $itemEntity->getLink() ?>"><span class="fa fa-view"/>view</a>
							<?php endif; ?>
						<?php endif; ?>
                    </td>
				</tr>
				<?php
				$i = 1 - $i;
				endforeach;
				endif;
				?>
			</tbody>
		</table>
		<!--pagination-->
		<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->sessions_pageNav->get('pages.total') > 1)) : ?>
			<div class="footpagination">
				<div class="pagination">
					<?php echo $this->sessions_pageNav->getPagesLinks(); ?>
				</div>
			</div>
		<?php  endif; ?>
		<!-- pagination end -->
		<?php endif; ?>
		<input type="hidden" name="limitstart" value="<?php echo $this->lists['limitstart']; ?>" class="redajax_limitstart" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" class="redajax_order"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" class="redajax_order_dir"/>
		<input type="hidden" name="task" value="myevents.managedsessions" />
	</div>
</form>