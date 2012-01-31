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
 * EventList Component Category Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelCategory extends JModel
{
	/**
	 * Category id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Category data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Groups data array
	 *
	 * @var array
	 */
	var $_groups = null;

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
	 * @param	int category identifier
	 */
	function setId($id)
	{
		// Set category id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Method to get content category data
	 *
	 * @access	public
	 * @return	array
	 * @since	0.9
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
	 * Method to get the group data
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function &getGroups()
	{
		$query = 'SELECT id AS value, name AS text'
			. ' FROM #__redevent_groups'
			. ' ORDER BY name'
			;
		$this->_db->setQuery( $query );

		$this->_groups = $this->_db->loadObjectList();

		return $this->_groups;
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
			$query = ' SELECT c.*, e.title AS event_template_name '
			       . ' FROM #__redevent_categories AS c '
			       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.id = c.event_template '
			       . ' LEFT JOIN #__redevent_events AS e ON e.id = x.eventid '
			       . ' WHERE c.id = '.$this->_id
			       ;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			if ($this->_data) {
				$files = REAttach::getAttachments('category'.$this->_data->id);
				$this->_data->attachments = $files;
			}

			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the category data
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
			$category = new stdClass();
			$category->id					= 0;
			$category->parent_id			= 0;
			$category->groupid				= 0;
			$category->catname				= null;
			$category->alias				= null;
			$category->catdescription		= null;
			$category->meta_description		= null;
			$category->meta_keywords		= null;
			$category->published			= 1;
			$category->image				= JText::_('COM_REDEVENT_SELECTIMAGE');
      $category->color        = '';
			$category->access				= 0;
			$category->event_template = 0;
			$category->event_template_name = '';
			$category->private			= 0;
			$category->attachments	= array();
			$this->_data					= $category;
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
			$category = & JTable::getInstance('redevent_categories', '');
			return $category->checkin($this->_id);
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
			// Make sure we have a user id to checkout the group with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$category = & JTable::getInstance('redevent_categories', '');
			return $category->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Tests if the category is checked out
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
	 * Method to store the category
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		$row  =& $this->getTable('redevent_categories', '');
		
		// bind it to the table
		if (!$row->bind($data)) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}

		if (!$row->id) {
			$row->ordering = $row->getNextOrder();
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		// Store it in the db
		if (!$row->store()) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}
	
		// attachments
		REAttach::store('category'.$row->id);
		
		return $row->id;
	}

	/**
	 * Method to set the access level of the category
	 *
	 * @access	public
	 * @param integer id of the category
	 * @param integer access level
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function access($id, $access)
	{
		$row  =& $this->getTable('redevent_categories', '');

		$row->load( $id );
		$row->access = $access;

		if ( !$row->check() ) {
			return $row->getError();
		}
		if ( !$row->store() ) {
			return $row->getError();
		}

		return true;
	}
	
	/**
	 * Get a list of all categories and put them in a select list
	 */
	public function getCategories() {
		$db = JFactory::getDBO();
		/* 1. Get all categories */
		$q = "SELECT id, parent_id, catname
			FROM #__redevent_categories"
			;

		if ($this->_id) {
			$q .= ' WHERE id <> ' . $db->Quote($this->_id);
		}
		$db->setQuery($q);
		$rawcats = $db->loadObjectList();
		
		/* 2. Group categories based on their parent_id */
		$categories = array();
		foreach ($rawcats as $key => $rawcat) {
			$categories[$rawcat->parent_id][$rawcat->id]['pid'] = $rawcat->parent_id;
			$categories[$rawcat->parent_id][$rawcat->id]['cid'] = $rawcat->id;
			$categories[$rawcat->parent_id][$rawcat->id]['catname'] = $rawcat->catname;
		}
		$html = '<select id="parent_id" class="inputbox" size="10" name="parent_id">';
		if (count($categories) > 0) {
			/* Take the toplevels first */
			foreach ($categories[0] as $key => $category) {
				$this->html = '';
				/* Write out toplevel */
				$html .= '<option value="'.$category['cid'].'"';
				if ($this->_data->parent_id == $category['cid']) $html .= 'selected="selected"';
				$html .= '>'.$category['catname'].'</option>';
				
				/* Write the subcategories */
				$this->buildCategory($categories, $category['cid'], array());
				$html .= $this->html;
			}
		}
		$html .= '</select>';
		
		return $html;
	}
	
	/**
	 * Create the subcategory layout
	 */
	private function buildCategory($cattree, $catfilter, $subcats, $loop=1) {
		if (isset($cattree[$catfilter])) {
			foreach ($cattree[$catfilter] as $subcatid => $category) {
				$this->html .= '<option value="'.$category['cid'].'"';
				if ($this->_data->parent_id == $category['cid']) $this->html .= 'selected="selected"';
				$this->html .= '>'.str_repeat('>', $loop).' '.$category['catname'].'</option>';
				$subcats = $this->buildCategory($cattree, $subcatid, $subcats, $loop+1);
			}
		}
	}
}
