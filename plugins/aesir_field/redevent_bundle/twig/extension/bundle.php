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
 * Bundle Twig extension.
 *
 * @since  3.2.0
 */
class PlgAesir_FieldRedevent_BundleTwigExtensionBundle extends \Twig_Extension
{
	/**
	 * Inject our filter.
	 *
	 * @return  array
	 */
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('redevent_bundle', array($this, 'getInstance'))
		);
	}

	/**
	 * Get an instance.
	 *
	 * @param   integer  $id  Item identifier
	 *
	 * @return  mixed  \Aesir\Entity\Twig\Category || null
	 */
	public function getInstance($id)
	{
		$item = \RedeventEntityBundle::load((int) $id);

		return $item->isLoaded() ? new \RedeventEntityTwigBundle($item) : null;
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
