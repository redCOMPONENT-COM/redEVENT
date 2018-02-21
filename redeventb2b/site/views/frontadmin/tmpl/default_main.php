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

JText::script('COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_LEAVE_PAGE');
?>
<script>
    window.onbeforeunload = function(e) {
        return Joomla.JText._('COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_LEAVE_PAGE');
    };
</script>

<h1 id="organisation-title"></h1>

<div id="main-content" class="row-fluid">
	<ul class="nav nav-tabs">
		<li><a href="#employees" data-toggle="tab">Employees</a></li>
		<li><a href="#bookings" data-toggle="tab">Bookings</a></li>
		<li><a href="#newbooking" data-toggle="tab">New Booking</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="employees">
			<div id="main-members" class="panel panel-default">
				<?php echo $this->loadTemplate('members'); ?>
			</div>
		</div>
		<div class="tab-pane" id="bookings">
			<div class="span10">
				<div id="main-bookings" class="panel panel-default">
				</div>
			</div>

			<div id="main-bookings-search" class="span2">
				<?php echo $this->loadTemplate('bookings_search'); ?>
			</div>
		</div>
		<div class="tab-pane" id="newbooking">
			<div id="main-right" class="span10">
				<div id="selected-members" class="panel panel-default">
					<?php echo $this->loadTemplate('selected_members'); ?>
				</div>

				<div id="main-course-results" class="panel panel-default">
				</div>
			</div>
			<div id="main-right" class="span2">
				<?php echo $this->loadTemplate('course_search'); ?>
			</div>
		</div>
	</div>
</div>
<div class="clear"></div>
