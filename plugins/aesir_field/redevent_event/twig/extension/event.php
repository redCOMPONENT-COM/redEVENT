<?php
/**
 * @package     Redevent.Library
 * @subpackage  Twig.Extension
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Event Twig extension.
 *
 * @since  3.2.3
 */
class PlgAesir_FieldRedevent_EventTwigExtensionEvent extends Redevent\Twig\Plugin
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
			array(new \Twig_SimpleFunction('redevent_event', array($this, 'getInstance')))
		);
	}

	/**
	 * Get an instance.
	 *
	 * @param   integer  $id  Item identifier
	 *
	 * @return  mixed  \RedeventEntityTwigEvent || null
	 */
	public function getInstance($id)
	{
		if (empty(self::$twigEntities[$id]))
		{
			$item = \RedeventEntityEvent::load((int) $id);
			self::$twigEntities[$id] = $item->isLoaded() ? \RedeventEntityTwigEvent::getInstance($item) : null;
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
		return 'redevent_event';
	}
}
