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
 * Event entity.
 *
 * @since  1.0
 */
class RedeventEntityEvent extends RedeventEntityBase
{
	/**
	 * @var JUser
	 */
	private $creator;

	/**
	 * Return creator
	 *
	 * @return JUser
	 */
	public function getCreator()
	{
		if (!$this->creator)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->creator = JFactory::getUser($item->created_by);
			}
		}

		return $this->creator;
	}

	/**
	 * Return associated redform form
	 *
	 * @return RdfEntityForm
	 */
	public function getForm()
	{
		$item = $this->getItem();

		if (!empty($item))
		{
			return RdfEntityForm::load($item->redform_id);
		}

		return false;
	}
}
