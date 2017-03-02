<?php
/**
 * @package     Aesir.Plugin
 * @subpackage  Aesir_Field.Redevent_venue
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('reditem.library');
JLoader::registerPrefix('PlgAesir_FieldRedevent_venue', __DIR__);

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
 * Redevent_venue field
 *
 * @since  1.0.0
 */
final class PlgAesir_FieldRedevent_Venue extends AbstractFieldPlugin
{
	/**
	 * Type for the form type="redevent_venue" tag
	 *
	 * @var  string
	 */
	protected $formFieldType = 'PlgAesir_FieldRedevent_venue.venue';

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
		$twig->addExtension(new PlgAesir_FieldRedevent_venueTwigExtensionVenue);
	}

	/**
	 * Integration for maersk
	 *
	 * @return void
	 *
	 * @since 3.2.3
	 */
	public function onAjaxGetVenueActiveSessions()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$venueId = $input->getInt('id');
		$limit = $input->getInt('limit', RedeventHelper::config()->get('show_num'));
		$limitstart = $input->getInt('limitstart', 0);
		$layout = $input->getString('layout', 'redevent.aesir_field.venue.sessions');

		$filter_date = $input->getString('filter_date');
		$filter_lang = $input->getString('filter_lang');

		$db = JFactory::getDbo();

		// First get from 'all_dates' bundle events
		$query = $db->getQuery(true)
			->select('x.*')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_events AS e ON x.eventid = e.id')
			->where('x.venueid = ' . $venueId)
			->where('x.published = 1')
			->where('e.published = 1');

		if ($filter_date)
		{
			$monthStart = JFactory::getDate(str_replace("_", " ", $filter_date));
			$monthEnd = clone $monthStart;
			$monthEnd->modify('+ 1 month - 1 day');
			$query->where('x.dates BETWEEN ' . $db->quote($monthStart->toSql()) . ' AND ' . $db->quote($monthEnd->toSql()));
		}

		if ($filter_lang)
		{
			$query->where('x.session_language = ' . $db->quote($filter_lang));
		}

		$open_order = RedeventHelper::config()->get('open_dates_ordering', 0);
		$ordering_def = ($open_order ? 'x.dates = 0 ' : 'x.dates > 0 ') . 'ASC'
			. ', x.dates ASC, x.times ASC';

		$query->order($ordering_def);

		$db->setQuery($query, $limitstart, $limit);
		$res = $db->loadObjectList() ?: array();

		$sessions = RedeventEntitySession::loadArray($res);

		$twigEntities = array_map(
			function($session)
			{
				return RedeventEntityTwigSession::getInstance($session);
			},
			$sessions
		);

		$twigLayout = RedeventLayoutHelper::render($layout, compact('sessions'), '', ['defaultLayoutsPath' => __DIR__ . '/layouts']);

		$loader = new Twig_Loader_Array(
			array (
				$layout => $twigLayout
			)
		);

		$twig = App::getTwig($loader);

		$html = $twig->render(
			$layout,
			array (
				'sessions' => $twigEntities
			)
		);

		echo new JResponseJson($html);

		$app->close();
	}
}
