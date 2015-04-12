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
class RedeventModelTimeline extends RedeventModelBaseeventlist
{
	/**
	 * Method for get latest start date of published event
	 *
	 * @return  boolean   True on success. False otherwise.
	 */
	public function timelinePrepareData()
	{
		$db = JFactory::getDbo();

		// Get all "Published" events
		$query = $db->getQuery(true)
			->select($db->qn('v.dates'))
			->from($db->qn('#__redevent_event_venue_xref', 'v'))
			->leftJoin($db->qn('#__redevent_events', 'e') . ' ON ' . $db->qn('e.id') . ' = ' . $db->qn('v.eventid'))
			->where($db->qn('v.published') . ' = 1')
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

	/**
	 * Method for get session price
	 *
	 * @param   int  $sessionId  ID of session
	 *
	 * @return  object           Price object
	 */
	public function getSessionPrice($sessionId)
	{
		$sessionId = (int) $sessionId;

		if (!$sessionId)
		{
			return false;
		}

		$db = $this->_db;

		$query = $db->getQuery(true)
			->select('sp.*')
			->select($db->qn(array('p.name', 'p.alias', 'p.image', 'p.tooltip')))
			->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', sp.id, p.alias) ELSE sp.id END as slug')
			->select('CASE WHEN CHAR_LENGTH(sp.currency) THEN sp.currency ELSE f.currency END as currency')
			->from($db->qn('#__redevent_sessions_pricegroups', 'sp'))
			->innerJoin($db->qn('#__redevent_pricegroups', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('sp.pricegroup_id'))
			->innerJoin($db->qn('#__redevent_event_venue_xref', 'x') . ' ON ' . $db->qn('x.id') . ' = ' . $db->qn('sp.xref'))
			->innerJoin($db->qn('#__redevent_events', 'e') . ' ON ' . $db->qn('e.id') . ' = ' . $db->qn('x.eventid'))
			->leftJoin($db->qn('#__rwf_forms', 'f') . ' ON ' . $db->qn('e.redform_id') . ' = ' . $db->qn('f.id'))
			->where($db->qn('sp.xref') . ' = ' . $db->quote($sessionId))
			->order($db->qn('p.ordering') . ' ASC');

		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
