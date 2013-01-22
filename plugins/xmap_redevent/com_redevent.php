<?php
/**
 * $Id: com_redevent.php 120 2010-06-26 11:51:39Z guilleva $
 * Xmap by Guillermo Vargas
 * a sitemap component for Joomla! CMS (http://www.joomla.org)
 * Author Website: http://joomla.vargas.co.cr
 * Project License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access.');

include_once(JPATH_SITE.'/components/com_redevent/helpers/route.php');

/*
 * Handles redEVENT Category structure
 */
class xmap_com_redevent {
    /*
    * This function is called before a menu item is printed. We use it to set the
    * proper uniqueid for the item and indicate whether the node is expandible or not
    */
	public static function prepareMenuItem($node, &$params) 
	{
		$link_query = parse_url($node->link);
		if (!isset($link_query['query'])) {
			return;
		}
		parse_str(html_entity_decode($link_query['query']), $link_vars);
		$view   = JArrayHelper::getValue($link_vars, 'view', '');
		$layout = JArrayHelper::getValue($link_vars, 'layout', '');
		$id     = JArrayHelper::getValue($link_vars, 'id', 0, 'INT');
		
		switch ($view)
		{
			case 'categoryevents':
			case 'upcomingvenueevents':
			case 'venueevents':
			case 'venuecategory':
				$node->expandible = true;
				$node->uid = 'com_redevent_'.$view.$id;
				break;
				
			case 'categories':
			case 'categoriesdetailled':	
			case 'featured':
			case 'upcomingevents':
			case 'venues':
				$node->uid = 'com_redevent_'.$view;
				$node->expandible = true;
				break;

			case 'details':
				$node->uid = 'com_redevent_ev'.$id;
				$node->expandible = false;
				break;
					
			case 'attendees':
			case 'editevent':
			case 'editvenue':
			case 'myevents':
			case 'search':
			case 'archive':
			case 'calendar':
			case 'day':
			case 'week':
			default:
				$node->uid = 'com_redevent_'.$view;
				$node->expandible = false;							
		}
	}

	/** Get the content tree for this kind of content */
	public static function getTree( $xmap, $parent, &$params )
	{        		
		$link_query = parse_url( $parent->link );
		parse_str( html_entity_decode($link_query['query']), $link_vars );
		$view = JArrayHelper::getValue($link_vars,'view','');
				
		$include_events = intval(JArrayHelper::getValue($params,'include_events',1));
		$include_events = ( $include_events == 1
                                  || ( $include_events == 2 && $xmap->view == 'xml') 
                                  || ( $include_events == 3 && $xmap->view == 'html'));
		$params['include_events'] = $include_events;

		$priority = JArrayHelper::getValue($params,'cat_priority',$parent->priority,'');
		$changefreq = JArrayHelper::getValue($params,'cat_changefreq',$parent->changefreq,'');
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['cat_priority'] = $priority;
		$params['cat_changefreq'] = $changefreq;

		$priority = JArrayHelper::getValue($params,'venue_priority',$parent->priority,'');
		$changefreq = JArrayHelper::getValue($params,'venue_changefreq',$parent->changefreq,'');
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['venue_priority'] = $priority;
		$params['venue_changefreq'] = $changefreq;

		$priority = JArrayHelper::getValue($params,'event_priority',$parent->priority,'');
		$changefreq = JArrayHelper::getValue($params,'event_changefreq',$parent->changefreq,'');
		if ($priority  == '-1')
			$priority = $parent->priority;

		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['event_priority'] = $priority;
		$params['event_changefreq'] = $changefreq;

		switch ($view)
		{
			case 'categoryevents':
			case 'categories':
			case 'categoriesdetailed':
				self::getCategoriesTree($xmap, $parent, $params);
				break;
				
			case 'venuecategory':
				self::getVenuesCategoriesTree($xmap, $parent, $params);
				break;
		
			case 'upcomingvenueevents':
			case 'venueevents':
			case 'venues':
				self::getVenueEventsTree($xmap, $parent, $params);
				break;
				
			case 'simplelist':
			case 'upcomingevents':
			case 'featured':
				self::getSimpleListTree($xmap, $parent, $params);
				break;
		}
		return true;
	}
	
