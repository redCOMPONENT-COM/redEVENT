<?php
/**
 * @version 1.0 $Id: category.php 160 2009-05-29 16:16:39Z julien $
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

jimport('joomla.application.component.modeladmin');

/**
 * EventList Component Category Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedEventModelVenuesCategory extends JModelAdmin
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
			$query = 'SELECT *'
					. ' FROM #__redevent_venues_categories'
					. ' WHERE id = '.$this->_id
					;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();

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
			$category->name				= null;
			$category->alias				= null;
			$category->description		= null;
			$category->meta_description		= null;
			$category->meta_keywords		= null;
			$category->published			= 1;
			$category->image				= null;
			$category->access				= 0;
			$category->private				= 0;
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
			$category = & JTable::getInstance('redevent_venues_categories', '');
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
			$category = & JTable::getInstance('redevent_venues_categories', '');
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
		$row  =& $this->getTable('redevent_venues_categories', '');
		
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
		$row  =& $this->getTable('redevent_venues_categories', '');

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
	 * Get a list of all categories as options
	 */
	public function getCategories() 
	{	  
	  $current = &$this->getData();
	  
    $query = ' SELECT c.id, c.name, (COUNT(parent.name) - 1) AS depth '
           . ' FROM #__redevent_venues_categories AS c, '
           . ' #__redevent_venues_categories AS parent '
           . ' WHERE c.lft BETWEEN parent.lft AND parent.rgt '
           ;
    if ($this->_id) { // prevent loops !
      $query .= '   AND c.lft NOT BETWEEN '. $this->_db->Quote($current->lft) .' AND '. $this->_db->Quote($current->rgt);
    }  
    $query .= ' GROUP BY c.id '
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
	 * Create the subcategory layout
	 */
	private function buildCategory($cattree, $catfilter, $subcats, $loop=1)
	{
		if (isset($cattree[$catfilter])) {
			foreach ($cattree[$catfilter] as $subcatid => $category) {
				$this->html .= '<option value="'.$category['cid'].'"';
				if ($this->_data->parent_id == $category['cid']) $this->html .= 'selected="selected"';
				$this->html .= '>'.str_repeat('>', $loop).' '.$category['name'].'</option>';
				$subcats = $this->buildCategory($cattree, $subcatid, $subcats, $loop+1);
			}
		}
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
	public function getTable($type = 'redevent_venues_categories', $prefix = '', $config = array())
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
		$form = $this->loadForm('com_redevent.venuecategory', 'venuecategory',
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
		$data = JFactory::getApplication()->getUserState('com_redevent.edit.venuecategory.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
}
