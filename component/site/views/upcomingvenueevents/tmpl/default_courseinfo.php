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
<table>
<thead>
	<tr>
		<th><?php echo JText::_('COM_REDEVENT_EVENT_NAME'); ?></th>
		<th colspan="2"><?php echo JText::_('COM_REDEVENT_EVENT_WHERE'); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_EVENT_DATE'); ?></th>
		<th></th>
		<th><?php echo JText::_('COM_REDEVENT_EVENT_VENUE'); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_EVENT_PRICE'); ?></th>
	</tr>
</thead>
<tbody>
<?php
$elsettings = RedeventHelper::config();
$imagepath = JURI::root().'media/com_redevent/images/';
foreach ($this->upcomingvenueevents as $key => $event) {
	$event_url = JRoute::_('index.php?option=com_redevent&view=details&xref=' . $event->xref . '&id=' . $event->slug);
	$venue_url = JRoute::_('index.php?option=com_redevent&view=venueevents&id='.$event->venueslug);
	?>
	<tr>
		<td><?php echo JHTML::_('link', $event_url, RedeventHelper::getSessionFullTitle($event)); ?></td>
		<td><?php echo $event->venue; ?></td>
		<td><?php echo RedeventHelperCountries::getCountryFlag( $event->country ); ?></td>
		<td><?php echo RedeventHelperOutput::formatdate($event->dates, $event->times); ?></td>
		<td><?php echo RedeventHelper::getEventDuration($event); ?></td>
		<td><?php echo JHTML::_('link', $venue_url, $event->venue); ?></td>
		<td class="re-price"><?php echo RedeventHelperOutput::formatListPrices($event->prices); ?></td>
		<td>
		<?php
		$registration_status = RedeventHelper::canRegister($event->xref);
		if (!$registration_status->canregister)
		{
			$imgpath = 'components/com_redevent/assets/images/'.$registration_status->error.'.png';
		  $img = JHTML::_('image', JURI::base() . $imgpath,
		                          $registration_status->status,
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
			echo RedeventHelperOutput::moreInfoIcon($event->xslug, $img, $registration_status->status);
		}
		else
		{
			$venues_html = '';
		/* Get the different submission types */
		$submissiontypes = explode(',', $event->submission_types);
		$venues_html = '';
		foreach ($submissiontypes as $key => $subtype) {
			switch ($subtype) {
				case 'email':
					$venues_html .= '<div class="vlink email">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&task=signup&subtype=email&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->get('signup_email_img', 'email_icon.gif'),  $elsettings->get('signup_email_text'), 'width="24px" height="24px"')).'</div> ';
					break;
				case 'phone':
					$venues_html .= '<div class="vlink phone">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&task=signup&subtype=phone&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->get('signup_phone_img', 'phone_icon.gif'),  $elsettings->get('signup_phone_text'), 'width="24px" height="24px"')).'</div> ';
					break;
				case 'external':
					$venues_html .= '<div class="vlink external hasTip" title="::'.$elsettings->get('signup_external_text').'">'.JHTML::_('link', $event->submission_type_external, JHTML::_('image', $imagepath.$elsettings->get('signup_external_img', 'external_icon.gif'),  $elsettings->get('signup_external_text')), 'target="_blank"').'</div> ';
					break;
				case 'webform':
					if ($event->prices && count($event->prices))
					{
						foreach ($event->prices as $p)
						{
							$title = ' title="'.$p->name.'::'.addslashes(str_replace("\n", "<br/>", $p->tooltip)).'"';
							$img = empty($p->image) ? JHTML::_('image', $imagepath.$elsettings->get('signup_webform_img', 'form_icon.gif'),  JText::_($elsettings->get('signup_webform_text')))
							                        : JHTML::_('image', $imagepath.$p->image,  JText::_($p->name));
							$link = JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug, $p->slug));

							$venues_html .= '<div class="vlink webform hasTip '.$p->alias.'"'.$title.'>'
								             .JHTML::_('link', $link, $img).'</div> ';
						}
					}
					else {
						$venues_html .= '<div class="vlink webform">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug)), JHTML::_('image', $imagepath.$elsettings->get('signup_webform_img', 'form_icon.gif'),  JText::_($elsettings->get('signup_webform_text')))).'</div> ';
					}
					break;
				case 'formaloffer':
					$venues_html .= '<div class="vlink formaloffer">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&subtype=formaloffer&task=signup&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->get('signup_formal_offer_img', 'formal_icon.gif'),  $elsettings->get('signup_formal_offer_text'), 'width="24px" height="24px"')).'</div> ';
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