	/**
	 * tree for categories and categories detailed views
	 *
	 * @param unknown $xmap
	 * @param unknown $parent
	 * @param unknown $params
	 */
	protected static function getSimpleListTree(&$xmap, &$parent, $params)
	{
		$link_query = parse_url( $parent->link );
		parse_str( html_entity_decode($link_query['query']), $link_vars );

		$view = JArrayHelper::getValue($link_vars,'view','');
		
		$xmap->changeLevel(1);
		if ($params['include_events'])
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			
			$query->select("x.id as xref, x.eventid, e.title, e.alias, x.dates, x.times");
			$query->select("UNIX_TIMESTAMP(e.created) as created, UNIX_TIMESTAMP(e.modified) as modified ");
			$query->select('CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(\':\', e.id, e.alias) ELSE e.id END as slug');
			$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
			$query->from('#__redevent_events AS e');
			$query->join('INNER', '#__redevent_event_venue_xref AS x on x.eventid = e.id');
			$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
			$query->where('x.published = 1');
			$query->order('x.dates,x.times,x.enddates,x.endtimes');
			
			if ($view == 'featured') {
				$query->where('x.featured = 1');
			}
			
			if ($view == 'upcomingevents') {
				$now = strftime('%Y-%m-%d %H:%M');
				$query->where('(CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > '.$db->Quote($now));
			}
			
			$db->setQuery($query, 0, JArrayHelper::getValue($params,'max_events',10,'int'));
			
			$rows = $db->loadObjectList();
			foreach ($rows as $event)
			{
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid  = $parent->uid .'x'.$event->xref;
				$node->name = $event->title.' - '.self::_formatdate($event->dates, $event->times, $params);
				$node->modified = ($event->modified? $event->modified : $event->created);
				$node->link = RedeventHelperRoute::getDetailsRoute($event->slug, $event->xslug);
				$node->priority   = $params['event_priority'];
				$node->changefreq = $params['event_changefreq'];
				$node->expandible = false;
				$xmap->printNode($node);
			}
		}
		$xmap->changeLevel(-1);
	}
		
	/**
	 * tree for categories and categories detailed views
	 * 
	 * @param unknown $xmap
	 * @param unknown $parent
	 * @param unknown $params
	 */
	protected static function getCategoriesTree(&$xmap, &$parent, $params)
	{
		$link_query = parse_url( $parent->link );
		parse_str( html_entity_decode($link_query['query']), $link_vars );
		$catid = intval(JArrayHelper::getValue($link_vars,'id',0));

		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('c.id , c.catname, c.alias');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__redevent_categories AS c');
		$query->where('c.published = 1 AND c.private = 0');
		$query->where('c.parent_id = '.$catid);
		$query->order('ordering');
		$db->setQuery($query);
		$cats = $db->loadObjectList();
		
		$xmap->changeLevel(1);
		foreach($cats as $cat)
		{
			$node = new stdclass;
			$node->id   = $parent->id;
			$node->uid  = $parent->uid.'c'.$cat->id;
			$node->name = $cat->catname;
			$node->link = RedeventHelperRoute::getCategoryEventsRoute($cat->slug);
			$node->priority   = $params['cat_priority'];
			$node->changefreq = $params['cat_changefreq'];
			$node->expandible = true;
			$xmap->printNode($node);
			self::getCategoriesTree($xmap, $node, $params);
		}

		if ($params['include_events'])
		{
			$query = $db->getQuery(true);
			
			$query->select("x.id as xref, x.eventid, e.title, e.alias, x.dates, x.times");
			$query->select("UNIX_TIMESTAMP(e.created) as created, UNIX_TIMESTAMP(e.modified) as modified ");
			$query->select('CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(\':\', e.id, e.alias) ELSE e.id END as slug');
			$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
			$query->from('#__redevent_events AS e');
			$query->join('INNER', '#__redevent_event_venue_xref AS x on x.eventid = e.id');
			$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
			$query->where('x.published = 1');
			$query->where('xcat.category_id = '.$catid);
			$query->order('x.dates,x.times,x.enddates,x.endtimes');
			$db->setQuery($query, 0, JArrayHelper::getValue($params,'max_events',10,'int'));
						
			$rows = $db->loadObjectList();
			foreach ($rows as $event) 
			{
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid  = $parent->uid .'x'.$event->xref;
				$node->name = $event->title.' - '.self::_formatdate($event->dates, $event->times, $params);
				$node->modified = ($event->modified? $event->modified : $event->created);
				$node->link = RedeventHelperRoute::getDetailsRoute($event->slug, $event->xslug);
				$node->priority   = $params['event_priority'];
				$node->changefreq = $params['event_changefreq'];
				$node->expandible = false;
				$xmap->printNode($node);
			}
		}
		$xmap->changeLevel(-1);
	}
	
	/**
	 * expands venue menus
	 * 
	 * @param unknown $xmap
	 * @param unknown $parent
	 * @param unknown $params
	 */
	protected static function getVenueEventsTree(&$xmap, &$parent, $params)
	{
		$link_query = parse_url( $parent->link );
		parse_str( html_entity_decode($link_query['query']), $link_vars );
		$venue_id = intval(JArrayHelper::getValue($link_vars,'id',0));
		
		$view = JArrayHelper::getValue($link_vars,'view','');
		
		$db = &JFactory::getDBO();

 		$xmap->changeLevel(1);
		if ( !$venue_id ) 
		{
			$query = $db->getQuery(true);
			
			$query->select('id , venue, alias');
			$query->select('CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug');
			$query->from('#__redevent_venues');
			$query->where('published = 1 AND private = 0');
			$query->order('ordering');
			$db->setQuery($query);
			$venues = $db->loadObjectList();

			foreach($venues as $venue)
			{
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid  = $parent->uid.'v'.$venue->id;
		   		$node->name = $venue->venue;
				$node->link = RedeventHelperRoute::getVenueEventsRoute($venue->slug);
				$node->priority   = $params['venue_priority'];
				$node->changefreq = $params['venue_changefreq'];
				$node->expandible = true;
				if ( $xmap->printNode($node) ) {
					self::getVenueEventsTree($xmap, $node, $params);
				}
	    	}
		} 
		else if ($params['include_events']) 
		{
			$query = $db->getQuery(true);
			
			$query->select("x.id as xref, x.eventid, e.title, e.alias, x.dates, x.times");
			$query->select("UNIX_TIMESTAMP(e.created) as created, UNIX_TIMESTAMP(e.modified) as modified ");
			$query->select('CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(\':\', e.id, e.alias) ELSE e.id END as slug');
			$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
			$query->from('#__redevent_events AS e');
			$query->join('INNER', '#__redevent_event_venue_xref AS x on x.eventid = e.id');
			$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
			$query->where('x.published = 1');
			$query->where('x.venueid = '.$venue_id);
			$query->order('x.dates,x.times,x.enddates,x.endtimes');
			
			if ($view == 'upcomingvenueevents') {
				$now = strftime('%Y-%m-%d %H:%M');
				$query->where('(CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > '.$db->Quote($now));
			}
			
			$db->setQuery($query, 0, JArrayHelper::getValue($params,'max_events',10,'int'));
			
			$rows = $db->loadObjectList();
			foreach($rows as $event) 
			{
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid  = $parent->uid .'x'.$event->xref;
				$node->name = $event->title.' - '.self::_formatdate($event->dates, $event->times, $params);
				$node->modified = ($event->modified? $event->modified : $event->created);
				$node->link = RedeventHelperRoute::getDetailsRoute($event->slug, $event->xslug);
				$node->priority   = $params['event_priority'];
				$node->changefreq = $params['event_changefreq'];
				$node->expandible = false;
				$xmap->printNode($node);
			}
		}
		$xmap->changeLevel(-1);
	}


	/**
	 * tree for venues categories view
	 *
	 * @param unknown $xmap
	 * @param unknown $parent
	 * @param unknown $params
	 */
	protected static function getVenuesCategoriesTree(&$xmap, &$parent, $params)
	{
		$link_query = parse_url( $parent->link );
		parse_str( html_entity_decode($link_query['query']), $link_vars );
		$catid = intval(JArrayHelper::getValue($link_vars,'id',0));
	
		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);
	
		$query->select('c.id , c.name, c.alias');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__redevent_venues_categories AS c');
		$query->where('c.published = 1 AND c.private = 0');
		$query->where('c.parent_id = '.$catid);
		$query->order('ordering');
		$db->setQuery($query);
		$cats = $db->loadObjectList();
	
		$xmap->changeLevel(1);
		
		// sub categories
		if (count($cats))
		{
			foreach($cats as $cat)
			{
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid  = $parent->uid.'vc'.$cat->id;
				$node->name = $cat->name;
				$node->link = RedeventHelperRoute::getVenueCategoryRoute($cat->slug);
				$node->priority   = $params['cat_priority'];
				$node->changefreq = $params['cat_changefreq'];
				$node->expandible = true;
				$xmap->printNode($node);
				self::getVenuesCategoriesTree($xmap, $node, $params);
			}
		}
		
		// category venues
		$query = $db->getQuery(true);
	
		$query->select('v.id, v.venue');
		$query->select('CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug');
		$query->from('#__redevent_venues AS v');
		$query->join('inner', '#__redevent_venue_category_xref AS x ON x.venue_id = v.id');
		$query->where('x.category_id = '.$catid);
		$query->order('v.ordering');
		$db->setQuery($query);
		$venues = $db->loadObjectList();
		
		if (count($venues))
		{
			foreach($venues as $venue)
			{
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid  = $parent->uid.'v'.$venue->id;
				$node->name = $venue->venue;
				$node->link = RedeventHelperRoute::getVenueEventsRoute($venue->slug);
				$node->priority   = $params['venue_priority'];
				$node->changefreq = $params['venue_changefreq'];
				$node->expandible = true;
				$xmap->printNode($node);
				self::getVenueEventsTree($xmap, $node, $params);
			}
		}		
		$xmap->changeLevel(-1);
	}
	
	/**
	 * return true is a date is valid (not null, or 0000-00...)
	 * 
	 * @param string $date
	 * @return boolean
	 */
	protected static function _isValidDate($date)
	{
		if (is_null($date)) {
			return false;
		}
		if ($date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
			return false;
		}
		if (!strtotime($date)) {
			return false;
		}
		return true;		
	}

	/**
	 * Formats date
	 *
	 * @param string $date
	 * @param string $time
	 * 
	 * @return string $formatdate
	 *
	 * @since 0.9
	 */
	protected static function _formatdate($date, $time, $params)
	{		
		if(!self::_isValidDate($date)) {
			return JText::_('Open date');
		}
		
		if(!$time) {
			$time = '00:00:00';
		}
		
		//Format date
		$date = JFactory::getDate($date.' '.$time);		
		return $date->format($params['dateformat'], true);
	}
}
