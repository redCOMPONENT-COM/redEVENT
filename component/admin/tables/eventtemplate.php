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

		// Prevent people from using {redform}x{/redform} inside the wysiwyg => replace with [redform]
		$this->datdescription = preg_replace('#(\{redform\}.*\{/redform\})#i', '[redform]', $this->datdescription);

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
		}

		if (is_array($this->submission_types))
		{
			$this->submission_types = implode(',', $this->submission_types);
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

		if (isset($src['showfields']) && is_array($src['showfields']))
		{
			$this->showfields = implode(',', $src['showfields']);
		}

		return true;
	}
}
