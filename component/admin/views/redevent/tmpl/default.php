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

defined('_JEXEC') or die('Restricted access');

$options = array(
		'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
		'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
		'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
		'useCookie' => true, // this must not be a string. Don't use quotes.
);
?>
	<table id="recevent-cpanel">
		<tr>
			<td valign="top">
			<table class="adminlist">
				<tr>
					<td>
						<div id="cpanel">
						<?php
						$option = JRequest::getCmd('option');

						$link = 'index.php?option='.$option.'&amp;view=events';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-events.png', JText::_('COM_REDEVENT_EVENTS' ) );

						$link = 'index.php?option='.$option.'&amp;view=venues';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-venues.png', JText::_('COM_REDEVENT_VENUES' ) );

						$link = 'index.php?option='.$option.'&amp;view=categories';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-categories.png', JText::_('COM_REDEVENT_CATEGORIES' ) );

						$link = 'index.php?option='.$option.'&amp;view=venuescategories';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-venuescategories.png', JText::_('COM_REDEVENT_VENUES_CATEGORIES' ) );

						$link = 'index.php?option='.$option.'&amp;view=registrations';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-registrations.png', JText::_('COM_REDEVENT_REGISTRATIONS' ) );
						
						$link = 'index.php?option='.$option.'&amp;view=groups';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-accessgroups.png', JText::_('COM_REDEVENT_GROUPS' ) );
						
						$link = 'index.php?option='.$option.'&amp;view=textsnippets';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-library.png', JText::_('COM_REDEVENT_TEXT_LIBRARY' ) );
						
						$link = 'index.php?option='.$option.'&amp;view=customfields';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-customfields.png', JText::_('COM_REDEVENT_CUSTOM_FIELDS' ) );
						
						$link = 'index.php?option='.$option.'&amp;view=roles';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-roles.png', JText::_('COM_REDEVENT_ROLES' ) );
						
						$link = 'index.php?option='.$option.'&amp;view=pricegroups';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-pricegroups.png', JText::_('COM_REDEVENT_MENU_PRICEGROUPS' ) );

						$link = 'index.php?option='.$option.'&amp;view=archive';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-archive.png', JText::_('COM_REDEVENT_ARCHIVESCREEN' ) );

						//only admins should be able to see this items
						if ($this->user->authorise('com_redevent', 'manage')) {
							$link = 'index.php?option='.$option.'&amp;view=editcss';
							RedEventViewRedEvent::quickiconButton( $link, 'icon-48-cssedit.png', JText::_('COM_REDEVENT_EDIT_CSS' ) );

							$link = 'index.php?option='.$option.'&amp;view=tools';
							RedEventViewRedEvent::quickiconButton( $link, 'icon-48-housekeeping.png', JText::_('COM_REDEVENT_TOOLS' ) );
							
							$link = 'index.php?option='.$option.'&amp;view=logs';
							RedEventViewRedEvent::quickiconButton( $link, 'icon-48-log.png', JText::_('COM_REDEVENT_LOG' ) );
						}

						$link = 'index.php?option='.$option.'&amp;view=help';
						RedEventViewRedEvent::quickiconButton( $link, 'icon-48-help.png', JText::_('COM_REDEVENT_HELP' ) );
						?>
						</div>
					</td>
				</tr>
			</table>
			</td>
			<td valign="top" width="320px" style="padding: 7px 0 0 5px">
			<?php
			echo JHtml::_('sliders.start', 'tab_group_id', $options);
			echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_EVENT_STATS'), 'events');

				?>
				<table class="adminlist">
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_EVENTS_PUBLISHED' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->events[0]; ?></b>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_EVENTS_UNPUBLISHED' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->events[1]; ?></b>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_EVENTS_ARCHIVED' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->events[2]; ?></b>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_EVENTS_TOTAL' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->events[3]; ?></b>
						</td>
					</tr>
				</table>
				<?php

				echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_VENUE_STATS'), 'venues');
				?>
				<table class="adminlist">
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_VENUES_PUBLISHED' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->venue[0]; ?></b>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_VENUES_UNPUBLISHED' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->venue[1]; ?></b>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_VENUES_TOTAL' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->venue[2]; ?></b>
						</td>
					</tr>
				</table>
				<?php

				echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_CATEGORY_STATS'), 'categories');
				?>
				<table class="adminlist">
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_CATEGORIES_PUBLISHED' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->category[0]; ?></b>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_CATEGORIES_UNPUBLISHED' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->category[1]; ?></b>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_REDEVENT_CATEGORIES_TOTAL' ).': '; ?>
						</td>
						<td>
							<b><?php echo $this->category[2]; ?></b>
						</td>
					</tr>
				</table>
				<?php 
				echo JHtml::_('sliders.end');
				?>
			</td>
		</tr>
		</table>
		
