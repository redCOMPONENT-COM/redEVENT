<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redevent Component venue category events Model
 *
 * @package     Joomla
 * @subpackage  redevent
 * @since       2.0
 */
class RedeventModelVenuecategory extends RedeventModelBasesessionlist
{
	/**
	 * category id
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * category data array
	 *
	 * @var array
	 */
	protected $category = null;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	public function __construct()
	{
		parent::__construct();

		$id = JFactory::getApplication()->input->getInt('id');
		$this->setId((int) $id);
	}

	/**
	 * Method to set the category id
	 *
	 * @param   int  $id  category ID number
	 *
	 * @return void
	 *
	 * @access	public
	 */
	public function setId($id)
	{
		// Set new category ID and wipe data
		$this->id			= $id;
		$this->data		= null;
	}

	/**
	 * Build the where clause
	 *
	 * @param   object  $query  query
	 *
	 * @return object
	 */
	protected function _buildWhere($query)
	{
		$query = parent::_buildWhere($query);
		$category = $this->getItem();

		$query->where('vc.lft BETWEEN ' . $this->_db->Quote($category->lft) . ' AND ' . $this->_db->Quote($category->rgt));

		return $query;
	}

	/**
	 * Method to get the Category
	 *
	 * @return integer
	 */
	public function getItem()
	{
		if (!$this->category)
		{
			$user		= JFactory::getUser();
			$gids = $user->getAuthorisedViewLevels();
			$gids = implode(',', $gids);

			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*');
			$query->select('CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug');
			$query->from('#__redevent_venues_categories');
			$query->where('id = ' . $this->id);
			$query->where('access IN (' . $gids . ')');

			$db->setQuery($query);
			$this->category = $db->loadObject();

			if (!$this->category)
			{
				JError::raiseError(403, JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'));
			}
		}

		return $this->category;
	}
}
