<?php
/**
 * @package    RedEVENT.Library
 * @copyright  Copyright (C) 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redevent\Tag;

\JLoader::import('reditem.library');

defined('_JEXEC') or die;

/**
 * Replacer tag base class
 *
 * @since  3.2.3
 */
abstract class Replacer
{
	/**
	 * @var    \RedeventTags
	 * @since  3.2.3
	 */
	protected $parent;

	/**
	 * @var    \RedeventTagsParsed
	 * @since  3.2.3
	 */
	protected $tagsParsed;

	/**
	 * Constructor
	 *
	 * @param   \RedeventTags        $parent      object calling this class	 *
	 * @param   \RedeventTagsParsed  $tagsParsed  parsed tag data
	 *
	 * @since  3.2.3
	 */
	public function __construct(\RedeventTags $parent = null, \RedeventTagsParsed $tagsParsed = null)
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
	public abstract function getDescription();

	/**
	 * Get replacement
	 *
	 * @return string
	 *
	 * @since 3.2.3
	 */
	public abstract function getReplace();
}
