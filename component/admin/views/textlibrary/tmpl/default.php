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
	<table id="textlibrary" class="adminlist" cellspacing="0" cellpadding="0" border="0" width="100%">
	<thead>
	<tr>
		<th width="20">#</th>
		<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows ); ?>);" />
		</th> 
		<th class="title">
			<?php echo JHTML::_('grid.sort',  JText::_('TEXT_TAG'), 'obj.text_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
		</th>
		<th class="title">
			<?php echo JText::_('TEXT_DESCRIPTION'); ?>
		</th>
	</tr>
	</thead>
	
	<tfoot>
		<tr>
			<td colspan="4">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	
	<tbody>
	<?php
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++) {
		$row = $this->rows[$i]; 
		$checked = JHTML::_('grid.checkedout',  $row, $i);
		$link 		= 'index.php?option=com_redevent&amp;controller=textlibrary&amp;task=edit&amp;cid[]='. $row->id;
		?>
		<tr>
		<td>
			<?php echo $row->id; ?>
		</td>
		<td>
			<?php echo $checked; ?>
		</td>
		<td>
				[<?php
				if ( $row->checked_out && ( $row->checked_out != $this->user->get('id') ) ) {
					echo htmlspecialchars($row->text_name, ENT_QUOTES, 'UTF-8');
				} else {
					?>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'EDIT TAG' );?>::<?php echo $row->text_name; ?>">
					<a href="<?php echo $link; ?>">
					<?php echo htmlspecialchars($row->text_name, ENT_QUOTES, 'UTF-8'); ?>
					</a></span>
				<?php
				}
				?>]
		</td>
			<td><?php echo $row->text_description; ?>
		</td>
		</tr>
	<?php } ?>
	</tbody>
	</table>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="id" value="" />
<input type="hidden" name="controller" value="textlibrary" />
<input type="hidden" name="view" value="textlibrary" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>

<p class="copyright">
	<?php echo ELAdmin::footer( ); ?>
</p>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>