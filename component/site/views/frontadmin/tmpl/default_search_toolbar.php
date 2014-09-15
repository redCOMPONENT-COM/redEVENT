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

<div class="search-toolbar">
	<form name="org-form" id="org-form" method="post">
		<input type="hidden" id="bookings_order" name="bookings_order" value="<?php echo $this->bookings_order; ?>"/>
		<input type="hidden" id="bookings_order_dir" name="bookings_order_dir" value="<?php echo $this->bookings_order_dir; ?>"/>
		<input type="hidden" id="bookings_limitstart" name="bookings_limitstart" value="<?php echo $this->bookings_limitstart; ?>"/>

		<input type="hidden" id="members_order" name="members_order" value="<?php echo $this->members_order; ?>"/>
		<input type="hidden" id="members_order_dir" name="members_order_dir" value="<?php echo $this->members_order_dir; ?>"/>
		<input type="hidden" id="members_limitstart" name="members_limitstart" value="<?php echo $this->members_limitstart; ?>"/>
	</form>
</div>

<div class="clear"></div>
