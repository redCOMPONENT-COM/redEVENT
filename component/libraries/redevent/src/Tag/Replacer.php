<?php
/**
 * @package     RedEVENT.Library
 * @copyright   Copyright (C) 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redevent\Tag;

\JLoader::import('reditem.library');

defined('_JEXEC') or die;

/**
 * Bundle Twig extension.
 *
 * @since  3.2.0
 */
abstract class Replacer
{
	protected $parent;

	protected $tagsParsed;

	/**
	 * Constructor
	 *
	 * @param   \RedeventTags        $parent      object calling this class	 *
	 * @param   \RedeventTagsParsed  $tagsParsed  parsed tag data
	 */
	public function __construct(\RedeventTags $parent, \RedeventTagsParsed $tagsParsed)
	{
		$this->parent = $parent;
		$this->tagsParsed = $tagsParsed;
	}

	/**
	 * Get supported tag description
	 *
	 * @return  \RedeventTagsTag
	 *
	 * @since 3.2.3
	 */
	public static abstract function getDescription();

	/**
	 * Get replacement
	 *
	 * @return string
	 *
	 * @since 3.2.3
	 */
	public abstract function getReplace();
}
