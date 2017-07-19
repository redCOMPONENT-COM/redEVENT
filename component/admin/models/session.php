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
	 * copy sessions
	 *
	 * @param   array  $session_ids  session ids
	 *
	 * @return boolean true on success
	 */
	public function copy($session_ids)
	{
		foreach ($session_ids as $id)
		{
			$row = $this->getTable('session');
			$row->load($id);
			$row->id = null;
			$row->checked_out = 0;
			$row->checked_out_time = 0;
			$row->note = Jtext::sprintf('COM_REDEVENT_COPY_OF_S', $id);

			// Pre-save checks

			if (!$row->check())
			{
				$this->setError($row->getError(), 'error');

				return false;
			}

			// Save the changes

			if (!$row->store())
			{
				$this->setError($row->getError(), 'error');

				return false;
			}

			// Copy associated prices
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('*')->from('#__redevent_sessions_pricegroups')->where('xref = ' . $id);

			$db->setQuery($query);
			$res = $db->loadObjectList();

			foreach ($res as $r)
			{
				// Load the table

				$pricerow = $this->getTable('Sessionpricegroup');
				$pricerow->bind(get_object_vars($r));
				$pricerow->id = null;
				$pricerow->xref = $row->id;

				// Save the changes

				if (!$pricerow->store())
				{
					$this->setError($pricerow->getError(), 'error');

					return false;
				}
			}
		}

		return true;
	}

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

		$recurrenceHelper = new RedeventRecurrenceHelper;

		if ($item && $item->id)
		{
			// Get additional data
			$query = $this->_db->getQuery(true);

			$query->select('e.title AS event_title')
				->select('r.id as recurrence_id, r.rrule, rp.count')
				->from('#__redevent_event_venue_xref AS x')
				->innerJoin('#__redevent_events AS e on x.eventid = e.id')
				->leftJoin('#__redevent_repeats AS rp on rp.xref_id = x.id')
				->leftJoin('#__redevent_recurrences AS r on r.id = rp.recurrence_id')
				->where('x.id = ' . $item->id);

			$this->_db->setQuery($query);
			$res = $this->_db->loadObject();

			$item->event_title = $res->event_title;
			$rule = $recurrenceHelper->getRule($res->rrule);
			$item->recurrence = $rule->getFormData();
			$item->recurrence->recurrenceid = $res->recurrence_id;
			$item->recurrence->repeat = $res->count;
		}
		else
		{
			$rule = $recurrenceHelper->getRule();
			$item->recurrence = $rule->getFormData();
			$item->recurrence->recurrenceid = 0;
			$item->recurrence->repeat = 0;

			if ($this->getState('eventId'))
			{
				$item->eventid = $this->getState('eventId');
			}
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

		// Do not allow to modify the session event once created
		if ($form->getValue('id') || $this->getState('eventId'))
		{
			$form->setFieldAttribute('eventid', 'readonly', '1');
		}

		// Only allow to modify the recurrence if this is the first session in it
		if ($form->getValue('recurrenceid', 'recurrence') && $form->getValue('repeat', 'recurrence') > 0)
		{
			foreach ($form->getFieldset('recurrence') as $field)
			{
				if ($field->fieldname != 'recurrenceid' && $field->fieldname != 'repeat')
				{
					$form->setFieldAttribute($field->fieldname, 'disabled', '1', 'recurrence');
				}
			}
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
				$validData[$dbname] = is_array($data[$dbname]) ? implode("\n", $data[$dbname]) : $data[$dbname];
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

		return true;
	}

	/**
	 * Save recurrence data
	 *
	 * @param   array  $data  post data
	 *
	 * @return boolean
	 */
	private function saveRecurrence($data)
	{
		if (!$sessionId = $this->getState('session.id'))
		{
			return false;
		}

		$recurrence = RTable::getInstance('Recurrence', 'RedeventTable');
		$recurrenceParser = new RedeventRecurrenceParser;

		if (!$data['recurrence']['recurrenceid'])
		{
			$rrule = $recurrenceParser->parsePost($data['recurrence']);

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
		elseif ($data['recurrence']['recurrenceid'] && $data['recurrence']['repeat'] == 0)
		{
			// Only update if it's the first session of the 'recurrence'.
			$recurrence->load($data['recurrence']['recurrenceid']);

			// Reset the status
			$recurrence->ended = 0;

			$rrule = $recurrenceParser->parsePost($data['recurrence']);
			$recurrence->rrule = $rrule;

			if (!$recurrence->store())
			{
				$this->setError($recurrence->getError());

				return false;
			}
		}

		if ($recurrence->id)
		{
			$recurrenceHelper = new RedeventRecurrenceHelper;
			$recurrenceHelper->generaterecurrences($recurrence->id);
		}

		return true;
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
	 * method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   3.2.1
	 */
	protected function populateState()
	{
		parent::populateState();

		$jform = JFactory::getApplication()->input->get('jform', array(), 'array');
		$eventId = !(empty($jform['eventid'])) ? $jform['eventid'] : 0;

		if ($eventId)
		{
			$this->setState('eventId', $eventId);
		}
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A reference to a JTable object.
	 *
	 * @return  void
	 */
	protected function prepareTable($table)
	{
		parent::prepareTable($table);

		$defnull = array('dates', 'times', 'enddates', 'endtimes', 'registrationend');

		foreach ($defnull as $val)
		{
			if (!strlen($table->$val))
			{
				$table->$val = null;
			}
		}
	}
}
