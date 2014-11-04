<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent events table class
 *
 * @package  Redevent.admin
 * @since    0.9
*/
class RedeventTableEvent extends RTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 */
	protected $_tableName = 'redevent_events';

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
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		$app = JFactory::getApplication();

		// Check fields
		$this->title = strip_tags(trim($this->title));
		$titlelength = JString::strlen($this->title);

		if (!$this->title)
		{
			$this->setError(JText::_('COM_REDEVENT_ADD_TITLE'));

			return false;
		}

		if ($titlelength > 100)
		{
			$this->setError(JText::_('COM_REDEVENT_ERROR_TITLE_LONG'));

			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->title);

		if (empty($this->alias) || $this->alias === $alias )
		{
			$this->alias = $alias;
		}

		// Check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->submission_type_email) > 0)
		{
			$this->setError(JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));

			return false;
		}

		if (preg_match('/\[[a-z]*signuppage\]/', $this->submission_type_phone) > 0)
		{
			$this->setError(JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));

			return false;
		}

		if (preg_match('/\[[a-z]*signuppage\]/', $this->submission_type_webform) > 0)
		{
			$this->setError(JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));

			return false;
		}

		if ($app->isAdmin() && !empty($this->review_message) && !strstr($this->review_message, '[redform]'))
		{
			$this->setError(JText::_('COM_REDEVENT_WARNING_REDFORM_TAG_MUST_BE_INCLUDED_IN_REVIEW_SCREEN_IF_NOT_EMPTY'));

			return false;
		}

		// Prevent people from using {redform}x{/redform} inside the wysiwyg => replace with [redform]
		$this->datdescription = preg_replace('#(\{redform\}.*\{/redform\})#i', '[redform]', $this->datdescription);
		$this->review_message = preg_replace('#(\{redform\}.*\{/redform\})#i', '[redform]', $this->review_message);

		return true;
	}

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		$user = JFactory::getUser();

		// Get the current time in the database format.
		$time = JFactory::getDate()->toSql();

		$this->modified = $time;
		$this->modified_by = $user->get('id');

		if (!$this->id)
		{
			$params = JComponentHelper::getParams('com_redevent');

			// Get IP, time and user id
			$this->created = $time;
			$this->created_by = $user->get('id');
			$this->author_ip = $params->get('storeip', '1') ? getenv('REMOTE_ADDR') : 'DISABLED';
		}

		return parent::beforeStore($updateNulls);
	}

	/**
	 * Called after store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		$this->setCategories($this->categories);

		return parent::afterStore($updateNulls);
	}

	/**
	 * Load by session id
	 *
	 * @param   int  $xref  session id
	 *
	 * @return bool
	 */
	public function loadBySessionId($xref)
	{
		$this->reset();

		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('e.*');
		$query->from('#__redevent_events as e');
		$query->join('INNER', '#__redevent_event_venue_xref as x ON x.eventid = e.id');
		$query->join('LEFT', '#__');
		$query->where('x.id = ' . (int) $xref);

		$db->setQuery($query);

		if ($result = $db->loadAssoc())
		{
			return $this->bind($result);
		}
		else
		{
			$this->setError($db->getErrorMsg());

			return false;
		}
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

		if (isset($src['categories']) && is_array($src['categories']))
		{
			$categories = $src['categories'];
			JArrayHelper::toInteger($categories);
			$this->categories = $categories;
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
		$query->where('object_key = ' . $db->Quote('redevent.event'));

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Sets categories of event
	 *
	 * @param   array  $categoryIds  category ids for the event
	 *
	 * @return bool
	 */
	function setCategories($categoryIds = array())
	{
		if (!$this->id)
		{
			$this->setError('COM_REDEVENT_EVENT_TABLE_NOT_INITIALIZED');

			return false;
		}

		// Update the event category xref
		// First, delete current rows for this event
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->delete('#__redevent_event_category_xref');
		$query->where('event_id = ' . $this->id);

		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		// Insert new ref
		foreach ((array) $categoryIds as $categoryId)
		{
			$query = $db->getQuery(true);

			$query->insert('#__redevent_event_category_xref');
			$query->columns('event_id, category_id');
			$query->values($this->id . ', ' . $categoryId);

			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
		}

		return true;
	}
}
