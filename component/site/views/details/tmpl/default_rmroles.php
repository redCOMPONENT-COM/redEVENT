<?php
/**
 * @version 1.0 $Id: default_attendees.php 299 2009-06-24 08:20:04Z julien $
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

$roles = array();
foreach ($this->roles as $r)
{
	if (!isset($r->role)) {
		$roles[$r->role] = array($r); 
	}
	else {
		$roles[$r->role][] = $r;
	}
}
?>	
<div class="event_roles">
	<?php foreach ($roles as $r): ?>
	<div class="event_roles_title"><?php echo $r[0]->role; ?></div>
	<table>
		<thead>
			<tr>
			<th><?php echo JText::_('COM_REDEVENT_Name'); ?></th>
			<?php foreach ($r[0]->rminfo as $field => $val): ?>
			<th><?php echo $field; ?></th>
			<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($r as $user): ?>
			<tr>
			<td><?php echo $user->name; ?></td>
			<?php foreach ($user->rminfo as $field => $val): ?>
			<td><?php echo $val; ?></td>
			<?php endforeach;?>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<?php endforeach;?>
</div>