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
	var $_xref = null;
	
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
	}

	/**
	 * Method to set the event id
	 *
	 * @access	public Event
	 */
	function setId($id)
	{
		// Set new event ID
		$this->_id = $id;
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

		$view		= JRequest::getWord('view');

		/*
		* If Id exists we will do the edit stuff
		*/
		if ($this->_id) {

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
			$editaccess	= ELUser::editaccess($elsettings->eventowner, $this->_event->created_by, $elsettings->eventeditrec, $elsettings->eventedit);			
			$maintainer = ELUser::ismaintainer();

			if ($maintainer || $editaccess ) $allowedtoeditevent = 1;

			if ($allowedtoeditevent == 0) {

				JError::raiseError( 403, JText::_( 'NO ACCESS' ) );

			}

			/*
			* If no Id exists we will do the add event stuff
			*/
		} else {

			//Check if the user has access to the form
			$maintainer = ELUser::ismaintainer();
			$genaccess 	= ELUser::validate_user( $elsettings->evdelrec, $elsettings->delivereventsyes );

			if ($maintainer || $genaccess ) $dellink = 1;

			if ($dellink == 0) {

				JError::raiseError( 403, JText::_( 'NO ACCESS' ) );

			}

			//prepare output
			$this->_event->id				= 0;
			$this->_event->xref				= 0;
			$this->_event->locid			= '';
      $this->_event->categories  = null;
			$this->_event->dates			= '';
			$this->_event->enddates			= null;
			$this->_event->title			= '';
			$this->_event->times			= null;
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
			$query = 'SELECT e.*, v.venue, x.id AS xref, x.eventid, x.venueid, x.dates, x.enddates, x.times, x.endtimes, x.maxattendees,
					x.maxwaitinglist, x.course_price, x.course_credit'
					. ' FROM #__redevent_events AS e'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id'
					. ' LEFT JOIN #__redevent_venues AS v ON v.id = x.venueid'
					. ' WHERE e.id = '.(int)$this->_id
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
	
	function getXref()
	{
		if (empty($this->_xref))
		{
			if ($this->_id)
			{
				$query = ' SELECT x.* '
				       . ' FROM #__redevent_event_venue_xref AS x '
				       . ' WHERE x.id = '. $this->_db->Quote($this->_id)
				       ;
	      $this->_db->setQuery( $query );
				$this->_xref = $this->_db->loadObjectList();
			}
			else
			{
				$obj = new stdclass();
				$obj->eventid           = 0;
				$obj->venueid           = 0;
				$obj->dates             = null;
				$obj->enddates          = null;
				$obj->times             = null;
				$obj->endtimes          = null;
				$obj->registrationend   = null;
				$obj->details           = null;
				$obj->maxattendees      = null;
				$obj->maxwaitinglist    = null;
				$obj->course_credit     = null;
				$obj->course_price      = null;
				$obj->published         = null;
				$this->_xref = $obj;
			}
		}
		return $this->_xref;
	}

	/**
	 * logic to get the categories options
	 *
	 * @access public
	 * @return void
	 */
	function getCategoryOptions( )
	{
		$user		= & JFactory::getUser();
		$elsettings = & redEVENTHelper::config();
		$userid		= (int) $user->get('id');
		$gid		= (int) $user->get('aid');
		$superuser	= ELUser::superuser();

		$where = ' WHERE c.published = 1 AND c.access <= '.$gid;

		//only check for maintainers if we don't have an edit action
		if(!$this->_id) {
			//get the ids of the categories the user maintaines
			$query = 'SELECT g.group_id'
					. ' FROM #__redevent_groupmembers AS g'
					. ' WHERE g.member = '.$userid
					;
			$this->_db->setQuery( $query );
			$catids = $this->_db->loadResultArray();

			$categories = implode(' OR c.groupid = ', $catids);

			//build ids query
			if ($categories) {
				//check if user is allowed to submit events in general, if yes allow to submit into categories
				//which aren't assigned to a group. Otherwise restrict submission into maintained categories only 
				if (ELUser::validate_user($elsettings->evdelrec, $elsettings->delivereventsyes)) {
					$where .= ' AND c.groupid = 0 OR c.groupid = '.$categories;
				} else {
					$where .= ' AND c.groupid = '.$categories;
				}
			} else {
				$where .= ' AND c.groupid = 0';
			}

		}

		//administrators or superadministrators have access to all categories, also maintained ones
		if($superuser) {
			$where = ' WHERE c.published = 1';
		}

		//get the maintained categories and the categories whithout any group
		//or just get all if somebody have edit rights	
    $query = ' SELECT c.id, c.catname, (COUNT(parent.catname) - 1) AS depth '
           . ' FROM #__redevent_categories AS c, '
           . ' #__redevent_categories AS parent '
           . $where
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
		global $mainframe;
		
		$params 	= & $mainframe->getParams();
		
		$where		= $this->_buildVenuesWhere(  );
		$orderby	= $this->_buildVenuesOrderBy(  );

		$limit			= $mainframe->getUserStateFromRequest('com_redevent.selectvenue.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart		= JRequest::getInt('limitstart');

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

		$where = array();
		
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
		global $mainframe;

		$user 		= & JFactory::getUser();
		$elsettings = & redEVENTHelper::config();

		//Get mailinformation
		$SiteName 		= $mainframe->getCfg('sitename');
		$MailFrom	 	= $mainframe->getCfg('mailfrom');
		$FromName 		= $mainframe->getCfg('fromname');
		$tzoffset 		= $mainframe->getCfg('offset');

		$row 	= & JTable::getInstance('redevent_events', '');

		//Sanitize
		$data['datdescription'] = JRequest::getVar( 'datdescription', '', 'post','string', JREQUEST_ALLOWRAW );

		//include the metatags
		$data['meta_description'] = addslashes(htmlspecialchars(trim($elsettings->meta_description)));
		if (strlen($data['meta_description']) > 255) {
			$data['meta_description'] = substr($data['meta_description'],0,254);
		}
		$data['meta_keywords'] = addslashes(htmlspecialchars(trim($elsettings->meta_keywords)));
		if (strlen($data['meta_keywords']) > 200) {
			$data['meta_keywords'] = substr($data['meta_keywords'],0,199);
		}
		
		$curimage = JRequest::getVar( 'curimage', '', 'post','string' );

		//bind it to the table
		if (!$row->bind($data)) {
			RedeventError::raiseError( 500, $this->_db->stderr() );
			return false;
		}

		//Are we saving from an item edit?
		if ($row->id) {

			//check if user is allowed to edit events
			$editaccess	= ELUser::editaccess($elsettings->eventowner, $row->created_by, $elsettings->eventeditrec, $elsettings->eventedit);
			$maintainer = ELUser::ismaintainer();

			if ($maintainer || $editaccess ) $allowedtoeditevent = 1;

			if ($allowedtoeditevent == 0) {
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

		} else {

			//check if user is allowed to submit new events
			$maintainer = ELUser::ismaintainer();
			$genaccess 	= ELUser::validate_user( $elsettings->evdelrec, $elsettings->delivereventsyes );

			if ($maintainer || $genaccess ) $dellink = 1;

			if ($dellink == 0){
				JError::raiseError( 403, JText::_( 'NO ACCESS' ) );
			}

			//get IP, time and userid
			$row->created 		= gmdate('Y-m-d H:i:s');

			$row->author_ip 	= $elsettings->storeip ? getenv('REMOTE_ADDR') : 'DISABLED';
			$row->created_by 	= $user->get('id');

			//Set owneredit to false
			$owneredit = 0;
		}

		/*
		* Autopublish
		* check if the user has the required rank for autopublish
		*/
		$autopubev = ELUser::validate_user( $elsettings->evpubrec, $elsettings->autopubl );
		if ($autopubev || $owneredit) {
				$row->published = 1 ;
			} else {
				$row->published = 0 ;
		}

		//Image upload

		//If image upload is required we will stop here if no file was attached
		if ( empty($file['name']) && $elsettings->imageenabled == 2 ) {

			$this->setError( JText::_( 'IMAGE EMPTY' ) );
			return false;
		}

		if ( ( $elsettings->imageenabled == 2 || $elsettings->imageenabled == 1 ) && ( !empty($file['name'])  ) )  {

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
		} else {
			//keep image if edited and left blank
			$row->datimage = $curimage;
		}//end image if

		$editoruser = ELUser::editoruser();

		if (!$editoruser) {
			//check datdescription --> wipe out code
			$row->datdescription = strip_tags($row->datdescription, '<br><br/>');

			//convert the linux \n (Mac \r, Win \r\n) to <br /> linebreaks
			$row->datdescription = str_replace(array("\r\n", "\r", "\n"), "<br />", $row->datdescription);

			// cut too long words
			$row->datdescription = wordwrap($row->datdescription, 75, ' ', 1);

			//check length
			$length = JString::strlen($row->datdescription);
			if ($length > $elsettings->datdesclimit) {
				//too long then shorten datdescription
				$row->datdescription = JString::substr($row->datdescription, 0, $elsettings->datdesclimit);
				//add ...
				$row->datdescription = $row->datdescription.'...';
			}
		}

		$row->title = trim( JFilterOutput::ampReplace( $row->title ) );

		//set registration regarding the el settings
		switch ($elsettings->showfroregistra) {
			case 0:
				$row->registra = 0;
			break;

			case 1:
				$row->registra = 1;
			break;

			case 2:
				$row->registra =  $row->registra ;
			break;
		}

		switch ($elsettings->showfrounregistra) {
			case 0:
				$row->unregistra = 0;
			break;

			case 1:
				$row->unregistra = 1;
			break;

			case 2:
				if ($elsettings->showfroregistra >= 1) {
					$row->unregistra = $row->unregistra;
				} else {
					$row->unregistra = 0;
				}
			break;
		}


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
		
		$this->_db->setQuery('SELECT * FROM #__redevent_venues AS v LEFT JOIN #__redevent_event_venue_xref AS x ON x.venueid = v.id WHERE x.eventid = '.(int)$row->id);
		$rowloc = $this->_db->loadObject();

		jimport('joomla.utilities.mail');

		$link 	= JURI::base().JRoute::_('index.php?option=com_redevent&view=details&id='.$row->id, false);

		//create the mail for the site owner
		if (($elsettings->mailinform == 1) || ($elsettings->mailinform == 3)) {

			$mail = JFactory::getMailer();

			$state 	= $row->published ? JText::sprintf('MAIL EVENT PUBLISHED', $link) : JText::_('MAIL EVENT UNPUBLISHED');

			if ($edited) {

				$modified_ip 	= getenv('REMOTE_ADDR');
				$edited 		= JHTML::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('MAIL EDIT EVENT', $user->name, $user->username, $user->email, $modified_ip, $edited, $row->title, $row->dates, $row->times, $rowloc->venue, $rowloc->city, $row->datdescription, $state);
				$mail->setSubject( $SiteName.JText::_( 'EDIT EVENT MAIL' ) );

			} else {

				$created 	= JHTML::Date( $row->created, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 	= JText::sprintf('MAIL NEW EVENT', $user->name, $user->username, $user->email, $row->author_ip, $created, $row->title, $row->dates, $row->times, $rowloc->venue, $rowloc->city, $row->datdescription, $state);
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
				$mailbody 		= JText::sprintf('USER MAIL EDIT EVENT', $user->name, $user->username, $edited, $row->title, $row->dates, $row->times, $rowloc->venue, $rowloc->city, $row->datdescription, $state);
				$usermail->setSubject( $SiteName.JText::_( 'EDIT USER EVENT MAIL' ) );

			} else {

				$created 	= JHTML::Date( $row->created, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 	= JText::sprintf('USER MAIL NEW EVENT', $user->name, $user->username, $created, $row->title, $row->dates, $row->times, $rowloc->venue, $rowloc->city, $row->datdescription, $state);
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
	 * return venues lists as options
	 * 
	 * @return array
	 */
	function getVenueOptions()
	{
		$app = &JFactory::getApplication();
		$params = $app->getParams();
		
		$query = ' SELECT v.id AS value, v.venue AS text '
		       . ' FROM #__redevent_venues AS v '
		       . ' ORDER BY v.venue ASC '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}

	/**
	 * return events lists as options
	 * 
	 * @return array
	 */
	function getEventOptions()
	{
		$app = &JFactory::getApplication();
		$params = $app->getParams();
		
		$query = ' SELECT e.id AS value, e.title AS text '
		       . ' FROM #__redevent_events AS e '
		       . ' ORDER BY e.title ASC '
		       ;
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
?>