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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Upcoming events View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventViewUpcomingevents extends RViewSite
{
	/**
	 * Creates the Venueevents View
	 *
	 * @since 0.9
	 */
	function display()
	{

		$document	=JFactory::getDocument();
		$document->link = JRoute::_('index.php?option=com_redevent&view=upcomingevents');
		$upcomingevents = $this->get('UpcomingEvents');
		$elsettings = RedeventHelper::config();
		$imagepath = JURI::root().'administrator/components/com_redevent/assets/images/';

		foreach ($upcomingevents as $key => $event) {

			$event_url = RedeventHelperRoute::getDetailsRoute($event->slug, $event->xslug);
			$venue_url = RedeventHelperRoute::getVenueEventsRoute($event->venueslug);
			$description = '<table>
			<tbody>
			<tr>
				<td width="100">Course:</td><td>'.JHTML::_('link', $event_url, RedeventHelper::getSessionFullTitle($event), 'target="_blank"').'</td>
			</tr><tr>
				<td>Where:</td><td>'.$event->location.' &nbsp; '.RedeventHelperCountries::getCountryFlag( $event->country ).'</td>
			</tr><tr>
				<td>Date:</td><td>'.RedeventHelperOutput::formatdate($event->dates, $event->times).'</td>
			</tr><tr>
				<td>Duration:</td><td>'.RedeventHelper::getEventDuration($event);
			$description .= '</td>
			</tr><tr>
				<td>Venue:</td><td>'.JHTML::_('link', $venue_url, $event->venue, 'target="_blank"').'</td>
			</tr><tr>
				<td>Price:</td><td class="re-price">'.RedeventHelperOutput::formatListPrices($event->prices).'</td>
			</tr><tr>
				<td>Credits:</td><td>'.$event->course_credit.'</td>
			</tr><tr>
				<td>Signup:</td><td>';

				/* Get the different submission types */
				$submissiontypes = explode(',', $event->submission_types);
				$venues_html = '';
				foreach ($submissiontypes as $key => $subtype) {
					switch ($subtype) {
						case 'email':
							$venues_html .= '&nbsp;'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&task=signup&subtype=email&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->get('signup_email_img', 'email_icon.gif'),  JText::_($elsettings->get('signup_email_text')), 'width="24px" height="24px" border="0"'), 'target="_blank"').'&nbsp; ';
							break;
						case 'phone':
							$venues_html .= '&nbsp;'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&task=signup&subtype=phone&xref='.$event->xref.'&id='.$event->id), JHTML::_('image', $imagepath.$elsettings->get('signup_phone_img', 'phone_icon.gif'),  JText::_($elsettings->get('signup_phone_text')), 'width="24px" height="24px" border="0"'), 'target="_blank"').'&nbsp; ';
							break;
						case 'external':
							$venues_html .= '&nbsp;'.JHTML::_('link', $event->submission_type_external, JHTML::_('image', $imagepath.$elsettings->get('signup_external_img', 'external_icon.gif'),  $elsettings->get('signup_external_text'), 'width="24px" height="24px" border="0"'), 'target="_blank"').'&nbsp; ';
							break;
						case 'webform':
							if ($event->prices && count($event->prices))
							{
								foreach ($event->prices as $p)
								{
									$img = empty($p->image) ? JHTML::_('image', $imagepath.$elsettings->get('signup_webform_img', 'form_icon.gif'),  JText::_($elsettings->get('signup_webform_text')))
									                        : JHTML::_('image', $imagepath.$p->image,  JText::_($p->name));
									$link = JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug, $p->slug));

									$venues_html .= '&nbsp;'.JHTML::_('link', $link, $img).'&nbsp; ';
								}
							}
							else {
								$venues_html .= '&nbsp;'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug)), JHTML::_('image', $imagepath.$elsettings->get('signup_webform_img', 'form_icon.gif'),  JText::_($elsettings->get('signup_webform_text')))).'&nbsp; ';
							}
							break;
						case 'formaloffer':
							$venues_html .= '&nbsp;'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&subtype=formaloffer&task=signup&xref='.$event->xslug.'&id='.$event->slug), JHTML::_('image', $imagepath.$elsettings->get('signup_formal_offer_img', 'formal_icon.gif'),  JText::_($elsettings->get('signup_formal_offer_text')), 'width="24px" height="24px" border="0"'), 'target="_blank"').'&nbsp; ';
							break;
					}
				}
			$description .= $venues_html;
			$description .= '</td></tr></tbody></table>';


			$item = new JFeedItem();
			$item->title 		= RedeventHelper::getSessionFullTitle($event);
			$item->link 		= $event_url;
			$item->description 	= $description;
			$item->date			= '';
			$item->category   	= $event->venue;
			// loads item info into rss array
			$document->addItem( $item );
		}
	}
}
