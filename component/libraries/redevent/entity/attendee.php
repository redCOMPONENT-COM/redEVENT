<?php
/**
 * @package     Redevent.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Attendee entity.
 *
 * @since  1.0
 */
class RedeventEntityAttendee extends RedeventEntityBase
{
	/**
	 * @var RedeventTags
	 */
	private $replacer;

	/**
	 * @var RedeventEntitySession
	 */
	private $session;

	/**
	 * @var JUser
	 */
	private $user;

	/**
	 * Generate unique id from registration data
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function getRegistrationUniqueId()
	{
		$item = $this->getItem();

		return $this->getSession()->getEvent()->course_code . '-' . $item->xref . '-' . $item->id;
	}

	/**
	 * Return creator
	 *
	 * @return RedeventEntitySession
	 */
	public function getSession()
	{
		if (!$this->session)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->session = RedeventEntitySession::load($item->xref);
			}
		}

		return $this->session;
	}

	/**
	 * Return joomla user
	 *
	 * @return JUser
	 */
	public function getUser()
	{
		if (!$this->user)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->user = JFactory::getUser($item->uid);
			}
		}

		return $this->user;
	}

	/**
	 * Return array of RdfEntitySubmitter
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return array
	 */
	public static function loadBySubmitKey($submit_key)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('r.*')
			->from('#__redevent_register AS r')
			->where('r.submit_key = ' . $db->q($submit_key));

		$db->setQuery($query);
		$res = $db->loadObjectList();

		if (!$res)
		{
			return false;
		}

		$attendees = array_map(
			function($item)
			{
				$instance = self::getInstance($item->id);
				$instance->bind($item);

				return $instance;
			},
			$res
		);

		return $attendees;
	}

	/**
	 * Replace tags in text
	 *
	 * @param   string  $text  text
	 *
	 * @return string
	 */
	public function replaceTags($text)
	{
		return $this->getReplacer()->replaceTags($text);
	}

	/**
	 * Get the associated table
	 *
	 * @param   string  $name  Main name of the Table. Example: Article for ContentTableArticle
	 *
	 * @return  RTable
	 */
	protected function getTable($name = null)
	{
		if (null === $name)
		{
			$name = 'register';
		}

		return RTable::getAdminInstance($name, array(), $this->getComponent());
	}

	/**
	 * Get replacer
	 *
	 * @return mixed|RedeventTags
	 */
	private function getReplacer()
	{
		if (!$this->replacer)
		{
			$item = $this->getItem();

			$tags = new RedeventTags;
			$tags->setXref($item->xref);
			$tags->addOptions(array('sids' => array($item->sid)));
			$tags->setSubmitkey($item->submit_key);

			$this->replacer = $tags;
		}

		return $this->replacer;
	}
}
