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
RHtml::_('rbootstrap.tooltip');
?>

<?php if ($this->category->attachments && count($this->category->attachments)):?>
<div class="re-files">
<div class="re-section-title"><?php echo JText::_( 'COM_REDEVENT_FILES' ); ?></div>
<table>
	<tbody>
	<?php foreach ($this->category->attachments as $file): ?>
		<tr>
			<td>
				<?php
					echo RHtml::tooltip(
						$this->escape($file->description),
						JText::_('COM_REDEVENT_Download').' '.$this->escape($file->file),
						null,
						JHTML::link(RedeventHelperRoute::getAttachment($file->id),
							RHelperAsset::load('download_16.png'), JText::_('COM_REDEVENT_Download')),
						'', '', "el-file-dl-icon hasTooltip"
					);
				?>
			</td>
			<td class="re-file-name"><?php echo $this->escape($file->name ? $file->name : $file->file); ?></td>
		</tr>
	</tbody>
	<?php endforeach; ?>
</table>
</div>
<?php endif;
