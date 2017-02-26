<?php
/**
 * @package     Aesir.Library
 * @subpackage  Twig.Extension
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Bundle Twig extension.
 *
 * @since  3.2.0
 */
class PlgAesir_FieldRedevent_BundleTwigExtensionBundle extends Redevent\Twig\Plugin
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
			array(new \Twig_SimpleFunction('redevent_bundle', array($this, 'getInstance')))
		);
	}

	/**
	 * Get an instance.
	 *
	 * @param   integer  $id  Item identifier
	 *
	 * @return  mixed  \RedeventEntityTwigBundle || null
	 */
	public function getInstance($id)
	{
		if (empty(self::$twigEntities[$id]))
		{
			$item = \RedeventEntityBundle::load((int) $id);
			self::$twigEntities[$id] = $item->isLoaded() ? \RedeventEntityTwigBundle::getInstance($item) : null;
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
		return 'redevent_bundle';
	}
}
