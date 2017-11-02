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
	const DUPLICATE_CREATE_NEW = "create_new";
	const DUPLICATE_IGNORE = "ignore";
	const DUPLICATE_UPDATE = "update";

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
	private $templates;

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
	private $updatedEvents = 0;

	/**
	 * @var int
	 */
	private $ignoredEvents = 0;

	/**
	 * @var int
	 */
	private $createdEvents = 0;

	/**
	 * @var int
	 */
	private $createdSessions = 0;

	/**
	 * @var int
	 */
	private $updatedSessions = 0;

	/**
	 * @var int
	 */
	private $ignoredSessions = 0;

	/**
	 * @var array();
	 */
	private $errorMessages = array();

	/**
	 * @var    integer
	 * @since  3.2.4
	 */
	private $currentEventId;

	/**
	 * Return error messages
	 *
	 * @return array
	 */
	public function getErrorMessages()
	{
		return $this->errorMessages;
	}

	/**
	 * insert events/sessions in database
	 *
	 * @param   array   $records           array of records to import
	 * @param   string  $duplicate_method  method for handling duplicate record (ignore, create_new, update)
	 *
	 * @return boolean true on success
	 */
	public function import($records, $duplicate_method = self::DUPLICATE_IGNORE)
	{
		$this->duplicateMethod = $duplicate_method;
		$this->storedTitles = array();

		foreach ($records as $row => $r)
		{
			$this->replaceCustoms($r);

			try
			{
				if (!$eventId = $this->storeEvent($r))
				{
					continue;
				}

				$this->storeSession($r, $eventId);
			}
			catch (Exception $e)
			{
				$this->errorMessages[] = "row $row: " . $e->getMessage();
			}
		}

		$count = array(
			'added' => $this->createdEvents, 'updated' => $this->updatedEvents, 'ignored' => $this->ignoredEvents,
			'addedSessions' => $this->createdSessions, 'updatedSessions' => $this->updatedSessions, 'ignoredSessions' => $this->ignoredSessions
		);

		return $count;
	}

	/**
	 * Convert custom fields keys from import to custom<id> keys
	 *
	 * @param   array  $row  data
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
	 * @return boolean
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

			if ($this->duplicateMethod !== self::DUPLICATE_CREATE_NEW && !empty($data['id']))
			{
				// Load existing data
				$found = $ev->load($data['id']);

				// Discard if set to ignore duplicate
				if ($found && $this->duplicateMethod == self::DUPLICATE_IGNORE)
				{
					$this->ignoredEvents++;
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

			$data['template_id'] = $this->getTemplateId($data);

			// Bind submitted data
			$ev->bind($data);

			if ($this->duplicateMethod == self::DUPLICATE_UPDATE && $found)
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
				throw new InvalidArgumentException(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $ev->getError());
			}

			if (!$ev->store())
			{
				throw new InvalidArgumentException(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $ev->getError());
			}

			// Track created
			$this->storedTitles[$ev->id] = $data['title'];

			// Trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterEventSaved', array($ev->id));

			($updating ? $this->updatedEvents++ : $this->createdEvents++);

			$this->currentEventId = $ev->id;

			return $this->currentEventId;
		}
		elseif ($this->currentEventId)
		{
			return $this->currentEventId;
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
		if (isset($data['xref']))
		{
			$data['id'] = $data['xref'];
			$session = RTable::getAdminInstance('Session');
			$session->load($data['xref']);

			$exists = $session->id > 0;

			if ($exists && $this->duplicateMethod == self::DUPLICATE_IGNORE)
			{
				$this->ignoredSessions++;

				return true;
			}

			$venueid = $this->getVenueId($data['venue'], $data['city']);
			$data['eventid'] = $eventId;
			$data['venueid'] = $venueid;

			// Remap fields
			$data['title'] = $data['session_title'] ?: null;
			$data['alias'] = $data['session_alias'] ?: null;
			$data['note'] = $data['session_note'] ?: null;
			$data['details'] = $data['session_details'] ?: null;
			$data['icaldetails'] = $data['session_icaldetails'] ?: null;
			$data['published'] = $data['session_published'] ?: null;

			// Import pricegroups
			if (isset($data['pricegroups_names']))
			{
				$pgs = explode('#!#', $data['pricegroups_names']);
				$prices = explode('#!#', $data['prices']);
				$currencies = explode('#!#', $data['currencies']);
				$pricegroups = array();

				$i = 0;

				foreach ($pgs as $k => $v)
				{
					if (empty($v))
					{
						continue;
					}

					$pricegroups['pricegroup'][$i] = $this->getPgId($v);
					$pricegroups['price'][$i] = $prices[$k];
					$pricegroups['currency'][$i] = $currencies[$k];
					$i++;
				}

				$data['new_prices'] = $pricegroups;
			}

			if ($this->duplicateMethod == self::DUPLICATE_UPDATE && $exists)
			{
				$isUpdate = true;
				$session->bind($data);
			}
			else
			{
				$isUpdate = false;
				$session->reset();
				$session->bind($data);

				// If session->id is set, JTable will run an update query, which is not what we want.
				$session->id = null;
			}

			// Check
			if (!$session->check())
			{
				throw new InvalidArgumentException(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $session->getError());
			}

			// Store
			if (!$session->store())
			{
				throw new InvalidArgumentException(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $session->getError());
			}

			// Trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterSessionSaved', array($session->id));

			if ($isUpdate)
			{
				$this->updatedSessions++;
			}
			else
			{
				$this->createdSessions++;
			}
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
	 * Get event template id
	 *
	 * @param   array  $data  csv row data
	 *
	 * @return integer
	 */
	private function getTemplateId($data)
	{
		$templates = $this->getTemplates();

		if (empty($data['template_name']))
		{
			throw new InvalidArgumentException('template_name column is required');
		}

		if ($id = array_search($data['template_name'], $templates))
		{
			return $id;
		}

		// Try to create if not found
		$model = RModel::getAdminInstance('Eventtemplate');
		unset($data['id']);
		$data['name'] = $data['template_name'];

		if (!$model->save($data))
		{
			throw new RuntimeException($model->getError());
		}

		$id = $model->getState('eventtemplate.id');
		$this->templates[$id] = $data['template_name'];

		return $id;
	}

	/**
	 * Get templates
	 *
	 * @return array index by template id
	 */
	private function getTemplates()
	{
		if (is_null($this->templates))
		{
			$this->templates = array();

			$model = RModel::getAdminInstance('Eventtemplates', array('ignore_request' => true));
			$model->setState('list.limit', 0);
			$templates = $model->getItems();

			if (!$templates)
			{
				return $this->templates;
			}

			foreach ($templates as $template)
			{
				$this->templates[$template->id] = $template->name;
			}
		}

		return $this->templates;
	}

	/**
	 * Return venue id matching name, creating if needed
	 *
	 * @param   string  $name  venue name
	 * @param   string  $city  venue city
	 *
	 * @return integer id
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
	 * @return integer id
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
