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
class RedeventTagsLibPlacesleftswitch extends \Redevent\Tag\Replacer
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
		return new RedeventTagsTag('placesleftswitch', JText::_('COM_REDEVENT_TAG_DESC_PLACESLEFTSWITCH'), 'registration');
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

		if (!$session->hasMaxAttendees() || !$res = $this->replaceLessThan())
		{
			return $this->getRegular();
		}

		return $res;
	}

	/**
	 * Get default tag if lessthan conditions are not met
	 *
	 * @return string
	 *
	 * @since 3.2.3
	 */
	private function getRegular()
	{
		return '[' . $this->tagsParsed->getParam('regular') . ']';
	}

	/**
	 * Get default tag if one of lessthan conditions is met
	 *
	 * @return string
	 *
	 * @since 3.2.3
	 */
	private function replaceLessThan()
	{
		$lessThanConditions = $this->tagsParsed->getParam('lessthan');
		$placesLeft = $this->parent->getSession()->getNumberLeft();

		if (empty($lessThanConditions))
		{
			return false;
		}

		if (!is_array($lessThanConditions))
		{
			$lessThanConditions = array($lessThanConditions);
		}

		$parsedConditions = array_map(
			function($condition)
			{
				list($limit, $tag) = explode(";", $condition);

				return array('limit' => $limit, 'tag' => $tag);
			},
			$lessThanConditions
		);

		uasort(
			$parsedConditions,
			function ($a, $b)
			{
				return $a['limit'] > $b['limit'] ? 1 : - 1;
			}
		);

		foreach ($parsedConditions as $condition)
		{
			if ($placesLeft <= $condition['limit'])
			{
				return '[' . $condition['tag'] . ']';
			}
		}

		// Shouldn't happen
		return $this->getRegular();
	}
}
