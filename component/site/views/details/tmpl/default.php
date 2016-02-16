<?php
/**
 * @version    1.0 $Id$
 * @package    Joomla
 * @subpackage redEVENT
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

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="redevent" class="event_id<?php echo $this->row->did; ?> el_details">
	<div class="event-header">
		<div class="buttons">
			<?php echo RedeventHelperOutput::mailbutton($this->row->slug, 'details', $this->params); ?>
			<?php echo RedeventHelperOutput::printbutton($this->print_link, $this->params); ?>
			<?php if ($this->row->enable_ical == 1 || ($this->row->enable_ical == 0 && $this->params->get('event_ics', 1))): ?>
				<?php $img = JHTML::image(JURI::base() . 'media/com_redevent/images/iCal2.0.png', JText::_('COM_REDEVENT_EXPORT_ICS')); ?>
				<?php echo JHTML::link(JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug, $this->row->xslug) . '&format=raw&layout=ics', false),
					$img); ?>
			<?php endif; ?>
			<?php echo RedeventHelperOutput::editbutton($this->row->did, $this->params, $this->allowedtoeditevent, 'editevent'); ?>
			<?php if ($this->manage_attendees): ?>
				<?php echo RedeventHelperOutput::xrefattendeesbutton($this->row->xref); ?>
			<?php endif; ?>
		</div>


		<?php if ($this->params->def('show_page_title', 1)) : ?>
			<h1 class="componentheading">
				<?php echo RedeventHelper::getSessionFullTitle($this->row); ?>
			</h1>
		<?php endif; ?>

	</div>

	<!-- Details EVENT -->
	<?php
	$review_txt = trim(strip_tags($this->row->review_message));
	echo $this->tags->replaceTags($this->row->datdescription, array('hasreview' => (!empty($review_txt))));

	if ($this->view_attendees_list) : ?>
		<!-- Registration -->
		<div class="registrations">
			<h2 class="register"><?php echo JText::_('COM_REDEVENT_REGISTERED_USERS') . ':'; ?></h2>
			<?php
			foreach ($this->venuedates AS $key => $venuedate)
			{
				if ($this->params->get('details_attendees_links', 0) == 0 && $venuedate->id != $this->row->xref)
				{
					// Only current session
					continue;
				}

				/* Get the date */
				$date = RedeventHelperDate::formatdate($venuedate->dates);
				$enddate = (!RedeventHelperDate::isValidDate($venuedate->enddates) || $venuedate->enddates == '0000-00-00' || $venuedate->enddates == $venuedate->dates)
					? ''
					: RedeventHelperDate::formatdate($venuedate->enddates);
				$displaydate = $date . ($enddate ? ' - ' . $enddate : '');

				$displaytime = '';
				if (RedeventHelperDate::isValidTime($venuedate->times) && $venuedate->times != '00:00:00')
				{
					$displaytime = RedeventHelperDate::formattime($venuedate->dates, $venuedate->times);

					if (RedeventHelperDate::isValidTime($venuedate->endtimes) && $venuedate->endtimes != '00:00:00')
					{
						$displaytime .= ' - ' . RedeventHelperDate::formattime($venuedate->enddates, $venuedate->endtimes);
					}
				}

				$attendees_layout = ($this->params->get('details_attendees_layout', 0) ? 'attendees' : 'attendees_table');

				echo JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=details&id=' . $this->row->slug . '&tpl=' . $attendees_layout . '&xref=' . $venuedate->id), JText::_('COM_REDEVENT_SHOW_REGISTERED_USERS') . ' ' . $displaydate . ' ' . $displaytime);
			}
			?>
		</div>
	<?php endif; ?>

	<?php if ($this->elsettings->get('commentsystem') != 0) : ?>

		<!-- Comments -->
		<?php echo $this->loadTemplate('comments'); ?>

	<?php endif; ?>

	<ul class="redevent-social">
		<?php if ($this->params->get('fbopengraph', 0)): ?>
			<li class="fb-like">
				<div>
					<fb:like send="true" layout="button_count" width="90" show_faces="false" font=""></fb:like>
				</div>
			</li>
		<?php endif; ?>
		<?php if ($this->params->get('tweet', 0)): ?>
			<li class="tweetevent">
				<div>
					<a href="http://twitter.com/share"
					   class="twitter-share-button"
					   data-text="<?php echo RedeventHelper::getSessionFullTitle($this->row); ?>"
					   data-count="horizontal"
						<?php echo($this->params->get('tweet_recommend') ? 'data-via="' . $this->params->get('tweet_recommend') . '"' : ''); ?>
						<?php if ($this->params->get('tweet_recommend2'))
						{
							if ($this->params->get('tweet_recommend2_text'))
							{
								$text = 'data-related="' . $this->params->get('tweet_recommend2') . ':' . htmlspecialchars($this->params->get('tweet_recommend2_text')) . '"';
							}
							else
							{
								$text = 'data-related="' . $this->params->get('tweet_recommend2') . '"';
							}
							echo $text;
						}
						?>
                       data-lang="<?php echo substr($this->lang->getTag(), 0, 2); ?>">Tweet</a>
				</div>
			</li>
		<?php endif; ?>
		<?php if ($this->params->get('gplusone', 0)): ?>
			<li class="plusonebutton">
				<div>
					<g:plusone size="small"></g:plusone>
				</div>
			</li>
		<?php endif; ?>
	</ul>

</div>
