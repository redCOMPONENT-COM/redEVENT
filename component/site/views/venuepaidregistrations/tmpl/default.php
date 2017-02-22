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

$uri = JUri::getInstance();
$action = $uri->toString();
?>
<div id="redevent" class="el_venuesview<?= $this->params->get('pageclass_sfx') ?>">
	<?php if ($this->params->def('show_page_heading', 1)) : ?>
		<h1 class='componentheading'>
			<?php echo $this->escape($this->pagetitle); ?>
		</h1>
	<?php endif; ?>

	<!-- use form for filters and pagination -->
	<form action="<?= $action ?>" method="post" id="adminForm">
		<table id="regTable" class="table table-striped table-hover">
			<thead>
			<tr>
				<th>Registration date</th>
				<th>Name</th>
				<th>Email</th>
				<th>Course</th>
				<th>Date</th>
				<th>Venue</th>
				<th>Invoice</th>
				<th>Gateway</th>
				<th>Payment</th>
			</tr>
			</thead>

			<tbody>
			<?php foreach ($this->rows as $row): ?>
				<?php $attendee = RedeventEntityAttendee::getInstance($row->id)->bind($row); ?>
				<tr>
					<td><?= $attendee->uregdate; ?></td>
					<td><?= $attendee->getUser()->name; ?></td>
					<td><?= $attendee->getUser()->email; ?></td>
					<td><?= $attendee->getSession()->getEvent()->title; ?></td>
					<td><?= $attendee->getSession()->getFormattedStartDate(); ?></td>
					<td><?= $attendee->getSession()->getVenue()->name; ?></td>
					<td><?= $row->invoice_id; ?></td>
					<td><?= $row->gateway; ?></td>
					<td><?= $row->data; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</form>

	<!--pagination-->
	<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pageNav->get('pages.total') > 1)) : ?>
	<div class="pagination">
		<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter">
					<?php echo $this->pageNav->getPagesCounter(); ?>
			</p>

			<?php endif; ?>
		<?php echo $this->pageNav->getPagesLinks(); ?>
	</div>
	<?php  endif; ?>
	<!-- pagination end -->
</div>
