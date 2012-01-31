<?php
/**
 * $Id: com_redevent.php 120 2010-06-26 11:51:39Z guilleva $
 * Xmap by Guillermo Vargas
 * a sitemap component for Joomla! CMS (http://www.joomla.org)
 * Author Website: http://joomla.vargas.co.cr
 * Project License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

/*
 * Handles redEVENT Category structure
 */
class xmap_com_redevent
{
	/*
	 * This function is called before a menu item is printed. We use it to set the
	 * proper uniqueid for the item
	 */
	function prepareMenuItem(&$node,&$params) {
		$link_query = parse_url( $node->link );
		parse_str( html_entity_decode($link_query['query']), $link_vars);
		$id = intval(xmap_com_redevent::getParam($link_vars,'id',0));
		$view = xmap_com_redevent::getParam($link_vars,'view',0);
		if ( !$id ) {
			$menu =& JSite::getMenu();
			$params = $menu->getParams($node->id);
			$id = $params->get('id',0);
		}
		if ( $id ) {
			if ( $view == 'details' ) {
				$node->uid = 'com_redevente'.$id;
				$node->expandible = false;
			} elseif ( $view == 'categoryevents'  ) {
				$node->expandible = true;
				$node->uid = 'com_redeventc'.$id;
			} elseif ( $view == 'venueevents'  ) {
				$node->expandible = true;
				$node->uid = 'com_redeventv'.$id;
			}
		} else {
			$node->expandible = true;
		}
	}

	/*
	 * Return Category tree 
	 */
	function getTree( &$xmap, &$parent, $params )
	{
		$catid=0;
		$venid=0;
		$link_query = parse_url( $parent->link );
		parse_str( html_entity_decode($link_query['query']), $link_vars );
		$view = JArrayHelper::getValue($link_vars,'view','');

		if ( $view == 'categoryevents' ) {
			$catid = intval(JArrayHelper::getValue($link_vars,'id',0));
		} elseif ( $view == 'venueevents' ) {
			$venid = intval(JArrayHelper::getValue($link_vars,'id',0));
		} elseif ( $view == 'simplelist' ) {
			$catid = 0;
		} elseif ( $view != 'venues' && $view != 'categories' ) {  //Do not expand other kind of menu items
			return true;
		}

		$include_events = JArrayHelper::getValue( $params, 'include_events',1,'' );
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

		if ( $include_events ) {
			$params['limit'] = '';
			$params['days'] = '';
			$limit = JArrayHelper::getValue($params,'max_events','','');

			if ( intval($limit) )
				$params['limit'] = ' LIMIT '.$limit;

			$days = JArrayHelper::getValue($params,'max_age','','');
			if ( intval($days) )
				$params['days'] = ' AND (x.dates = 0 OR x.dates >= "'.date('Y-m-d H:m:s', ($xmap->now - ($days*86400)) ) .'") ';
		}

		switch ($view) {
			case 'categories':
			case 'categoriesdetailed':
			case 'categoryevents':
			case 'simplelist':
				xmap_com_redevent::getCategoryTree( $xmap, $parent, $params, $catid );
				break;
			case 'venues':
			case 'venueevents':
				xmap_com_redevent::getVenueTree( $xmap, $parent, $params, $venid );
				break;
		}
	}
	
