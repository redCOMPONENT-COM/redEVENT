<?php
/**
 * @package     Aesir.Library
 * @subpackage  Twig.Extension
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Aesir\Twig\Extension;

defined('_JEXEC') or die;

/**
 * Category Twig extension.
 *
 * @since  3.2.0
 */
class PlgAesir_FieldRedevent_CategoryTwigExtensionCategory extends Redevent\Twig\Plugin
{
	/**
	 * Inject our filter.
	 *
	 * @return  array
	 */
	public function getFunctions()
	{
		return array_merge(
			parent::getFunctions(),
			array(
				new \Twig_SimpleFunction('redevent_category', array($this, 'getInstance'))
			)
		);
	}

	/**
	 * Get an instance.
	 *
	 * @param   integer  $id  Item identifier
	 *
	 * @return  mixed  \RedeventEntityTwigCategory || null
	 */
	public function getInstance($id)
	{
		if (empty(self::$twigEntities[$id]))
		{
			$item = \RedeventEntityCategory::load((int) $id);
			self::$twigEntities[$id] = $item->isLoaded() ? \RedeventEntityTwigCategory::getInstance($item) : null;
		}

		return self::$twigEntities[$id];
	}

	/**
	 * Get the name of this extension.
	 *
	 * @return  string
	 */
	public function getName()
	{
		return 'redevent_category';
	}
}
