<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Ibc.Sessionsdump
 *
 * @copyright   Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Class Tablerow
 *
 * @since  3.0
 */
class Tablerow
{
	private $sessions;

	/**
	 * Tablerow constructor.
	 */
	public function __construct()
	{
		$this->sessions = array();
	}

	/**
	 * is utilized for reading data from inaccessible members.
	 *
	 * @param   string  $name  name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		$first = $this->sessions[0];

		switch ($name)
		{
			case 'title':
				return $first->title;

			case 'categories':
				$cats = $first->categories;
				sort($cats);

				return $cats;

			case 'dates':
				return array_map(
					function ($item) {
						return $item->dates;
					},
					$this->sessions
				);

			case 'times':
				return array_map(
					function ($item) {
						return $item->times;
					},
					$this->sessions
				);

			case 'venues':
				$venues = array_map(
					function ($item) {
						return $item->venue;
					},
					$this->sessions
				);
				$venues = array_unique($venues);
				sort($venues);

				return $venues;

			case 'attendees':
				return array_map(
					function ($item) {
						return $item->registered;
					},
					$this->sessions
				);

			case 'niveau':
				return explode("\n", $first->custom5);

			case 'active':
				if ($first->event_state != 1)
				{
					return false;
				}

				foreach ($this->sessions as $s)
				{
					if ($s->session_state == 1)
					{
						return true;
					}
				}

				return false;

			case 'link':
				return $this->getLink();


			case 'varighed':
				return explode("\n", $first->custom8);

			case 'price':
				return $this->getPrice();

			case 'amuprice':
				return $this->getPrice('Amu Pris');

			case 'standardprice':
				return $this->getPrice('Standard Pris');

			case 'budget':
				return $this->getBudget();
		}
	}

	/**
	 * Add row
	 *
	 * @param   object  $row  row
	 *
	 * @return void
	 */
	public function add($row)
	{
		$this->sessions[] = $row;
	}

	/**
	 * Get adwords budget
	 *
	 * @return int
	 */
	private function getBudget()
	{
		$price = $this->getPrice();

		if (in_array('AMU', $this->niveau))
		{
			return $price * 0.2;
		}

		return $price * 0.35;
	}

	/**
	 * Get price of most recent session
	 *
	 * @param   string  $name  name of pris group
	 *
	 * @return float
	 */
	private function getPrice($name = null)
	{
		$sessions = array_reverse($this->sessions);

		foreach ($sessions as $session)
		{
			if (!$session_prices = $session->prices)
			{
				return 0;
			}

			if ($name)
			{
				$session_prices = array_filter(
					$session_prices,
					function($element) use ($name)
					{
						return strcasecmp($element->name, $name) == 0;
					}
				);
			}

			if (!$session_prices)
			{
				return 0;
			}

			$prices = JArrayHelper::getColumn($session_prices, 'price');

			return max($prices);
		}

		return 0;
	}

	/**
	 * Get link to item
	 *
	 * @return string
	 */
	private function getLink()
	{
		$reditem_id = 0;

		foreach ($this->sessions as $session)
		{
			if ($session->reditem_id)
			{
				$reditem_id = $session->reditem_id;
				break;
			}
		}

		return JRoute::_(ReditemHelperRoute::getItemRoute($reditem_id));
	}
}
