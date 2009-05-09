<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component Details Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelDetails extends JModel
{
	/**
	 * Details data in details array
	 *
	 * @var array
	 */
	var $_details = null;


	/**
	 * registeres in array
	 *
	 * @var array
	 */
	var $_registers = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$id = JRequest::getInt('id');
		$this->setId((int)$id);
		$xref = JRequest::getInt('xref');
		$this->setXref((int)$xref);
	}

	/**
	 * Method to set the details id
	 *
	 * @access	public
	 * @param	int	details ID number
	 */

	function setId($id)
	{
		// Set new details ID and wipe data
		$this->_id			= $id;
	}
	
	/**
	 * Method to set the details id
	 *
	 * @access	public
	 * @param	int	details ID number
	 */

	function setXref($xref)
	{
		// Set new details ID and wipe data
		$this->_xref			= $xref;
	}

	/**
	 * Method to get event data for the Detailsview
	 *
	 * @access public
	 * @return array
	 * @since 0.9
	 */
	function getDetails( )
	{
		/*
		 * Load the Category data
		 */
		if ($this->_loadDetails())
		{
			$user	= & JFactory::getUser();

			// Is the category published?
			if (!$this->_details->published && $this->_details->catsid)
			{
				JError::raiseError( 404, JText::_("CATEGORY NOT PUBLISHED") );
			}

			// Do we have access to the category?
			if (($this->_details->access > $user->get('aid')) && $this->_details->catsid)
			{
				JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			}

		}

		return $this->_details;
	}

	/**
	 * Method to load required data
	 *
	 * @access	private
	 * @return	array
	 * @since	0.9
	 */
	function _loadDetails()
	{
		if (empty($this->_details))
		{
			// Get the WHERE clause
			$where	= $this->_buildDetailsWhere();

			$query = 'SELECT a.id AS did, x.id AS xref, a.title, a.datdescription, a.meta_keywords, a.meta_description, a.datimage, a.registra, a.unregistra,' 
					. ' x.*, a.catsid, a.created_by, a.redform_id, x.maxwaitinglist, x.maxattendees, a.juser, a.show_names, a.showfields, a.show_attendants, a.show_waitinglist, '
					. ' a.submission_type_email, a.submission_type_external, a.submission_type_phone,'
					. " a.confirmation_message, x.course_price, IF (x.course_credit = 0, '', x.course_credit) AS course_credit, a.course_code, a.submission_types, c.catname, c.published, c.access,"
					. ' a.id as slug, '
					. ' c.id as categoryslug'
					. ' FROM #__redevent_events AS a'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
					. ' LEFT JOIN #__redevent_categories AS c ON c.id = a.catsid'
					. $where
					;
    		$this->_db->setQuery($query);
			$this->_details = $this->_db->loadObject();
			return (boolean) $this->_details;
		}
		return true;
	}

	/**
	 * Method to build the WHERE clause of the query to select the details
	 *
	 * @access	private
	 * @return	string	WHERE clause
	 * @since	0.9
	 */
	function _buildDetailsWhere()
	{
		$where = '';
		if ($this->_xref) $where = ' WHERE x.id = '.$this->_xref;
		else if ($this->_id) $where = ' WHERE x.eventid = '.$this->_id;

		return $where;
	}

	/**
	 * Method to check if the user is already registered
	 *
	 * @access	public
	 * @return	array
	 * @since	0.9
	 */
	function getUsercheck()
	{
		// Initialize variables
		$user 		= & JFactory::getUser();
		$userid		= (int) $user->get('id', 0);

		//usercheck
		$query = 'SELECT uid'
				. ' FROM #__redevent_register'
				. ' WHERE uid = '.$userid
				. ' AND xref = '.$this->_xref
				;
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

	/**
	 * Method to get the registered users
	 *
	 * @access	public
	 * @return	object
	 * @since	0.9
	 * @todo Complete CB integration
	 */
	function getRegisters() {
		$db = JFactory::getDBO();
	if (0) {	
		/* Avatars should be displayed */
		$elsettings = redEVENTHelper::config();
		
		$avatar	= '';
		$join	= '';
		
		if ($elsettings->comunoption == 1 && $elsettings->comunsolution == 1) {
			$avatar = ', c.avatar';
			$join	= ' LEFT JOIN #__comprofiler as c ON c.user_id = r.uid';
		}
		
		$name = $elsettings->regname ? 'u.name' : 'u.username';
		
		//Get registered users
		$query = 'SELECT '.$name.' AS name, r.uid'
				. $avatar
				. ' FROM #__redevent_register AS r'
				. ' LEFT JOIN #__users AS u ON u.id = r.uid'
				. $join
				. ' WHERE event = '.$this->_id
				;
		$this->_db->setQuery( $query );
		$this->_registers = $this->_db->loadObjectList();
	}
		/* At least 1 redFORM field must be selected to show the user data from */
		if (!empty($this->_details->showfields) && $this->_details->redform_id > 0) {
			$q = "SELECT field, form_id 
				FROM #__rwf_fields j
				WHERE j.id in (".$this->_details->showfields.")";
			$db->setQuery($q);
			if (!$db->query()) {
				JError::raiseWarning('error', JText::_('Cannot load fields').$db->getErrorMsg());
				return false;
			}
			else {
				$fields = $db->loadObjectList();
				$query = "SELECT ";
				$lastkey = end(array_keys($fields));
				foreach ($fields as $key => $field) {
					$q = "SELECT LOWER(REPLACE(".$db->Quote($field->field).", ' ',''))";
					$db->setQuery($q);
					$query .= "`".$db->loadResult()."`";
					if ($lastkey != $key) $query .= ', ';
				}
				$query .= " FROM #__redevent_register r 
						LEFT JOIN #__rwf_submitters s 
						ON r.submit_key = s.submit_key 
						LEFT JOIN #__rwf_forms_".$fields[0]->form_id." a
						ON s.answer_id = a.id 
						WHERE s.xref = ".$this->_xref." 
						AND s.waitinglist = 0 
						AND s.confirmed = 1";
				$db->setQuery($query);
				if (!$db->query()) {
					JError::raiseWarning('error', JText::_('Cannot load registered users').' '.$db->getErrorMsg());
					return false;
				}
				else return $db->loadObjectList();
			}
		}
		return false;
	}
	
	/**
	 * Saves the registration to the database
	 *
	 * Contact person is defined as:
	 * If logged in, this is the contact person used for multiple signups
	 * If not logged in, the e-mail address of the submitted form is used
	 */
	public function userregister() {
		global $mainframe;
		
		/* Get the global settings */
		$elsettings = redEVENTHelper::config();
		
		/* Get the event unique ID */
		$event 		= (int) $this->_id;
		$xref 		= (int) $this->_xref;
		
		/* Get the submitter key */
		$submit_key = JRequest::getVar('submit_key');
		
		/* Get the event settings */
		$eventsettings = $this->getDetails();
		
		/* Load redEVENT setttings */
		$elsettings = redEVENTHelper::config();
		$tzoffset	= $mainframe->getCfg('offset');
		
		/* Determine contact person */
		$user = JFactory::getUser();
		
		/* Set a user ID for validating */
		$uid = (int) $user->get('id');
		
		/* Create the object to store the data in redEVENT */
		$obj = new stdClass();
		$obj->xref 		= $xref;
		$obj->uid   	= (int)$uid;
		$obj->uregdate 	= gmdate('Y-m-d H:i:s');
		$obj->uip   	= $elsettings->storeip ? getenv('REMOTE_ADDR') : 'DISABLED';
		/* Submit key for identifying submissions */
		$obj->submit_key = $submit_key;
		
		/* Store the user registration */
		$this->_db->insertObject('#__redevent_register', $obj);
		
		/* Set the message all is good */
		// $mainframe->enqueueMessage(JText::_('REGISTERED SUCCESSFULL'));
		
		/** 
		 * Check if redFORM wants control again
		 * in case of a VirtueMart redirect
		 */
		if (JRequest::getBool('redformback', false)) {
			$mainframe->redirect('index.php?option=com_redform&task=redeventvm&controller=redform&form_id='.JRequest::getInt('form_id'));
		}
		
		/* All is good */
		return true;
	}
	
	/**
	 * Deletes a registered user
	 *
	 * @access public
	 * @return true on success
	 * @since 0.7
	 * @todo Fix as it is broken now
	 */
	function delreguser() {
		$db = JFactory::getDBO();
		$user =  JFactory::getUser();
		$userid = $user->get('id');
		$xref = JRequest::getInt('xref');
		
		if ($userid < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}
		
		/* Get the form details */
		$q = "SELECT r.id, submit_key, redform_id
			FROM #__redevent_event_venue_xref x
			LEFT JOIN #__redevent_register r
			ON x.id = r.xref
			LEFT JOIN #__redevent_events e
			ON x.eventid = e.id
			WHERE uid = ".$userid." 
			AND xref = ".$xref;
		$db->setQuery($q);
		$submitterinfo = $db->loadObject();
		
		/* Delete the redFORM entry first */
		/* Submitter answers first*/
		$deleteids = '';
		$q = "SELECT CONCAT_WS(',', f.id) AS id
				FROM #__rwf_forms_1 f
				LEFT JOIN #__rwf_submitters s
				ON s.answer_id = f.id
				WHERE s.submit_key = '".$submitterinfo->submit_key."'";
		$db->setQuery($q);
		$deleteids = $db->loadResult();
		if (strlen($deleteids) > 0) {
			$q = "DELETE FROM #__rwf_forms_".$submitterinfo->redform_id."
				WHERE id IN (".$deleteids.")";
			$db->setQuery($q);
			if (!$db->query()) {
				JError::raiseWarning('error', JText::_('Cannot delete answers').' '.$db->getErrorMsg());
				return false;
			}
		}
		/* Submitter second */
		$q = "DELETE FROM #__rwf_submitters
			WHERE submit_key = '".$submitterinfo->submit_key."'";
		$db->setQuery($q);
		if (!$db->query()) {
			JError::raiseWarning('error', JText::_('Cannot delete registration').' '.$db->getErrorMsg());
			return false;
		}
		
		/* Now remove the redevent registration */
		$q = "DELETE FROM #__redevent_register WHERE id = ".$submitterinfo->id;
		$db->setQuery($q);
		if (!$db->query()) {
			JError::raiseWarning('error', JText::_('Cannot delete registration').' '.$db->getErrorMsg());
			return false;
		}
		return true;
	}
	
	/**
	 * Get a list of venues
	 */
	public function getVenues() {
		$db = JFactory::getDBO();
		$q = "SELECT *
			FROM #__redevent_venues v
			LEFT JOIN #__redevent_event_venue_xref x
			ON v.id = x.venueid
			WHERE x.eventid IN (".$this->_details->did.")";
		$db->setQuery($q);
		return $db->loadObjectList('id');
	}
}
?>