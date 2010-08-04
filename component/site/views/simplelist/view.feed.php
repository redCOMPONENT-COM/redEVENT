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
 * @subpackage EventList
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
		global $mainframe;
	
		if ($this->getLayout() == 'rsscal') {
			return $this->_displayRssCal();
		}
		
		$doc 		= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();

		// Get some data from the model
		JRequest::setVar('limit', $mainframe->getCfg('feed_limit'));
		$rows = & $this->get('Data');

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = $this->escape( $row->title );
			$title = html_entity_decode( $title );

		  // handle categories
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
			$date = strftime( $elsettings->formatdate, strtotime( $row->dates ));
			if (!$row->enddates || $row->enddates == $row->dates) {
				$displaydate = $date;
			} else {
				$enddate 	= strftime( $elsettings->formatdate, strtotime( $row->enddates ));
				$displaydate = $date.' - '.$enddate;
			}

			//Format time
			if ($row->times) {
				$time = strftime( $elsettings->formattime, strtotime( $row->times ));
				$time = $time.' '.$elsettings->timename;
				$displaytime = $time;
			}
			if ($row->endtimes) {
				$endtime = strftime( $elsettings->formattime, strtotime( $row->endtimes ));
				$endtime = $endtime.' '.$elsettings->timename;
				$displaytime = $time.' - '.$endtime;
			}

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$link = 'index.php?option=com_redevent&view=details&id='. $row->id;
			$link = JRoute::_( $link );

			// feed item description text
			$description = JText::_( 'TITLE' ).': '.$title.'<br />';
			$description .= JText::_( 'VENUE' ).': '.$row->venue.' / '.$row->city.'<br />';
			$description .= JText::_( 'CATEGORY' ).': '.$category.'<br />';
			$description .= JText::_( 'DATE' ).': '.$displaydate.'<br />';
			$description .= JText::_( 'TIME' ).': '.$displaytime.'<br />';
			//$description .= JText::_( 'DESCRIPTION' ).': '.$row->datdescription;

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
		$model->setLimit($elsettings->params->get('ical_max_items', 100));
		$model->setLimitstart(0);
		$rows = & $this->get('Data');
		foreach ( $rows as $row )
		{			
			// strip html from feed item title
			$title = $this->escape( $row->title );
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
			$date = strftime( $elsettings->formatdate, strtotime( $row->dates ));
			$rssstartdate = $row->dates;
			if (!$row->enddates) {
				$displaydate = $date;
				$rssenddate = $row->dates;
			} else {
				$enddate 	= strftime( $elsettings->formatdate, strtotime( $row->enddates ));
				$rssenddate = $row->enddates;
				$displaydate = $date.' - '.$enddate;
			}

			//Format time
			if ($row->times) {
				$time = strftime( $elsettings->formattime, strtotime( $row->times ));
				$time = $time.' '.$elsettings->timename;
				$displaytime = $time;
				$rssstartdate .= 'T'.$row->times.$utcoffset;	
			}
			if ($row->endtimes) {
				$endtime = strftime( $elsettings->formattime, strtotime( $row->endtimes ));
				$endtime = $endtime.' '.$elsettings->timename;
				$displaytime = $time.' - '.$endtime;
				$rssenddate .= 'T'.$row->endtimes.$utcoffset;	
			}

			// url link to event
			$link = JURI::base().RedeventHelperRoute::getDetailsRoute($row->id);
			$link = JRoute::_( $link );
			
			$item = new rsscalItem($row->title, $link);
			$item->addElement( 'ev:type',      $category );
//			$item->addElement( 'ev:organizer', "" );
			$item->addElement( 'ev:location',  $row->venue );
			$item->addElement( 'ev:startdate', $rssstartdate );
			$item->addElement( 'ev:enddate',   $rssenddate );
			$item->addElement( 'dc:subject',   $row->title );
			
			$feed->addItem( $item );
		}		
		
		$feed->returnRSS( CACHE ); 
	}
}
?>