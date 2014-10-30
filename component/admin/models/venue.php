<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Venue
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelVenue extends RModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  Record Id
	 *
	 * @return  mixed
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		if ($result && $result->id)
		{
			$helper = new RedeventHelperAttachment;
			$files = $helper->getAttachments('venue' . $result->id);
			$result->attachments = $files;

			$result->categories = $this->getVenueCategories($result);
		}
		else
		{
			$result->attachments = array();
			$result->categories = array();
		}

		return $result;
	}

	/**
	 * Method to get the category data
	 *
	 * @param   object  $result  result to get categories from
	 *
	 * @return  array
	 */
	private function getVenueCategories($result)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('c.id');
		$query->from('#__redevent_venues_categories AS c');
		$query->join('INNER', '#__redevent_venue_category_xref AS x ON x.category_id = c.id');
		$query->where('x.venue_id = ' . $result->id);

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{

		// Are we saving from an item edit?
		if (!$data['id'])
		{
			$row->modified 		= gmdate('Y-m-d H:i:s');
			$row->modified_by 	= $user->get('id');
			$isNew = false;
		} else {
			$row->modified 		= $nullDate;
			$row->modified_by 	= '';

			//get IP, time and userid
			$row->created 			= gmdate('Y-m-d H:i:s');

			$row->author_ip 		= $elsettings->get('storeip', '1') ? getenv('REMOTE_ADDR') : 'DISABLED';
			$row->created_by		= $user->get('id');
			$isNew = true;
		}


		$result = parent::save($data);

		if ($result)
		{
			// Attachments
			$helper = new RedeventHelperAttachment;
			$helper->store('venue' . $this->getState($this->getName() . '.id'));
		}

		return $result;
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

		// triggers for smart search
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');



		// bind it to the table
		if (!$row->bind($data)) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}

		// Check if image was selected
		jimport('joomla.filesystem.file');
		$format 	= strtolower(JFile::getExt($row->locimage));

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
			$isNew = false;
		} else {
			$row->modified 		= $nullDate;
			$row->modified_by 	= '';

			//get IP, time and userid
			$row->created 			= gmdate('Y-m-d H:i:s');

			$row->author_ip 		= $elsettings->get('storeip', '1') ? getenv('REMOTE_ADDR') : 'DISABLED';
			$row->created_by		= $user->get('id');
			$isNew = true;
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

		// Trigger the onFinderBeforeSave event.
		$results = $dispatcher->trigger('onFinderBeforeSave', array($this->option . '.' . $this->name, $row, $isNew));

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
    if (isset($data['categories']))
    {
	    foreach ((array) $data['categories'] as $cat_id)
	    {
	      $query = ' INSERT INTO #__redevent_venue_category_xref (venue_id, category_id) VALUES (' . $this->_db->Quote($row->id) . ', '. $this->_db->Quote($cat_id) . ')';
	      $this->_db->setQuery($query);
	      if (!$this->_db->query()) {
	        $this->setError($this->_db->getErrorMsg());
	        return false;
	      }
	    }
    }

		// attachments
		RedeventHelperAttachment::store('venue'.$row->id);

		// Trigger the onFinderAfterSave event.
		$results = $dispatcher->trigger('onFinderAfterSave', array($this->option . '.' . $this->name, $row, $isNew));
		return $row->id;
	}
}
