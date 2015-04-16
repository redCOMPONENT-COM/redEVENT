<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent events csv import Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelEventscsvimport extends RModel
{
	/**
	 * @var string
	 */
	private $duplicateMethod;

	/**
	 * @var array
	 */
	private $customsimport;

	/**
	 * @var array
	 */
	private $categories;

	/**
	 * @var array
	 */
	private $venues;

	/**
	 * @var array
	 */
	private $pgs;

	/**
	 * @var array
	 */
	private $storedTitles;

	/**
	 * @var int
	 */
	private $updated = 0;

	/**
	 * @var int
	 */
	private $ignored = 0;

	/**
	 * @var int
	 */
	private $createdEvents = 0;

	/**
	 * @var int
	 */
	private $createdSessions = 0;

	/**
	 * insert events/sessions in database
	 *
	 * @param   array   $records           array of records to import
	 * @param   string  $duplicate_method  method for handling duplicate record (ignore, create_new, update)
	 *
	 * @return boolean true on success
	 */
	public function import($records, $duplicate_method = 'ignore')
	{
		$this->duplicateMethod = $duplicate_method;
		$this->storedTitles = array();

		foreach ($records as $r)
		{
			$this->replaceCustoms($r);

			if (!$eventId = $this->storeEvent($r))
			{
				continue;
			}

			$this->storeSession($r, $eventId);
		}

		$count = array('added' => $this->createdEvents, 'updated' => $this->updated, 'ignored' => $this->ignored);

		return $count;
	}

	/**
	 * Convert custom fields keys from import to custom<id> keys
	 *
	 * @param   array  &$row  data
	 *
	 * @return array
	 */
	private function replaceCustoms(&$row)
	{
		$fields = $this->getCustoms();

		foreach ($row as $col => $val)
		{
			if ($name = array_search($col, $fields))
			{
				$row[$name] = $row[$col];
				unset($row[$col]);
			}
		}

		return $row;
	}

	/**
	 * return csv header names for event tags
	 *
	 * @return array
	 */
	private function getCustoms()
	{
		if (empty($this->customsimport))
		{
			$query = $this->_db->getQuery(true);

			$query->select('CONCAT("custom", id) as col, CONCAT("custom_", name, "#", tag) as csvcol')
				->from('#__redevent_fields');

			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			$result = array();

			foreach ($res as $r)
			{
				$result[$r->col] = $r->csvcol;
			}

			$this->customsimport = $result;
		}

		return $this->customsimport;
	}

	/**
	 * Store event
	 *
	 * @param   array  $data  event data from import
	 *
	 * @return bool
	 */
	private function storeEvent($data)
	{
		$app = JFactory::getApplication();

		if (!empty($data['title']))
		{
			if ($id = array_search($data['title'], $this->storedTitles))
			{
				// Already stored
				return $id;
			}

			$ev = RTable::getAdminInstance('Event');

			if ($this->duplicateMethod !== 'create_new' && isset($data['id']) && $data['id'])
			{
				// Load existing data
				$found = $ev->load($data['id']);

				// Discard if set to ignore duplicate
				if ($found && $this->duplicateMethod == 'ignore')
				{
					$this->ignored++;
					$this->storedTitles[$data['id']] = $data['title'];

					return $data['id'];
				}
			}
			else
			{
				$found = false;
			}

			// Categories relations
			$cats = explode('#!#', $data['categories_names']);
			$cats_ids = array();

			foreach ($cats as $c)
			{
				$cats_ids[] = $this->getCategoryId($c);
			}

			$data['categories'] = $cats_ids;

			// Bind submitted data
			$ev->bind($data);

			if ($this->duplicateMethod == 'update' && $found)
			{
				$updating = 1;
			}
			else
			{
				// Create new
				$ev->id = null;
				$updating = 0;
			}

			// Store !
			if (!$ev->check())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $ev->getError(), 'error');

				return false;
			}

			if (!$ev->store())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $ev->getError(), 'error');

				return false;
			}

			// Track created
			$this->storedTitles[$ev->id] = $data['title'];

			// Trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterEventSaved', array($ev->id));

			($updating ? $this->updated++ : $this->createdEvents++);

			return $ev->id;
		}

		return false;
	}

	/**
	 * Store a session
	 *
	 * @param   array  $data     data
	 * @param   int    $eventId  event id
	 *
	 * @return boolean
	 */
	private function storeSession($data, $eventId)
	{
		$app = JFactory::getApplication();

		if (isset($data['xref']) && $data['xref'])
		{
			$venueid = $this->getVenueId($data['venue'], $data['city']);

			$session = RTable::getAdminInstance('Session');
			$session->bind($data);

			$session->id = null;
			$session->eventid = $eventId;
			$session->venueid = $venueid;

			// Renamed fields
			if (isset($data['session_title']))
			{
				$session->title = $data['session_title'];
			}

			if (isset($data['session_alias']))
			{
				$session->alias = $data['session_alias'];
			}

			if (isset($data['session_note']))
			{
				$session->note = $data['session_note'];
			}

			if (isset($data['session_details']))
			{
				$session->details = $data['session_details'];
			}

			if (isset($data['session_icaldetails']))
			{
				$session->icaldetails = $data['session_icaldetails'];
			}

			if (isset($data['session_published']))
			{
				$session->published = $data['session_published'];
			}

			// Check
			if (!$session->check())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $session->getError(), 'error');

				return false;
			}

			// Store
			if (!$session->store())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $session->getError(), 'error');

				return false;
			}

			// Trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterSessionSaved', array($session->id));

			// Import pricegroups
			$pgs = explode('#!#', $data['pricegroups_names']);
			$prices = explode('#!#', $data['prices']);
			$currencies = explode('#!#', $data['currencies']);
			$pricegroups = array();

			foreach ($pgs as $k => $v)
			{
				if (empty($v))
				{
					continue;
				}

				$price = new stdclass;
				$price->pricegroup_id    = $this->getPgId($v);
				$price->price = $prices[$k];
				$price->currency = $currencies[$k];
				$pricegroups[] = $price;
			}

			$session->setPrices($pricegroups);

			$this->createdSessions++;

			return true;
		}

		return true;
	}

	/**
	 * Return category id matching name, creating if needed
	 *
	 * @param   string  $name  category name
	 *
	 * @return id cat id
	 */
	private function getCategoryId($name)
	{
		$id = array_search($name, $this->getCategories());

		if ($id === false)
		{
			// Doesn't exist, create it
			$new = RTable::getAdminInstance('Category');
			$new->name = $name;
			$new->published = 1;
			$new->store();
			$id = $new->id;
			$this->categories[$id] = $name;
		}

		return $id;
	}

	/**
	 * returns array of current categories names indexed by ids
	 *
	 * @return array
	 */
	private function getCategories()
	{
		if (empty($this->categories))
		{
			$this->categories = array();

			$query = $this->_db->getQuery(true);

			$query->select('id, name')
				->from('#__redevent_categories');

			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();

			foreach ((array) $res as $r)
			{
				$this->categories[$r->id] = $r->name;
			}
		}

		return $this->categories;
	}


	/**
	 * Return venue id matching name, creating if needed
	 *
	 * @param   string  $name  venue name
	 * @param   string  $city  venue city
	 *
	 * @return int id
	 */
	private function getVenueId($name, $city)
	{
		if (empty($name))
		{
			return 0;
		}

		$id = 0;

		foreach ($this->getVenues() as $k => $v)
		{
			if ($name == $v->venue && $city == $v->city)
			{
				$id = $k;
				break;
			}
		}

		if (!$id)
		{
			// Doesn't exist, create it
			$new = RTable::getAdminInstance('Venue');
			$new->venue = $name;
			$new->city  = $city;
			$new->store();
			$id = $new->id;
			$this->venues[$id] = $new;
		}

		return $id;
	}

	/**
	 * returns array of current venue indexed by ids
	 *
	 * @return array
	 */
	private function getVenues()
	{
		if (empty($this->venues))
		{
			$this->venues = array();

			$query = $this->_db->getQuery(true);

			$query->select('id, venue, city')
				->from('#__redevent_venues');

			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();

			foreach ((array) $res as $r)
			{
				$this->venues[$r->id] = $r;
			}
		}

		return $this->venues;
	}

	/**
	 * Return price group id matching name, creating if needed
	 *
	 * @param   string  $name  price group name
	 *
	 * @return int id
	 */
	private function getPgId($name)
	{
		$id = array_search($name, $this->getPricegroups());

		if ($id === false)
		{
			// Doesn't exist, create it
			$new = RTable::getAdminInstance('Pricegroup');
			$new->name = $name;
			$new->store();
			$id = $new->id;
			$this->pgs[$id] = $name;
		}

		return $id;
	}

	/**
	 * returns array of current cats names indexed by ids
	 *
	 * @return array
	 */
	private function getPricegroups()
	{
		if (empty($this->pgs))
		{
			$this->pgs = array();

			$query = $this->_db->getQuery(true);

			$query->select('id, name')
				->from('#__redevent_pricegroups');

			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();

			foreach ((array) $res as $r)
			{
				$this->pgs[$r->id] = $r->name;
			}
		}

		return $this->pgs;
	}
}
