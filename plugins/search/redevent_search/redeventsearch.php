<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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

// Import library dependencies
jimport('joomla.plugin.plugin');
 
class plgSearchRedeventSearch extends JPlugin {
	
	/**
	* Constructor
	*
	* @access      protected
	* @param       object  $subject The object to observe
	* @param       array   $config  An array that holds the plugin configuration
	* @since       1.5
	*/
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * @return array An array of search areas
	 */
	public function onContentSearchAreas() 
	{
		static $areas = array(
		     'redeventevents' => 'PLG_REDEVENT_SEARCH_EVENTS',
		     'redeventcategories' => 'PLG_REDEVENT_SEARCH_CATEGORIES',
		     'redeventvenues' => 'PLG_REDEVENT_SEARCH_VENUES',
		);
		return $areas;
	}
 
	/** 
	 * Handles onSearchAreas event
	 * 
	 * @param string $text the text to search
	 * @param string $phrase the type of search (exact/all/any)
	 * @param string $ordering ordering of the results (alpha/newest/...)
	 * @param array $areas areas to be search
	 * @return array matches
	 */
	public function onContentSearch( $text, $phrase='', $ordering='', $areas=null )
	{
		$db   =& JFactory::getDBO();
		$user =& JFactory::getUser();
		
		require_once JPATH_SITE.'/components/com_redevent/helpers/route.php';
		
		//If the array is not correct, return it:
		if (is_array( $areas )) {
			if (!array_intersect( $areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}
	
		//It is time to define the parameters! First get the right plugin; 'search' (the group), 'nameofplugin'.
		$plugin =& JPluginHelper::getPlugin('search', 'redeventsearch');
	
		//Then load the parameters of the plugin..
		$pluginParams = $this->params;
		
	  $limit = $pluginParams->def( 'search_limit', 50 );
	
		//Use the function trim to delete spaces in front of or at the back of the searching terms
		$text = trim( $text );
	
		//Return Array when nothing was filled in
		if ($text == '') {
			return array();
		}
	
		$rows = array();
		
		$search = $db->Quote(JText::_( 'PLG_REDEVENT_SEARCH_EVENTS' ));
	
		if (!$areas || in_array('redeventevents', $areas))
		{
			$where = array();
			switch ($phrase) {
	
				//search exact
				case 'exact':
					$string        = $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
					$where[]   = 'LOWER(e.title) LIKE '.$string. ' OR LOWER(x.title) LIKE '.$string;
					break;
	
					//search all or any
				case 'all':
				case 'any':
	
					//set default
				default:
					$words         = explode( ' ', $text );
					$wheres = array();
					foreach ($words as $word)
					{
						$word          = $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
		        $wheres[]   = 'LOWER(e.title) LIKE '.$word. ' OR LOWER(x.title) LIKE '.$word;
					}
					$where[] = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
					break;
			}
			if ($customs = $this->_buildCustomFieldsQuery($text, $phrase)) {
				$where[] = $customs;
			}
			
			//ordering of the results
			switch ( $ordering ) {
	
				//alphabetic, ascending
				case 'alpha':
					$order = 'e.title ASC, x.dates ASC';
					break;
	
					//oldest first
				case 'oldest':
	        $order = 'x.dates ASC';
	
					//popular first
				case 'popular':
	
					//newest first
				case 'newest':
	        $order = 'x.dates DESC';
	
					//default setting: alphabetic, ascending
				default:
	        $order = 'x.dates ASC';
			}
	
			//the database query; 
			$query = 'SELECT e.summary AS text, x.id AS xref, x.dates, x.times, '
			. ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', e.title, x.title) ELSE e.title END as title, '
			. ' CONCAT_WS( " / ", '. $search .', '.$db->Quote(JText::_( 'PLG_REDEVENT_SEARCH_EVENTS' )).' ) AS section,'
	    . ' CASE WHEN CHAR_LENGTH( e.alias ) THEN CONCAT_WS( \':\', x.id, e.alias ) ELSE x.id END AS slug, '
	    . ' NULL AS created, '
			. ' "2" AS browsernav'
			. ' FROM #__redevent_events AS e'
	    . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
			. ' WHERE ( '. implode(' OR ', $where) .' )'
			. '   AND x.published = 1 '
			. ' ORDER BY '. $order
			;
			
			//Set query
			$db->setQuery( $query, 0, $limit );
			$results = $db->loadObjectList();
	
			foreach($results as $key => $row) 
			{
				// The 'output' of the displayed link
				$results[$key]->href = RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref);

				//date
				if ($this->params->get('include_date', 1))
				{
					if (strtotime($row->dates))
					{
						if ($this->params->get('include_date', 1) == 2 && $row->times <> '00:00:00') 
						{
							$results[$key]->title .= ' - ' . strftime($this->params->get('date_format', '%x'), strtotime($row->dates.' '.$row->times));
						}
						else
						{
							$results[$key]->title .= ' - ' . strftime($this->params->get('date_format', '%x'), strtotime($row->dates));
						}
					}
					else {
						$results[$key]->title .= ' - ' . JText::_('PLG_REDEVENT_SEARCH_OPEN_DATE');
					}
				}
			}
			
			$rows = array_merge($rows, $results);
		}
		
	 if (!$areas || in_array('redeventcategories', $areas))
	  {
	    $where = array();
	    $where[] = 'c.published = 1';
	    switch ($phrase) {
	
	      //search exact
	      case 'exact':
	        $string          = $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
	        $where[]   = 'LOWER(c.catname) LIKE '.$string;
	        break;
	
	        //search all or any
	      case 'all':
	      case 'any':
	
	        //set default
	      default:
	        $words         = explode( ' ', $text );
	        $wheres        = array();
	        foreach ($words as $word)
	        {
	          $word          = $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
	          $wheres[]      = 'LOWER(c.catname) LIKE '.$word;
	        }
	        $where[] = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
	        break;
	    }
	
	    //ordering of the results
	    switch ( $ordering ) {
	
	      //alphabetic, ascending
	      case 'alpha':
	        $order = 'c.catname ASC';
	        break;
	
	        //oldest first
	      case 'oldest':
	
	        //popular first
	      case 'popular':
	
	        //newest first
	      case 'newest':
	
	        //default setting: alphabetic, ascending
	      default:
	        $order = 'c.catname ASC';
	    }
	
	    //the database query; differs per situation! It will look something like this:
	    $query = 'SELECT c.catname AS title,'
	    . ' CONCAT_WS( " / ", '. $search .', '.$db->Quote(JText::_( 'PLG_REDEVENT_SEARCH_CATEGORIES' )).' ) AS section,'
	    . ' CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( \':\', c.id, c.alias ) ELSE c.id END AS slug, '
	    . ' NULL AS created, '
	    . ' "2" AS browsernav'
	    . ' FROM #__redevent_categories AS c'
	    . ' WHERE ( '. implode(' AND ', $where) .' )'
	    . ' ORDER BY '. $order
	    ;
	
	    //Set query
	    $db->setQuery( $query, 0, $limit );
	    $results = $db->loadObjectList();
	
	    //The 'output' of the displayed link
	    foreach($results as $key => $row) {
	      $results[$key]->href = RedeventHelperRoute::getCategoryEventsRoute($row->slug);
	    }
	    $rows = array_merge($rows, $results);
	  }
	
	  if (!$areas || in_array('redeventvenues', $areas))
	  {
	    $where = array();
	    switch ($phrase) 
	    {
	      //search exact
	      case 'exact':
	        $string          = $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
	        $where[]   = 'LOWER(v.venue) LIKE '.$string;
	        break;
	
	        //search all or any
	      case 'all':
	      case 'any':
	
	        //set default
	      default:
	        $words         = explode( ' ', $text );
	        $wheres = array();
	        foreach ($words as $word)
	        {
	          $word          = $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
	          $wheres[]   = 'LOWER(v.venue) LIKE '.$word;
	        }
	        $where[] = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
	        break;
	    }
	
	    //ordering of the results
	    switch ( $ordering ) {
	
	      //alphabetic, ascending
	      case 'alpha':
	        $order = 'v.venue ASC';
	        break;
	
	        //oldest first
	      case 'oldest':
	
	        //popular first
	      case 'popular':
	
	        //newest first
	      case 'newest':
	
	        //default setting: alphabetic, ascending
	      default:
	        $order = 'v.venue ASC';
	    }
	
	    //the database query; differs per situation! It will look something like this:
	    $query = 'SELECT v.venue AS title,'
	    . ' CONCAT_WS( " / ", '. $search .', '.$db->Quote(JText::_( 'PLG_REDEVENT_SEARCH_VENUES' )).' ) AS section,'
	    . ' CASE WHEN CHAR_LENGTH( v.alias ) THEN CONCAT_WS( \':\', v.id, v.alias ) ELSE v.id END AS slug, '
	    . ' NULL AS created, '
	    . ' "2" AS browsernav'
	    . ' FROM #__redevent_venues AS v'
	    . ' WHERE ( '. implode(' AND ', $where) .' )'
	    . ' ORDER BY '. $order
	    ;
	
	    //Set query
	    $db->setQuery( $query, 0, $limit );
	    $results = $db->loadObjectList();
	
	    //The 'output' of the displayed link
	    foreach($results as $key => $row) {
	      $results[$key]->href = RedeventHelperRoute::getVenueEventsRoute($row->slug);
	    }
	    $rows = array_merge($rows, $results);
	  }
	  
		//Return the search results in an array
		return $rows;
	}

	/**
	 * build the query parts for custom fields
	 * 
	 * @param string $text to search
	 * @param string $phrase type of search
	 */
	protected function _buildCustomFieldsQuery($text, $phrase)
	{
		$db = &JFactory::getDBO();
		// get the fields
		$query = ' SELECT f.id, f.object_key FROM #__redevent_fields AS f '
	           . ' WHERE f.published = 1 '
	           . '   AND f.searchable = 1 '
	           . ' ORDER BY f.ordering ASC '
	           ;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$where = array();
		foreach ($rows as $field)
		{
			if ($field->object_key == 'redevent.event') {
				$fieldname = 'e.custom'.$field->id;
			}
			else if ($field->object_key == 'redevent.xref') {
				$fieldname = 'x.custom'.$field->id;
			}
			else {
				continue;
			}
			
	    switch ($phrase) 
	    {
	      //search exact
	      case 'exact':
	        $string  = $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
	        $where[] = 'LOWER('.$fieldname.') LIKE '.$string;
	        break;
	
	        //search all or any
	      case 'all':
	      case 'any':
	
	        //set default
	      default:
	        $words         = explode( ' ', $text );
	        $wheres = array();
	        foreach ($words as $word)
	        {
	          $word          = $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
	          $wheres[]   = 'LOWER('.$fieldname.') LIKE '.$word;
	        }
	        $where[] = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
	        break;
	    }
		}
		return count($where) ? implode(" OR ", $where) : false;	
	}
}
