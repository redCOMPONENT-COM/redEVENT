<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Event
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelEditevent extends RModelAdmin
{
	protected $formName = 'event';

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
		$userAcl = RedeventUserAcl::getInstance();
		$pk = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');

		if (!$pk)
		{
			$data = $this->mergeTemplateData($data);
		}

		if (!$pk && !$userAcl->canPublishEvent())
		{
			$data['published'] = RedeventHelper::config()->get('default_submit_published_state');
		}

		$result = parent::save($data);

		if ($result)
		{
			$id = $this->getState($this->getName() . '.id');

			// Attachments
			$helper = new RedeventHelperAttachment;
			$helper->store('event' . $id);

			$isNew = isset($data['id']) && $data['id'] ? false : true;
			$notify = RModel::getFrontInstance('Editeventnotify');
			$notify->notify($id, $isNew);
		}

		return $result;
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
			$name = 'Event';
		}

		return parent::getTable($name, $prefix, $config);
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
		$pk = $app->input->getInt('e_id');
		$this->setState($this->getName() . '.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 */
	protected function loadFormData()
	{
		$app = JFactory::getApplication();

		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			$this->context . '.data',
			array()
		);

		if (empty($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');

			// Load data from event template
			if (!$id && $templateId = $app->getParams()->get('event_template', 0))
			{
				$data = $this->getItem($templateId);
				$data->id = null;
				$data->title = null;
				$data->alias = null;
				$data->attachments = null;
			}
			else
			{
				$data = $this->getItem();
			}
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  array
	 */
	private function mergeTemplateData($data)
	{
		if ($templateId = $this->getTemplateId($data))
		{
			$query = $this->_db->getQuery(true)
				->select('e.*')
				->from('#__redevent_events AS e')
				->where('e.id = ' . (int) $templateId);

			$this->_db->setQuery($query);

			if ($templateData = $this->_db->loadAssoc())
			{
				$unset = array_fill_keys(
					array(
						'id', 'title', 'alias', 'course_code', 'created_by', 'modified', 'modified_by',
						'author_ip', 'created', 'alias', 'alias', 'alias'
						, 'checked_out', 'checked_out_time', 'alias', 'alias', 'alias', 'alias', 'alias', 'alias', 'alias', 'alias'
					),
					0
				);
				$templateData = array_diff_key($templateData, $unset);
				$data = array_merge($templateData, $data);
			}

			if (!isset($data['categories']))
			{
				$query = $this->_db->getQuery(true)
					->select('category_id')
					->from('#__redevent_event_category_xref')
					->where('event_id = ' . (int) $templateId);

				$this->_db->setQuery($query);
				$data['categories'] = $this->_db->loadColumn();
			}
		}

		return $data;
	}

	/**
	 * Get template id
	 *
	 * @param   array  $data  posted data
	 *
	 * @return mixed
	 */
	private function getTemplateId($data)
	{
		if (isset($data['categories']))
		{
			$categoryids = $data['categories'];
			JArrayHelper::toInteger($categoryids);

			if (count($categoryids))
			{
				$query = $this->_db->getQuery(true)
					->select('event_template')
					->from('#__redevent_categories')
					->where('id IN (' . implode(', ', $categoryids) . ')')
					->where('event_template > 0');

				$this->_db->setQuery($query);

				if ($res = $this->_db->loadResult())
				{
					return $res;
				}
			}
		}

		return JFactory::getApplication()->getParams()->get('event_template', 0);
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		parent::preprocessForm($form, $data, $group);

		$config = RedeventHelper::config();

		if (!$config->get('edit_categories'))
		{
			$form->setFieldAttribute('categories', 'required', 'false');
		}

		$form->setFieldAttribute('datimage', 'directory', RedeventHelper::config()->get('default_image_path', 'redevent/events'));
	}
}
