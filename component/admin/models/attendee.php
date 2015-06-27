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
	 * Init data
	 *
	 * @return bool
	 */
	private function initData()
	{
		$obj = RTable::getAdminInstance('Attendee');

		// Get form id and answer id
		$query = $this->_db->getQuery(true);

		$query->select('a.redform_id as form_id, a.course_code, x.id as xref')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_events AS a ON a.id =  x.eventid')
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

		if (isset($data['sessionpricegroup_id']))
		{
			$pricegroup = intval($data['sessionpricegroup_id']);
		}
		else
		{
			$pricegroup = 0;
		}

		$field = new RedeventRfieldSessionprice;
		$field->setOptions($this->getPricegroups());
		$field->setFormIndex(1);

		if ($pricegroup)
		{
			$field->setValue($pricegroup);
		}

		$id = $data['id'];

		// Get price and activate
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('pg.price, a.activate');
		$query->select('CASE WHEN CHAR_LENGTH(pg.currency) THEN pg.currency ELSE f.currency END as currency');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS a ON a.id =  x.eventid');
		$query->join('LEFT', '#__redevent_sessions_pricegroups AS pg ON pg.id = ' . $pricegroup);
		$query->join('LEFT', '#__rwf_forms AS f on f.id = a.redform_id');
		$query->where('x.id = ' . $xref);

		$db->setQuery($query);
		$details = $db->loadObject();

		// First save redform data
		$rfcore = RdfCore::getInstance();

		try
		{
			$result = $rfcore->saveAnswers('redevent', array('extrafields' => array(1 => array($field)), 'currency' => $details->currency, 'edit' => 1));
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

		if ($details->activate == 0)
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
		if (!$this->pricegroups)
		{
			$db = $this->_db;
			$query = $db->getQuery(true);

			$query->select('sp.*, p.name, p.alias, p.tooltip, f.currency AS form_currency');
			$query->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', p.id, p.alias) ELSE p.id END as slug');
			$query->select('CASE WHEN CHAR_LENGTH(sp.currency) THEN sp.currency ELSE f.currency END as currency');
			$query->from('#__redevent_sessions_pricegroups AS sp');
			$query->join('INNER', '#__redevent_pricegroups AS p on p.id = sp.pricegroup_id');
			$query->join('INNER', '#__redevent_event_venue_xref AS x on x.id = sp.xref');
			$query->join('INNER', '#__redevent_events AS e on e.id = x.eventid');
			$query->join('LEFT', '#__rwf_forms AS f on e.redform_id = f.id');
			$query->where('sp.xref = ' . $db->Quote($this->sessionId));
			$query->order('p.ordering ASC');

			$db->setQuery($query);
			$this->pricegroups = $db->loadObjectList();
		}

		return $this->pricegroups;
	}
}
