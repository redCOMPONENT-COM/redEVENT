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
 * @since  __DEPLOY_VERSION__
 */
class PlgAesir_FieldRedevent_EventTwigExtensionEvent extends \Twig_Extension
{
	/**
	 * Inject our filter.
	 *
	 * @return  array
	 */
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('redevent_event', array($this, 'getInstance'))
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
		$item = RedeventEntityEvent::load((int) $id);

		return $item->isLoaded() ? new PlgAesir_FieldRedevent_eventEntityTwigEvent($item) : null;
	}

	/**
	 * Get the name of this extension.
	 *
	 * @return  string
	 */
	public function getName()
	{
		return 'redevent_event';
	}
}
