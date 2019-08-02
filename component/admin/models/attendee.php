<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Attendee
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelAttendee extends RModelAdmin
{
	/**
	 * Caching for price groups
	 *
	 * @var array
	 */
	protected $pricegroups = null;

	private $data;

	private $sessionId;

	private $id;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$app = JFactory::getApplication();

		if ($this->id = JFactory::getApplication()->input->getInt('id', 0))
		{
			$this->getSessionId();
		}
		elseif ($sessionId = JFactory::getApplication()->input->getInt('filter[session]', 0))
		{
			$this->setSessionId($sessionId);
		}
		elseif ($sessionId = JFactory::getApplication()->input->getInt('sessionId', 0))
		{
			$this->setSessionId($sessionId);
		}
		elseif ($sessionId = $app->getUserState($this->context . '.session_id'))
		{
			$this->setSessionId($sessionId);
		}
	}

	/**
	 * Set session if
	 *
	 * @param   int  $id  id
	 *
	 * @return void
	 */
	public function setSessionId($id)
	{
		$this->sessionId = (int) $id;
	}

	/**
	 * Get session id
	 *
	 * @return mixed
	 */
	public function getSessionId()
	{
		if ((!$this->sessionId) && $this->id)
		{
			$attendee = $this->getData();

			$this->sessionId = $attendee->xref;
		}

		return $this->sessionId;
	}

	/**
	 * Get session
	 *
	 * @return mixed
	 */
	public function getSession()
	{
		$model = RModel::getAdminInstance('Session', array('ignore_request' => true));
		$session = $model->getItem($this->getSessionId());

		return $session;
	}

	/**
	 * Logic for the Group edit screen
	 *
	 * @return object
	 */
	public function getData()
	{
		if (!$this->loadData())
		{
			$this->initData();
		}

		return $this->data;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  $pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since 3.2.9
	 */
	public function delete(&$pks)
	{
		$sessionIds = $this->getAttendeesSessionIds($pks);

		if (!parent::delete($pks))
		{
			return false;
		}

		$this->updateWaitingLists($sessionIds);

		foreach ($pks as $attendeeId)
		{
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeDeleted', array($attendeeId));
		}

		return true;
	}

	/**
	 * Init data
	 *
	 * @return boolean
	 */
	private function initData()
	{
		$obj = RTable::getAdminInstance('Attendee');

		// Get form id and answer id
		$query = $this->_db->getQuery(true);

		$query->select('t.redform_id as form_id, a.course_code, x.id as xref')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_events AS a ON a.id = x.eventid')
			->join('INNER', '#__redevent_event_template AS t ON t.id = a.template_id')
			->where('x.id = ' . $this->sessionId);

		$this->_db->setQuery($query);
		$ac = $this->_db->loadObject();

		$obj->form_id = $ac->form_id;
		$obj->course_code = $ac->course_code;
		$obj->xref = $this->sessionId;
		$obj->currency = null;
		$obj->answers = null;

		$this->data = $obj;

		return true;
	}

	/**
	 * Method to load content data
	 *
	 * @return boolean True on success
	 */
	private function loadData()
	{
		// Lets load the content if it doesn't already exist
		if (!$this->id)
		{
			return false;
		}

		if (empty($this->data))
		{
			// Get form id and answer id
			$db = $this->_db;
			$query = $db->getQuery(true);

			$query->select('r.*, s.form_id, a.course_code, sp.price, sp.vatrate, sp.id AS sessionpricegroup_id, sp.currency');
			$query->from('#__redevent_register AS r');
			$query->join('INNER', '#__rwf_submitters AS s ON s.id =  r.sid');
			$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id =  r.xref');
			$query->join('INNER', '#__redevent_events AS a ON a.id =  x.eventid');
			$query->join('LEFT', '#__redevent_sessions_pricegroups AS sp ON sp.id =  r.sessionpricegroup_id');
			$query->where('r.id = ' . $this->id);

			$db->setQuery($query);
			$this->data = $db->loadObject();

			if (!$this->data)
			{
				echo $this->_db->getErrorMsg();
			}

			return (boolean) $this->data;
		}

		return true;
	}

	/**
	 * Method to store the attendee
	 *
	 * @param   array  $data  the attendee data to save from post
	 *
	 * @return  boolean  True on success
	 */
	public function store($data)
	{
		$xref = $data['xref'];
		$session = RedeventEntitySession::load($xref);

		if (isset($data['sessionpricegroup_id']))
		{
			$pricegroupId = intval($data['sessionpricegroup_id']);
		}
		else
		{
			$pricegroupId = 0;
		}

		$options = array('edit' => 1);

		if ($pricegroups = $session->getPricegroups())
		{
			if (!$pricegroupId)
			{
				$msg = JText::_('COM_REDEVENT_REGISTRATION_MISSING_PRICE');
				$this->setError($msg);

				return false;
			}

			$field = $session->getPricefield();
			$field->setValue($pricegroupId);

			$extrafields = array(1 => array($field));

			$options['extrafields'] = $extrafields;
			$options['currency'] = $session->getEvent()->getForm()->currency;
		}

		$id = $data['id'];

		// First save redform data
		$rfcore = RdfCore::getInstance();

		try
		{
			$result = $rfcore->saveAnswers('redevent', $options);
		}
		catch (Exception $e)
		{
			$msg = JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED');
			$this->setError($msg . ' - ' . $e->getMessage());

			return false;
		}

		if (!$result)
		{
			$msg = JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED');
			$this->setError($msg . ' - ' . $rfcore->getError());

			return false;
		}

		// Adding to data for register saving
		$data['submit_key'] = $result->submit_key;
		$data['sid'] = $result->posts[0]['sid'];

		if ($session->getEvent()->getEventtemplate()->activate == 0)
		{
			// No activation
			$data['confirmed'] = 1;
			$data['confirmdate'] = gmdate('Y-m-d H:i:s');
			$data['paymentstart'] = gmdate('Y-m-d H:i:s');
		}

		$row = $this->getTable('Attendee');

		if ($id)
		{
			$row->load($id);
		}

		// Save data
		if (!$row->save($data))
		{
			$this->setError($row->getError());

			return false;
		}

		return $row->id;
	}

	/**
	 * Get attendee session price groups
	 *
	 * @return array
	 */
	public function getPricegroups()
	{
		if (is_null($this->pricegroups))
		{
			$session = RedeventEntitySession::load($this->sessionId);
			$this->pricegroups = $session->getPricegroups();
		}

		return $this->pricegroups;
	}

	/**
	 * Get attendees sessions ids
	 *
	 * @param   mixed  $pks  ids
	 *
	 * @return mixed
	 *
	 * @since 3.2.9
	 */
	private function getAttendeesSessionIds($pks)
	{
		$rows = $this->getRowsByIds($pks);

		return $rows ? array_unique(JArrayHelper::getColumn($rows, 'xref')) : false;
	}

	/**
	 * Get rows by ids
	 *
	 * @param   int[]  $ids  ids
	 *
	 * @return mixed
	 *
	 * @since 3.2.9
	 */
	private function getRowsByIds($ids)
	{
		if (!$ids)
		{
			return false;
		}

		JArrayHelper::toInteger($ids);
		$id = RHelperArray::quote($ids);
		$id = implode(',', $id);

		$query = $this->_db->getQuery(true)
			->select('*')
			->from('#__redevent_register')
			->where('id IN (' . $id . ')');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * Update sessions waiting list
	 *
	 * @param   array  $sessionIds  sessions ids
	 *
	 * @return void
	 *
	 * @since 3.2.9
	 */
	private function updateWaitingLists($sessionIds)
	{
		foreach ($sessionIds as $sessionId)
		{
			$model = RModel::getAdminInstance('Waitinglist');
			$model->setXrefId($sessionId);
			$model->updateWaitingList();
		}
	}
}
