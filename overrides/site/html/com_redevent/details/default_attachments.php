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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<?php if ($this->row->attachments && count($this->row->attachments)):?>
<div class="re-files">
<table>
	<tbody>
	<?php foreach ($this->row->attachments as $file): ?>
		<tr>
			<td><span class="el-file-dl-icon hasTooltip" title="<?php echo JText::_('COM_REDEVENT_Download').' '.($file->file).'<br/>'.($file->description);?>">
				<?php echo JHTML::link('index.php?option=com_redevent&task=getfile&format=raw&file='.$file->id, ($file->name ? $file->name : JText::_('COM_REDEVENT_Download'))); ?></span>
			</td>
		</tr>
	</tbody>
	<?php endforeach; ?>
</table>
</div>
<?php endif;
