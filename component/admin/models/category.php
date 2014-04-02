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

/**
 * RedEvent Model Category
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedEventModelCategory extends FOFModel
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
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$this->setId(JRequest::getVar('id',  0, '', 'int'));
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
	function &getItem()
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
			$query = ' SELECT c.*, e.title AS event_template_name '
			       . ' FROM #__redevent_categories AS c '
			       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.id = c.event_template '
			       . ' LEFT JOIN #__redevent_events AS e ON e.id = x.eventid '
			       . ' WHERE c.id = '.$this->_id
			       ;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			if ($this->_data) {
				$files = RedeventHelperAttachment::getAttachments('category'.$this->_data->id);
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
			$category->catname				= null;
			$category->alias				= null;
			$category->catdescription		= null;
			$category->meta_description		= null;
			$category->meta_keywords		= null;
			$category->published			= 1;
			$category->image				= null;
      $category->color        = '';
			$category->access				= 0;
			$category->event_template = 0;
			$category->event_template_name = '';
			$category->attachments	= array();
			$this->_data					= $category;
			return (boolean) $this->_data;
		}
		return true;
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
		// triggers for smart search
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');

		$row  =& $this->getTable();

		// bind it to the table
		if (!$row->bind($data)) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}

		$isNew = false;
		if (!$row->id) {
			$row->ordering = $row->getNextOrder();
			$isNew = true;
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		// Trigger the onFinderBeforeSave event.
		$results = $dispatcher->trigger('onFinderBeforeSave', array($this->option . '.' . $this->name, $row, $isNew));

		// Store it in the db
		if (!$row->store()) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}

		// attachments
		RedeventHelperAttachment::store('category'.$row->id);

		// Trigger the onFinderAfterSave event.
		$results = $dispatcher->trigger('onFinderAfterSave', array($this->option . '.' . $this->name, $row, $isNew));

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
		$row = $this->getTable();

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
	public function getCategories()
	{
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
	private function buildCategory($cattree, $catfilter, $subcats, $loop=1)
	{
		if (isset($cattree[$catfilter])) {
			foreach ($cattree[$catfilter] as $subcatid => $category) {
				$this->html .= '<option value="'.$category['cid'].'"';
				if ($this->_data->parent_id == $category['cid']) $this->html .= 'selected="selected"';
				$this->html .= '>'.str_repeat('>', $loop).' '.$category['catname'].'</option>';
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
	public function getTable($type = 'redevent_categories', $prefix = '', $config = array())
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
		$form = $this->loadForm('com_redevent.category', 'category',
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
		$data = JFactory::getApplication()->getUserState('com_redevent.edit.category.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
}
