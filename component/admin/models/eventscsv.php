<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent events csv Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelEventscsv extends RModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  Record Id
	 *
	 * @return  mixed
	 */
	public function getItem($pk = null)
	{
		return false;
	}

	/**
	 * Get rows
	 *
	 * @return mixed
	 */
	public function getItems()
	{
		$query = $this->_db->getQuery(true);

		$query->select('e.id, e.title, e.alias, e.course_code')
			->select('e.summary, e.datdescription')
			->select('e.datimage, e.published, e.registra, e.unregistra, e.max_multi_signup')
			->select('t.name as template_name')
			->select('x.title as session_title, x.alias as session_alias, x.id AS xref')
			->select('x.allday, x.dates, x.enddates, x.times, x.endtimes, x.registrationend')
			->select('x.note AS session_note, x.details AS session_details, x.icaldetails AS session_icaldetails')
			->select('x.maxattendees, x.maxwaitinglist, x.course_credit')
			->select('x.featured, x.external_registration_url, x.published as session_published')
			->select('u.name as creator_name, u.email AS creator_email')
			->select('v.venue, v.city')
			->from('#__redevent_events AS e')
			->join('INNER', '#__redevent_event_template AS t ON t.id =  e.template_id')
			->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = e.id')
			->join('LEFT', '#__redevent_venues AS v ON v.id = x.venueid')
			->join('LEFT', '#__redevent_event_category_xref AS xc ON xc.event_id = e.id')
			->join('LEFT', '#__users AS u ON e.created_by = u.id')
			->group('x.id, e.id')
			->order('e.id, x.dates');

		if ($categories = $this->getState('categories'))
		{
			$query->where("(xc.category_id = " . implode(" OR xc.category_id = ", $categories) . ')');
		}

		if ($venues = $this->getState('venues'))
		{
			$query->where("(x.venueid = " . implode(" OR x.venueid = ", $venues) . ')');
		}

		// Custom fields
		$replace = array();
		$fields = $this->getEventsCustomFieldsColumns();

		foreach ((array) $fields AS $f)
		{
			$query->select('e.' . $f->col);
			$replace[$f->name . '#' . $f->tag] = $f->col;
		}

		$fields = $this->getSessionsCustomFieldsColumns();

		foreach ((array) $fields AS $f)
		{
			$query->select('x.' . $f->col);
			$replace[$f->name . '#' . $f->tag] = $f->col;
		}

		$this->_db->setQuery($query);
		$results = $this->_db->loadAssocList();

		$cats = $this->getCategories();
		$pgs = $this->getPricegroups();

		foreach ($results as $k => $r)
		{
			if (isset($cats[$r['id']]))
			{
				$results[$k]['categories_names'] = $cats[$r['id']]->categories_names;
			}
			else
			{
				$results[$k]['categories_names'] = null;
			}

			if ($r['xref'] && isset($pgs[$r['xref']]))
			{
				$results[$k]['prices'] = $pgs[$r['xref']]->prices;
				$results[$k]['pricegroups_names'] = $pgs[$r['xref']]->pricegroups_names;
				$results[$k]['currencies'] = $pgs[$r['xref']]->currencies;
			}
			else
			{
				$results[$k]['prices'] = null;
				$results[$k]['pricegroups_names'] = null;
				$results[$k]['currencies'] = null;
			}

			foreach ($r as $col => $val)
			{
				if ($tag = array_search($col, $replace))
				{
					$results[$k]['custom_' . $tag] = $results[$k][$col];
					unset($results[$k][$col]);
				}
			}
		}

		return $results;
	}

	/**
	 * Get event custom fields
	 *
	 * @return mixed
	 */
	private function getEventsCustomFieldsColumns()
	{
		$query = $this->_db->getQuery(true);

		$query->select('CONCAT("custom", id) as col, name, tag')
			->from('#__redevent_fields')
			->where('object_key = ' . $this->_db->Quote('redevent.event'));

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * Get session custom fields
	 *
	 * @return mixed
	 */
	private function getSessionsCustomFieldsColumns()
	{
		$query = $this->_db->getQuery(true);

		$query->select('CONCAT("custom", id) as col, name, tag')
			->from('#__redevent_fields')
			->where('object_key = ' . $this->_db->Quote('redevent.xref'));

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * Get categories indexed by event id
	 *
	 * @return array
	 */
	private function getCategories()
	{
		$query = $this->_db->getQuery(true);

		$query->select('xc.event_id, GROUP_CONCAT(c.name SEPARATOR "#!#") AS categories_names')
			->from('#__redevent_event_category_xref AS xc')
			->join('LEFT', '#__redevent_categories AS c ON c.id = xc.category_id')
			->group('xc.event_id');
		$this->_db->setQuery($query);

		$cats = $this->_db->loadObjectList('event_id');

		return $cats;
	}

	/**
	 * Get price groups indexed by session id
	 *
	 * @return mixed
	 */
	private function getPricegroups()
	{
		$query = $this->_db->getQuery(true);

		$query->select('spg.xref')
			->select('GROUP_CONCAT(spg.price SEPARATOR "#!#") AS prices')
			->select('GROUP_CONCAT(pg.name SEPARATOR "#!#") AS pricegroups_names')
			->select('GROUP_CONCAT(spg.currency SEPARATOR "#!#") AS currencies')
			->from('#__redevent_sessions_pricegroups AS spg')
			->join('INNER', '#__redevent_pricegroups AS pg ON pg.id = spg.pricegroup_id')
			->group('spg.xref');

		$this->_db->setQuery($query);
		$pgs = $this->_db->loadObjectList('xref');

		return $pgs;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$filters = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (isset($filters['categories']) && is_array($filters['categories']))
		{
			$categories = $filters['categories'];
			JArrayHelper::toInteger($categories);
			$this->setState('categories', $categories);
		}

		if (isset($filters['venues']) && is_array($filters['venues']))
		{
			$venues = $filters['venues'];
			JArrayHelper::toInteger($venues);
			$this->setState('venues', $venues);
		}
	}
}
