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
 * EventList Component Editvenue Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelEditvenue extends JModel
{
	/**
	 * Venue data in Venue array
	 *
	 * @var array
	 */
	var $_venue = null;

	var $_categories = null;
	
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
	 * Method to set the Venue id
	 *
	 * @access	public
	 * @param	int	Venue ID number
	 */
	function setId($id)
	{
		// Set new venue ID
		$this->_id			= $id;
	}

	/**
	 * Logic to get the venue
	 *
	 * @return array
	 */
	function &getVenue(  )
	{
		global $mainframe;

		// Initialize variables
		$user       = & JFactory::getUser();
		$elsettings = & redEVENTHelper::config();

		$view		= JRequest::getWord('view');

		if ($this->_id) 
		{
			// Load the Event data
			$this->_loadVenue();

			/*
			* Error if allready checked out
			*/
			if ($this->_venue->isCheckedOut( $user->get('id') )) {
				$mainframe->redirect( 'index.php?option=&view='.$view, JText::_( 'THE VENUE' ).' '.$this->_venue->venue.' '.JText::_( 'EDITED BY ANOTHER ADMIN' ) );
			} else {
				$this->_venue->checkout( $user->get('id') );
			}

		} 
		else 
		{			
			//prepare output
			$this->_venue->id				= '';
			$this->_venue->venue			= '';
			$this->_venue->published	= 0;
      $this->_venue->categories = null;
			$this->_venue->url				= '';
			$this->_venue->street			= '';
			$this->_venue->plz				= '';
			$this->_venue->locdescription	= '';
			$this->_venue->city				= '';
			$this->_venue->state			= '';
			$this->_venue->country			= '';
			$this->_venue->map				= $elsettings->showmapserv ? 1 : 0;
			$this->_venue->created			= '';
			$this->_venue->created_by		= '';
			$this->_venue->author_ip		= '';
			$this->_venue->locimage			= '';
			$this->_venue->meta_keywords	= '';
			$this->_venue->meta_description	= '';
			$this->_venue->attachments	= array();

		}

		return $this->_venue;

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
			$managed = $acl->getManagedVenuesCategories();
			if (!$managed || !count($managed)) {
				return false;
			}
			$cwhere = ' WHERE c.id IN ('.implode(',', $managed).') ';
		}

		//get the maintained categories and the categories whithout any group
		//or just get all if somebody have edit rights	
    $query = ' SELECT c.id, c.name, (COUNT(parent.name) - 1) AS depth, c.ordering '
           . ' FROM #__redevent_venues_categories AS c, '
           . ' #__redevent_venues_categories AS parent '
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
      $options[] = JHTML::_('select.option', $cat->id, str_repeat('>', $cat->depth) . ' ' . $cat->name);
    }

    $this->_categories = $options;

		return $this->_categories;
	}

	/**
	 * logic to get the venue
	 *
	 * @access private
	 * @return array
	 */
	function _loadVenue( )
	{
		if (empty($this->_venue)) 
		{
			$user	= & JFactory::getUser();
			$this->_venue =& JTable::getInstance('redevent_venues', '');
			$this->_venue->load( $this->_id );			
		
      if ($this->_venue->id) 
      {
        $query =  ' SELECT c.id '
              . ' FROM #__redevent_venues_categories as c '
              . ' INNER JOIN #__redevent_venue_category_xref as x ON x.category_id = c.id '
              . ' WHERE c.published = 1 '
              . '   AND x.venue_id = ' . $this->_db->Quote($this->_venue->id)
              ;
        $this->_db->setQuery( $query );
  
        $this->_venue->categories = $this->_db->loadResultArray();
				$this->_venue->attachments = REAttach::getAttachments('venue'.$this->_venue->id, $user->get('aid'));	
      }
		}
	  return $this->_venue;
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
			$item = & $this->getTable('redevent_venues', '');
			if(! $item->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return false;
	}

	/**
	 * Method to store the venue
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

		$row 		= & JTable::getInstance('redevent_venues', '');

		//bind it to the table
		if (!$row->bind($data)) {
			RedeventError::raiseError( 500, $this->_db->stderr() );
			return false;
		}

		//Are we saving from an item edit?
		if ($row->id) {

			$row->modified 		= gmdate('Y-m-d H:i:s');

			$row->modified_by 	= $user->get('id');

			//Is editor the owner of the venue
			//This extra Check is needed to make it possible
			//that the venue is published after an edit from an owner
			if ($elsettings->venueowner == 1 && $row->created_by == $user->get('id')) {
				$owneredit = 1;
			} else {
				$owneredit = 0;
			}

		} else {

			//get IP, time and userid
			$row->created 			= gmdate('Y-m-d H:i:s');

			$row->author_ip 		= $elsettings->storeip ? getenv('REMOTE_ADDR') : 'DISABLED';
			$row->created_by		= $user->get('id');

			//set owneredit to false
			$owneredit = 0;
		}

		//Image upload

		//If image upload is required we will stop here if no file was attached
		if ( empty($file['name']) && $elsettings->imageenabled == 2 ) {
			$this->setError( JText::_( 'IMAGE EMPTY' ) );
			return false;
		}

		if ( ( $elsettings->imageenabled == 2 || $elsettings->imageenabled == 1 ) && ( !empty($file['name'])  ) )  {

			jimport('joomla.filesystem.file');

			$base_Dir 	= JPATH_SITE.'/images/redevent/venues/';

			//check the image
			$check = redEVENTImage::check($file, $elsettings);

			if ($check === false) {
				$mainframe->redirect($_SERVER['HTTP_REFERER']);
			}

			//sanitize the image filename
			$filename = redEVENTImage::sanitize($base_Dir, $file['name']);
			$filepath = $base_Dir . $filename;

			if (!JFile::upload( $file['tmp_name'], $filepath )) {
				$this->setError( JText::_( 'UPLOAD FAILED' ) );
				return false;
			} else {
				$row->locimage = $filename;
			}
		} else {
			//keep image if edited and left blank
			$row->locimage = $row->curimage;
		}//end image upload if

		//Check description
		$editoruser = ELUser::editoruser();

		if (!$editoruser) {
			//check description --> wipe out code
			$row->locdescription = strip_tags($row->locdescription, '<br><br/>');

			//convert the linux \n (Mac \r, Win \r\n) to <br /> linebreaks
			$row->locdescription = str_replace(array("\r\n", "\r", "\n"), "<br />", $row->locdescription);

			//cut too long words
			$row->locdescription = wordwrap($row->locdescription, 75, " ", 1);

			//check length
			$length = JString::strlen($row->locdescription);
			if ($length > $elsettings->datdesclimit) {

				// if required shorten it
				$row->locdescription = JString::substr($row->locdescription, 0, $elsettings->datdesclimit);
				//if shortened add ...
				$row->locdescription = $row->locdescription.'...';
			}
		}

		$row->venue = trim( JFilterOutput::ampReplace( $row->venue ) );

		//Make sure the data is valid
		if (!$row->check($elsettings)) {
			$this->setError($row->getError());
			return false;
		}

		//is this an edited venue or not?
		//after store we allways have an id
		$edited = $row->id ? $row->id : false;

		//store it in the db
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}		
	        
    // update the event category xref
    // first, delete current rows for this event
    $query = ' DELETE FROM #__redevent_venue_category_xref WHERE venue_id = ' . $this->_db->Quote($row->id);
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError($this->_db->getErrorMsg());
      return false;     
    }
    // insert new ref
    foreach ((array) $data['categories'] as $cat_id) {
      $query = ' INSERT INTO #__redevent_venue_category_xref (venue_id, category_id) VALUES (' . $this->_db->Quote($row->id) . ', '. $this->_db->Quote($cat_id) . ')';
      $this->_db->setQuery($query);
      if (!$this->_db->query()) {
        $this->setError($this->_db->getErrorMsg());
        return false;     
      }     
    }

		// attachments    
		if ($params->get('allow_attachments', 1)) {
			REAttach::store('venue'.$row->id);
		}
		
		jimport('joomla.utilities.mail');

		$link 	= JRoute::_(JURI::base().RedeventHelperRoute::getVenueEventsRoute($row->id), false);

		//create mail
		if (($elsettings->mailinform == 2) || ($elsettings->mailinform == 3)) {

			$mail = JFactory::getMailer();

			$state 	= $row->published ? JText::sprintf('MAIL VENUE PUBLISHED', $link) : JText::_('MAIL VENUE UNPUBLISHED');

			If ($edited) {

				$modified_ip 	= getenv('REMOTE_ADDR');
				$edited 		= JHTML::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('MAIL EDIT VENUE', $user->name, $user->username, $user->email, $modified_ip, $edited, $row->venue, $row->url, $row->street, $row->plz, $row->city, $row->country, $row->locdescription, $state);
				$mail->setSubject( $SiteName.JText::_( 'EDIT VENUE MAIL' ) );

			} else {

				$created 		= JHTML::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('MAIL NEW VENUE', $user->name, $user->username, $user->email, $row->author_ip, $created, $row->venue, $row->url, $row->street, $row->plz, $row->city, $row->country, $row->locdescription, $state);
				$mail->setSubject( $SiteName.JText::_( 'NEW VENUE MAIL' ) );

			}

			$receivers = explode( ',', trim($elsettings->mailinformrec));

			$mail->addRecipient( $receivers );
			$mail->setSender( array( $MailFrom, $FromName ) );
			$mail->setBody( $mailbody );

      if (!$mail->Send()) {
        RedeventHelperLog::simpleLog('Error sending created/edited venue notification to site owner');
      }
		}

		//create the mail for the user
		if (($elsettings->mailinformuser == 2) || ($elsettings->mailinformuser == 3)) {

			$usermail = JFactory::getMailer();

			$state 	= $row->published ? JText::sprintf('USER MAIL VENUE PUBLISHED', $link) : JText::_('USER MAIL VENUE UNPUBLISHED');

			If ($edited) {

				$edited 		= JHTML::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('USER MAIL EDIT VENUE', $user->name, $user->username, $edited, $row->venue, $row->url, $row->street, $row->plz, $row->city, $row->country, $row->locdescription, $state);
				$usermail->setSubject( $SiteName.JText::_( 'EDIT USER VENUE MAIL' ) );

			} else {

				$created 		= JHTML::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('USER MAIL NEW VENUE', $user->name, $user->username, $created, $row->venue, $row->url, $row->street, $row->plz, $row->city, $row->country, $row->locdescription, $state);
				$usermail->setSubject( $SiteName.JText::_( 'NEW USER VENUE MAIL' ) );

			}

			$usermail->addRecipient( $user->email );
			$usermail->setSender( array( $MailFrom, $FromName ) );
			$usermail->setBody( $mailbody );

      if (!$usermail->Send()) {
        RedeventHelperLog::simpleLog('Error sending created/edited venue notification to venue owner');
      }
		}

		//update item order
		$row->reorder();

		return $row->id;
	}
	
	function _inAdminGroup()
	{
		$venue = $this->_loadVenue();
				
		$user 		= & JFactory::getUser();
		
		$query = ' SELECT gv.id '
		       . ' FROM #__redevent_groups_venues AS gv ' 
		       . ' INNER JOIN #__redevent_groupmembers AS gm ON gv.group_id = gm.group_id '
		       . ' WHERE gm.member ='. $this->_db->Quote($user->id)
  	       . '   AND gm.edit_venues > 0 '
  	       . '   AND gv.accesslevel > 0 '
		       . '   AND gv.venue_id ='. $this->_db->Quote($venue->id)
		       ;
		$this->_db->setQuery($query);
		
		return (int) $this->_db->loadResult();
	}	
}
?>