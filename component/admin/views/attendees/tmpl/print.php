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

defined('_JEXEC') or die('Restricted access'); ?>

	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
		<tr>
		  	<td class="sectionname" width="100%"><font style="color: #C24733; font-size : 18px; font-weight: bold;"><?php echo JText::_( 'REGISTERED USER' ); ?></font></td>
		  	<td><div class="button2-left"><div class="blank"><a href="#" onclick="window.print();return false;"><?php echo JText::_('PRINT'); ?></a></div></div></td>
		</tr>
	</table>

	<br />

	<table class="adminlist" cellspacing="1">
		<tr>
		  	<td align="left">
				<b><?php echo JText::_( 'DATE' ).':'; ?></b>&nbsp;<?php echo $this->event->dates; ?><br />
				<b><?php echo JText::_( 'EVENT TITLE' ).':'; ?></b>&nbsp;<?php echo htmlspecialchars($this->event->title, ENT_QUOTES, 'UTF-8'); ?>
			</td>
		  </tr>
	</table>

	<br />

	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title"><?php echo JText::_( 'IP ADDRESS' ); ?></th>
				<th class="title"><?php echo JText::_( 'REGDATE' ); ?></th>
				<th class="title"><?php echo JText::_( 'CONFIRMDATE' ); ?></th>
				<th class="title"><?php echo JText::_( 'USER ID'); ?></th>
				<th class="title"><?php echo JText::_( 'CONFIRMED' ); ?></th>
				<th class="title"><?php echo JText::_( 'WAITINGLIST' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			$k = 0;
			$i = 0;
			foreach ($this->rows as $subid => $row) {
   			?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $row->uip; ?></td>
				<td><?php echo JHTML::Date( $row->uregdate, JText::_( 'DATE_FORMAT_LC2' ) ); ?></td>
				<td><?php echo JHTML::Date( $row->confirmdate, JText::_( 'DATE_FORMAT_LC2' ) ); ?></td>
				<td><?php echo $row->uid; ?></td>
				<td><?php echo $row->confirmed == 0 ? JText::_('NO') : JText::_('YES'); ?></td>
				<td><?php echo $row->waitinglist == 0 ? JText::_('NO') : JText::_('YES'); ?></td>
			</tr>
			<?php $k = 1 - $k; $i++; } ?>
		</tbody>

	</table>

	<p class="copyright">
		<?php echo ELAdmin::footer( ); ?>
	</p>