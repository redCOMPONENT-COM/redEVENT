<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskgatewayfiltertag
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
 * @subpackage  Redevent.Maerskgatewayfiltertag
 * @since       3.2.3
 */
class plgRedeventMaerskgatewayfiltertag extends JPlugin
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

		if (preg_match_all('/\[filter_hasgateway(?:\s*)([^\]]*)\]/i', $text, $alltags, PREG_SET_ORDER))
		{
			$recurse = true;

			foreach ($alltags as $tagPreg)
			{
				$tag_obj = new RedeventTagsParsed($tagPreg[0]);
				$this->replaceTag($tag_obj, $text);
			}
		}

		return true;
	}

	/**
	 * Do the replacement
	 *
	 * @param   RedeventTagsParsed  $tag   tag replacer
	 * @param   string              $text  text to replace
	 *
	 * @since version
	 */
	private function replaceTag(RedeventTagsParsed $tag, &$text)
	{
		$gateways = explode(",", $tag->getParam('id'));
		$gateways = array_map('trim', $gateways);

		$venue = $this->replacer->getSession()->getVenue();

		$params = new JRegistry($venue->params);
		$venueGateways = $params->get('allowed_gateways');

		$common = array_intersect($gateways, $venueGateways);

		if (!empty($common))
		{
			$replace = $tag->getParam('tag');
		}

		if (empty($common))
		{
			$replace = $tag->getParam('alt');
		}

		$text = str_replace($tag->getFullMatch(), $replace ? '[' . $replace . ']' : '', $text);
	}
}
