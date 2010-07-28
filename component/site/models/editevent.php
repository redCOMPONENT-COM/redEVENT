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
 * EventList Component Editevent Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelEditevent extends JModel
{
	/**
	 * event id
	 * @var int
	 */
	var $_id;
	/**
	 * xref id
	 * @var int
	 */
	var $_xref;
	/**
	 * Event data in Event array
	 *
	 * @var array
	 */
	var $_event = null;

	/**
	 * Category data in category array
	 *
	 * @var array
	 */
	var $_categories = null;

	/**
	 * Xref data
	 *
	 * @var array
	 */
	var $_xrefdata = null;
	
	/**
	 * event custom fields data array
	 *
	 * @var array
	 */
	var $_customfields = null;
	
	/**
	 * Xrefs custom fields data array
	 *
	 * @var array
	 */
	var $_xrefcustomfields = null;
	
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$id = JRequest::getInt('id');
		$this->setId($id);
		$xref = JRequest::getInt('xref');
		$this->setXref($xref);
	}

	/**
	 * Method to set the event id
	 *
	 * @access	public Event
	 */
	function setId($id)
	{
		// Set new event ID
		if ($this->_id != $id) {
			$this->_id = intval($id);
			$this->_event = null;
		}
	}
	/**
	 * Method to set the event session xref
	 *
	 * @access	public Event
	 */
	function setXref($xref)
	{
		// Set new xref ID
		if ($this->_xref != $xref) {
			$this->_xref = intval($xref);
			$this->_xrefdata = null;
		}
	}

	/**
	 * logic to get the event
	 *
	 * @access public
	 * @since	0.9
	 * 
	 * @return object
	 */
	function &getEvent(  )
	{
		global $mainframe;

		// Initialize variables
		$user		= & JFactory::getUser();
		$elsettings = & redEVENTHelper::config();
		$acl = UserAcl::getInstance();

		$view		= JRequest::getWord('view');

		/*
		* If Id exists we will do the edit stuff
		*/
		if ($this->_id) 
		{
			/*
			* Load the Event data
			*/
			$this->_loadEvent();

			/*
			* Error if allready checked out otherwise check event out
			*/
			if ($this->isCheckedOut( $user->get('id') )) {
				$mainframe->redirect( 'index.php?view='.$view, JText::_( 'THE EVENT' ).': '.$this->_event->title.' '.JText::_( 'EDITED BY ANOTHER ADMIN' ) );
			} else {
				$this->checkout( $user->get('id') );
			}

			/*
			* access check
			*/
			$allowedtoeditevent = $acl->canEditEvent($this->_id);

			if ($allowedtoeditevent == 0) {

				JError::raiseError( 403, JText::_( 'NO ACCESS' ) );

			}

			/*
			* If no Id exists we will do the add event stuff
			*/
		} 
		else 
		{
			//Check if the user has access to the form
			if (!$acl->canAddEvent()) {

				JError::raiseError( 403, JText::_( 'NO ACCESS' ) );

			}

			//prepare output
			$this->_event->id				= 0;
			$this->_event->xref				= 0;
			$this->_event->locid			= '';
      $this->_event->categories  = null;
			$this->_event->dates			= '';
			$this->_event->enddates			= null;
			$this->_event->registrationend = null;
			$this->_event->title			  = '';
			$this->_event->times			  = null;
			$this->_event->endtimes			= null;
			$this->_event->created			= null;
			$this->_event->author_ip		= null;
			$this->_event->created_by		= null;
			$this->_event->datdescription	= '';
			$this->_event->registra			= 0;
			$this->_event->unregistra		= 0;
			$this->_event->recurrence_number	= 0;
			$this->_event->recurrence_type		= 0;
			$this->_event->recurrence_counter	= '0000-00-00';
			$this->_event->sendername		= '';
			$this->_event->sendermail		= '';
			$this->_event->datimage			= '';
			$this->_event->venue			= JText::_('SELECTVENUE');
			$this->_event->maxattendees				= 0;
			$this->_event->maxwaitinglist				= 0;
			$this->_event->notify_on_list_subject 		= null;
			$this->_event->notify_on_list_body 		= null;
			$this->_event->notify_off_list_subject	 	= null;
			$this->_event->notify_off_list_body 		= null;
			$this->_event->notify_confirm_subject 		= null;
			$this->_event->notify_confirm_body 		= null;
			$this->_event->juser						= false;
			$this->_event->notify						= false;
			$this->_event->notify_subject 				= null;
			$this->_event->notify_body 				= null;
			$this->_event->confirmation_message 		= null;
			$this->_event->redform_id					= null;
			$this->_event->activate					= null;
			$this->_event->show_names					= 0;
			$this->_event->showfields					= '';
			$this->_event->course_credit				= 0;
			$this->_event->course_price				= 0;
			$this->_event->course_code					= 0;
			$this->_event->submission_types			= null;
			$this->_event->submission_type_email		= null;
			$this->_event->submission_type_external	= null;
			$this->_event->submission_type_phone		= null;
			$this->_event->max_multi_signup			= 1;
			$this->_event->formal_offer		= null;
			$this->_event->formal_offer_subject		= null;
			$this->_event->published					= 1;
		}

		return $this->_event;

	}

	/**
	 * logic to get the event
	 *
	 * @access private
	 * @return object
	 */
	function _loadEvent(  )
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_event))
		{
			$customs = $this->_getCustomFields();
			
			$query = 'SELECT e.*, v.venue, x.id AS xref, x.eventid, x.venueid, x.dates, x.enddates, x.times, x.endtimes, x.maxattendees,
					x.maxwaitinglist, x.course_price, x.course_credit'
					   ;
		
			// add the custom fields
			foreach ((array) $customs as $c)
			{
				$query .= ', c'. $c->id .'.value AS custom'. $c->id;
			}
			
			$query .= ' FROM #__redevent_events AS e'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id'
					. ' LEFT JOIN #__redevent_venues AS v ON v.id = x.venueid'
					    ;
			// add the custom fields tables
			foreach ((array) $customs as $c)
			{
				$query .= ' LEFT JOIN #__redevent_fields_values AS c'. $c->id .' ON c'. $c->id .'.object_id = e.id';
			}
					    
					    
			$query .= ' WHERE e.id = '.(int)$this->_id
					;
			$this->_db->setQuery($query);
			$this->_event = $this->_db->loadObject();
			
			if ($this->_event->id) {
				$query =  ' SELECT c.id, c.catname, '
              . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
              . ' FROM #__redevent_categories as c '
              . ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
              . ' WHERE c.published = 1 '
              . '   AND x.event_id = ' . $this->_db->Quote($this->_event->id)
              . ' ORDER BY c.ordering'
              ;
	      $this->_db->setQuery( $query );
	
	      $this->_event->categories = $this->_db->loadObjectList();
			}

			return (boolean) $this->_event;
		}
		return true;
	}
	
	function getSessionDetails()
	{
		if (empty($this->_xrefdata))
		{
			if ($this->_xref)
			{
				$customs = $this->_getXCustomFields();
				
				$query = ' SELECT x.*, e.title ';
				// add the custom fields
				foreach ((array) $customs as $c)
				{
					$query .= ', c'. $c->id .'.value AS custom'. $c->id;
				}
				$query .= ' FROM #__redevent_event_venue_xref AS x ';
				$query .= ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id ';
				// add the custom fields tables
				foreach ((array) $customs as $c)
				{
					$query .= ' LEFT JOIN #__redevent_fields_values AS c'. $c->id .' ON c'. $c->id .'.object_id = x.id';
				}
				$query .= ' WHERE x.id = '. $this->_db->Quote($this->_xref)
				       ;
	      $this->_db->setQuery( $query );
				$this->_xrefdata = $this->_db->loadObject();
			}
			else
			{
				$obj = new stdclass();
				$obj->id                = null;
				$obj->eventid           = 0;
				$obj->venueid           = 0;
				$obj->groupid           = 0;
				$obj->dates             = null;
				$obj->enddates          = null;
				$obj->times             = null;
				$obj->endtimes          = null;
				$obj->registrationend   = null;
				$obj->external_registration_url   = null;
				$obj->details           = null;
				$obj->maxattendees      = 0;
				$obj->maxwaitinglist    = 0;
				$obj->course_credit     = 0;
				$obj->course_price      = 0;
				$obj->published         = 1;
				$this->_xrefdata = $obj;
			}
		}
		return $this->_xrefdata;
	}

	/**
	 * logic to get the categories options
	 *
	 * @access public
	 * @return void
	 */
	function getCategoryOptions( )
	{
		$user = &JFactory::getUser();
		$app = &JFactory::getApplication();
		$params = $app->getParams();
		$superuser	= UserAcl::superuser();

		//administrators or superadministrators have access to all categories, also maintained ones
		if($superuser) {
			$cwhere = ' WHERE c.published = 1';
		}
		else
		{					
			$acl = UserACl::getInstance();
			$managed = $acl->getManagedCategories();
			if (!$managed || !count($managed)) {
				return false;
			}
			$cwhere = ' WHERE c.id IN ('.implode(',', $managed).') ';
		}

		//get the maintained categories and the categories whithout any group
		//or just get all if somebody have edit rights	
    $query = ' SELECT c.id, c.catname, (COUNT(parent.catname) - 1) AS depth, c.event_template, c.ordering '
           . ' FROM #__redevent_categories AS c, '
           . ' #__redevent_categories AS parent '
           . $cwhere
           . ' AND c.lft BETWEEN parent.lft AND parent.rgt '
           . ' GROUP BY c.id '
           . ' ORDER BY c.lft;'
           ;
    $this->_db->setQuery($query);
    
    $results = $this->_db->loadObjectList();
    
    $options = array();
    foreach((array) $results as $cat)
    {
      $options[] = JHTML::_('select.option', $cat->id, str_repeat('>', $cat->depth) . ' ' . $cat->catname);
    }

    $this->_categories = $options;

		return $this->_categories;
	}

	/**
	 * logic to get the venueslist
	 *
	 * @access public
	 * @return array
	 */
	function getVenues( )
	{
		$app  = &JFactory::getApplication();
		$params = $app->getParams();
		
		$where		= $this->_buildVenuesWhere();
		$orderby	= $this->_buildVenuesOrderBy();

		$limit			= $app->getUserStateFromRequest('com_redevent.selectvenue.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart = JRequest::getInt('limitstart');

		$query = 'SELECT l.id, l.venue, l.city, l.country, l.published'
				.' FROM #__redevent_venues AS l'
				. $where
				. $orderby
				;

		$this->_db->setQuery( $query, $limitstart, $limit );
		$rows = $this->_db->loadObjectList();

		return $rows;
	}

	/**
	 * Method to build the ordering
	 *
	 * @access private
	 * @return array
	 */
	function _buildVenuesOrderBy( )
	{
		$filter_order		= JRequest::getCmd('filter_order');
		$filter_order_Dir	= JRequest::getCmd('filter_order_Dir');

		$orderby = ' ORDER BY ';

		if ($filter_order && $filter_order_Dir)
		{
			$orderby .= $filter_order.' '.$filter_order_Dir.', ';
		}

		$orderby .= 'l.ordering';

		return $orderby;
	}

	/**
	 * Method to build the WHERE clause
	 *
	 * @access private
	 * @return array
	 */
	function _buildVenuesWhere(  )
	{
		$filter_type		= JRequest::getInt('filter_type');
		$filter 			= JRequest::getString('filter');
		$filter 			= $this->_db->getEscaped( trim(JString::strtolower( $filter ) ) );		
		
		$user   = &JFactory::getUser();
		$app    = &JFactory::getApplication();
		$params = $app->getParams();
		
		$superuser	= UserAcl::superuser();

		
		$where = array();
		
		//administrators or superadministrators have access to all venues, also maintained ones
		if (!$superuser) 
		{					
			$acl = UserACl::getInstance();
			$managed = $acl->getManagedVenues();
			if ($managed && count($managed)) {
				$where[] = ' l.id IN ('.implode(',', $managed).')';
			}
			else {
				$where[] = ' 0 ';
			}
		}		
		
		$where[] = 'l.published = 1';

		if ($filter && $filter_type == 1) {
			$where[] = 'LOWER(l.venue) LIKE "%'.$filter.'%"';
		}

		if ($filter && $filter_type == 2) {
			$where[] = 'LOWER(l.city) LIKE "%'.$filter.'%"';
		}

		$where = ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '');

		return $where;
	}

	/**
	 * Method to get the total number
	 *
	 * @access public
	 * @return integer
	 */
	function getCountitems ()
	{
		// Initialize variables
		$where		= $this->_buildVenuesWhere(  );

		$query = 'SELECT count(*)'
				. ' FROM #__redevent_venues AS l'
				. $where
				;
		$this->_db->SetQuery($query);

  		return $this->_db->loadResult();
	}

	/**
	 * Method to checkin/unlock the item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$item = & $this->getTable('redevent_events', '');
			if(! $item->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return false;
	}

		/**
	 * Method to checkout/lock the item
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the item out
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$item = & $this->getTable('redevent_events', '');
			if(!$item->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			return true;
		}
		return false;
	}

	/**
	 * Tests if the event is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	0.9
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadEvent())
		{
			if ($uid) {
				return ($this->_event->checked_out && $this->_event->checked_out != $uid);
			} else {
				return $this->_event->checked_out;
			}
		}
	}

	/**
	 * Method to store the event
	 *
	 * @access	public
	 * @return	id
	 * @since	0.9
	 */
	function store($data, $file)
	{
		$mainframe =& JFactory::getApplication();

		$user 		  = & JFactory::getUser();
		$elsettings = & redEVENTHelper::config();
		$params     = $mainframe->getParams();
		$acl        = UserAcl::getInstance();

		//Get mailinformation
		$SiteName 		= $mainframe->getCfg('sitename');
		$MailFrom	 	= $mainframe->getCfg('mailfrom');
		$FromName 		= $mainframe->getCfg('fromname');
		$tzoffset 		= $mainframe->getCfg('offset');

		$row 	= & JTable::getInstance('redevent_events', '');
		
		if ($data['id'])
		{
			$row->load((int) $data['id']);
		}
		else 
		{
			$category_ids = (isset($data['categories']) ? $data['categories'] : array());
			$template_event = $this->_getEventTemplate($category_ids);
			$template_event = ($template_event ? $template_event : $params->get('event_template', 0));
			
			if ($template_event) 
			{
				$row->load($template_event);
				$row->id    = null;
				$row->alias = null;
			}
		}
		
		//Sanitize
		$data['datdescription'] = JRequest::getVar( 'datdescription', $row->datdescription, 'post','string', JREQUEST_ALLOWRAW );
		
		$curimage = JRequest::getVar( 'curimage', '', 'post','string' );

		//bind it to the table
		if (!$row->bind($data)) {
			RedeventError::raiseError( 500, $this->_db->stderr() );
			return false;
		}
		

		//Are we saving from an item edit?
		if ($row->id) 
		{
			//check if user is allowed to edit events
			if (!$acl->canEditEvent($this->_id)) {
				JError::raiseError( 403, JText::_( 'NO ACCESS' ) );
			}

			$row->modified 		= gmdate('Y-m-d H:i:s');
			$row->modified_by 	= $user->get('id');

			/*
			* Is editor the owner of the event
			* This extra Check is needed to make it possible
			* that the venue is published after an edit from an owner
			*/
			if ($elsettings->venueowner == 1 && $row->created_by == $user->get('id')) {
				$owneredit = 1;
			} else {
				$owneredit = 0;
			}
		} 
		else 
		{
			//check if user is allowed to submit new events
			if (!$acl->canAddEvent()){
				JError::raiseError( 403, JText::_( 'NO ACCESS' ) );
			}

			//get IP, time and userid
			$row->created 		= gmdate('Y-m-d H:i:s');

			$row->author_ip 	= $elsettings->storeip ? getenv('REMOTE_ADDR') : 'DISABLED';
			$row->created_by 	= $user->get('id');

			//Set owneredit to false
			$owneredit = 0;
		}

		//Image upload

		//If image upload is required we will stop here if no file was attached
		if ( empty($file['name']) && $elsettings->imageenabled == 2 ) 
		{
			$this->setError( JText::_( 'IMAGE EMPTY' ) );
			return false;
		}

		if ( ( $elsettings->imageenabled == 2 || $elsettings->imageenabled == 1 ) && ( !empty($file['name'])  ) )  
		{
			jimport('joomla.filesystem.file');

			$base_Dir 		= JPATH_SITE.'/images/redevent/events/';

			//check the image
			$check = redEVENTImage::check($file, $elsettings);

			if ($check === false) {
				$mainframe->redirect($_SERVER['HTTP_REFERER']);
			}

			//sanitize the image filename
			$filename = redEVENTImage::sanitize($base_Dir, $file['name']);
			$filepath = $base_Dir . $filename;

			if (!JFile::upload($file['tmp_name'], $filepath)) {
				$this->setError( JText::_( 'UPLOAD FAILED' ) );
				return false;
			} else {
				$row->datimage = $filename;
			}
		} 
		else 
		{
			//keep image if edited and left blank
			$row->datimage = $curimage;
		}//end image if

		$editoruser = UserAcl::editoruser();

		if (!$editoruser) 
		{
			//check datdescription --> wipe out code
			$row->datdescription = strip_tags($row->datdescription, '<br><br/>');

			//convert the linux \n (Mac \r, Win \r\n) to <br /> linebreaks
			$row->datdescription = str_replace(array("\r\n", "\r", "\n"), "<br />", $row->datdescription);

			// cut too long words
			$row->datdescription = wordwrap($row->datdescription, 75, ' ', 1);

			//check length
			$length = JString::strlen($row->datdescription);
			if ($length > $elsettings->datdesclimit) 
			{
				//too long then shorten datdescription
				$row->datdescription = JString::substr($row->datdescription, 0, $elsettings->datdesclimit);
				//add ...
				$row->datdescription = $row->datdescription.'...';
			}
		}

		$row->title = trim( JFilterOutput::ampReplace( $row->title ) );

		//Make sure the table is valid
		if (!$row->check($elsettings)) {
			$this->setError($row->getError());
			return false;
		}

		//is this an edited event or not?
		//after store we allways have an id
		$edited = $row->id ? $row->id : false;

		//store it in the db
		if (!$row->store(true)) {
			JError::raiseError( 500, $this->_db->stderr() );
			return false;
		}
				
    // update the event category xref
		if (isset($data['categories']))
		{
	    // first, delete current rows for this event
	    $query = ' DELETE FROM #__redevent_event_category_xref WHERE event_id = ' . $this->_db->Quote($row->id);
	    $this->_db->setQuery($query);
	    if (!$this->_db->query()) {
	      $this->setError($this->_db->getErrorMsg());
	      return false;     
	    }
	    // insert new ref
	    foreach ((array) $data['categories'] as $cat_id) {
	      $query = ' INSERT INTO #__redevent_event_category_xref (event_id, category_id) VALUES (' . $this->_db->Quote($row->id) . ', '. $this->_db->Quote($cat_id) . ')';
	      $this->_db->setQuery($query);
	      if (!$this->_db->query()) {
	        $this->setError($this->_db->getErrorMsg());
	        return false;     
	      }     
	    }
		}
		else
		{
			// copy category from template event
			$query = ' INSERT INTO #__redevent_event_category_xref (event_id, category_id) '
			       . ' SELECT '. $this->_db->Quote($row->id).', category_id '
			       . '       FROM #__redevent_event_category_xref '
			       . '       WHERE event_id = '. $this->_db->Quote($template_event)
			       ;
			$this->_db->setQuery($query);
	    if (!$this->_db->query()) {
	    	$this->setError($this->_db->getErrorMsg());
				JError::raiseWarning(0, JTEXT::_('copying categories failed').': '.$xref->getError());
	    }     
		}
		
		// is there a date ?	
		if (isset($data['dates']) && strlen($data['dates']))
		{
			$xref = & JTable::getInstance('redevent_eventvenuexref', '');
			$xref->bind($data);
			$xref->id        = null;
			$xref->eventid   = $row->id;
			$xref->published = $row->published;
			
			if (!($xref->check() && $xref->store())) {
				JError::raiseWarning(0, JTEXT::_('Saving event session failed').': '.$xref->getError());
			}
		}	
	    
    // custom fields
    // first copy those from event template
    if (!$data['id'] && $template_event)
    {
			// copy category from template event
			$query = ' INSERT INTO #__redevent_fields_values (object_id, field_id, value) '
			       . ' SELECT '. $this->_db->Quote($row->id).', fv.field_id, fv.value '
			       . '       FROM #__redevent_fields_values AS fv '
			       . '       INNER JOIN #__redevent_fields AS f ON f.id = fv.field_id '
			       . '       WHERE fv.object_id = '. $this->_db->Quote($template_event)
			       . '         AND f.object_key = '. $this->_db->Quote('redevent.event')
			       ;
			$this->_db->setQuery($query);
	    if (!$this->_db->query()) {
	    	$this->setError($this->_db->getErrorMsg());
				JError::raiseWarning(0, JTEXT::_('copying custom fields failed').': '.$xref->getError());
	    }
    }
    
    foreach ($data as $key => $value)
    {
      if (strstr($key, "custom"))
      {
        $fieldid = (int) substr($key, 6);
        // get the field details
        $query = ' SELECT f.* FROM #__redevent_fields AS f WHERE f.id = '. $this->_db->Quote($fieldid);
        $this->_db->setQuery($query, 0 ,1);
        $field = $this->_db->loadObject();
        if ($field->object_key == 'redevent.event')
        {
	        // delete previous value
					$query = ' DELETE fv FROM #__redevent_fields_values as fv '
	               . ' WHERE fv.field_id = ' . $this->_db->Quote($fieldid)
	               . '   AND fv.object_id = ' . $this->_db->Quote($row->id)
	               ;
					$this->_db->setQuery($query);
					if (!$this->_db->query()) {
						JError::raiseWarning(0, JTEXT::_('Failed deleting previous custom value').': '.$this->_db->getErrorMsg());
					}
        }
        
        $fieldvalue = & $this->getTable('Redevent_customfieldvalue','');
        $fieldvalue->object_id = ($field->object_key == 'redevent.xref' ? $xref->id : $row->id);
        $fieldvalue->field_id = $fieldid;
        if (is_array($value)) {
          $value = implode("\n", $value);
        }
        $fieldvalue->value = $value;
        
        if (!$fieldvalue->check()) {
          $this->setError($fieldvalue->getError());
          return false;         
        }
        if (!$fieldvalue->store()) {
          $this->setError($fieldvalue->getError());
          return false;         
        }       
      }
    }
		
		
		// MAIL HANDLING
		$this->_db->setQuery('SELECT * FROM #__redevent_venues AS v LEFT JOIN #__redevent_event_venue_xref AS x ON x.venueid = v.id WHERE x.eventid = '.(int)$row->id);
		$rowloc = $this->_db->loadObject();

		jimport('joomla.utilities.mail');

		$link 	= JRoute::_(RedeventHelperRoute::getDetailsRoute($row->id), false);

		//create the mail for the site owner
		if (($elsettings->mailinform == 1) || ($elsettings->mailinform == 3)) {

			$mail = JFactory::getMailer();

			$state 	= $row->published ? JText::sprintf('MAIL EVENT PUBLISHED', $link) : JText::_('MAIL EVENT UNPUBLISHED');

			if ($edited) {

				$modified_ip 	= getenv('REMOTE_ADDR');
				$edited 		= JHTML::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('MAIL EDIT EVENT', $user->name, $user->username, $user->email, $modified_ip, $edited, $row->title, $xref->dates, $xref->times, $rowloc->venue, $rowloc->city, $row->datdescription, $state);
				$mail->setSubject( $SiteName.JText::_( 'EDIT EVENT MAIL' ) );

			} else {

				$created 	= JHTML::Date( $row->created, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 	= JText::sprintf('MAIL NEW EVENT', $user->name, $user->username, $user->email, $row->author_ip, $created, $row->title, $xref->dates, $xref->times, $rowloc->venue, $rowloc->city, $row->datdescription, $state);
				$mail->setSubject( $SiteName.JText::_( 'NEW EVENT MAIL' ) );

			}

			$receivers = explode( ',', trim($elsettings->mailinformrec));

			$mail->addRecipient( $receivers );
			$mail->setSender( array( $MailFrom, $FromName ) );
			$mail->setBody( $mailbody );

			$sent = $mail->Send();
      if (!$sent) {
        RedeventHelperLog::simpleLog('Error sending created/edited event notification to site owner');
      }

		}//mail end

		//create the mail for the user
		if (($elsettings->mailinformuser == 1) || ($elsettings->mailinformuser == 3)) {

			$usermail = JFactory::getMailer();

			$state 	= $row->published ? JText::sprintf('USER MAIL EVENT PUBLISHED', $link) : JText::_('USER MAIL EVENT UNPUBLISHED');

			if ($edited) {

				$edited 		= JHTML::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('USER MAIL EDIT EVENT', $user->name, $user->username, $edited, $row->title, $xref->dates, $xref->times, $rowloc->venue, $rowloc->city, $row->datdescription, $state);
				$usermail->setSubject( $SiteName.JText::_( 'EDIT USER EVENT MAIL' ) );

			} else {

				$created 	= JHTML::Date( $row->created, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 	= JText::sprintf('USER MAIL NEW EVENT', $user->name, $user->username, $created, $row->title, $xref->dates, $xref->times, $rowloc->venue, $rowloc->city, $row->datdescription, $state);
				$usermail->setSubject( $SiteName.JText::_( 'NEW USER EVENT MAIL' ) );

			}

			$usermail->addRecipient( $user->email );
			$usermail->setSender( array( $MailFrom, $FromName ) );
			$usermail->setBody( $mailbody );

			$sent = $usermail->Send();
      if (!$sent) {
        RedeventHelperLog::simpleLog('Error sending created/edited event notification to event owner');
      }
		}

		return $row->id;
	}
	
	/**
	 * Function to retrieve the form fields
	 */
	function getFormFields() {
		$db = JFactory::getDBO();
		$q = "SELECT id, ".$db->nameQuote('field')."
			FROM #__rwf_fields
			WHERE form_id = ".$this->_event->redform_id."
			AND published = 1
			ORDER BY ordering";
		$db->setQuery($q);
		if ($db->query()) return $db->loadObjectList('id');
		else return false;
	}
	
	/**
	 * Function to retrieve the redFORM forms
	 */
	function getRedForms() {
		$db = JFactory::getDBO();
		$q = "SELECT id, formname
			FROM #__rwf_forms
			WHERE published = 1
			ORDER BY formname";
		$db->setQuery($q);
		if ($db->query()) return $db->loadObjectList('id');
		else return false;
	}
	
	/**
	 * Retrieve a list of events, venues and times
	 */
	public function getEventVenue() {
		$db = JFactory::getDBO();
		$q = "SELECT x.*
			FROM #__redevent_event_venue_xref x
			WHERE eventid = ".$this->_id."
			ORDER BY dates";
		$db->setQuery($q);
		$datetimes = $db->loadObjectList();
		$ardatetimes = array();
		foreach ($datetimes as $key => $datetime) {
			$ardatetimes[$datetime->venueid][] = $datetime;
		}
		return $ardatetimes;
	}
	
	/**
	 * return venues lists as options, according to group ACL
	 * 
	 * @return array
	 */
	function getVenueOptions()
	{
		$user = &JFactory::getUser();
		$app  = &JFactory::getApplication();
		$params = $app->getParams();
			
		$superuser	= UserAcl::superuser();		
		
		$query = ' SELECT v.id AS value, '
		       . ' CASE WHEN CHAR_LENGTH(v.city) THEN CONCAT_WS(\' - \', v.venue, v.city) ELSE v.venue END as text '
		       . ' FROM #__redevent_venues AS v '
		       ;		       		
		
		$where = array();
		
		//administrators or superadministrators have access to all venues, also maintained ones
		if (!$superuser) 
		{					
			$acl = UserACl::getInstance();
			$managed = $acl->getManagedVenues();
			if ($managed && count($managed)) {
				$where[] = ' v.id IN ('.implode(',', $managed).')';
			}
			else {
				$where[] = ' 0 ';
			}
		}		
		$where[] = ' v.published = 1 ';
		
		if (count($where)) {
			$query .= ' WHERE '. implode(' AND ', $where);
		}
		        
		$query .= ' GROUP BY v.id ';
		$query .= ' ORDER BY v.venue ASC ';
		
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}

	/**
	 * return events lists as options, according to group ACL
	 * 
	 * @return array
	 */
	function getEventOptions()
	{
		$user = &JFactory::getUser();
		$app = &JFactory::getApplication();
		$params = $app->getParams();
		
		$query = ' SELECT e.id AS value, e.title AS text '
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
		       . ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = xcat.category_id '
		       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gc.group_id '
		       ;
		       
		$where = array();
		
		$where[] = ' e.published = 1 ';
		$where[] = ' gc.accesslevel > 0 AND (gm.manage_xrefs = 1 OR gm.manage_events > 0) AND gm.member =' . $this->_db->Quote($user->get('id'));
		
		if (count($where)) {
			$query .= ' WHERE '. implode(' AND ', $where);
		}
		        
		$query .= ' GROUP BY e.id ';
		$query .= ' ORDER BY e.title ASC ';
		
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	

	/**
	 * return user groups as options
	 * 
	 * @return array
	 */
	function getGroupOptions()
	{
		$user = &JFactory::getUser();
		
		$query = ' SELECT g.id AS value, g.name AS text '
		       . ' FROM #__redevent_groups AS g '
		       . ' INNER JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
		       ;
		       
		$where = array();		
		$where[] = 'gm.member =' . $this->_db->Quote($user->get('id'));
		
		if (count($where)) {
			$query .= ' WHERE '. implode(' AND ', $where);
		}
		        
		$query .= ' ORDER BY g.name ASC ';
		
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	/**
	 * Saves xref data
	 * @param array
	 * @return boolean true on success
	 */
	function storeXref($data)
	{		
		$user 		= & JFactory::getUser();
		$settings = & redEVENTHelper::config();
		
		// TODO : check user group access ?
  	$row = & JTable::getInstance('RedEvent_eventvenuexref', '');
  	
		if ($data['id']) {
			if (!$this->canManageXref($data['id'])) {
				$this->setError('YOU ARE NOT ALLOWED TO EDIT THIS DATE');
				return false;				
			}
			$row->load($data['id']);
		}
		else {
			if (!$this->getCanAddXref()) {
				$this->setError('YOU ARE NOT ALLOWED TO ADD EVENT DATE');
				return false;				
			}			
		}
		
		if (!$row->bind($data)) {
			$this->setError('SUBMIT XREF ERROR BINDING DATA');
			RedeventHelperLog::simplelog('SUBMIT XREF ERROR BINDING DATA');
			return false;
		}
	
		if (!$row->check()) {
			$this->setError('SUBMIT XREF ERROR CHECK DATA');
			RedeventHelperLog::simplelog('SUBMIT XREF ERROR CHECK DATA');
			return false;
		}
			
		if (!$row->store()) {
			$this->setError('SUBMIT XREF ERROR STORE DATA');
			RedeventHelperLog::simplelog('SUBMIT XREF ERROR STORE DATA');
			return false;
		}
	
  	// custom fields
    // first delete records for this object
    $query = ' DELETE fv FROM #__redevent_fields_values as fv '
           . ' INNER JOIN #__redevent_fields as f ON f.id = fv.field_id '
           . ' WHERE fv.object_id = ' . $this->_db->Quote($row->id)
           . '   AND f.object_key = ' . $this->_db->Quote('redevent.xref')
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError($this->_db->getErrorMsg());
      return false;     
    }
    
    // input new values
    foreach ($data as $key => $value)
    {
      if (strstr($key, "custom"))
      {
        $fieldid = (int) substr($key, 6);
        $field = & $this->getTable('Redevent_customfieldvalue','');
        $field->object_id = $row->id;
        $field->field_id = $fieldid;
        if (is_array($value)) {
          $value = implode("\n", $value);
        }
        $field->value = $value;
        
        if (!$field->check()) {
          $this->setError($field->getError());
          return false;         
        }
        if (!$field->store()) {
          $this->setError($field->getError());
          return false;         
        }       
      }
    }
    
		return true;
	}
	
	function publishxref($xref_id, $newstate)
	{
		if (!$this->canManageXref($xref_id)) {
			$this->setError(JText::_('YOU ARE NOT ALLOWED TO EDIT THIS DATE'));
			return false;				
		}
  	$row = & JTable::getInstance('RedEvent_eventvenuexref', '');
  	
		if (!$row->publish(array($xref_id), $newstate)) {
			$this->setError(JText::_('ERROR CHANGING STATE')).'<br>'.$row->getError();
			return false;
		}
		return true;
	}
	
	function deletexref($xref_id)
	{
		if (!$this->canManageXref($xref_id)) {
			$this->setError(JText::_('YOU ARE NOT ALLOWED TO DELETE THIS DATE'));
			return false;				
		}
  	$row = & JTable::getInstance('RedEvent_eventvenuexref', '');
  	
		if (!$row->delete($xref_id)) {
			$this->setError(JText::_('ERROR DELETING EVENT DATE').': '.$row->getError());
			return false;
		}
		return true;
	}
	
  function canManageXref($xref_id = 0)
  {
  	if (!$xref_id) {
  		$xref_id = $this->_xref;
  	}
  	if (!$xref_id) {
  		return false;
  	}
		$acl = UserAcl::getInstance();
  	
  	return $acl->canEditXref($xref_id);
  }
	
  /**
   * check if user is allowed to addxrefs
   * @return boolean
   */
	function getCanAddXref()
  {
  	$user = & JFactory::getUser();
  	
  	$query = ' SELECT gm.id '
  	       . ' FROM #__redevent_groups AS g '
  	       . ' INNER JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
  	       . ' WHERE gm.member = '. $this->_db->Quote($user->get('id'))
  	       . '   AND (gm.manage_xrefs > 0 OR gm.manage_events > 0) '
  	       ;
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();
  	return count($res) > 0;
  } 

  /**
   * returns all custom fields for event
   * 
   * @return array
   */
  function _getCustomFields()
  {
  	if (empty($this->_customfields))
  	{
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable '
	  	       . ' FROM #__redevent_fields AS f'
	  	       . ' WHERE f.published = 1'
	  	       . '   AND f.object_key = '. $this->_db->Quote('redevent.event')
	  	       . ' ORDER BY f.ordering ASC '
	  	       ;
	  	$this->_db->setQuery($query);
	  	$this->_customfields = $this->_db->loadObjectList();
  	}
  	return $this->_customfields;
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
   * get custom fields
   *
   * @return objects array
   */
  function getXrefCustomfields()
  {
    $query = ' SELECT f.*, fv.value '
           . ' FROM #__redevent_fields AS f '
           . ' LEFT JOIN #__redevent_fields_values AS fv ON fv.field_id = f.id AND fv.object_id = '.(int) $this->_xref
           . ' WHERE f.object_key = '. $this->_db->Quote("redevent.xref")
           . '   AND f.frontend_edit = 1 '
           . ' ORDER BY f.ordering '
           ;
    $this->_db->setQuery($query);
    $result = $this->_db->loadObjectList();    
  
    if (!$result) {
      return array();
    }
    $fields = array();
    foreach ($result as $c)
    {
      $field =& redEVENTHelper::getCustomField($c->type);
      $field->bind($c);
      $fields[] = $field;
    }
    return $fields;     
  }
  
  /**
   * get custom fields
   *
   * @return objects array
   */
  function getCustomfields()
  {
    $query = ' SELECT f.*, fv.value '
           . ' FROM #__redevent_fields AS f '
           . ' LEFT JOIN #__redevent_fields_values AS fv ON fv.field_id = f.id AND fv.object_id = '.(int) $this->_id
           . ' WHERE f.object_key = '. $this->_db->Quote("redevent.event")
           . '   AND f.frontend_edit = 1 '
           . ' ORDER BY f.ordering '
           ;
    $this->_db->setQuery($query);
    $result = $this->_db->loadObjectList();    
  
    if (!$result) {
      return array();
    }
    $fields = array();
    foreach ($result as $c)
    {
      $field =& redEVENTHelper::getCustomField($c->type);
      $field->bind($c);
      $fields[] = $field;
    }
    return $fields;     
  }
  
  /**
   * returns id of event to use as template for the submission
   * 
   * @param array categories ids submitted for the event
   * @return int event id
   */
  function _getEventTemplate($categories)
  {
		$mainframe = &JFactory::getApplication();
		$params    = $mainframe->getParams('com_redevent');
		
  	if (!is_array($categories) || !count($categories)) {
			$template_event = (int) $params->get('event_template');  		
  	}
  	
  	// get all categories
    $query = ' SELECT c.id, c.catname, (COUNT(parent.catname) - 1) AS depth, c.event_template, c.ordering '
           . ' FROM #__redevent_categories AS c, '
           . ' #__redevent_categories AS parent '
           . ' WHERE c.lft BETWEEN parent.lft AND parent.rgt '
           . '   AND c.id IN ('.implode(',', $categories).')'
           . ' GROUP BY c.id '
           . ' ORDER BY c.lft;'
           ;
    $this->_db->setQuery($query);
    
    $cats = $this->_db->loadObjectList();
  	// try to find an event template in the categories of the event, or their parents.
  	// try first with deepest category with smallest ordering value
  	uasort($cats, array($this, "_cmpCatEventTemplate"));
  	foreach ($cats as $cat)
  	{
  		$event = $this->_getCategoryEventTemplate($cat->id);
  		if ($event) {
  			return $event;
  		}
  	}
  	return 0;
  }
  
  /**
   * returns the event id of the event template for this category
   * 
   * if no event template defined, it looks up in parent categories
   * @param int category $id
   * @param int event id
   */
  function _getCategoryEventTemplate($id)
  {
  	$query = ' SELECT c.event_template '
		       . ' FROM #__redevent_categories AS c, #__redevent_categories AS ci '
		       . ' WHERE ci.id = '. $this->_db->Quote($id)
		       . '   AND c.lft <= ci.lft AND c.rgt >= ci.lft '
		       . '   AND c.event_template > 0 '
		       . ' ORDER BY c.lft DESC '
		       ;
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadResult();
  	
  	return $res;
  }
  
  function _cmpCatEventTemplate($a, $b)
  {
  	if ($a->depth != $b->depth) {
  		return ($a->depth > $b->depth ? -1 : 1);
  	}
  	
  	if ($a->ordering != $b->ordering) {
  		return ($a->ordering > $b->ordering ? 1 : -1);
  	}
  	
  	return 0;
  }
}
?>