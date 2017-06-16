<?php
/**
 * @package     Aesir.Plugin
 * @subpackage  Aesir_Field.Redevent_category
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('reditem.library');
JLoader::registerPrefix('PlgAesir_FieldRedevent_category', __DIR__);

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception('redCORE is required', 404);
}

include_once $redcoreLoader;

JLoader::import('redevent.bootstrap');
RedeventBootstrap::bootstrap();

use Aesir\Plugin\AbstractFieldPlugin;
use Aesir\Entity\FieldInterface;
use Aesir\App;
use Aesir\Entity\Twig;

/**
 * Redevent_category field
 *
 * @since  1.0.0
 */
final class PlgAesir_FieldRedevent_Category extends AbstractFieldPlugin
{
	/**
	 * Type for the form type="redevent_category" tag
	 *
	 * @var  string
	 */
	protected $formFieldType = 'PlgAesir_FieldRedevent_category.category';

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
		$twig->addExtension(new PlgAesir_FieldRedevent_categoryTwigExtensionCategory);
	}
}
