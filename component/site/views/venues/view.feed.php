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
 * HTML View class for the Venueevents View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventViewVenues extends JView
{
	/**
	 * Creates the Event Feed of the Venue
	 *
	 * @since 0.9
	 */
	function display( )
	{
		$mainframe = &JFactory::getApplication();

		$doc 	= & JFactory::getDocument();

		// Get some data from the model
		JRequest::setVar('limit', $mainframe->getCfg('feed_limit'));
		$rows = & $this->get('Data');

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = $this->escape( $row->venue );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$link = RedeventHelperRoute::getVenueEventsRoute($row->slug);
			$link = JRoute::_( $link );

			// strip html from feed item description text
			$description = $row->locdescription;
			@$created = ( $row->created ? date( 'r', strtotime($row->created) ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $created;

			// loads item info into rss array
			$doc->addItem( $item );
		}
	}
}
