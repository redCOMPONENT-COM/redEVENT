<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );
?>
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="my-managed-events" class="redevent-ajaxnav">

<div id="el_filter" class="floattext">
	<div>
		<input type="text" name="filter" id="filter"
			value="<?php echo $this->lists['filter'];?>" class="inputbox" placeholder="<?php echo JText::_('COM_REDEVENT_SEARCH'); ?>"/>
		<button type="button" id="filter-go"><?php echo JText::_('COM_REDEVENT_GO'); ?></button>
		<button type="reset" id="filter-reset"><?php echo JText::_('COM_REDEVENT_RESET'); ?></button>
	</div>

	<?php if ($this->params->get('display_limit_select')) : ?>
		<div>
			<?php
				echo '<label for="limit">' . JText::_('COM_REDEVENT_DISPLAY_NUM') . '</label>&nbsp;';
				echo $this->events_pageNav->getLimitBox();
			?>
		</div>
	<?php endif; ?>
</div>

<table class="eventtable" summary="eventlist">

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

				<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Edit'); ?></th>
				<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Published'); ?></th>
				<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_Delete'); ?></th>
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
		?>
  			<tr class="sectiontableentry<?php echo $i +1 . $this->params->get( 'pageclass_sfx' ); ?>" >

				<?php
				//Link to details
				$detaillink = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug));
				//title
				?>
				<td headers="el_title" align="left" valign="top">
					<a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape($row->title); ?></a>
				</td>

				<?php if ($this->params->get('showcat', 1)) : ?>
		          <td headers="el_category" align="left" valign="top">
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
		          </td>
		        <?php endif; ?>

				<td headers="el_edit" align="left" valign="top"><?php echo $this->eventeditbutton($row->slug); ?></td>
				<td headers="el_edit" align="left" valign="top">
					<?php if ($row->published == '1'): ?>
							<?php echo JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' )); ?>
					<?php elseif ($row->published == '0'):?>
							<?php echo JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' )); ?>
					<?php endif;?>
				</td>
				<td headers="el_delete" align="left" valign="top">
					<?php if ($this->acl->canEditEvent($row->id)): ?>
						<?php echo $this->eventdeletebutton($row->id); ?>
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
	<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter">
				<?php echo $this->events_pageNav->getPagesCounter(); ?>
		</p>

		<?php endif; ?>
	<?php echo $this->events_pageNav->getPagesLinks(); ?>
</div>
<?php  endif; ?>
<!-- pagination end -->

	<input type="hidden" name="limitstart" value="<?php echo $this->lists['limitstart']; ?>" class="redajax_limitstart" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" class="redajax_order"/>
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" class="redajax_order_dir"/>
<input type="hidden" name="task" value="myevents.managedevents" />

</form>
