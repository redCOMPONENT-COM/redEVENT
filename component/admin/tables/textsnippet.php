<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Textsnippet Table class
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventTableTextsnippet extends RedeventTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableName = 'redevent_textlibrary';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 * @since 0.9.1
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
		if (!$this->text_name)
		{
			$this->setError(JText::_( 'COM_REDEVENT_NAME_IS_REQUIRED'));

			return false;
		}

		if ($this->checkTagExists())
		{
			$this->setError(JText::_('COM_REDEVENT_ERROR_TAG_ALREADY_EXISTS'));

			return false;
		}

		return parent::check();
	}

	/**
	 * checks whether the tag already exists
	 *
	 * @return mixed boolean false if doesn't exists, tag object if it does
	 */
	private function checkTagExists()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('text_name');
		$query->from('#__redevent_textlibrary');
		$query->where('text_name = ' . $db->quote($this->text_name));

		if ($this->id)
		{
			$query->where('id <> ' . $this->id);
		}

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res ? true : false;
	}
}
