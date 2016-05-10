<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Aesir\Entity\Twig\AbstractTwigEntity;
use Aesir\Entity\Twig\Traits;

defined('_JEXEC') or die;

/**
 * redEVENT event Twig Entity.
 *
 * @since  3.3.10
 */
final class PlgAesir_FieldRedevent_eventEntityTwigEvent extends AbstractTwigEntity
{
	/**
	 * Constructor.
	 *
	 * @param   \RedeventEntityEvent  $entity  The entity
	 */
	public function __construct(\RedeventEntityEvent $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * is utilized for reading data from inaccessible members.
	 *
	 * @param   string  $name  string
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		if (isset($this->entity->$name))
		{
			return $this->entity->$name;
		}

		throw new RuntimeException('unsupported property in __get: ' . $name);
	}

	/**
	 * is triggered by calling isset() or empty() on inaccessible members.
	 *
	 * @param   string  $name  string
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->entity->$name);
	}

	/**
	 * Return sessions
	 *
	 * @param   bool  $published  publish state
	 *
	 * @return array|bool
	 */
	public function getSessions($published = 1, $featured = 0)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__redevent_event_venue_xref')
			->where('eventid = ' . $this->entity->id)
			->order('dates DESC, times DESC');

		if ($published == 1)
		{
			$query->where('published = 1');
		}

		if ($featured == 1)
		{
			$query->where('featured = 1');
		}

		$db->setQuery($query);

		if (!$res = $db->loadObjectList())
		{
			return false;
		}

		return array_map(
			function($row)
			{
				$instance = RedeventEntitySession::getInstance();
				$instance->bind($row);

				return new PlgAesir_FieldRedevent_eventEntityTwigSession($instance);
			},
			$res
		);
	}
}
