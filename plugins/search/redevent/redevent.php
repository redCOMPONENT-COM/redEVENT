<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 *
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

// Load redEVENT library
$redeventLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';

if (!file_exists($redeventLoader))
{
	throw new Exception(JText::_('COM_REDEVENT_INIT_FAILED'), 404);
}

include_once $redeventLoader;

RedeventBootstrap::bootstrap();

/**
 * Class plgSearchRedevent
 *
 * @package     Redevent.Frontend
 * @subpackage  Plugins
 * @since       1.0
 */
class PlgSearchRedevent extends JPlugin
{
	/**
	 * @var array
	 */
	protected $customFieldsQuoted;

	/**
	 * @var JDatabase|JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();

		$this->db = isset($config['db']) ? $config['db'] : JFactory::getDbo();
	}

	/**
	 * Return search areas
	 *
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
	 * Redevent Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 *
	 * @param   string  $text      Target search string
	 * @param   string  $phrase    matching option, exact|any|all
	 * @param   string  $ordering  ordering option, newest|oldest|popular|alpha|category
	 * @param   mixed   $areas     An array if the search it to be restricted to areas, null if search all
	 *
	 * @return array
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$user = JFactory::getUser();

		// If the array is not correct, return it:
		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$limit = $this->params->def('search_limit', 50);

		$text = trim($text);

		if (!$text)
		{
			return array();
		}

		$rows = array();

		$search = $this->db->Quote(JText::_('PLG_REDEVENT_SEARCH_EVENTS'));

		if (!$areas || in_array('redeventevents', $areas))
		{
			$query = $this->db->getQuery(true);

			switch ($phrase)
			{
				// Search exact
				case 'exact':
					$query->where($this->eventLike($text));
					break;

				case 'all':
				case 'any':
				default:
					$words = explode(' ', $text);
					$wheres = array();

					foreach ($words as $word)
					{
						$wheres[] = $this->eventLike($word);
					}

					$query->where('(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')');
					break;
			}

			switch ($ordering)
			{
				// Alphabetic, ascending
				case 'alpha':
					$order = 'e.title ASC, x.dates ASC';
					break;

				// Oldest first
				case 'oldest':
					$order = 'x.dates ASC';
					break;

				// Newest first
				case 'newest':
					$order = 'x.dates DESC';
				break;

				case 'popular':
				default:
					$order = 'x.dates ASC';
			}

			$query->order($order);

			// The database query;
			$query->select('e.summary AS text, x.id AS xref, x.dates, x.times')
				->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', e.title, x.title) ELSE e.title END as title')
				->select('CONCAT_WS( " / ", ' . $search . ', ' . $this->db->Quote(JText::_('PLG_REDEVENT_SEARCH_EVENTS')) . ' ) AS section')
				->select('CASE WHEN CHAR_LENGTH( e.alias ) THEN CONCAT_WS( \':\', x.id, e.alias ) ELSE x.id END AS slug')
				->select('NULL AS created')
				->select('"2" AS browsernav')
				->from('#__redevent_events AS e')
				->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id')
				->where('x.published = 1');

			// Set query
			$this->db->setQuery($query, 0, $limit);
			$results = $this->db->loadObjectList();

			foreach ($results as $key => $row)
			{
				// The 'output' of the displayed link
				$results[$key]->href = RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref);

				// Date
				if ($this->params->get('include_date', 1))
				{
					if (strtotime($row->dates))
					{
						if ($this->params->get('include_date', 1) == 2 && $row->times <> '00:00:00')
						{
							$results[$key]->title .= ' - '
								. strftime($this->params->get('date_format', '%x'), strtotime($row->dates . ' ' . $row->times));
						}
						else
						{
							$results[$key]->title .= ' - ' . strftime($this->params->get('date_format', '%x'), strtotime($row->dates));
						}
					}
					else
					{
						$results[$key]->title .= ' - ' . JText::_('PLG_REDEVENT_SEARCH_OPEN_DATE');
					}
				}
			}

			$rows = array_merge($rows, $results);
		}

		if (!$areas || in_array('redeventcategories', $areas))
		{
			$query = $this->db->getQuery(true)
				->where('c.published = 1');

			switch ($phrase)
			{
				// Search exact
				case 'exact':
					$string = $this->db->Quote('%' . $this->db->escape($text, true) . '%', false);
					$query->where('LOWER(c.name) LIKE ' . $string);
					break;

				// Search all or any
				case 'all':
				case 'any':
				default:
					$words = explode(' ', $text);
					$wheres = array();

					foreach ($words as $word)
					{
						$word = $this->db->Quote('%' . $this->db->escape($word, true) . '%', false);
						$wheres[] = 'LOWER(c.name) LIKE ' . $word;
					}

					$query->where('(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')');
					break;
			}

			// Ordering of the results
			switch ($ordering)
			{
				// Alphabetic, ascending
				case 'alpha':
					$order = 'c.name ASC';
					break;

				// Oldest first
				case 'oldest':

					// Popular first
				case 'popular':

					// Newest first
				case 'newest':

					// Default setting: alphabetic, ascending
				default:
					$order = 'c.name ASC';
			}

			$query->order($order);

			// The database query; differs per situation! It will look something like this:
			$query->select('c.name AS title')
				->select('CONCAT_WS( " / ", ' . $search . ', ' . $this->db->Quote(JText::_('PLG_REDEVENT_SEARCH_CATEGORIES')) . ' ) AS section')
				->select('CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( \':\', c.id, c.alias ) ELSE c.id END AS slug')
				->select('NULL AS created')
				->select('"2" AS browsernav')
				->from('#__redevent_categories AS c');

			// Set query
			$this->db->setQuery($query, 0, $limit);
			$results = $this->db->loadObjectList();

			// The 'output' of the displayed link
			foreach ($results as $key => $row)
			{
				$results[$key]->href = RedeventHelperRoute::getCategoryEventsRoute($row->slug);
			}

			$rows = array_merge($rows, $results);
		}

		if (!$areas || in_array('redeventvenues', $areas))
		{
			$query = $this->db->getQuery(true);

			switch ($phrase)
			{
				case 'exact':
					$string = $this->db->Quote('%' . $this->db->escape($text, true) . '%', false);
					$query->where('LOWER(v.venue) LIKE ' . $string);
					break;

				case 'all':
				case 'any':
				default:
					$words = explode(' ', $text);
					$wheres = array();

					foreach ($words as $word)
					{
						$word = $this->db->Quote('%' . $this->db->escape($word, true) . '%', false);
						$wheres[] = 'LOWER(v.venue) LIKE ' . $word;
					}

					$query->where('(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')');
					break;
			}

			switch ($ordering)
			{
				case 'alpha':
					$order = 'v.venue ASC';
					break;

				case 'oldest':
				case 'popular':
				case 'newest':
				default:
					$order = 'v.venue ASC';
			}

			$query->order($order);

			$query->select('v.venue AS title')
				->select('CONCAT_WS( " / ", ' . $search . ', ' . $this->db->Quote(JText::_('PLG_REDEVENT_SEARCH_VENUES')) . ' ) AS section')
				->select('CASE WHEN CHAR_LENGTH( v.alias ) THEN CONCAT_WS( \':\', v.id, v.alias ) ELSE v.id END AS slug')
				->select('NULL AS created')
				->select(' "2" AS browsernav')
				->from('#__redevent_venues AS v')
				->where('v.published = 1');

			$this->db->setQuery($query, 0, $limit);
			$results = $this->db->loadObjectList();

			foreach ($results as $key => $row)
			{
				$results[$key]->href = RedeventHelperRoute::getVenueEventsRoute($row->slug);
			}

			$rows = array_merge($rows, $results);
		}

		return $rows;
	}

	/**
	 * return OR condition on field matches for event
	 *
	 * @param   string  $text  to search
	 *
	 * @return string
	 */
	protected function eventLike($text)
	{
		$fields = array(
			$this->db->quoteName('e.title'),
			$this->db->quoteName('x.title')
		);

		if ($custom = $this->getQuotedCustomFields())
		{
			$fields = array_merge($fields, $custom);
		}

		$conditions = array();
		$search = $this->db->Quote('%' . $this->db->escape($text, true) . '%', false);

		foreach ($fields as $field)
		{
			$conditions[] = $field . ' LIKE ' . $search;
		}

		return '(' . implode(' OR ', $conditions) . ')';
	}

	/**
	 * Get quoted custom fields
	 *
	 * @return array
	 */
	protected function getQuotedCustomFields()
	{
		if (!$this->customFieldsQuoted)
		{
			$fieldNames = array();

			// Get the fields
			$query = $this->db->getQuery(true)
				->select('f.id, f.object_key')
				->from('#__redevent_fields AS f')
				->where('f.published = 1')
				->where('f.searchable = 1')
				->order('f.ordering ASC');
			$this->db->setQuery($query);
			$rows = $this->db->loadObjectList();

			foreach ($rows as $field)
			{
				if ($field->object_key == 'redevent.event')
				{
					$fieldname = 'e.custom' . $field->id;
				}
				elseif ($field->object_key == 'redevent.xref')
				{
					$fieldname = 'x.custom' . $field->id;
				}
				else
				{
					continue;
				}

				$fieldNames[] = $this->db->quoteName($fieldname);
			}

			$this->customFieldsQuoted = $fieldNames;
		}

		return $this->customFieldsQuoted;
	}
}
