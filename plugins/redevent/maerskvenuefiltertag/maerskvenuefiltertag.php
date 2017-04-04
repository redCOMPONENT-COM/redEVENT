<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskvenuefiltertag
 *
 * @copyright   Copyright (C) 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

jimport('redevent.bootstrap');

/**
 * Venue filter tag for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskvenuefiltertag
 * @since       3.2.3
 */
class plgRedeventMaerskvenuefiltertag extends JPlugin
{
	/**
	 * @var RedeventTags
	 * @since 3.2.3
	 */
	private $replacer;

	/**
	 * Replace text
	 *
	 * @param   RedeventTags  $replacer  tag replacer
	 * @param   string        $text      text to replace
	 * @param   boolean       $recurse   should we run replacement again (if something was replaced...)
	 *
	 * @return boolean
	 *
	 * @since 3.2.3
	 */
	public function onRedeventTagsReplace(RedeventTags $replacer, &$text, &$recurse)
	{
		$this->replacer = $replacer;

		if (preg_match_all('/\[filter_venue(?:\s*)([^\]]*)\]/i', $text, $alltags, PREG_SET_ORDER))
		{

			foreach ($alltags as $tagPreg)
			{
				$tag_obj = new RedeventTagsParsed($tagPreg[0]);
				$this->replaceTag($tag_obj, $text);
			}
		}

		return true;
	}

	private function replaceTag(RedeventTagsParsed $tag, &$text)
	{
		$venues = explode(",", $tag->getParam('id'));
		JArrayHelper::toInteger($venues);

		$session = $this->replacer->getSession();

		if (in_array($session->venue_id, $venues) && $replace = $tag->getParam('tag'))
		{
			$text = str_replace($tag->getFullMatch(), '[' . $replace . ']', $text);
		}

		if (!in_array($session->venue_id, $venues) && $replace = $tag->getParam('alt'))
		{
			$text = str_replace($tag->getFullMatch(), '[' . $replace . ']', $text);
		}
	}
}
