<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Session
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelEditsession extends RModelAdmin
{
	protected $formName = 'session';

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
		if ($form->getValue('id'))
		{
			$form->setFieldAttribute('eventid', 'readonly', '1');
		}

		if ($this->getState($this->getName() . '.eventid'))
		{
			$form->setValue('eventid', '', $this->getState($this->getName() . '.eventid'));
		}

		if (RedeventHelper::config()->get('frontendsubmit_allow_past_dates', 0) == 0)
		{
			$class = $form->getFieldAttribute('dates', 'class');
			$class = ($class ? ' ' : '') . 'validate-futuredate';
			$form->setFieldAttribute('dates', 'class', $class);

			$class = $form->getFieldAttribute('enddates', 'class');
			$class = ($class ? ' ' : '') . 'validate-futuredate';
			$form->setFieldAttribute('enddates', 'class', $class);

			$class = $form->getFieldAttribute('registrationend', 'class');
			$class = ($class ? ' ' : '') . 'validate-futuredate';
			$form->setFieldAttribute('registrationend', 'class', $class);
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

		if (RedeventHelper::config()->get('frontendsubmit_allow_past_dates', 0) == 0)
		{
			if (RedeventHelperDate::isValidDate($data['dates']) && JFactory::getDate($data['dates']) < JFactory::getDate('today'))
			{
				$this->setError(JText::_('COM_REDEVENT_FRONTEND_SUBMIT_SESSION_ERROR_DATE_IN_THE_PAST'));

				return false;
			}
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
	 * Get the associated JTable
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  JTable
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		if (empty($name))
		{
			$name = 'Session';
		}

		return parent::getTable($name, $prefix, $config);
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

		if (!$this->saveRoles($data))
		{
			return false;
		}

		if (!$this->savePrices($data))
		{
			return false;
		}

		$isNew = isset($data['id']) && $data['id'] ? false : true;
		$notify = RModel::getFrontInstance('Editsessionnotify');
		$notify->notify($this->getState($this->getName() . '.id'), $isNew);

		return true;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('s_id');
		$this->setState($this->getName() . '.id', $pk);

		$this->setState($this->getName() . '.eventid', $app->input->getInt('e_id'));

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));
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
		if (!$sessionId = $this->getState($this->getName() . '.id'))
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
	 * Save roles data
	 *
	 * @param   array  $data  post data
	 *
	 * @return bool
	 */
	private function saveRoles($data)
	{
		if (!$sessionId = $this->getState($this->getName() . '.id'))
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

		if (!isset($data['rrole']))
		{
			return true;
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
		if (!$sessionId = $this->getState($this->getName() . '.id'))
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
	 * Return session roles
	 *
	 * @return mixed
	 */
	public function getSessionRoles()
	{
		if (!$this->getState($this->getName() . '.id'))
		{
			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->select('sr.*')
			->from('#__redevent_sessions_roles AS sr')
			->join('INNER', '#__redevent_roles AS r ON r.id = sr.role_id')
			->where('sr.xref = ' . $this->_db->Quote($this->getState($this->getName() . '.id')))
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
		if (!$this->getState($this->getName() . '.id'))
		{
			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->select('r.*')
			->from('#__redevent_sessions_pricegroups AS r')
			->join('INNER', '#__redevent_pricegroups AS pg ON pg.id = r.pricegroup_id')
			->where('r.xref = ' . $this->_db->Quote($this->getState($this->getName() . '.id')))
			->order('pg.ordering');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}
}
