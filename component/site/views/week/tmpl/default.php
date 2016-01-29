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
$days = $this->sortByDay();
?>
<div id="redevent" class="weekview">
	<h1 class="componentheading">
		<?php echo $this->params->get('page_title'); ?>
	</h1>
	<div class="week-details"><?php echo date('l, j F Y',strtotime(reset($this->weekdays))).' - '.date('l, j F Y',strtotime(end($this->weekdays))); ?></div>
	<table class="week-nav">
		<tbody>
			<tr>
				<td class="week-prev"><?php echo JHTML::link(RedeventHelperRoute::getWeekRoute($this->previous), JText::_('COM_REDEVENT_PREVIOUS')); ?></td>
				<td class="week-current"><?php echo JText::sprintf('COM_REDEVENT_WEEK_HEADER', $this->weeknumber, $this->year); ?></td>
				<td class="week-next"><?php echo JHTML::link(RedeventHelperRoute::getWeekRoute($this->next), JText::_('COM_REDEVENT_NEXT')); ?></td>
			</tr>
		</tbody>
	</table>
	<?php for ($i = 0; $i < 7; $i++): ?>
		<div class="day-events">
		<div class="day-title"><?php echo $this->getDayName($i); ?></div>
		<?php if (isset($days[$i]) && count($days[$i])): ?>
			<?php $this->rows = $days[$i]; ?>
			<?php echo $this->loadTemplate('table');	?>
		<?php endif; ?>
		</div>
	<?php endfor; ?>
</div>
