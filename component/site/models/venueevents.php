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

require_once 'baseeventslist.php';

/**
 * Redevent Model Venue events
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
 */
class RedeventModelVenueevents extends RedeventModelBaseEventList
{
	/**
	 * venue id
	 *
	 * @var int
	 */
	protected $_id = 0;

	/**
	 * venue data array
	 *
	 * @var array
	 */
	protected $_venue = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = &JFactory::getApplication();

		$id = JRequest::getInt('id');
		$this->setId((int) $id);
	}

	/**
	 * Method to set the venue id
	 *
	 * @param   int  $id  venue ID number
	 *
	 * @return void
	 *
	 * @access	public
	 */
	public function setId($id)
	{
		// Set new venue ID and wipe data
		$this->_id			= $id;
		$this->_data		= null;
	}

	/**
	 * (non-PHPdoc)
	 * @see RedeventModelBaseEventList::_buildWhere()
	 */
	protected function _buildWhere($query)
	{
		$query = parent::_buildWhere($query);

		/* Check if a venue ID is set */
		if ($this->_id > 0)
		{
			$query->where('x.venueid = ' . $this->_id);
		}

		return $query;
	}

	/**
	 * Method to get the Venue
	 *
	 * @access public
	 * @return array
	 */
	public function getVenue()
	{
		$user		= JFactory::getUser();
		$gids = $user->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*, id AS venueid');
		$query->select('CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug');
		$query->from('#__redevent_venues');
		$query->where('id = ' . $this->_id);
		$query->where('access IN (' . $gids . ')');

		$db->setQuery($query);
		$_venue = $db->loadObject();

		if (!$this->_category)
		{
			JError::raiseError(403, JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'));
		}

		$_venue->attachments = REAttach::getAttachments('venue' . $_venue->id, $user->getAuthorisedViewLevels());

		return $_venue;
	}
}
