<?php
/**
 * @package    Redevent
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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
<?php if ($this->sessions): ?>
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ALL_EVENTS'); ?></h2>

	<table class="table">
		<thead>
			<tr>
				<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECT_SESSION'); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_DATE'), 'x.dates', $this->order_Dir, $this->order); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_TIME'); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_EVENT_DURATION'); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TITLE'), 'a.title', $this->order_Dir, $this->order); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_VENUE'), 'l.venue', $this->order_Dir, $this->order); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_CATEGORY'), 'c.catname', $this->order_Dir, $this->order); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_B2B_SEATS'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->sessions as $row): ?>
			<?php
				$eventediturl = RedeventHelperRoute::getEditEventRoute($row->id).'&tmpl=component';
				$eventeditimg = JHTML::image('com_redevent/b2b-edit.png', JText::_('COM_REDEVENT_EDIT_EVENT')
							, array('class' => 'hasTip'
									, 'title' => JText::_('COM_REDEVENT_EDIT_EVENT')
									, 'tip' => JText::_('COM_REDEVENT_FRONTEND_ADMIN_CLICK_TO_EDIT_EVENT')), true);
				$eventeditlink = JHtml::link($eventediturl, $eventeditimg, array('class' => 'xrefmodal'));

				if ($this->useracl->canEditXref($row->xref))
				{
					$editsessionlink = JHtml::link(RedeventHelperRoute::getEditXrefRoute($row->id, $row->xref).'&tmpl=component'
						, RedeventHelperOutput::formatdate($row->dates, false)
						, array('class' => 'xrefmodal hasTip',
							'title' => JText::_('COM_REDEVENT_EDIT_XREF'),
							'tip' => JText::_('COM_REDEVENT_EDIT_XREF_TIP')));
				}
				else
				{
					$editsessionlink = RedeventHelperOutput::formatdate($row->dates, false);
				}
			?>
				<tr xref="<?php echo $row->xref; ?>">
					<td><input type="radio" name="select-session" value="<?php echo $row->xref; ?>" class="select-session-radio"/></td>
					<td><?php echo $editsessionlink; ?></td>
					<td><?php echo RedeventHelperOutput::formattime($row->dates, $row->times); ?></td>
					<td><?php echo RedeventHelper::getEventDuration($row); ?></td>
					<td><?php echo $row->title; ?></td>
					<td><?php echo $row->venue; ?></td>
					<td class="re_category">
						<?php $cats = array();
						foreach ($row->categories as $cat)
						{
							if ($this->params->get('catlinklist', 1) == 1)
							{
								$cats[] = JHTML::link(RedeventHelperRoute::getCategoryEventsRoute($cat->slug), $cat->catname);
							}
							else
							{
								$cats[] = $this->escape($cat->catname);
							}
						}
						echo implode("<br/>", $cats);
						?>
					</td>
					<td>
						<?php echo $this->bookbutton($row->xref); ?>
						<?php echo $this->printPlaces($row, false); ?>
					</td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>

	<!--pagination-->
	<?php if (($this->pagination->get('pages.total') > 1)) : ?>
	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php  endif; ?>
	<!-- pagination end -->

<?php endif; ?>
