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



<script type="text/javascript">
function tableOrdering( order, dir, view )
{
	var form = document.getElementById("venuesform");

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	form.submit( view );
}
</script>

<form action="" method="post" id="adminForm">
<div id="upcomingevents">
<table class="courseinfo_tabel">
<thead>
	<tr>
			<!-- <th class="courseinfo_titlename"><?php echo JText::_('COM_REDEVENT_EVENT_NAME'); ?></th> -->
			<th class="courseinfo_titledate"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_EVENT_DATE', 'x.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th class="courseinfo_titleduration"><?php echo JText::_('COM_REDEVENT_EVENT_DURATION'); ?></th>
			<th class="courseinfo_titlevenue" colspan="2"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_LOCATION', 'v.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th class="courseinfo_titleprice"><?php echo JText::_('COM_REDEVENT_EVENT_PRICE'); ?></th>
			<th class="courseinfo_titlecredit"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_EVENT_AVAILABLE_SEAT', 'x.maxattendees', $this->lists['order_Dir'], $this->lists['order'] ); JText::_('COM_REDEVENT_EVENT_AVAILABLE_SEAT'); ?></th>
			<th class="courseinfo_titlesignup"><?php echo JText::_('COM_REDEVENT_EVENT_SIGNUP'); ?></th>
	</tr>
</thead>
<tbody>
<?php
$elsettings = redEVENTHelper::config();
$imagepath = JURI::base() . 'administrator/components/com_redevent/assets/images/';
foreach ($this->_eventlinks as $key => $event) {

	$event_url = JRoute::_(RedeventHelperRoute::getDetailsRoute($event->slug, $event->xslug));
	$venue_url = JRoute::_(RedeventHelperRoute::getUpcomingVenueEventsRoute($event->venueslug));
if ($event->maxattendees)
{
	$placesleft = $event->maxattendees - $event->registered;
	$placesleft = $placesleft >= 6 ? '6+' : $placesleft;
}
else
{
	$placesleft = JText::_('COM_REDEVENT_EVENT_NOLIMIT');
}
	?>
	<tr>
			<!--<td class="courseinfo_name"><?php echo JHTML::_('link', $event_url, $event->full_title); ?></td>-->
			<td class="courseinfo_date"><?php echo REOutput::formatdate($event->dates, $event->times); ?></td>
			<td class="courseinfo_duration"><?php echo redEVENTHelper::getEventDuration($event); ?></td>
			<td class="courseinfo_venue"><?php echo JHTML::_('link', $venue_url, $event->venue); ?></td>
			<td class="courseinfo_country"><?php echo REOutput::getFlag($event->country)
					. ($event->currency ? '(' . $event->currency . ')' : ''); ?></td>
			<td class="courseinfo_prices re-price"><?php echo REOutput::formatListPrices($event->prices); ?></td>
			<td class="courseinfo_credit"><?php echo $placesleft;?></td>
		<td class="courseinfo_signup" width="*">
		<?php
		$registration_status = redEVENTHelper::canRegister($event->xref);
		if (!$registration_status->canregister)
		{
			$imgpath = 'components/com_redevent/assets/images/'.$registration_status->error.'.png';
		  $img = JHTML::_('image', JURI::base() . $imgpath,
		                          $registration_status->status,
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
			echo REOutput::moreInfoIcon($event->xslug, $img, $registration_status->status);

		}else if( REOutput::formatdate($event->dates, $event->times) == JText::_('COM_REDEVENT_OPEN_DATE') ){

			echo '<div title="Request date" href="#" class="clickTip exampleTip">Book';
			echo '<div class="tip_content" style="display:none">';
				echo $event->venue_description;
			echo '</div>';
			echo '</div>';

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
						$venues_html .= '<div class="courseinfo_vlink courseinfo_email">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('email', $event->slug, $event->xslug)), JHTML::_('image', $imagepath.$elsettings->get('signup_email_img'),  JText::_($elsettings->get('signup_email_text')), 'width="24px" height="24px"')).'</div> ';
						break;
					case 'phone':
						$venues_html .= '<div class="courseinfo_vlink courseinfo_phone">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('phone', $event->slug, $event->xslug)), JHTML::_('image', $imagepath.$elsettings->get('signup_phone_img'),  JText::_($elsettings->get('signup_phone_text')), 'width="24px" height="24px"')).'</div> ';
						break;
					case 'external':
			      if (!empty($event->external_registration_url)) {
			      	$link = $event->external_registration_url;
			      }
			      else {
			      	$link = $event->submission_type_external;
			      }
						$venues_html .= '<div class="courseinfo_vlink courseinfo_external hasTip" title="::'.$elsettings->get('signup_external_text').'">'.JHTML::_('link', $link, JHTML::_('image', $imagepath.$elsettings->get('signup_external_img'),  $elsettings->get('signup_external_text')), 'target="_blank"').'</div> ';
						break;
					case 'webform':
						if ($event->prices && count($event->prices))
						{
							foreach ($event->prices as $p)
							{
								$title = ' title="'.$p->name.'::'.addslashes(str_replace("\n", "<br/>", $p->tooltip)).'"';
								$img = empty($p->image) ? JHTML::_('image', $imagepath.$elsettings->get('signup_webform_img'),  JText::_($p->name))
								                        : JHTML::_('image', JURI::base().$p->image,  JText::_($p->name));
								$link = JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug, $p->slug));

								//$venues_html .= '<div class="courseinfo_vlink courseinfo_webform hasTip '.$p->alias.'"'.$title.'>'
									             //.JHTML::_('link', $link, $img).'</div> ';
								//$venues_html .= '<div class="courseinfo_vlink courseinfo_webform '.$p->alias.'">'
												  //.JHTML::_('link', $link, JText::_('BOOK_COURSE')).'</div> ';
								$venues_html .= JHTML::_('link', $link, JText::_('BOOK_COURSE'));
							}
						}
						else {
							//$venues_html .= '<div class="courseinfo_vlink courseinfo_webform">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug)), JHTML::_('image', $imagepath.$elsettings->get('signup_webform_img'),  JText::_($elsettings->get('signup_webform_text')))).'</div> ';
							//$venues_html .= '<div class="courseinfo_vlink courseinfo_webform">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug)), JText::_('BOOK_COURSE') ,  '').'</div> ';
							$venues_html .= JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug)), JText::_('BOOK_COURSE') ,  '');
						}
						break;
					case 'formaloffer':
						$venues_html .= '<div class="courseinfo_vlink courseinfo_formaloffer">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('formaloffer', $event->slug, $event->xslug)), JHTML::_('image', $imagepath.$elsettings->get('signup_formal_offer_img'),  JText::_($elsettings->get('signup_formal_offer_text')), 'width="24px" height="24px"')).'</div> ';
						break;
				}
			}
			echo $venues_html;
		}
		?>
		</td>
	</tr>
	<?php if ($event->details): ?>
	<tr>
	 <td colspan="7">
			<?php $tagsHelper = new redEVENT_tags(); ?>
			<?php $tagsHelper->setXref($event->xref); ?>
	   <?php echo $tagsHelper->ReplaceTags($event->details); ?>
	 </td>
	</tr>
	<?php endif; ?>
<?php }
?>
</tbody>
</table>
</div>

<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>
