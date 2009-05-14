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
?>
<div id="upcomingevents">
<table width="940">
<thead>
	<tr>
			<th align="left"><?php echo JText::_('EVENT_NAME'); ?></th>
			<th align="left"><?php echo JText::_('EVENT_DATE'); ?></th>
			<th align="left"><?php echo JText::_('EVENT_DURATION'); ?></th>
			<th align="left" colspan="2"><?php echo JText::_('EVENT_VENUE'); ?></th>
			<th align="left"><?php echo JText::_('EVENT_PRICE'); ?></th>
			<th align="left"><?php echo JText::_('EVENT_CREDITS'); ?></th>
			<th align="left"><?php echo JText::_('EVENT_SIGNUP'); ?></th>
	</tr>
</thead>
<tbody>
<?php
$elsettings = redEVENTHelper::config();
$imagepath = JURI::base() . '/administrator/components/com_redevent/assets/images/';
foreach ($this->_eventlinks as $key => $event) {
	$event_url = JRoute::_('index.php?option=com_redevent&view=details&xref='.$event->xref);
	$venue_url = JRoute::_('index.php?option=com_redevent&view=upcomingvenueevents&id='.$event->venueid);
	?>
	<tr>
			<td width="350"><?php echo JHTML::_('link', $event_url, $event->title); ?></td>
			<td width="90"><?php echo ELOutput::formatdate($event->dates, $event->times); ?></td>
			<td width="80"><?php echo $event->duration;
			if ($event->duration == 1) echo JText::_('DAY');
			else if ($event->duration > 1) echo JText::_('DAYS');?></td>
			<td width="65"><?php echo JHTML::_('link', $venue_url, $event->location); ?></td>
			<td width="30"><?php echo ELOutput::getFlag( $event->country ); ?></td>
			<td width="60"><?php echo ELOutput::formatprice($event->course_price) ?></td>
			<td width="70"><?php echo $event->course_credit ?></td>
		<td width="*">
		<?php
		if ($event->unixdates >= time() && $event->registra) {
			/* Get the different submission types */
			$submissiontypes = explode(',', $event->submission_types);
			$venues_html = '';
			foreach ($submissiontypes as $key => $subtype) {
				switch ($subtype) {
					case 'email':
						$venues_html .= '<div class="vlink email">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&task=signup&subtype=email&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->signup_email_img,  JText::_($elsettings->signup_email_text), 'width="24px" height="24px"')).'</div> ';
						break;
					case 'phone':
						$venues_html .= '<div class="vlink phone">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&task=signup&subtype=phone&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->signup_phone_img,  JText::_($elsettings->signup_phone_text), 'width="24px" height="24px"')).'</div> ';
						break;
					case 'external':
						$venues_html .= '<div class="vlink external">'.JHTML::_('link', $event->submission_type_external, JHTML::_('image', $imagepath.$elsettings->signup_external_img,  $elsettings->signup_external_text), 'target="_blank"').'</div> ';
						break;
					case 'webform':
						$venues_html .= '<div class="vlink webform">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&subtype=webform&task=signup&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->signup_webform_img,  JText::_($elsettings->signup_webform_text), 'width="24px" height="24px"')).'</div> ';
						break;
					case 'formaloffer':
						$venues_html .= '<div class="vlink formaloffer">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&subtype=formaloffer&task=signup&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->signup_formal_offer_img,  JText::_($elsettings->signup_formal_offer_text), 'width="24px" height="24px"')).'</div> ';
						break;
				}
			}
			echo $venues_html;
		}
		?>
		</td>
	</tr>
<?php }
?>
</tbody>
</table>
</div>
