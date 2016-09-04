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
final class PlgAesir_FieldRedevent_EventEntityTwigEvent extends AbstractTwigEntity
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
	 * Return signup form
	 *
	 * @return string
	 */
	public function getSignupform()
	{
		$helper = new RedeventTagsRegistrationEvent($this->entity->id);

		return $helper->getHtml();
	}

	/**
	 * Return signup url
	 *
	 * @return string
	 */
	public function getSignuplink()
	{
		return JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $this->entity->id));
	}

	/**
	 * Return sessions
	 *
	 * @param   int     $published  publish state
	 * @param   string  $ordering   ordering
	 * @param   bool    $featured   filtered featured
	 *
	 * @return array|bool
	 */
	public function getSessions($published = 1, $ordering = 'dates.asc', $featured = false)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__redevent_event_venue_xref')
			->where('eventid = ' . $this->entity->id);

		switch ($ordering)
		{
			case 'dates.desc':
				$query->order('dates DESC, times DESC');
				break;
			case 'dates.asc':
			default:
				$query->order('dates ASC, times ASC');
		}

		if (is_numeric($published))
		{
			$query->where('published = ' . $published);
		}

		if ($featured)
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
