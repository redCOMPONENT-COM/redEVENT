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

<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="my-managed-venues" class="redevent-ajaxnav">
<table class="eventtable default-events" summary="venues list">
	<thead>
		<tr>
			<th id="el_title" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_VENUE'); ?></th>
			<th id="el_city" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_CITY'); ?></th>
			<th id="el_published" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_PUBLISHED'); ?></th>
			<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_EDIT'); ?></th>
		</tr>
	</thead>
	<tbody>
  <?php
  $i = 0;
  foreach ((array) $this->venues as $row) :
  ?>
  <?php $link = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug)); ?>
    <tr class="sectiontableentry<?php echo $i + 1 . $this->params->get( 'pageclass_sfx' ); ?>" >
      <td headers="el_title" align="left" valign="top"><?php echo JHTML::link($link, $row->venue); ?></td>
      <td headers="el_city" align="left" valign="top"><?php echo $row->city ? $row->city : '-'; ?></td>
      <td headers="el_published" align="center" valign="top" class="publishevents_col">
      	<?php echo $row->published ? '<div class="publishevents">'.JHTML::image('media/com_redevent/images/ok.png', JText::_('COM_REDEVENT_Published' )).'</div>'
      	                           : '<div class="publishevents">'.JHTML::image('media/com_redevent/images/no.png', JText::_('COM_REDEVENT_Unpublished' )).'</div>' ; ?>
      </td>
      <td headers="el_edit" align="left" valign="top" class="el_edit_events"><?php echo $this->venueeditbutton($row->id, ''); ?></td>
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
	<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter">
				<?php echo $this->venues_pageNav->getPagesCounter(); ?>
		</p>

		<?php endif; ?>
	<?php echo $this->venues_pageNav->getPagesLinks(); ?>
</div>
<?php  endif; ?>
<!-- pagination end -->

	<input type="hidden" name="limitstart" value="<?php echo $this->lists['limitstart']; ?>" class="redajax_limitstart" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" class="redajax_order"/>
<input type="hidden" name="filter_order_Dir" value="" class="redajax_order_dir"/>
<input type="hidden" name="task" value="myevents.managedvenues" />

</form>
