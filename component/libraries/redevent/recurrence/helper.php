<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper for handling recurrence
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventRecurrenceHelper
{
	private $params;

	/**
	 * Constructor
	 *
	 * @param   array  $config  optional config
	 */
	public function __construct($config = null)
	{
		if (is_array($config) && isset($config['params']))
		{
			$this->params = $config['params'];
		}
		else
		{
			$this->params = JComponentHelper::getParams('com_redevent');
		}
	}

	/**
	 * Parse an ical recurrence rule, and return a RedeventRecurrenceRule object
	 *
	 * @param   string  $icalRule  rule string
	 *
	 * @return RedeventRecurrenceRule
	 */
	public function getRule($icalRule = null)
	{
		$rule = new RedeventRecurrenceRule;

		if (!$icalRule)
		{
			return $rule;
		}

		$parts = explode(';', $icalRule);

		foreach ($parts as $p)
		{
			if (!strpos($p, '='))
			{
				continue;
			}

			list($element, $value) = explode('=', $p);

			switch ($element)
			{
				case 'RRULE:FREQ':
					$rule->type = $value;
					break;

				case 'INTERVAL':
					$rule->interval = $value;
					break;

				case 'COUNT':
					$rule->repeat_type = 'count';
					$rule->repeat_until_count = $value;
					break;

				case 'UNTIL':
					$rule->repeat_type = 'until';
					$rule->repeat_until_date = $this->icalDatetotime($value);
					break;

				case 'BYDAY':
					$days = explode(',', $value);

					foreach ($days as $d)
					{
						preg_match('/([-]*)([0-9]*)([A-Z]*)/', $d, $res);
						$revert = ($res[1] == '-');

						if ($res[2] && $res[3])
						{
							// Has number and day
							if ($rule->type == 'MONTHLY')
							{
								$rule->monthtype = 'byday';
							}

							if ($revert)
							{
								if (!in_array($res[2], $rule->rweeks))
								{
									$rule->rweeks[] = $res[2];
								}

								if (!in_array($res[3], $rule->rweekdays))
								{
									$rule->rweekdays[] = $res[3];
								}
							}
							else
							{
								if (!in_array($res[2], $rule->weeks))
								{
									$rule->weeks[] = $res[2];
								}

								if (!in_array($res[3], $rule->weekdays))
								{
									$rule->weekdays[] = $res[3];
								}
							}
						}
						elseif ($res[2])
						{
							// Only number
							$rule->bydays[] = $res[2];

							if ($rule->type == 'MONTHLY')
							{
								$rule->monthtype = 'bymonthdays';
							}

							if ($revert)
							{
								$rule->reverse_bydays = 1;
							}
						}
						elseif ($res[3])
						{
							// Only day
							if ($rule->type == 'MONTHLY')
							{
								$rule->monthtype = 'byday';
							}

							if ($revert)
							{
								if (!in_array($res[3], $rule->rweekdays))
								{
									$rule->rweekdays[] = $res[3];
								}
							}
							else
							{
								if (!in_array($res[3], $rule->weekdays))
								{
									$rule->weekdays[] = $res[3];
								}
							}
						}
					}
					break;
				default:
					break;
			}
		}

		return $rule;
	}

	/**
	 * adds xref repeats to the database.
	 *
	 * @param   int  $recurrence_id  recurrence id
	 *
	 * @return boolean true on success
	 *
	 * @TODO: refactor !
	 */
	public function generaterecurrences($recurrence_id = null)
	{
		$db = JFactory::getDBO();

		// Generate until limit
		$params = $this->params;
		$limit = $params->get('recurrence_limit', 30);
		$limit_date_int = time() + $limit * 3600 * 24;

		$query = $db->getQuery(true);

		$query->select('MAX(rp.xref_id) as xref_id, r.rrule, r.id as recurrence_id')
			->from('#__redevent_repeats AS rp')
			->join('INNER', '#__redevent_recurrences AS r on r.id = rp.recurrence_id')

			// Make sure there are still events associated...
			->join('INNER', '#__redevent_event_venue_xref AS x on x.id = rp.xref_id')

			->where('r.ended = 0')
			->where('x.dates IS NOT NULL');

		if ($recurrence_id)
		{
			$query->where('r.id = ' . $db->Quote($recurrence_id));
		}

		$query->group('rp.recurrence_id');

		$db->setQuery($query);
		$recurrences = $db->loadObjectList();

		if (empty($recurrences))
		{
			return true;
		}

		// Get corresponding xrefs
		$rids = array();

		foreach ($recurrences as $r)
		{
			$rids[] = $r->xref_id;
		}

		$query = $db->getQuery(true);
		$query->select('x.*, rp.count')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_repeats AS rp ON rp.xref_id = x.id')
			->where('x.id IN (' . implode(",", $rids) . ')');

		$db->setQuery($query);
		$xrefs = $db->loadObjectList('id');

		$recurrenceHelper = new RedeventRecurrenceHelper;
		$rule = $recurrenceHelper->getRule($r->rrule);
		$nextHelper = new RedeventRecurrenceNext($rule);

		// Now, do the job...
		foreach ($recurrences as $r)
		{
			$next = $nextHelper->getNext($xrefs[$r->xref_id]);

			while ($next)
			{
				if (strtotime($next->dates) > $limit_date_int)
				{
					break;
				}

				// Record xref
				$object = RTable::getAdminInstance('Session');
				$object->bind(get_object_vars($next));

				if ($object->store())
				{
					// Copy the roles
					$query = ' INSERT INTO #__redevent_sessions_roles (xref, role_id, user_id) '
						. ' SELECT ' . $object->id . ', role_id, user_id '
						. ' FROM #__redevent_sessions_roles '
						. ' WHERE xref = ' . $db->Quote($r->xref_id);
					$db->setQuery($query);

					if (!$db->execute())
					{
						RedeventHelperLog::simpleLog('recurrence copying roles error: ' . $db->getErrorMsg());
					}

					// Copy the prices
					$query = ' INSERT INTO #__redevent_sessions_pricegroups (xref, pricegroup_id, price, currency) '
						. ' SELECT ' . $object->id . ', pricegroup_id, price, currency '
						. ' FROM #__redevent_sessions_pricegroups '
						. ' WHERE xref = ' . $db->Quote($r->xref_id);
					$db->setQuery($query);

					if (!$db->execute())
					{
						RedeventHelperLog::simpleLog('recurrence copying prices error: ' . $db->getErrorMsg());
					}

					// Update repeats table
					$query = ' INSERT INTO #__redevent_repeats '
						. ' SET xref_id = ' . $db->Quote($object->id)
						. '   , recurrence_id = ' . $db->Quote($r->recurrence_id)
						. '   , count = ' . $db->Quote($next->count);
					$db->setQuery($query);

					if (!$db->execute())
					{
						RedeventHelperLog::simpleLog('saving repeat error: ' . $db->getErrorMsg());
					}
				}
				else
				{
					RedeventHelperLog::simpleLog('saving recurrence xref error: ' . $db->getErrorMsg());
				}

				$next = $nextHelper->getNext($next);
			}

			if (!$next)
			{
				// No more events to generate, we can disable the rule
				$query = ' UPDATE #__redevent_recurrences SET ended = 1 WHERE id = ' . $db->Quote($r->recurrence_id);
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Convert ical date to time
	 *
	 * @param   string  $date  date in ical format
	 *
	 * @return boolean|string
	 */
	private function icalDatetotime($date)
	{
		if (preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2})([0-9]{2})([0-9]{2})(Z?)/', $date, $res))
		{
			$res = mktime($res[4], $res[5], $res[6], $res[2], $res[3], $res[1]);

			return strftime('%Y-%m-%d %H:%M:%S', $res);
		}
		else
		{
			return false;
		}
	}
}
