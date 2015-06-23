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
?>
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="my-managed-events" class="redevent-ajaxnav">

<?php if ($this->params->get('filter_text',1) || $this->params->get('display_limit_select') || $this->params->get('showeventfilter')) : ?>
<div id="el_filter" class="floattext">
    <?php if ($this->params->get('showeventfilter', 1)) : ?>
    <div>
    	<label for="filter_event"><?php echo JText::_('COM_REDEVENT_Event'); ?></label><div class="styled-select"> <?php echo $this->lists['filter_event']; ?><div class="row"></div></div>
    </div>
    <?php endif; ?>
    <?php if ($this->params->get('filter_text',1)) : ?>
    <div>
      <label for="filter_type"><?php echo JText::_('COM_REDEVENT_FILTER'); ?></label><div class="styled-select"> <?php
      echo $this->lists['filter_types'].'&nbsp;';
      ?>
		<div class="row"></div></div>
      <input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" />
      <button type="button" id="filter-go"><?php echo JText::_('COM_REDEVENT_GO' ); ?></button>
      <button type="reset" id="filter-reset"><?php echo JText::_('COM_REDEVENT_RESET' ); ?></button>
    </div>
    <?php endif; ?>
    <?php if ($this->params->get('display_limit_select')) : ?>
    <div>
      <?php
      echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
      echo $this->events_pageNav->getLimitBox();
      ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<table class="eventtable" summary="eventlist">

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
		$editsessionLink = RedeventHelperRoute::getEditXrefRoute($row->slug, $row->xref);
		?>
  			<tr class="sectiontableentry<?php echo $i +1 . $this->params->get( 'pageclass_sfx' ); ?>" >

    			<td headers="el_date" align="left">
    				<?php if ($this->acl->canEditXref($row->xref)): ?>
   					<?php echo JHTML::link($editsessionLink,
   					                       RedeventHelperOutput::formatEventDateTime($row),
   					                       array('class' => 'hasTooltip',
   					                             'title' => '<strong>' . JText::_('COM_REDEVENT_EDIT_XREF' ).'</strong><br/>'.JText::_('COM_REDEVENT_EDIT_XREF_TIP' )));	?>
    				<?php else: ?>
   					<?php echo RedeventHelperOutput::formatEventDateTime($row);	?>
   					<?php endif; ?>
					</td>

				<?php
				//Link to details
				$detaillink = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug));
				//title
				?>
				<td headers="el_title" align="left" valign="top">
					<a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape($row->title); ?></a>
				</td>

				<?php if ($this->params->get('showlocate', 1)) :	?>

					<td headers="el_location" align="left" valign="top">
						<?php
						if ($this->params->get('showlinkvenue',1) == 1 ) :
							echo $row->locid != 0 ? "<a href='".JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>" : '-';
						else :
							echo $row->locid ? $this->escape($row->venue) : '-';
						endif;
						?>
					</td>

				<?php
				endif;

				if ($this->params->get('showcity', 0)) :
				?>

					<td headers="el_city" align="left" valign="top"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>

				<?php
				endif;

				if ($this->params->get('showstate', 0)) :
				?>

					<td headers="el_state" align="left" valign="top"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>

				<?php
				endif;

				if ($this->params->get('showcat', 1)) : ?>
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


				<?php if ($this->params->get('showcode', 1)): ?>
				<td headers="el_code" align="left" valign="top"><?php echo $this->escape(RedeventHelper::getSessionCode($row)); ?></td>
				<?php endif; ?>
				<td headers="el_edit" align="left" valign="top"><?php echo $row->registered.($row->maxattendees ? '/'.$row->maxattendees : ''); ?> <?php echo $this->xrefattendeesbutton($row->xref); ?></td>
				<td headers="el_edit" align="left" valign="top"><?php echo $this->eventeditbutton($row->slug, $row->xref).$this->xrefeditbutton($row->slug, $row->xref); ?></td>
				<td headers="el_edit" align="left" valign="top">
					<?php if ($row->published == '1'): ?>
						<?php if ($this->acl->canEditXref($row->xref)): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&task=unpublishxref&xref='. $row->xref, JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' ))); ?>
						<?php else: ?>
							<?php echo JHTML::_('image', 'media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' )); ?>
						<?php endif; ?>
					<?php elseif ($row->published == '0'):?>
						<?php if ($this->acl->canEditXref($row->xref)): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&task=publishxref&xref='. $row->xref, JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' ))); ?>
						<?php else: ?>
							<?php echo JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' )); ?>
						<?php endif; ?>
					<?php endif;?>
				</td>
				<td headers="el_delete" align="left" valign="top">
					<?php if ($this->acl->canEditXref($row->xref)): ?>
						<?php echo $this->xrefdeletebutton($row->xref); ?>
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
<style>
.styled-select {
   width: 224px;
   height: 27px;
   overflow: hidden;
   position: relative;
   border: 1px solid #ccc;
	box-shadow: 0 1px 0 #F6F6F6;
   }
.styled-select select {
    background: none repeat scroll 0 0 transparent;
    border: 0 none;
    border-radius: 0 0 0 0;
    height: 27px;
    line-height: 1;
    padding: 5px;
    width: 224px;
}
.styled-select .row {
    background: url("templates/redweb/images/use/new_row_select.png") no-repeat scroll right center #DDDDDD;
    height: 26px;
    position: absolute;
    right: 0;
    top: 0;
    width: 26px;
}
/*
select {
    padding:3px 0 3px 3px;
    margin: 0;
    -webkit-border-radius:0px;
    -moz-border-radius:0px;
    border-radius:0px;
    -webkit-box-shadow: 0 1px 0 #F6F6F6;
    -moz-box-shadow: 0 1px 0 #F6F6F6;
    box-shadow: 0 1px 0 #F6F6F6;
    background: #f8f8f8;
    color:#888;
    border:none;
    outline:none;
    display: inline-block;
    -webkit-appearance:none;
    -moz-appearance:none;
    appearance:none;
    cursor:pointer;
	border: 1px solid #CCCCCC;
	height: 25px;
}

@media screen and (-webkit-min-device-pixel-ratio:0) {
    select {padding-right:18px}
}

span.test {position:relative}
span.test:after {
    content:'';
    font:11px "Consolas", monospace;
    color:#aaa;
    -webkit-transform:rotate(90deg);
    -moz-transform:rotate(90deg);
    -ms-transform:rotate(90deg);
    transform:rotate(90deg);
    right:2px; top:-4px;
    padding:0 0 2px;

    position:absolute;
    pointer-events:none;
}
span.test:before {
    content:'';
    right:2px; top:-4px;
    width:25px; height:25px;
    background:url("templates/redweb/images/use/new_row_select.png") no-repeat scroll 0 0 #000000;
    position:absolute;
    pointer-events:none;
    display:block;
}
*/
</style>
