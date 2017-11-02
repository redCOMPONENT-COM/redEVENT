<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redevent\Plugin;

defined('_JEXEC') or die;

/**
 * Interface for tag replacement plugin
 *
 * @since  3.2.4
 */
interface TagReplace
{
	/**
	 * Callback to add supported tags
	 *
	 * @param   array  $tags  supported tags
	 *
	 * @return mixed
	 *
	 * @since  3.2.4
	 */
	public function onRedeventGetAvailableTags(&$tags);

	/**
	 * Callback for tags replacement
	 *
	 * @param   \RedeventTags  $replacer  replacer
	 * @param   string         $text      text to replace
	 * @param   boolean        $recurse   set to true if replacements should be run again (e.g some tags were found and expanded)
	 *
	 * @return mixed
	 *
	 * @since  3.2.4
	 */
	public function onRedeventTagsReplace(\RedeventTags $replacer, &$text, &$recurse);
}
