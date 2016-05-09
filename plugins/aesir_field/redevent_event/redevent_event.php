<?php
/**
 * @package     Aesir.Plugin
 * @subpackage  Aesir_Field.Redevent_event
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('reditem.library');
JLoader::registerPrefix('PlgAesir_FieldRedevent_event', __DIR__);

JLoader::import('redevent.bootstrap');
RedeventBootstrap::bootstrap();

use Aesir\Plugin\AbstractFieldPlugin;
use Aesir\Entity\FieldInterface;

/**
 * Redevent_event field
 *
 * @since  1.0.0
 */
final class PlgAesir_FieldRedevent_event extends AbstractFieldPlugin
{
	/**
	 * Type for the form type="redevent_event" tag
	 *
	 * @var  string
	 */
	protected $formFieldType = 'PlgAesir_FieldRedevent_event.event';

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Change the Twig enviroment after it has been loaded
	 *
	 * @param   \Aesir\Twig\Enviroment      $twig     Twig enviroment
	 * @param   \Twig_LoaderInterface|null  $loader   Twig loader
	 * @param   array                       $options  Options for the enviroment
	 *
	 * @return  void
	 */
	public function onAesirAfterTwigLoad(\Aesir\Twig\Enviroment $twig, \Twig_LoaderInterface $loader = null, $options = array())
	{
		$twig->addExtension(new PlgAesir_FieldRedevent_eventTwigExtensionEvent);
	}
}
