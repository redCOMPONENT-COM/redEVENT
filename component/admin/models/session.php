<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Session
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelSession extends RModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item && $item->id)
		{
			// Get additional data
			$query = $this->_db->getQuery(true);

			$query->select('e.title AS event_title')
				->from('#__redevent_events AS e')
				->where('e.id = ' . $item->eventid);

			$this->_db->setQuery($query);
			$res = $this->_db->loadObject();

			$item->event_title = $res->event_title;
		}

		return $item;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		if ($this->getState('session.id'))
		{
			$form->setFieldAttribute('eventid', 'readonly', '1');
		}

		return $form;
	}

	/**
	 * Override for custom fields
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		// First get the data from form itself
		if (!$validData = parent::validate($form, $data, $group))
		{
			return false;
		}

		// Now add custom fields
		$fields = $this->getSessionCustomFieldsFromDb();

		foreach ($fields as $field)
		{
			$dbname = 'custom' . $field->id;

			if (isset($data[$dbname]))
			{
				$validData[$dbname] = $data[$dbname];
			}
		}

		return $validData;
	}

	/**
	 * get custom fields
	 *
	 * @return objects array
	 */
	public function getCustomfields()
	{
		$result = $this->getSessionCustomFieldsFromDb();

		$fields = array();
		$data = $this->getItem();

		foreach ($result as $c)
		{
			$field = RedeventFactoryCustomfield::getField($c->type);
			$field->bind($c);
			$prop = 'custom' . $c->id;

			if (isset($data->$prop))
			{
				$field->value = $data->$prop;
			}

			$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * Get custom field raw object from db
	 *
	 * @return array|mixed
	 */
	protected function getSessionCustomFieldsFromDb()
	{
		$query = $this->_db->getQuery(true);

		$query->select('f.*')
			->from('#__redevent_fields AS f')
			->where('f.object_key = ' . $this->_db->Quote("redevent.xref"))
			->order('f.ordering');

		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();

		if (!$result)
		{
			return array();
		}

		return $result;
	}

	/**
	 * return xref from request
	 *
	 * @return unknown
	 */
	public function getXref()
	{
		$xref = $this->_id;

		if ($xref)
		{
			$customs = $this->_getXCustomFields();

			$query = ' SELECT x.*, v.venue, r.id as recurrence_id, r.rrule, rp.count ';
			$query .= ' , e.title as event_title ';
			// add the custom fields
			foreach ((array) $customs as $c)
			{
				$query .= ', x.custom'. $c->id;
			}

			$query .= ' FROM #__redevent_event_venue_xref AS x '
			. ' INNER JOIN #__redevent_events AS e on e.id = x.eventid '
			. ' LEFT JOIN #__redevent_venues AS v on v.id = x.venueid '
			. ' LEFT JOIN #__redevent_repeats AS rp on rp.xref_id = x.id '
			. ' LEFT JOIN #__redevent_recurrences AS r on r.id = rp.recurrence_id '
			;

			$query .= ' WHERE x.id = '. $this->_db->Quote($xref);

			$this->_db->setQuery($query);
			$object = $this->_db->loadObject();
			$object->rrules = RedeventHelperRecurrence::getRule($object->rrule);
		}
		else
		{
			$object = JTable::getInstance('RedEvent_eventvenuexref', '');
			$object->id    = null;
			$object->venue = 0;
			$object->recurrence_id = 0;
			$object->rrule = '';
			$object->count = 0;
			$object->rrules = RedeventHelperRecurrence::getRule();

			// event title and id from request, if event is already created
			if ($object->event_id = JFactory::getApplication()->input->getInt('eventid'))
			{
				$db      = $this->_db;
				$query = $db->getQuery(true);

				$query->select('title');
				$query->from('#__redevent_events');
				$query->where('id = ' . $object->event_id);

				$db->setQuery($query);

				if (!$object->event_title = $db->loadResult())
				{
					throw new Exception('Undefined event id for new session');
				}
			}
		}

		return $object;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		if (!parent::save($data))
		{
			return false;
		}

		if (!$this->saveRecurrence($data))
		{
			return false;
		}

		if (!$this->saveRoles($data))
		{
			return false;
		}

		if (!$this->savePrices($data))
		{
			return false;
		}

		return true;
	}

	/**
	 * Save recurrence data
	 *
	 * @param   array  $data  post data
	 *
	 * @return bool
	 */
	private function saveRecurrence($data)
	{
		if (!$sessionId = $this->getState('session.id'))
		{
			return false;
		}

		$recurrence = RTable::getInstance('Recurrence', 'RedeventTable');
echo '<pre>'; echo print_r($data, true); echo '</pre>'; exit;
		if (!$data['recurrenceid'])
		{
			$rrule = RedeventHelperRecurrence::parsePost($data);

			if (!empty($rrule))
			{
				// New recurrence
				$recurrence->rrule = $rrule;

				if (!$recurrence->store())
				{
					$this->setError($recurrence->getError());

					return false;
				}

				// Add repeat record
				$repeat = RTable::getInstance('Repeat', 'RedeventTable');
				$repeat->set('xref_id', $sessionId);
				$repeat->set('recurrence_id', $recurrence->id);
				$repeat->set('count', 0);

				if (!$repeat->store())
				{
					$this->setError($repeat->getError());

					return false;
				}
			}
		}
		else
		{
			// Only update if it's the first session of the 'recurrence'.
			if ($data['repeat'] == 0)
			{
				$recurrence->load($data['recurrenceid']);

				// Reset the status
				$recurrence->ended = 0;

				// TODO: maybe add a check to have a choice between updating rrule or not...
				$rrule = RedeventHelperRecurrence::parsePost($data);
				$recurrence->rrule = $rrule;

				if (!$recurrence->store())
				{
					$this->setError($recurrence->getError());

					return false;
				}
			}
		}

		if ($recurrence->id)
		{
			RedeventHelper::generaterecurrences($recurrence->id);
		}

		return true;
	}

	/**
	 * Save roles data
	 *
	 * @param   array  $data  post data
	 *
	 * @return bool
	 */
	private function saveRoles($data)
	{
		if (!$sessionId = $this->getState('session.id'))
		{
			return false;
		}

		// First remove current rows
		$query = $this->_db->getQuery(true);

		$query->delete('#__redevent_sessions_roles')
			->where('xref = ' . $sessionId);
		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Then recreate them if any
		foreach ((array) $data['rrole'] as $k => $r)
		{
			if (!($data['rrole'][$k] && $data['urole'][$k]))
			{
				continue;
			}

			$new = RTable::getAdminInstance('Sessionrole');
			$new->set('xref', $sessionId);
			$new->set('role_id', $r);
			$new->set('user_id', $data['urole'][$k]);

			if (!($new->check() && $new->store()))
			{
				$this->setError($new->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Save prices data
	 *
	 * @param   array  $data  post data
	 *
	 * @return bool
	 */
	private function savePrices($data)
	{
		if (!$sessionId = $this->getState('session.id'))
		{
			return false;
		}

		// First remove current rows
		$query = $this->_db->getQuery(true);

		$query->delete('#__redevent_sessions_pricegroups')
			->where('xref = ' . $sessionId);
		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Then recreate them if any
		foreach ((array) $data['pricegroup'] as $k => $r)
		{
			if (!($data['pricegroup'][$k]))
			{
				continue;
			}

			$new = RTable::getInstance('Sessionpricegroup', 'RedeventTable');
			$new->set('xref', $sessionId);
			$new->set('pricegroup_id', $r);
			$new->set('price', $data['price'][$k]);
			$new->set('currency', $data['currency'][$k]);

			if (!($new->check() && $new->store()))
			{
				$this->setError($new->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * remove xref if there is no attendees
	 *
	 * @param int xref_id
	 * @return boolean result true on success
	 */
	function removexref($id)
	{
		// do not delete xref if there are attendees
		$query = ' SELECT COUNT(*) FROM #__redevent_register WHERE xref = '. $this->_db->Quote((int)$id);
		$this->_db->setQuery($query);
		if ($this->_db->loadResult()) {
			$this->setError(JText::_('COM_REDEVENT_CANNOT_DELETE_XREF_HAS_REGISTRATIONS'));
			return false;
		}

		$q = "DELETE FROM #__redevent_event_venue_xref WHERE id =". $this->_db->Quote((int)$id);
		$this->_db->setQuery($q);
		if (!$this->_db->query()) {
			$this->setError(JText::_('COM_REDEVENT_DB_ERROR_DELETING_XREF'));
			return false;
		}

		// delete corresponding roles
		$q = "DELETE FROM #__redevent_sessions_roles WHERE xref =". $this->_db->Quote((int)$id);
		$this->_db->setQuery($q);
		if (!$this->_db->query()) {
			$this->setError(JText::_('COM_REDEVENT_DB_ERROR_DELETING_XREF_ROLES'));
			return false;
		}

		// delete corresponding prices
		$q = "DELETE FROM #__redevent_sessions_pricegroups WHERE xref =". $this->_db->Quote((int)$id);
		$this->_db->setQuery($q);
		if (!$this->_db->query()) {
			$this->setError(JText::_('COM_REDEVENT_DB_ERROR_DELETING_XREF_ROLES'));
			return false;
		}

		// delete corresponding record in repeats table in case of recurrences
		$q = "DELETE FROM #__redevent_repeats WHERE xref_id =". $this->_db->Quote((int)$id);
		$this->_db->setQuery($q);
		if (!$this->_db->query()) {
			$this->setError(JText::_('COM_REDEVENT_DB_ERROR_DELETING_XREF_REPEAT'));
			return false;
		}

		return true;
	}

	/**
	 * returns all custom fields for xrefs
	 *
	 * @return array
	 */
	function _getXCustomFields()
	{
		if (empty($this->_xrefcustomfields))
		{
			$query = ' SELECT f.id, f.name, f.in_lists, f.searchable '
			. ' FROM #__redevent_fields AS f'
			. ' WHERE f.published = 1'
			. '   AND f.object_key = '. $this->_db->Quote('redevent.xref')
			. ' ORDER BY f.ordering ASC '
			;
			$this->_db->setQuery($query);
			$this->_xrefcustomfields = $this->_db->loadObjectList();
		}
		return $this->_xrefcustomfields;
	}

	/**
	 * Return session roles
	 *
	 * @return mixed
	 */
	public function getSessionRoles()
	{
		if (!$this->getState('session.id'))
		{
			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->select('sr.*')
			->from('#__redevent_sessions_roles AS sr')
			->join('INNER', '#__redevent_roles AS r ON r.id = sr.role_id')
			->where('sr.xref = ' . $this->_db->Quote($this->getState('session.id')))
			->order('r.ordering');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * Return session price groups
	 *
	 * @return mixed
	 */
	public function getSessionPrices()
	{
		if (!$this->getState('session.id'))
		{
			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->select('r.*')
			->from('#__redevent_sessions_pricegroups AS r')
			->join('INNER', '#__redevent_pricegroups AS pg ON pg.id = r.pricegroup_id')
			->where('r.xref = ' . $this->_db->Quote($this->getState('session.id')))
			->order('pg.ordering');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * Return Roles types
	 *
	 * @return mixed
	 */
	public function getRolesOptions()
	{
		$query = $this->_db->getQuery(true);

		$query->select('id AS value, name AS text')
			->from('#__redevent_roles')
			->order('ordering ASC');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * Return price groups names
	 *
	 * @return mixed
	 */
	public function getPricegroupsOptions()
	{
		$query = $this->_db->getQuery(true);

		$query->select('id AS value, name AS text')
			->from('#__redevent_pricegroups')
			->order('ordering ASC');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}
}
