<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Redevent Component Details Model
 *
 * @TODO: should be refactored
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelDetails extends RModel
{
	/**
	 * Details data in details array
	 *
	 * @var array
	 */
	protected $details = null;

	/**
	 * registered in array
	 *
	 * @var array
	 */
	protected $registers = null;

	protected $id = null;

	protected $xref = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$input = JFactory::getApplication()->input;

		$id = $input->getInt('id');
		$this->setId((int) $id);
		$xref = $input->getInt('xref');
		$this->setXref((int) $xref);
	}

	/**
	 * Method to set the details event id
	 *
	 * @param   int  $id  details ID number
	 *
	 * @return void
	 */
	public function setId($id)
	{
		// Set new details ID and wipe data
		$this->id = $id;
	}

	/**
	 * Method to set the session id
	 *
	 * @param   int  $xref  session ID number
	 *
	 * @return void
	 */
	public function setXref($xref)
	{
		// Set new details ID and wipe data
		$this->xref = $xref;
	}

	/**
	 * Method to get event data for the Detailsview
	 *
	 * @return array
	 */
	public function getDetails()
	{
		/*
		 * Load the Category data
		*/
		if ($this->_loadDetails())
		{
			$user = JFactory::getUser();

			// Is the category published?
			if (!count($this->details->categories))
			{
				RedeventError::raiseError(404, JText::_("COM_REDEVENT_CATEGORY_NOT_PUBLISHED"));
			}

			// Do we have access to any category ?
			$access = false;

			foreach ($this->details->categories as $cat)
			{
				if (in_array($cat->access, $user->getAuthorisedViewLevels()))
				{
					$access = true;
					break;
				}
			}

			if (!$access)
			{
				JError::raiseError(403, JText::_("COM_REDEVENT_ALERTNOTAUTH"));
			}
		}

		return $this->details;
	}

	/**
	 * Method to load required data
	 *
	 * @return    array
	 */
	protected function _loadDetails()
	{
		if (empty($this->details))
		{
			$user = JFactory::getUser();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Get the WHERE clause
			$query = $this->_buildDetailsWhere($query);

			$query->select('a.id AS did, a.id AS event_id, a.title AS event_title, a.datdescription');

			$query->select('x.id AS xref, x.title as session_title');
			$query->select('x.*');
			$query->select('a.published, x.published as session_published');

			$query->select('t.meta_keywords, t.meta_description, a.datimage, a.registra, a.unregistra, a.summary, t.details_layout');
			$query->select('a.created_by, t.redform_id, t.juser, t.show_names, t.showfields, t.enable_ical');
			$query->select('t.submission_type_email, t.submission_type_external, t.submission_type_phone, t.review_message');
			$query->select('t.confirmation_message, a.course_code, t.submission_types');
			$query->select(' t.submission_type_webform, t.submission_type_formal_offer, '
				. ' t.submission_type_email_pdf, t.submission_type_formal_offer_pdf, t.send_pdf_form, t.pdf_form_data'
			);

			$query->select(
				'v.venue, v.email AS venue_email, v.id AS venue_id, v.city, v.locimage, '
				. 'v.map, v.country, v.street, v.plz, v.state, v.locdescription, v.url'
			);

			$query->select('c.name AS catname, c.access');

			$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
			$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
			$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
			$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug');
			$query->select('CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as venueslug');

			$query->select('u.name AS creator_name, u.email AS creator_email');

			foreach (RedeventHelper::getEventCustomFields() as $custom)
			{
				$query->select('a.custom' . $custom->id);
			}

			foreach (RedeventHelper::getSessionCustomFields() as $custom)
			{
				$query->select('x.custom' . $custom->id);
			}

			$query->from('#__redevent_events AS a');
			$query->innerJoin('#__redevent_event_template AS t ON t.id = a.template_id');

			if ($this->xref)
			{
				$query->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = a.id');
			}
			else
			{
				// If xref is not specified, only join on published sessions
				$query->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = a.id AND x.published = 1');

				// And try to get the one with the 'smallest' date
				$query->order('x.dates > 0 DESC, x.dates ASC');
			}

			$query->join('LEFT', '#__redevent_venues AS v ON x.venueid = v.id');
			$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
			$query->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id');
			$query->join('LEFT', '#__users AS u ON a.created_by = u.id');

			$db->setQuery($query);
			$this->details = $db->loadObject();

			if ($this->details)
			{
				$this->details = $this->_getEventCategories($this->details);
				$attachementHelper = new RedeventHelperAttachment;
				$this->details->attachments = $attachementHelper->getAttachments('event' . $this->details->did, $user->getAuthorisedViewLevels());
			}

			return (boolean) $this->details;
		}

		return true;
	}

	/**
	 * Method to build the WHERE clause of the query to select the details
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return JDatabaseQuery
	 */
	protected function _buildDetailsWhere(JDatabaseQuery $query)
	{
		if ($this->xref)
		{
			$query->where('x.id = ' . $this->xref);
		}
		elseif ($this->id)
		{
			$query->where('a.id = ' . $this->id);
		}

		return $query;
	}

	/**
	 * Method to check if the user is already registered
	 *
	 * @return    array
	 */
	public function getUsercheck()
	{
		// Initialize variables
		$user = JFactory::getUser();
		$userid = (int) $user->get('id', 0);

		$query = $this->_db->getQuery(true)
			->select('uid')
			->from('#__redevent_register')
			->where('uid = ' . $userid)
			->where('xref = ' . $this->xref);

		$this->_db->setQuery($query);

		return $this->_db->loadResult();
	}

	/**
	 * Method to get the registered users
	 *
	 * @param   bool  $all_fields  show al fields
	 * @param   bool  $admin       is admin
	 *
	 * @return    object
	 */
	public function getRegisters($all_fields = false, $admin = false)
	{
		// Make sure the init is done
		$this->getDetails();

		if (!$this->details->registra && !$admin)
		{
			return null;
		}

		$query = $this->_db->getQuery(true)
			->select('r.*, r.waitinglist, r.confirmed, r.confirmdate, r.submit_key')
			->from('#__redevent_register AS r')
			->join('LEFT', '#__users AS u ON r.uid = u.id')
			->where('r.xref = ' . $this->xref)
			->where('r.confirmed = 1')
			->where('r.cancelled = 0');

		$this->_db->setQuery($query);
		$submitters = $this->_db->loadObjectList('submit_key');

		if ($submitters === null)
		{
			$msg = JText::_('COM_REDEVENT_ERROR_GETTING_ATTENDEES');
			$this->setError($msg);
			RedeventError::raiseWarning(5, $msg);

			return null;
		}
		elseif (empty($submitters))
		{
			// No submitters
			return null;
		}

		// At least 1 redFORM field must be selected to show the user data from
		$table_fields = array();
		$fields_names = array();

		if ($fields = $this->getFormFields($all_fields))
		{
			foreach ($fields as $key => $field)
			{
				$table_fields[] = 'a.field_' . $field->fieldId;
				$fields_names['field_' . $field->fieldId] = $field->field_header ?: $field->field;
			}
		}

		$query = $this->_db->getQuery(true)
			->select('s.submit_key, s.id')
			->from('#__redevent_register AS r')
			->join('INNER', '#__rwf_submitters AS s ON r.sid = s.id')
			->join('INNER', '#__rwf_forms_' . $this->details->redform_id . ' AS a ON s.answer_id = a.id')
			->where('r.xref = ' . $this->xref)
			->where('r.confirmed = 1')
			->where('r.cancelled = 0')
			->order('r.confirmdate');

		if ($table_fields)
		{
			$query->select(implode(', ', $table_fields));
		}

		$this->_db->setQuery($query);
		$answers = $this->_db->loadObjectList();

		if ($answers === false)
		{
			RedeventError::raiseWarning('error', JText::_('COM_REDEVENT_Cannot_load_registered_users') . ' ' . $this->_db->getErrorMsg());

			return null;
		}

		// Add the answers to submitters list
		$registers = array();

		foreach ($answers as $answer)
		{
			if (!isset($submitters[$answer->submit_key]))
			{
				$msg = JText::_('COM_REDEVENT_ERROR_REGISTRATION_WITHOUT_SUBMITTER') . ': ' . $answer->id;
				$this->setError($msg);
				RedeventError::raiseWarning(10, $msg);

				return null;
			}

			// Build the object
			$register = new stdclass;
			$register->id = $answer->id;
			$register->attendee_id = $submitters[$answer->submit_key]->id;
			$register->submitter = $submitters[$answer->submit_key];
			$register->answers = $answer;
			$register->fields = $fields_names;

			// Just the fields
			unset($register->answers->id);
			unset($register->answers->submit_key);

			$registers[] = $register;
		}

		return $registers;
	}

	/**
	 * returns the fields to be shown in attendees list
	 *
	 * @param   boolean  $getAll  get all fields
	 *
	 * @return array
	 */
	public function getFormFields($getAll = false)
	{
		// Make sure the init is done
		$this->getDetails();

		if (empty($this->details->showfields) && !$getAll)
		{
			return false;
		}

		$fields = RdfEntityForm::load($this->details->redform_id)->getFormFields();

		if (!$getAll)
		{
			// Only return allowed fields
			$allowed = explode(",", $this->details->showfields);
			$fields = array_filter(
				$fields,
				function ($item) use ($allowed)
				{
					return in_array($item->fieldId, $allowed);
				}
			);
		}

		return $fields;
	}

	/**
	 * Get a list of venues
	 *
	 * @return array
	 */
	public function getVenues()
	{
		if (!$this->details)
		{
			return false;
		}

		$query = $this->_db->getQuery(true)
			->select('*')
			->from('#__redevent_venues v')
			->join('LEFT', '#__redevent_event_venue_xref AS x ON v.id = x.venueid')
			->where('x.eventid = ' . $this->details->did);

		$this->_db->setQuery($query);

		return $this->_db->loadObjectList('id');
	}

	/**
	 * Get a list of venue/date relations
	 *
	 * @return array
	 */
	public function getVenueDates()
	{
		if (!$this->details)
		{
			return false;
		}

		$query = $this->_db->getQuery(true)
			->select('x.*')
			->from('#__redevent_event_venue_xref AS x')
			->where('x.eventid = ' . $this->_db->Quote($this->details->did))
			->where('x.published = 1')
			->order('x.dates ASC, x.times ASC');

		$this->_db->setQuery($query);

		return $this->_db->loadObjectList('id');
	}

	/**
	 * adds categories property to event row
	 *
	 * @param   object  $row  session data
	 *
	 * @return object
	 */
	protected function _getEventCategories($row)
	{
		if (!$row)
		{
			return false;
		}

		$query = $this->_db->getQuery(true)
			->select('c.id, c.name, c.access')
			->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug')
			->from('#__redevent_categories as c')
			->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id')
			->where('c.published = 1')
			->where('x.event_id = ' . $this->_db->Quote($row->did))
			->order('c.ordering');

		$this->_db->setQuery($query);
		$row->categories = $this->_db->loadObjectList();

		return $row;
	}

	/**
	 * Can user manage attendees ?
	 *
	 * @return boolean
	 */
	public function getManageAttendees()
	{
		$acl = RedeventUserAcl::getInstance();

		return $acl->canManageAttendees($this->xref);
	}

	/**
	 * Can user view full attendees details ?
	 *
	 * @return boolean
	 */
	public function getViewFullAttendees()
	{
		$acl = RedeventUserAcl::getInstance();

		return $acl->canViewAttendees($this->xref);
	}

	/**
	 * return roles for the current session
	 *
	 * @TODO: reimplement when redMEMBER2 is out
	 *
	 * @return array
	 */
	public function getRoles()
	{
		$event = $this->getDetails();

		$query = $this->_db->getQuery(true)
			->select('u.name, u.username')
			->select('r.name AS role, sr.role_id, sr.user_id, rr.usertype, rr.fields')
			->from('#__redevent_sessions_roles AS sr')
			->join('INNER', '#__users AS u ON u.id = sr.user_id')
			->join('INNER', '#__redevent_roles AS r on r.id = sr.role_id')
			->join('LEFT', '#__redevent_roles_redmember AS rr ON rr.role_id = r.id')
			->where('sr.xref = ' . $this->_db->Quote($event->xref))
			->order('r.ordering ASC, u.name ASC');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		// Disable integration while redmember2 is maturing
		if ($res && file_exists(JPATH_ADMINISTRATOR . DS . 'component' . DS . 'com_redmember') && 0)
		{
			$uids = array();
			$types = array();

			foreach ($res as $r)
			{
				$uids[] = $r->user_id;

				if ($r->usertype)
				{
					$types[] = $r->usertype;
				}
			}

			// User data from redmember
			$query = ' SELECT *, user_id '
				. ' FROM #__redmember_users '
				. ' WHERE user_id IN (' . implode(',', $uids) . ')';
			$this->_db->setQuery($query);
			$rm_users = $this->_db->loadObjectList('user_id');

			// All fields from redmember
			$query = ' SELECT *, field_id '
				. ' FROM #__redmember_fields '
				. ' ORDER by ordering ';
			$this->_db->setQuery($query);
			$rm_fields = $this->_db->loadObjectList('field_id');

			foreach ($res as $k => $r)
			{
				$info = array();

				if (isset($rm_users[$r->user_id]))
				{
					$ufields = explode(',', $r->fields);

					foreach ($ufields as $f)
					{
						if (isset($rm_fields[$f]))
						{
							$fdb_name = $rm_fields[$f]->field_dbname;
							$info[$rm_fields[$f]->field_name] = $rm_users[$r->user_id]->$fdb_name;
						}
					}
				}

				$res[$k]->rminfo = $info;
			}
		}

		return $res;
	}

	/**
	 * get current session prices
	 *
	 * @return array
	 */
	public function getPrices()
	{
		$allowedViewLevels = JFactory::getUser()->getAuthorisedViewLevels();
		$event = $this->getDetails();

		$query = $this->_db->getQuery(true)
			->select('sp.*, p.name, p.alias, p.image, p.tooltip')
			->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', sp.id, p.alias) ELSE sp.id END as slug')
			->select('CASE WHEN CHAR_LENGTH(sp.currency) THEN sp.currency ELSE f.currency END as currency')
			->from('#__redevent_sessions_pricegroups AS sp')
			->join('INNER', '#__redevent_pricegroups AS p on p.id = sp.pricegroup_id')
			->join('INNER', '#__redevent_event_venue_xref AS x on x.id = sp.xref')
			->join('INNER', '#__redevent_events AS e on e.id = x.eventid')
			->join('INNER', '#__redevent_event_template AS t ON t.id =  e.template_id')
			->join('LEFT', '#__rwf_forms AS f on t.redform_id = f.id')
			->where('sp.xref = ' . $this->_db->Quote($event->xref))
			->where('sp.active = 1')
			->where('p.access in (' . implode(',', $allowedViewLevels) . ')')
			->order('p.ordering ASC');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}
}
