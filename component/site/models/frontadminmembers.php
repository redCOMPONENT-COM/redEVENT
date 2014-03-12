<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class RedeventModelFrontadminMembers extends RedeventModelBaseeventlist
{
	/**
	 * caching for sessions
	 *
	 * @var array
	 */
	protected $members = null;
	protected $data = null;
	protected $pagination = null;
	protected $total = null;

	protected $xref = 0;
	protected $organizationId = 0;

	/**
	 * Constructor
	 *
	 * @param   array  $config  config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		$limit = $app->getUserStateFromRequest('com_redevent.limit', 'limit', $params->def('b2b_members_display_num', 20), 'int');
		$this->setState('limit', $limit);

		// Members list
		$this->setState('members_order',     JRequest::getCmd('members_order', 'u.name'));
		$this->setState('members_order_dir', strtoupper(JRequest::getCmd('members_order_dir', 'ASC')) == 'DESC' ? 'DESC' : 'ASC');

		$members_limitstart = JRequest::getVar('members_limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$members_limitstart = ($limit != 0 ? (floor($members_limitstart / $limit) * $limit) : 0);
		$this->setState('members_limitstart', $members_limitstart);
	}

	/**
	 * return organization members and their booking status for the session
	 *
	 * @param   int     $xref          session id
	 * @param   int     $organization  organization id
	 * @param   string  $filter_user   filter user
	 *
	 * @return array
	 */
	public function getAttendees($xref, $organization, $filter_user)
	{
		$this->xref = (int) $xref;
		$this->organizationId = (int) $organization;
		$this->filter_user = $filter_user;

		// Get organization members
		$this->getMembers();

		// Return with bookings
		$all = $this->getBooked();

		return array_slice($all, $this->getState('members_limitstart'), $this->getState('limit'));
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			jimport('joomla.html.pagination');
			$this->pagination = new REAjaxPagination($this->getTotal(), $this->getState('members_limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}

	public function getTotal()
	{
		return count($this->getMembers());
	}

	/**
	 * Get organnization members
	 *
	 * @return array
	 */
	protected function getMembers()
	{
		// Get organization members
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('u.*');
		$query->from('#__redmember_user_organization_xref AS rmuo');
		$query->join('INNER', '#__redmember_users AS rmu ON rmuo.user_id = rmu.user_id');
		$query->join('INNER', '#__users AS u ON u.id = rmu.user_id');
		$query->where('rmuo.organization_id = ' . $this->organizationId);

		$query->order($this->getState('members_order') . ' ' . $this->getState('members_order_dir') . ', u.name');

		if ($this->filter_user)
		{
			$like = $db->Quote("%{$this->filter_user}%");
			$cond = array();
			$cond[] = 'u.username LIKE ' . $like;
			$cond[] = 'u.name LIKE ' . $like;
			$cond[] = 'u.email LIKE ' . $like;
			$query->where('(' . implode(' OR ', $cond) . ')');
		}

		$db->setQuery($query);
		$this->members = $db->loadObjectList();

		return $this->members;
	}

	/**
	 * Get members with registered/non registered info
	 *
	 * @return array
	 */
	protected function getBooked()
	{
		if (!$this->members)
		{
			return false;
		}

		$memberIds = $this->getMemberIds();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*');
		$query->from('#__redevent_register AS r');
		$query->where('r.xref = ' . $this->xref);
		$query->where('r.uid IN (' . implode(',', $memberIds) . ')');
		$query->where('r.cancelled = 0');

		$db->setQuery($query);
		$regs = $db->loadObjectList('uid');

		$booked = array();
		$notBooked = array();

		foreach ($this->members as $member)
		{
			if (isset($regs[$member->id]))
			{
				$member->registered = $regs[$member->id];
				$booked[] = $member;
			}
			else
			{
				$member->registered = null;
				$notBooked[] = $member;
			}
		}

		$result = array_merge($booked, $notBooked);

		return $result;
	}

	/**
	 * Get member ids
	 *
	 * @return array
	 */
	protected function getMemberIds()
	{
		if (!$this->members)
		{
			return false;
		}

		$ids = array();

		foreach ($this->members as $u)
		{
			$ids[] = $u->id;
		}

		return $ids;
	}
}
