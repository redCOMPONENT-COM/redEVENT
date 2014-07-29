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

require_once JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'rsscalCreator.class.php';

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the EventList View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventViewSimpleList extends JView
{
	/**
	 * Creates the Event Feed
	 *
	 * @since 0.9
	 */
	function display( )
	{
		setlocale(LC_ALL, "da_DK.UTF-8");
		$mainframe = &JFactory::getApplication();

		if ($this->getLayout() == 'rsscal')
		{
			return $this->_displayRssCal();
		}

		$doc 		= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();

		// Get data from the model
		$model = $this->getModel();
		$model->setLimit(0);
		$rows = $model->getData();

		// Get custom fields list
		$xcustoms = $this->get('XrefCustomFields');
		$sessionField = '';

		foreach ($xcustoms as $custom)
		{
			if ($custom->name == 'Type for session')
			{
				$sessionField = 'custom' . $custom->id;
			}
		}

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = $this->escape( $row->full_title );
			$title = html_entity_decode( $title );

			// handle categories
			/*if (!empty($row->categories))
			{
				$category = array();
				foreach ($row->categories AS $cat)
				{
					$category[] = $cat->catname;
				}
				$category = $this->escape( implode(', ', $category) );
				$category = html_entity_decode( $category );
			}
			else
			{
				$category = '';
			}*/
			$category = '';

			if (($sessionField) && isset($row->$sessionField))
			{
				$category = $row->$sessionField;
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

			// Process on event description
			$eventTag = array('[title]', '[a_name]', '[catsid]', '[dates]', '[times]', '[enddates]', '[endtimes]', '[venues]', '[info]');
			$eventDesc = str_replace($eventTag, '', $row->datdescription);

			// feed item description text
			$description = '<p><img src="' . JUri::root() . $row->datimage . '" /></p>';
			$description .= JText::_('COM_REDEVENT_SESSION') . ': ' . $row->session_title . '<br />';
			$description .= JText::_('COM_REDEVENT_PRICE') . ': ' . $this->formatPrice($row) . '<br />';
			$description .= JText::_('COM_REDEVENT_VENUE').': '.$row->venue.' / ' . $row->street . ', ' . $row->city . ', ' . $row->plz . '<br />';
			$description .= JText::_('COM_REDEVENT_CATEGORY' ).': '. $category .'<br />';
			$description .= JText::_('COM_REDEVENT_DATE' ).': ' . ucfirst($displaydate) . '<br />';
			$description .= JText::_('COM_REDEVENT_TIME' ).': '.$displaytime.'<br />';
			$description .= JText::_('COM_REDEVENT_DESCRIPTION' ).': '.$eventDesc;

			@$created = ( $row->created ? date( 'r', strtotime($row->created) ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title		= $row->title;
			$item->link			= $link;
			$item->description	= $description;
			$item->date			= $created;
			$item->category		= $category;

			// loads item info into rss array
			$doc->addItem( $item );
		}
	}

	function _displayRssCal()
	{
		define( 'CACHE', './cache' );

		$mainframe  = &JFactory::getApplication();
		$elsettings = redEVENTHelper::config();

		$offset = (float) $mainframe->getCfg('offset');
		$hours = ($offset >= 0) ? floor($offset) : ceil($offset);
		$mins = abs($offset - $hours) * 60;
		$utcoffset = sprintf('%+03d:%02d', $hours, $mins);

		$feed = new rsscalCreator( 'redEVENT feed', JURI::base(), 'Test feed' );
		$feed->setFilename( CACHE, 'events.rss' );

		// get data
		$model = $this->getModel();
		$model->setLimit($elsettings->get('ical_max_items', 100));
		$model->setLimitstart(0);
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
			//Format date
			if (redEVENTHelper::isValidDate($row->dates))
			{
				$date = strftime( $elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $row->dates ));
				$rssstartdate = $row->dates;
				if (!redEVENTHelper::isValidDate($row->enddates) || $row->enddates == $row->dates) {
					$displaydate = $date;
					$rssenddate = $row->dates;
				}
				else {
					$enddate 	= strftime( $elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $row->enddates ));
					$rssenddate = $row->enddates;
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
				$rssstartdate .= 'T'.$row->times.$utcoffset;
			}
			if ($row->endtimes) {
				$endtime = strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $row->endtimes ));
				$displaytime = $time.' - '.$endtime;
				$rssenddate .= 'T'.$row->endtimes.$utcoffset;
			}

			// url link to event
			$link = JURI::base().RedeventHelperRoute::getDetailsRoute($row->id);
			$link = JRoute::_( $link );

			$item = new rsscalItem($row->full_title, $link);
			$item->addElement( 'ev:type',      $category );
//			$item->addElement( 'ev:organizer', "" );
			$item->addElement( 'ev:location',  $row->venue );
			$item->addElement( 'ev:startdate', $rssstartdate );
			$item->addElement( 'ev:enddate',   $rssenddate );
			$item->addElement( 'dc:subject',   $row->full_title );

			$feed->addItem( $item );
		}

		$feed->returnRSS( CACHE );
	}

	private function formatPrice($row)
	{
		if (!$row->prices)
		{
			return JText::_('COM_REDEVENT_EVENT_PRICE_FREE');
		}

		$prices = array();

		foreach ($row->prices as $price)
		{
			if (!$price->price)
			{
				$prices[] = JText::_('COM_REDEVENT_EVENT_PRICE_FREE');
			}
			else
			{
				$prices[] = $price->currency ? $price->currency . ' ' . $price->price : $price->price;
			}
		}

		return implode(' / ', $prices);
	}
}
