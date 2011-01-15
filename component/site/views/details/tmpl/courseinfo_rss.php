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
<table class="courseinfo_tabel">
<thead>
	<tr>
			<th class="courseinfo_titlename"><?php echo JText::_('EVENT_NAME'); ?></th>
			<th class="courseinfo_titledate"><?php echo JText::_('EVENT_DATE'); ?></th>
			<th class="courseinfo_titleduration"><?php echo JText::_('EVENT_DURATION'); ?></th>
			<th class="courseinfo_titlevenue" colspan="2"><?php echo JText::_('EVENT_VENUE'); ?></th>
			<th class="courseinfo_titleprice"><?php echo JText::_('EVENT_PRICE'); ?></th>
			<th class="courseinfo_titlecredit"><?php echo JText::_('EVENT_CREDITS'); ?></th>
			<th class="courseinfo_titlesignup"><?php echo JText::_('EVENT_SIGNUP'); ?></th>
	</tr>
</thead>
<tbody>
<?php
$elsettings = redEVENTHelper::config();
$imagepath = JURI::base() . 'administrator/components/com_redevent/assets/images/';
foreach ($this->_eventlinks as $key => $event) {
	$event_url = JRoute::_(RedeventHelperRoute::getDetailsRoute($event->id, $event->xref));
	$venue_url = JRoute::_(RedeventHelperRoute::getUpcomingVenueEventsRoute($event->venueslug));
	?>
	<tr>
			<td class="courseinfo_name"><?php echo JHTML::_('link', $event_url, $event->title); ?></td>
			<td class="courseinfo_date"><?php echo ELOutput::formatdate($event->dates, $event->times); ?></td>
			<td class="courseinfo_duration"><?php echo redEVENTHelper::getEventDuration($event); ?></td>
			<td class="courseinfo_venue"><?php echo JHTML::_('link', $venue_url, $event->venue); ?></td>
			<td class="courseinfo_country"><?php echo ELOutput::getFlag( $event->country ); ?></td>
			<td class="courseinfo_prices"><?php echo ELOutput::formatprices($event->prices) ?></td>
			<td class="courseinfo_credit"><?php echo $event->course_credit ?></td>
		<td class="courseinfo_signup" width="*"><div class="courseinfo_signupwrapper">
		<?php
		$registration_status = redEVENTHelper::canRegister($event->xref);
		if (!$registration_status->canregister) {
		  $img = JHTML::_('image', JURI::base() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
			echo $img;
		}
		else 
		{
			$venues_html = '';	
			/* Get the different submission types */
			$submissiontypes = explode(',', $event->submission_types);
			foreach ($submissiontypes as $key => $subtype) 
			{
				switch ($subtype) {
					case 'email':
						$venues_html .= '<div class="courseinfo_vlink courseinfo_email">'.JHTML::_('link', JRoute::_(JURI::root().RedeventHelperRoute::getSignupRoute('email', $event->id, $event->xref)), JHTML::_('image', $imagepath.$elsettings->signup_email_img,  JText::_($elsettings->signup_email_text), 'width="24px" height="24px"')).'</div> ';
						break;
					case 'phone':
						$venues_html .= '<div class="courseinfo_vlink courseinfo_phone">'.JHTML::_('link', JRoute::_(JURI::root().RedeventHelperRoute::getSignupRoute('phone', $event->id, $event->xref)), JHTML::_('image', $imagepath.$elsettings->signup_phone_img,  JText::_($elsettings->signup_phone_text), 'width="24px" height="24px"')).'</div> ';
						break;
					case 'external':
			      if (!empty($event->external_registration_url)) {
			      	$link = $event->external_registration_url;
			      }
			      else {
			      	$link = $event->submission_type_external;
			      }
						$venues_html .= '<div class="courseinfo_vlink courseinfo_external">'.JHTML::_('link', $link, JHTML::_('image', $imagepath.$elsettings->signup_external_img,  $elsettings->signup_external_text), 'target="_blank"').'</div> ';
						break;
					case 'webform':
						$venues_html .= '<div class="courseinfo_vlink courseinfo_webform">'.JHTML::_('link', JRoute::_(JURI::root().RedeventHelperRoute::getSignupRoute('webform', $event->id, $event->xref)), JHTML::_('image', $imagepath.$elsettings->signup_webform_img,  JText::_($elsettings->signup_webform_text), 'width="24px" height="24px"')).'</div> ';
						break;
					case 'formaloffer':
						$venues_html .= '<div class="courseinfo_vlink courseinfo_formaloffer">'.JHTML::_('link', JRoute::_(JURI::root().RedeventHelperRoute::getSignupRoute('formaloffer', $event->id, $event->xref)), JHTML::_('image', $imagepath.$elsettings->signup_formal_offer_img,  JText::_($elsettings->signup_formal_offer_text), 'width="24px" height="24px"')).'</div> ';
						break;
				}
			}
			echo $venues_html;
		}
		?>
		</div></td>
	</tr>
	<?php if ($event->details): ?>
	<tr>
	 <td colspan="7">
	   <?php echo $event->details; ?>
	 </td>
	</tr>
	<?php endif; ?>
<?php }
?>
</tbody>
</table>
</div>