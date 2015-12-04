<?php

class Tablerow
{
	private $sessions;

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

			case 'budget':
				return 0.35 * $this->getPrice();
		}
	}

	public function add($row)
	{
		$this->sessions[] = $row;
	}

	private function getPrice()
	{
		$first = $this->sessions[0];

		// One and only one price for all sessions of same event
		return !empty($first->prices) ? $first->prices[0]->price : 0;
	}

	private function getLink()
	{
		$first = $this->sessions[0];

		$target = $first->custom13;

		if (strstr($target, 'http') !== false)
		{
			return $target;
		}

		if (strpos($target, '/') !== 0)
		{
			$target = "/" . $target;
		}

		return 'https://kurser.ibc.dk' . $target;
	}
}