	function getCategoryTree(&$xmap, &$parent, $params,$catid=0)
	{		
		$db = &JFactory::getDBO();
		$gid = intval($xmap->gid);
		
		$query = "SELECT id , catname, alias"
				. "\nFROM #__redevent_categories"
				. "\nWHERE published = 1 and parent_id=$catid"
				. "\nAND private = 0"
				. "\nORDER BY ordering";
		$db->setQuery($query);
		$cats = $db->loadObjectList();

	 	$xmap->changeLevel(1);
		foreach($cats as $cat)
		{
			$node = new stdclass;
			$node->id   = $parent->id;
			$node->uid   = $parent->uid.'c'.$cat->id;
		   	$node->name = $cat->catname;
			$node->link = 'index.php?option=com_redevent&amp;view=categoryevents&amp;id='.$cat->id.':'.$cat->alias;
			$node->expandible = true;
			$xmap->printNode($node);
	    	}

		if ($params['include_events']) 
		{
			$query = " SELECT x.id as xref, x.eventid, e.title, e.alias, x.dates, x.times, UNIX_TIMESTAMP(e.created) as created, UNIX_TIMESTAMP(e.modified) as modified "
			       . ' FROM #__redevent_events AS e '
			       . ' INNER JOIN #__redevent_event_venue_xref AS x on x.eventid = e.id'
			       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id'
			       . ' WHERE xcat.category_id = '.$catid
			       . '   AND x.published = 1 '
			       . $params['days']
			       ." ORDER by x.dates,x.times,x.enddates,x.endtimes " . $params['limit'];
			$db->setQuery ($query);
			# echo $db->getQuery ();
			$rows = $db->loadObjectList();
			foreach ($rows as $event) {
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid  = $parent->uid .'x'.$event->xref;
				$node->name = $event->title.' - '.self::_formatdate($event->dates, $event->times, $params);
				$node->modified = ($event->modified? $event->modified : $event->created);
				$node->link = 'index.php?option=com_redevent&amp;view=details&amp;id='.$event->eventid.':'.$event->alias.'&amp;xref='.$event->xref;
				$node->priority   = $params['event_priority'];
				$node->changefreq = $params['event_changefreq'];
				$node->expandible = false;
				$xmap->printNode($node);
			}
		}

		$xmap->changeLevel(-1);
		
	}

	function getVenueTree(&$xmap, &$parent, $params,$id=0)
	{
		
		$db = &JFactory::getDBO();
		$gid = intval($xmap->gid);

 		$xmap->changeLevel(1);
		if ( !$id ) {
			$query = "SELECT id , venue, alias"
				. "\nFROM #__eventlist_venues"
				. "\nWHERE published = 1"
				. "\nORDER BY ordering";
			$db->setQuery($query);
			$venues = $db->loadObjectList();

			foreach($venues as $venue)
			{
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid   = $parent->uid.'v'.$venue->id;
		   		$node->name = $venue->venue;
				$node->link = 'index.php?option=com_redevent&amp;view=venueevents&amp;id='.$venue->id.':'.$venue->alias;
				$node->expandible = true;
				if ( $xmap->printNode($node) ) {
					xmap_com_redevent::getVenueTree($xmap, $parent,$params,$venue->id);
			}
	    	}
		} else {
			if ($params['include_events']) {

			$query = " SELECT x.id as xref, x.eventid, e.title,e.alias, x.dates, x.times, UNIX_TIMESTAMP(e.created) as created,UNIX_TIMESTAMP(e.modified) as modified "
			       . ' FROM #__redevent_events AS e '
			       . ' INNER JOIN #__redevent_event_venue_xref AS x on x.eventid = e.id'
			       . ' WHERE x.venueid = '.$id
			       . '   AND x.published = 1 '
			       . $params['days']
			       ." ORDER by x.dates,x.times,x.enddates,x.endtimes " . $params['limit'];
			$db->setQuery ($query);
			$rows = $db->loadObjectList();
			foreach($rows as $event) {
				$node = new stdclass;
				$node->id   = $parent->id;
				$node->uid  = $parent->uid .'x'.$event->xref;
				$node->name = $event->title.' - '.self::_formatdate($event->dates, $event->times, $params);
				$node->modified = ($event->modified? $event->modified : $event->created);
				$node->link = 'index.php?option=com_redevent&amp;view=details&amp;id='.$event->eventid.':'.$event->alias.'&amp;xref='.$event->xref;
				$node->priority   = $params['event_priority'];
				$node->changefreq = $params['event_changefreq'];
				$node->expandible = false;
				$xmap->printNode($node);
			}
			}
		}

		$xmap->changeLevel(-1);
		
	}

	function &getParam($arr, $name, $def) {
		$var = JArrayHelper::getValue( $arr, $name, $def, '' );
		return $var;
	}
	
	/**
	 * return true is a date is valid (not null, or 0000-00...)
	 * 
	 * @param string $date
	 * @return boolean
	 */
	function _isValidDate($date)
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
	function _formatdate($date, $time, $params)
	{		
		if(!self::_isValidDate($date)) {
			return JText::_('Open date');
		}
		
		if(!$time) {
			$time = '00:00:00';
		}
		
		//Format date
		$formatdate = strftime( $params['dateformat'], strtotime( $date.' '.$time ));
		
		return $formatdate;
	}
}
