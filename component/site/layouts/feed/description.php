<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

/**
 * Layout for description field of sessions rss feed item
 */
$sessionData = $displayData;

$event_url = RedeventHelperRoute::getDetailsRoute($sessionData->slug, $sessionData->xslug);
$venue_url = RedeventHelperRoute::getVenueEventsRoute($sessionData->venueslug);
$duration = RedeventHelperDate::getEventDuration($sessionData);
?>
<table>
	<tbody>
		<tr>
			<td width="100">Course:</td>
			<td><?php echo JHTML::_('link', $event_url, RedeventHelper::getSessionFullTitle($sessionData), 'target="_blank"'); ?></td>
		</tr>
		<tr>
			<td>Where:</td>
			<td><?php echo $sessionData->venue . ' &nbsp; ' . RedeventHelperCountries::getCountryFlag($sessionData->country); ?></td>
		</tr>
		<tr>
			<td>Date:</td>
			<td><?php echo RedeventHelperDate::formatdate($sessionData->dates, $sessionData->times); ?></td>
		</tr>
		<tr>
			<td>Duration:</td>
			<td>
				<?php echo $duration; ?>
			</td>
		</tr>
		<tr>
			<td>Venue:</td>
			<td><?php echo JHTML::_('link', $venue_url, $sessionData->venue, 'target="_blank"'); ?></td>
		</tr>
		<tr>
			<td>Price:</td>
			<td class="re-price"><?php echo RedeventHelperOutput::formatListPrices($sessionData->prices); ?></td>
		</tr>
		<tr>
			<td>Credits:</td>
			<td><?php echo $sessionData->course_credit; ?></td>
		</tr>
		<tr>
			<td>Signup:</td>
			<td>
				<?php
				/* Get the different submission types */
				$submissiontypes = explode(',', $sessionData->submission_types);
				$text = array();

				foreach ($submissiontypes as $key => $subtype)
				{
					$text[] = RedeventHtmlSignup::getSignupImageLink($subtype, $sessionData);
				}

				echo implode('<br>', $text);
				?>
			</td>
		</tr>
	</tbody>
</table>';

