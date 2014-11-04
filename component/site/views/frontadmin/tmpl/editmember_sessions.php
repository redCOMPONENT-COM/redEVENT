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
	<table class="table">
		<thead>
			<tr>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_DATE'), 'x.dates', $this->order_dir, $this->order); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_EVENT_DURATION'); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TITLE'), 'a.title', $this->order_dir, $this->order); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_VENUE'), 'l.venue', $this->order_dir, $this->order); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_CITY'), 'l.city', $this->order_dir, $this->order); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_CATEGORY'), 'c.name', $this->order_dir, $this->order); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_STATUS'); ?></th>
				<?php if ($this->allow_edit_sessions): ?>
				<th colspan="3"><?php echo JText::_('COM_REDEVENT_ACTIONS'); ?></th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->sessions as $row): ?>
			<tr xref="<?php echo $row->xref; ?>" rid="<?php echo $row->rid; ?>">
				<td><?php echo RedeventHelperOutput::formatEventDateTime($row, false); ?></td>
				<td><?php echo RedeventHelper::getEventDuration($row); ?></td>
				<td><?php echo RedeventHelper::getSessionFullTitle($row); ?></td>
				<td><?php echo $row->venue; ?></td>
				<td><?php echo $row->city; ?></td>
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
				<td><?php echo RedeventHelper::getStatusIcon($row->status); ?></td>
				<?php if ($this->allow_edit_sessions): ?>
				<td><?php echo JHTML::image('com_redevent/b2b-delete.png', 'remove'
						, array('class' => 'unregister hasTip'
								, 'title' => JText::_('COM_REDEVENT_FRONTEND_ADMIN_CANCEL_REGISTRATION')
								, 'tip' => JText::_('COM_REDEVENT_FRONTEND_ADMIN_CANCEL_REGISTRATION_TIP')), true); ?>
				</td>
				<?php  endif; ?>
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
<input type="hidden" name="controller" value="frontadmin"/>
<input type="hidden" name="tmpl" value="component"/>
<input type="hidden" name="task" value="<?php echo $this->task; ?>"/>
<input type="hidden" name="<?php echo $this->order_input; ?>" class="redajax_order" value="<?php echo $this->order; ?>"/>
<input type="hidden" name="<?php echo $this->order_dir_input; ?>" class="redajax_order_dir" value="<?php echo $this->order_dir; ?>"/>
<input type="hidden" name="uid" value="<?php echo $this->uid; ?>"/>
<input type="hidden" class="redajax_limitstart" name="<?php echo $this->limitstart_name; ?>" value="<?php echo $this->limitstart; ?>"/>
