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

<form action="index.php" method="post" name="adminForm" id="adminForm">

<table class="adminform">
	<tr>
		<td width="100%">
			 <?php echo JText::_('COM_REDEVENT_SEARCH' ).' '.$this->lists['filter']; ?>
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
			<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
		</td>
		<td nowrap="nowrap"><?php echo $this->lists['state']; ?>

		<select name="filter_language" class="inputbox" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
			<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter_language')); ?>
		</select>
		</td>
	</tr>
</table>

<table class="adminlist">
	<thead>
		<tr>
			<th width="5">#</th>
			<th width="5"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
			<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_VENUE', 'l.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="20%"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_ALIAS', 'l.alias', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="20%"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_COMPANY', 'l.company', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_WEBSITE' ); ?></th>
			<th><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_CITY', 'l.city', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th><?php echo JText::_('COM_REDEVENT_CATEGORY' ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_REDEVENT_PUBLISHED' ); ?></th>
			<th><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_LABEL_PRIVATE', 'l.private', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_CREATION' ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_REDEVENT_SESSIONS' ); ?></th>
		    <th width="80" colspan="2"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_REORDER', 'l.ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'c.language', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
		    <th width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_ID', 'l.id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="20">
				<?php echo $this->pageNav->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>

	<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->rows ); $i < $n; $i++) {
			$row = &$this->rows[$i];
			$link 		= 'index.php?option=com_redevent&amp;view=venue&id='. $row->id;
			$sessionslink = 'index.php?option=com_redevent&view=sessions&venueid='. $row->id;
			$checked 	= JHTML::_('grid.checkedout', $row, $i );
			$published 	= JHTML::_('grid.published', $row, $i );
   		?>
		<tr class="<?php echo "row$k"; ?>">
			<td><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
			<td><?php echo $checked; ?></td>
			<td align="left">
				<?php
				if ( $row->checked_out && ( $row->checked_out != $this->user->get('id') ) ) {
					echo htmlspecialchars($row->venue, ENT_QUOTES, 'UTF-8');
				} else {
					?>
					<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_VENUE' );?>::<?php echo $row->venue; ?>">
					<a href="<?php echo $link; ?>">
					<?php echo htmlspecialchars($row->venue, ENT_QUOTES, 'UTF-8'); ?>
					</a></span>
				<?php
				}
				?>
			</td>
			<td>
				<?php
				if (JString::strlen($row->alias) > 25) {
					echo JString::substr( htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8'), 0 , 25).'...';
				} else {
					echo htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8');
				}
				?>
			</td>
			<td><?php echo $row->company; ?></td>
			<td align="left">
				<?php
				if ($row->url) {
				?>
					<a href="<?php echo htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
						<?php
						if (JString::strlen($row->url) > 25) {
							echo JString::substr( htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8'), 0 , 25).'...';
						} else {
							echo htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8');
						}
						?>
					</a>
				<?php
				} else {
					echo  '-';
				}
				?>
			</td>
			<td align="left"><?php echo $row->city ? htmlspecialchars($row->city, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
        <td>
          <?php
          //$cats_html = array();
          foreach ((array) $row->categories as $ck => $cat)
          {
            if ($cat->checked_out && ( $cat->checked_out != $this->user->get('id') ) ) {
              echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8');
            } else {
              $catlink    = 'index.php?option=com_redevent&amp;controller=venuescategories&amp;task=edit&amp;cid[]='.$cat->id;
              ?>
                <span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_CATEGORY' );?>::<?php echo $cat->name; ?>">
                <a href="<?php echo $catlink; ?>">
                  <?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>
                </a></span>
              <?php
              if ($ck < count($row->categories)-1) {
                echo "<br/>";
              }
            }
          }
          ?>
        </td>
			<td align="center"><?php echo $published; ?></td>
			<td align="center">
				<?php echo $row->private ? JHTML::image('administrator/components/com_redevent/assets/images/icon-16-private.png', JText::_('COM_REDEVENT_LABEL_PRIVATE')) : ''; ?>
			</td>
			<td>
				<?php echo JText::_('COM_REDEVENT_AUTHOR' ).': '; ?><a href="<?php echo 'index.php?option=com_users&amp;task=edit&amp;hidemainmenu=1&amp;cid[]='.$row->created_by; ?>"><?php echo $row->author; ?></a><br />
				<?php echo JText::_('COM_REDEVENT_EMAIL' ).': '; ?><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a><br />
				<?php
				$delivertime 	= JHTML::Date( $row->created, JText::_('DATE_FORMAT_LC2' ) );
				$edittime 		= JHTML::Date( $row->modified, JText::_('DATE_FORMAT_LC2' ) );
				$ip				= $row->author_ip == 'DISABLED' ? JText::_('COM_REDEVENT_DISABLED' ) : $row->author_ip;
				$image 			= JHTML::_('image', 'administrator/templates/'. $this->template .'/images/menu/icon-16-info.png', JText::_('COM_REDEVENT_NOTES') );
				$overlib 		= JText::_('COM_REDEVENT_CREATED_AT' ).': '.$delivertime.'<br />';
				$overlib		.= JText::_('COM_REDEVENT_WITH_IP' ).': '.$ip.'<br />';
				if ($row->modified != '0000-00-00 00:00:00') {
					$overlib 	.= JText::_('COM_REDEVENT_EDITED_AT' ).': '.$edittime.'<br />';
					$overlib 	.= JText::_('COM_REDEVENT_EDITED_FROM' ).': '.$row->editor.'<br />';
				}
				?>
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_VENUE_STATS'); ?>::<?php echo $overlib; ?>">
					<?php echo $image; ?>
				</span>
			</td>
			<td align="center"><?php echo JHTML::link($sessionslink, $row->assignedevents); ?></td>
			<td align="right">
				<?php
				echo $this->pageNav->orderUpIcon( $i, true, 'orderup', 'Move Up', $this->ordering );
				?>
			</td>
			<td align="left">
				<?php
				echo $this->pageNav->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $this->ordering );
				?>
			</td>
			<td align="center"><?php echo $row->language == '*' ? Jtext::_('All') : $row->language_title; ?></td>
			<td align="center"><?php echo $row->id; ?></td>
		</tr>
		<?php $k = 1 - $k; } ?>

	</tbody>

</table>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="view" value="venues" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="venues" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>