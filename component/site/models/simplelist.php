<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Redevents Component events list Model
 *
 * @package Joomla
 * @subpackage Redevent
 * @since		0.9
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
			->select($db->qn('id'))
			->from($db->qn('#__redevent_events'))
			->where($db->qn('published') . ' = 1');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result)
		{
			return false;
		}

		$currentDate = JFactory::getDate();
		$startDate   = null;
		$dateValue   = null;

		foreach ($result as $event)
		{
			$query->clear()
				->select('DISTINCT (' . $db->qn('dates') . ')')
				->from($db->qn('#__redevent_event_venue_xref'))
				->where($db->qn('eventid') . ' = ' . $event->id)
				->order($db->qn('dates') . ' DESC');
			$db->setQuery($query, 0, 1);
			$result = $db->loadObject();

			$tmpDate = new JDate($result->dates);

			if (!$startDate)
			{
				$startDate = $tmpDate;
			}
			elseif ($startDate > $tmpDate)
			{
				$startDate = $tmpDate;
			}
		}

		if ($currentDate > $startDate)
		{
			$dateValue = $currentDate->format('Y-m-d');
		}
		else
		{
			$dateValue = $startDate->format('Y-m-d');
		}

		$this->setState($dateValue);

		return true;
	}
}
