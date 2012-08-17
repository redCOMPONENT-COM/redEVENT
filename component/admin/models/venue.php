<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');

/**
 * EventList Component Venue Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelVenue extends JModelAdmin
{
	/**
	 * venue id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * venue data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int event identifier
	 */
	function setId($id)
	{
		// Set venue id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Logic for the event edit screen
	 *
	 * @access public
	 * @return array
	 * @since 0.9
	 */
	function &getData()
	{
		if ($this->_loadData())
		{

		}
		else  $this->_initData();

		return $this->_data;
	}

	/**
	 * Method to load content event data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT *'
					. ' FROM #__redevent_venues'
					. ' WHERE id = '.$this->_id
					;

			$this->_db->setQuery($query);

			$this->_data = $this->_db->loadObject();
		
      if ($this->_data) {
        $this->_data->categories = $this->getVenueCategories();
				$this->_data->attachments = REAttach::getAttachments('venue'.$this->_data->id);
      }
      
			return (boolean) $this->_data;
		}
		return true;
	}
	
  /**
   * Method to get the category data
   *
   * @access  public
   * @return  boolean True on success
   * @since 0.9
   */
  function &getVenueCategories()
  {
    $query = ' SELECT c.id '
        . ' FROM #__redevent_venues_categories as c '
        . ' INNER JOIN #__redevent_venue_category_xref as x ON x.category_id = c.id '
        . ' WHERE x.venue_id = ' . $this->_db->Quote($this->_id)
        ;
    $this->_db->setQuery( $query );

    $this->_categories = $this->_db->loadResultArray();

    return $this->_categories;
  }

	/**
	 * Method to initialise the venue data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$venue = new stdClass();
			$venue->id          = 0;
			$venue->venue       = null;
			$venue->alias       = null;
			$venue->company     = null;
			$venue->url         = null;
			$venue->street      = null;
			$venue->city        = null;
			$venue->plz					= null;
			$venue->state				= null;
			$venue->country				= null;
			$venue->locimage			= null;
			$venue->map					= 1;
      $venue->latitude      = null;
      $venue->longitude     = null;
			$venue->published			= 1;
			$venue->locdescription		= null;
			$venue->meta_keywords		= null;
			$venue->meta_description	= null;
			$venue->created				= null;
			$venue->author_ip			= null;
			$venue->created_by			= null;
			$venue->dates 				= null;
			$venue->enddates			= null;
			$venue->times 				= null;
			$venue->endtimes			= null;
      $venue->categories    = null;
			$venue->private			= 0;
			$venue->attachments = array();
			$this->_data				= $venue;
			return (boolean) $this->_data;
		}
		return true;
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
			$venue = & JTable::getInstance('redevent_venues', '');
			return $venue->checkin($this->_id);
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
			// Make sure we have a user id to checkout the venue with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$venue = & JTable::getInstance('redevent_venues', '');
			return $venue->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Tests if the venue is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	0.9
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		} elseif ($this->_id < 1) {
			return false;
		} else {
			RedeventError::raiseWarning( 0, 'Unable to Load Data');
			return false;
		}
	}

	/**
	 * Method to store the venue
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		$elsettings = JComponentHelper::getParams('com_redevent');
		$user		= & JFactory::getUser();
		$config 	= & JFactory::getConfig();

		$tzoffset 	= $config->getValue('config.offset');

		$row  =& $this->getTable('redevent_venues', '');

		// bind it to the table
		if (!$row->bind($data)) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}

		// Check if image was selected
		jimport('joomla.filesystem.file');
		$format 	= JFile::getExt(JPATH_SITE.'/images/redevent/venues/'.$row->locimage);

		$allowable 	= array ('gif', 'jpg', 'png');
		if (in_array($format, $allowable)) {
			$row->locimage = $row->locimage;
		} else {
			$row->locimage = '';
		}

		// sanitise id field
		$row->id = (int) $row->id;

		$nullDate	= $this->_db->getNullDate();

		// Are we saving from an item edit?
		if ($row->id) {
			$row->modified 		= gmdate('Y-m-d H:i:s');
			$row->modified_by 	= $user->get('id');
		} else {
			$row->modified 		= $nullDate;
			$row->modified_by 	= '';

			//get IP, time and userid
			$row->created 			= gmdate('Y-m-d H:i:s');

			$row->author_ip 		= $elsettings->get('storeip', '1') ? getenv('REMOTE_ADDR') : 'DISABLED';
			$row->created_by		= $user->get('id');
		}

		//uppercase needed by mapservices
		if ($row->country) {
			$row->country = JString::strtoupper($row->country);
		}

		//update item order
		if (!$row->id) {
			$row->ordering = $row->getNextOrder();
		}

		// Make sure the data is valid
		if (!$row->check($elsettings)) {
			$this->setError($row->getError());
			return false;
		}

		// Store it in the db
		if (!$row->store()) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}
		
    // update the venue category xref
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
		REAttach::store('venue'.$row->id);

		return $row->id;
	}
	
  /**
   * Get a option list of all categories
   */
  public function getCategories() 
  {
   $query = ' SELECT c.id, c.name, (COUNT(parent.name) - 1) AS depth '
           . ' FROM #__redevent_venues_categories AS c, '
           . ' #__redevent_venues_categories AS parent '
           . ' WHERE c.lft BETWEEN parent.lft AND parent.rgt '
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
    return $options;
  }
  
  
  /**
  * Returns a Table object, always creating it
  *
  * @param	type	The table type to instantiate
  * @param	string	A prefix for the table class name. Optional.
  * @param	array	Configuration array for model. Optional.
  * @return	JTable	A database object
  * @since	1.6
  */
  public function getTable($type = 'redevent_venues', $prefix = '', $config = array())
  {
		return JTable::getInstance($type, $prefix, $config);
  }
  
  /**
  * Method to get the record form.
  *
  * @param	array	$data		Data for the form.
  * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
  * @return	mixed	A JForm object on success, false on failure
  * @since	1.7
  */
  public function getForm($data = array(), $loadData = true)
  {
	  // Get the form.
	  $form = $this->loadForm('com_redevent.venue', 'venue',
	  array('load_data' => $loadData) );
	  		if (empty($form))
	  		{
	  			return false;
	  }
	  return $form;
  }
  
  /**
  * Method to get the data that should be injected in the form.
  *
  * @return	mixed	The data for the form.
  * @since	1.7
  */
  protected function loadFormData()
  {
	  // Check the session for previously entered form data.
	  $data = JFactory::getApplication()->getUserState('com_redevent.edit.venue.data', array());
	  if (empty($data))
	  {
	  	$data = $this->getData();
	  }
	  return $data;
  }
}
?>