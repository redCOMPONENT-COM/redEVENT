<?php
/**
 * @version     1.0 $Id$
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Redevents Component events list Model
 *
 * @package     Redevent
 * @subpackage  Redevent
 * @since       0.9
 */
class RedeventModelSimpleList extends RedeventModelBaseeventlist
{
	/**
	 * Method for get latest start date of published event
	 *
	 * @return  boolean   True on success. False otherwise.
	 */
	public function timelinePrepareData()
	{
		$db = JFactory::getDbo();

		// Get all "Publish" events
		$query = $db->getQuery(true)
			->select($db->qn('v.dates'))
			->from($db->qn('#__redevent_event_venue_xref', 'v'))
			->leftJoin($db->qn('#__redevent_events', 'e') . ' ON ' . $db->qn('e.id') . ' = ' . $db->qn('v.eventid'))
			->where($db->qn('e.published') . ' = 1')
			->order($db->qn('v.dates') . ' ASC');
		$db->setQuery($query, 0, 1);
		$result = $db->loadObject();

		if (!$result)
		{
			return false;
		}

		$currentDate = JFactory::getDate();
		$startDate   = new JDate($result->dates);
		$dateValue   = null;

		if ($currentDate > $startDate)
		{
			$dateValue = $currentDate->format('Y-m-d');
		}
		else
		{
			$dateValue = $startDate->format('Y-m-d');
		}

		$this->setState('filter_date', $dateValue);

		return true;
	}
}
