<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Handles redform tag
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventTagsLibPriceswitch extends \Redevent\Tag\Replacer
{
	/**
	 * Get supported tag description
	 *
	 * @return  RedeventTagsTag
	 *
	 * @since 3.2.3
	 */
	public function getDescription()
	{
		return new RedeventTagsTag('priceswitch', JText::_('COM_REDEVENT_TAG_DESC_PRICESWITCH'), 'registration');
	}

	/**
	 * Get replacement
	 *
	 * @return string
	 *
	 * @since 3.2.3
	 */
	public function getReplace()
	{
		$session = $this->parent->getSession();

		if (!$session->isValid())
		{
			return false;
		}

		if ($this->isFree())
		{
			return $this->tagsParsed->getParam('free') ? '[' . $this->tagsParsed->getParam('free') . ']' : '';
		}
		else
		{
			return $this->tagsParsed->getParam('paid') ? '[' . $this->tagsParsed->getParam('paid') . ']' : '';
		}
	}

	/**
	 * check if session is free
	 *
	 * @return boolean
	 *
	 * @since  __deploy_version__
	 */
	private function isFree()
	{
		$session = $this->parent->getSession();

		$pricegroups = $session->getActivePricegroups(true);

		if (empty($pricegroups))
		{
			return true;
		}

		foreach ($pricegroups as $pricegroup)
		{
			if ($pricegroup->price)
			{
				return false;
			}
		}

		return true;
	}
}
