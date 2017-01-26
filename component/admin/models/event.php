<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Event
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelEvent extends RModelAdmin
{
	/**
	 * copy
	 *
	 * @param   array  $ids  ids to copy
	 *
	 * @return boolean true on success
	 *
	 * @since 3.2.1
	 */
	public function copy($ids)
	{
		foreach ($ids as $id)
		{
			$row = $this->getTable('event');
			$row->load($id);
			$row->id = null;
			$row->checked_out = 0;
			$row->checked_out_time = 0;
			$row->title = Jtext::sprintf('COM_REDEVENT_COPY_OF_S', $row->title);

			$categories = $this->getEventCategories($id);
			$row->categories = array_keys($categories);

			/* pre-save checks */
			if (!$row->check())
			{
				$this->setError($row->getError(), 'error');

				return false;
			}

			/* save the changes */
			if (!$row->store())
			{
				$this->setError($row->getError(), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  Record Id
	 *
	 * @return  mixed
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		if ($result)
		{
			$helper = new RedeventHelperAttachment;
			$files = $helper->getAttachments('event' . $result->id);
			$result->attachments = $files;

			$categories = $this->getEventCategories($result->id);
			$result->categories = array_keys($categories);
		}

		return $result;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$result = parent::save($data);

		if ($result)
		{
			// Attachments
			$helper = new RedeventHelperAttachment;
			$helper->store('event' . $this->getState($this->getName() . '.id'));
		}

		return $result;
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

		// Do not allow to modify the registration form once there are attendees
		if ($form->getValue('id') && $this->hasAttendees($form->getValue('id')))
		{
			$form->setFieldAttribute('template_id', 'disabled', '1');
			$form->setFieldAttribute('template_id', 'required', '0');
		}

		$form->setFieldAttribute('datimage', 'directory', RedeventHelper::config()->get('default_image_path', 'redevent/events'));

		return $form;
	}

	/**
	 * Method to get the category data
	 *
	 * @param   int  $eventId  event id
	 *
	 * @return array
	 */
	private function getEventCategories($eventId)
	{
		$query = $this->_db->getQuery(true);

		$query->select('c.id, c.name')
			->from('#__redevent_categories as c')
			->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id')
			->where('x.event_id = ' . (int) $eventId);

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList('id');

		return $res;
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
		if ($data['id'] && $this->hasAttendees($data['id']))
		{
			$form->setFieldAttribute('template_id', 'required', '0');
		}

		// First get the data from form itself
		if (!$validData = parent::validate($form, $data, $group))
		{
			return false;
		}

		// Now add custom fields
		$fields = $this->getEventCustomFieldsFromDb();

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
		$result = $this->getEventCustomFieldsFromDb();

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
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 */
	public function publish(&$pks, $value = 1)
	{
		if (!parent::publish($pks, $value))
		{
			return false;
		}

		// Trigger event for plugins
		JPluginHelper::importPlugin('redevent');
		JPluginHelper::importPlugin('finder');
		$dispatcher = JDispatcher::getInstance();

		foreach ($pks as $eventid)
		{
			$dispatcher->trigger('onAfterEventSaved', array($eventid));
			$dispatcher->trigger('onFinderChangeState', array('com_redevent.event', $eventid, $value));
		}

		return true;
	}

	/**
	 * Get custom field raw object from db
	 *
	 * @return array|mixed
	 */
	protected function getEventCustomFieldsFromDb()
	{
		$query = $this->_db->getQuery(true);

		$query->select('f.*')
			->from('#__redevent_fields AS f')
			->where('f.object_key = ' . $this->_db->Quote("redevent.event"))
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
	 * Check if event has attendeees
	 *
	 * @param   int  $event_id  event id
	 *
	 * @return bool
	 */
	private function hasAttendees($event_id)
	{
		$query = $this->_db->getQuery(true)
				->select('r.id')
				->from('#__redevent_register AS r')
				->join('INNER', '#__redevent_event_venue_xref AS x on x.id = r.xref')
				->where('x.eventid = ' . (int) $event_id);

		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadResult();

		return $res ? true : false;
	}
}
