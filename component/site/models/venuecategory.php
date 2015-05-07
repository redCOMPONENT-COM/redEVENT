<?php
/**
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
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

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redevent Component venue category events Model
 *
 * @package     Joomla
 * @subpackage  redevent
 * @since       2.0
 */
class RedeventModelVenuecategory extends RedeventModelBaseeventlist
{
	/**
	 * category id
	 *
	 * @var int
	 */
	protected $_id = null;

	/**
	 * category data array
	 *
	 * @var array
	 */
	protected $_category = null;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	public function __construct()
	{
		parent::__construct();

		$id = JRequest::getInt('id');
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
		$this->_id			= $id;
		$this->data		= null;
	}

	/**
	 * (non-PHPdoc)
	 * @see RedeventModelBaseeventlist::_buildWhere()
	 */
	protected function _buildWhere($query)
	{
		$query = parent::_buildWhere($query);
		$category = $this->getItem();

		$user		= JFactory::getUser();
		$gid		= max($user->getAuthorisedViewLevels());

		$query->where('vc.lft BETWEEN ' . $this->_db->Quote($category->lft) . ' AND ' . $this->_db->Quote($category->rgt));

		return $query;
	}

	/**
	 * Method to get the Category
	 *
	 * @access public
	 * @return integer
	 */
	public function getItem()
	{
		if (!$this->_category)
		{
			$user		= JFactory::getUser();
			$gids = $user->getAuthorisedViewLevels();
			$gids = implode(',', $gids);

			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*');
			$query->select('CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug');
			$query->from('#__redevent_venues_categories');
			$query->where('id = ' . $this->_id);
			$query->where('access IN (' . $gids . ')');

			$db->setQuery($query);
			$this->_category = $db->loadObject();

			if (!$this->_category)
			{
				JError::raiseError(403, JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'));
			}
		}

		return $this->_category;
	}
}
