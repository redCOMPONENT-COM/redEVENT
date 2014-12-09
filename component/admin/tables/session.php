<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent sessions table class
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventTableSession extends RTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 */
	protected $_tableName = 'redevent_event_venue_xref';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 */
	protected $_tableKey = 'id';

	/**
	 * Field name to publish/unpublish table registers. Ex: state
	 *
	 * @var  string
	 */
	protected $_tableFieldState = 'published';

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		if (!$this->eventid)
		{
			$this->setError(JText::_('COM_REDEVENT_SESSION_EVENTID_IS_REQUIRED'));

			return false;
		}

		// Allow credit to be null
		if ($this->course_credit === '')
		{
			$this->course_credit = null;
		}

		if ($this->times === '')
		{
			$this->times = null;
		}

		if ($this->endtimes === '')
		{
			$this->endtimes = null;
		}

		$alias = JFilterOutput::stringURLSafe($this->title);

		if (empty($this->alias) && $alias)
		{
			$this->alias = $alias;
		}

		return true;
	}

	public function canDelete($id)
	{
		// can't delete if there are attendees
		$query = ' SELECT COUNT(*) FROM #__redevent_register WHERE xref = '. intval( $id );
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();

		if ($res) {
			$this->setError(JText::_('COM_REDEVENT_EVENT_DATE_HAS_ATTENDEES'));
			return false;
		}

		return true;
	}
	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function bind($src, $ignore = array())
	{
		if (!parent::bind($src, $ignore))
		{
			return false;
		}

		// Custom fields
		$customs = $this->_getCustomFieldsColumns();

		foreach ($customs as $c)
		{
			if (isset($src[$c]))
			{
				$src[$c] = is_array($src[$c]) ? implode("\n", $src[$c]) : $src[$c];
			}
			else
			{
				$src[$c] = '';
			}
		}

		return true;
	}

	/**
	 * get custom fields for table
	 *
	 * @return array
	 */
	protected function _getCustomFieldsColumns()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('CONCAT("custom", id)');
		$query->from('#__redevent_fields');
		$query->where('object_key = ' . $db->Quote('redevent.xref'));

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	public function setPrices($prices = array())
	{
		// first remove current rows
		$query = ' DELETE FROM #__redevent_sessions_pricegroups '
		. ' WHERE xref = ' . $this->_db->Quote($this->id);
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// then recreate them if any
		foreach ((array) $prices as $k => $price)
		{
			if (!isset($price->pricegroup_id) || !isset($price->price))
			{
				continue;
			}

			$new = JTable::getInstance('RedEvent_sessions_pricegroups', '');
			$new->set('xref',          $this->id);
			$new->set('pricegroup_id', $price->pricegroup_id);
			$new->set('price',         $price->price);
			$new->set('currency',      $price->currency);

			if (!($new->check() && $new->store()))
			{
				$this->setError($new->getError());

				return false;
			}
		}
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link	http://docs.joomla.org/JTable/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		// Make sure the language is same as event
		$db      = $this->_db;
		$query = $db->getQuery(true);

		$query->select('language');
		$query->from('#__redevent_events');
		$query->where('id = ' . $this->eventid);

		$db->setQuery($query);
		$res = $db->loadResult();

		$this->language = $res;

		return parent::store($updateNulls);
	}
}
