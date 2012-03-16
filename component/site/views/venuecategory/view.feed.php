<?php
/**
 * @version 1.0 $Id: view.feed.php 354 2009-06-29 15:03:53Z julien $
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
 * redevent Component venue Category Feed
 *
 * @package Joomla
 * @subpackage redevent
 * @since		2.0
 */
class RedeventViewVenuecategory extends JView
{
	/**
	 * Creates the Event Feed of the Venue Category
	 *
	 * @since 0.9
	 */
	function display( )
	{
		$mainframe = &JFactory::getApplication();

		$doc 		= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();

		// Get some data from the model
		JRequest::setVar('limit', $mainframe->getCfg('feed_limit'));
		$rows = & $this->get('Data');

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = $this->escape( $row->full_title );
			$title = html_entity_decode( $title );

			// strip html from feed item category
			if (!empty($row->categories)) 
			{
				$category = array();
				foreach ($row->categories AS $cat) {
					$category[] = $cat->catname;
				}
				$category = $this->escape( implode(', ', $category) );
				$category = html_entity_decode( $category );				
			}
			else {
				$category = '';
			}

			//Format date
			if (redEVENTHelper::isValidDate($row->dates))
			{
				$date = strftime( $elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $row->dates ));
				if (!redEVENTHelper::isValidDate($row->enddates) || $row->enddates == $row->dates) {
					$displaydate = $date;
				} else {
					$enddate 	= strftime( $elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $row->enddates ));
					$displaydate = $date.' - '.$enddate;
				}
			}
			else {
				$displaydate = JText::_('COM_REDEVENT_OPEN_DATE');
			}

			//Format time
			if ($row->times) {
				$time = strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $row->times ));
				$displaytime = $time;
			}
			if ($row->endtimes) {
				$endtime = strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $row->endtimes ));
				$displaytime = $time.' - '.$endtime;
			}

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			
			$link = RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug);
			$link = JRoute::_( $link );

			// feed item description text
			$description = JText::_('COM_REDEVENT_TITLE' ).': '.$title.'<br />';
			$description .= JText::_('COM_REDEVENT_VENUE' ).': '.$row->venue.' / '.$row->city.'<br />';
			$description .= JText::_('COM_REDEVENT_CATEGORY' ).': '.$category.'<br />';
			$description .= JText::_('COM_REDEVENT_DATE' ).': '.$displaydate.'<br />';
			$description .= JText::_('COM_REDEVENT_TIME' ).': '.$displaytime.'<br />';
			//$description .= JText::_('COM_REDEVENT_DESCRIPTION' ).': '.$row->datdescription;

			@$created = ( $row->created ? date( 'r', strtotime($row->created) ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $created;
			$item->category   	= $category;

			// loads item info into rss array
			$doc->addItem( $item );
		}
	}
}
