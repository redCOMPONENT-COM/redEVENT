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
 * Session Twig extension.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgAesir_FieldRedevent_SessionTwigExtensionSession extends Redevent\Twig\Plugin
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
			array(new \Twig_SimpleFunction('redevent_session', array($this, 'getInstance')))
		);
	}

	/**
	 * Get an instance.
	 *
	 * @param   integer  $id  Item identifier
	 *
	 * @return  mixed  \RedeventEntityTwigSession || null
	 */
	public function getInstance($id)
	{
		if (empty(self::$twigEntities[$id]))
		{
			$item = \RedeventEntitySession::load((int) $id);
			self::$twigEntities[$id] = $item->isLoaded() ? \RedeventEntityTwigSession::getInstance($item) : null;
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
		return 'redevent_session';
	}
}
