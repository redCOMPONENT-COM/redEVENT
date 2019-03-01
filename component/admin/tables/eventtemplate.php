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
class RedeventTableEventtemplate extends RedeventTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 */
	protected $_tableName = 'redevent_event_template';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 */
	protected $_tableKey = 'id';

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

		if ($app->isAdmin() && !empty($this->review_message))
		{
			$tagHelper = new RedeventTags;
			$fullReviewMessage = $tagHelper->replaceLibraryTags($this->review_message);

			if (!strstr($fullReviewMessage, '[redform]'))
			{
				$this->setError(JText::_('COM_REDEVENT_WARNING_REDFORM_TAG_MUST_BE_INCLUDED_IN_REVIEW_SCREEN_IF_NOT_EMPTY'));

				return false;
			}
		}

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
		if (is_array($this->submission_types))
		{
			$this->submission_types = implode(',', $this->submission_types);
		}

		if (is_array($this->showfields))
		{
			$this->showfields = implode(',', $this->showfields);
		}

		return parent::beforeStore($updateNulls);
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

		return true;
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		$ids = $pk;

		if ($ids && !is_array($ids))
		{
			$ids = array($ids);
		}

		if (count($ids))
		{
			$cids = implode(',', $ids);

			$query = $this->_db->getQuery(true);

			$query->select('e.id')
				->from('#__redevent_events AS e')
				->where('e.template_id IN (' . $cids . ')');

			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();

			if ($res || count($res))
			{
				$this->setError(Jtext::_('COM_REDEVENT_ERROR_TEMPLATE_REMOVE_HAS_EVENTS'));

				return false;
			}
		}

		return parent::delete($pk);
	}
}
