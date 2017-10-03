<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  altdatetag
 *
 * @copyright   Copyright (C) 2008-2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Specific parameters for redEVENT.
 *
 * @since  __deploy_version__
 */
class PlgRedeventAltdatetag extends JPlugin implements \Redevent\Plugin\TagReplace
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __deploy_version__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Callback to add supported tags
	 *
	 * @param   array  $tags  supported tags
	 *
	 * @return mixed
	 *
	 * @since  __deploy_version__
	 */
	public function onRedeventGetAvailableTags(&$tags)
	{
		$tags[] = new RedeventTagsTag(
			'alt_date', JText::_('PLG_REDEVENT_ALTDATETAG_TAG_ALT_DATE')
		);
	}

	/**
	 * Callback for tags replacement
	 *
	 * @param   RedeventTags  $replacer  replacer
	 * @param   string        $text      text to replace
	 * @param   boolean       $recurse   set to true if replacements should be run again (e.g some tags were found and expanded)
	 *
	 * @return mixed
	 *
	 * @since  __deploy_version__
	 */
	public function onRedeventTagsReplace(RedeventTags $replacer, &$text, &$recurse)
	{
		$session = $replacer->getSession();

		if (!$session)
		{
			return true;
		}

		$tags = $replacer->extractTags($text);

		$search = array();
		$replace = array();

		$format = $this->params->get('format', 'd M, Y');

		foreach ($tags as $tag)
		{
			if ($tag->getName() == 'alt_date')
			{
				$search[] = $tag->getFullMatch();

				if ($session->isOpenDate() && RedeventHelperDate::isValidDate($session->custom3))
				{
					$date = JFactory::getDate($session->custom3);
					$string = $date->format($format);
				}
				else
				{
					$string = $session->getFormattedStartDate($format);
				}

				$replace[] = $string;
			}
		}

		$text = str_replace($search, $replace, $text);

		return true;
	}
}
