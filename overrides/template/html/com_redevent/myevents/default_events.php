<?php
/**
* @package    Redevent.Site
* @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
* @license    GNU General Public License version 2 or later, see LICENSE.
*/
defined('_JEXEC') or die( 'Restricted access' );
$jinput = JFactory::getApplication()->input;
$itemId = $jinput->get('Itemid', 'default_value', 'filter');

$helper = new PlgSystemAesir_Redevent_SyncSyncEvents;
?>
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="my-managed-events" class="table-responsive redevent-ajaxnav">
	<div id="el_filter" class="floattext">
		<div class="hidden">
			<input type="text" name="filter" id="filter"
			value="<?php echo $this->lists['filter'];?>" class="inputbox" placeholder="<?php echo JText::_('COM_REDEVENT_SEARCH'); ?>"/>
			<button type="button" id="filter-go"><?php echo JText::_('COM_REDEVENT_GO'); ?></button>
			<button type="reset" id="filter-reset"><?php echo JText::_('COM_REDEVENT_RESET'); ?></button>
		</div>
		<?php if ($this->params->get('display_limit_select')) : ?>
		<div class="hidden">
			<?php
				echo '<label for="limit">' . JText::_('COM_REDEVENT_DISPLAY_NUM') . '</label>&nbsp;';
				echo $this->events_pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
	</div>
	<div class="el_content">
		<div class="table-header">
			<div class="table-header-title"><?php echo JText::_('COM_REDEVENT_MYEVENTS_EVENTS'); ?></div>
			<?php if ($this->canAddEvent): ?>
			<div><?php echo JHTML::link('index.php?option=com_redevent&task=editevent.add' . $this->returnAppend . '&Itemid=' . $itemId, JText::_('COM_REDEVENT_MYEVENTS_ADD_NEW_EVENT')); ?></div>
			<?php endif; ?>
		</div>
		<?php if (empty($this->events)): ?>
			<div class="alert alert-info dark-blue">
				<div class="pagination-centered">
					<h3><?php echo JText::_('COM_REDITEM_NOTHING_TO_DISPLAY' ); ?></h3>
				</div>
			</div>
		<?php else: ?>
		<table class="eventtable table clean-table" summary="eventlist">
			<colgroup>
			<?php if ($this->params->get('showtitle', 1)) : ?>
			<col class="el_col_title" />
			<?php endif; ?>
			<?php if ($this->params->get('showcat', 1)) : ?>
			<col class="el_col_category" />
			<?php endif; ?>
			<?php if ($this->params->get('showcode', 1)): ?>
			<col width="10" class="el_col_code" />
			<?php endif; ?>
			<col width="5" class="el_col_edit" />
			<col width="5" class="el_col_published" />
			<col width="5" class="el_col_delete" />
            <col class="el_col_aesir_item" />
			</colgroup>
			<thead>
				<tr>
					<?php
					if ($this->params->get('showtitle', 1)) :
					?>
					<th id="el_title" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_TITLE'), 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					<?php
					endif;
					if ($this->params->get('showcat', 1)) :
					?>
					<th id="el_category" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_CATEGORY'), 'c.name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					<?php
					endif;
					?>
					<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Published'); ?></th>
					<th id="el_action" class="sectiontableheader" align="left"></th>
                    <th id="el_aesir_item" class="sectiontableheader" align="left"><?= JText::_('COM_REDEVENT_AESIR_SYNC_LABEL_ITEM') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if (count((array)$this->events) == 0) :
				?>
				<tr align="center"><td><?php echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?></td></tr>
				<?php
				else :
				$i = 0;
				foreach ((array) $this->events as $row) :
                    $itemEntity = $helper->getAesirEventItem($row->id);
				?>
				<tr class="sectiontableentry<?php echo $i +1 . $this->params->get( 'pageclass_sfx' ); ?>" >
					<?php
					//Link to details
					$detaillink = JRoute::_('index.php?option=com_redevent&task=editevent.edit&e_id=' . $row->slug);
					//title
					?>
					<td headers="el_title" align="left" valign="top">
						<!-- <a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape($row->title); ?></a> -->
						<div><?php echo $this->escape($row->title); ?></div>
					</td>
					<?php if ($this->params->get('showcat', 1)) : ?>
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
					<td headers="el_edit" align="left" valign="middle">
						<?php if ($row->published == '1'): ?>
							<div class="el_publish el_icon">
							<?php if ($this->acl->canPublishEvent($row->id)): ?>
								<?php echo JHTML::link('index.php?option=com_redevent&task=myevents.unpublishevent&id='. $row->id, JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' ))); ?>
							<?php else: ?>
								<?php echo JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' )); ?>
							<?php endif; ?>
							</div>
						<?php elseif ($row->published == '0'):?>
							<div class="el_unpublish el_icon">
							<?php if ($this->acl->canPublishEvent($row->id)): ?>
								<?php echo JHTML::link('index.php?option=com_redevent&task=myevents.publishevent&id='. $row->id, JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' ))); ?>
							<?php else: ?>
								<?php echo JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' )); ?>
							<?php endif; ?>
							</div>
						<?php endif;?>
					</td>
					<td headers="el_action" align="left" valign="middle">
						<div class="el_action">
							<div class="el_edit el_icon">
								<?php echo $this->eventeditbutton($row->slug); ?>
							</div>
							<div class="el_delete el_icon">
								<?php if ($this->acl->canEditEvent($row->id)): ?>
								<?php echo $this->eventdeletebutton($row->id); ?>
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
		<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->events_pageNav->get('pages.total') > 1)) : ?>
		<div class="pagination">
			<?php echo $this->events_pageNav->getPagesLinks(); ?>
		</div>
		<?php  endif; ?>
		<!-- pagination end -->
		<?php endif; ?>
		<input type="hidden" name="limitstart" value="<?php echo $this->lists['limitstart']; ?>" class="redajax_limitstart" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" class="redajax_order"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" class="redajax_order_dir"/>
		<input type="hidden" name="task" value="myevents.managedevents" />
	</div>
</form>