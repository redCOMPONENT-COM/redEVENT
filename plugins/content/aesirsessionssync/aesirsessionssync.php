<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load redEVENT library
$redeventLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';

if (!file_exists($redeventLoader))
{
	throw new Exception(JText::_('COM_REDEVENT_INIT_FAILED'), 404);
}

include_once $redeventLoader;

RedeventBootstrap::bootstrap();

// Import library dependencies
jimport('joomla.plugin.plugin');

// Import Aesir library
JLoader::import('reditem.library');

/**
 * Class PlgContentAesirsessionssync
 *
 * @since  3.2.0
 */
class PlgContentAesirsessionssync extends JPlugin
{
	/**
	 * Sync session to aesir after save
	 *
	 * @param   string  $context  context
	 * @param   object  $table    table data
	 * @param   bool    $isNew    is new
	 *
	 * @return void
	 */
	public function oncontentaftersave($context, $table, $isNew)
	{
		if (!'com_redevent.session' == $context)
		{
			return;
		}

		$session = RedeventEntitySession::getInstance($table->id)->bind($table);


	}

	/**
	 * @param $session
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	private function getAesirItem($session)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('as.*')
			->from('#__reditem_types_session_2 AS as')
			->join('INNER', '#__reditem_items AS i ON i.id = as.id')
			->where('');

		$db->setQuery($query);

		if ($res = $db->loadObject())
		{
			$entity = ReditemEntityItem::getInstance($res->id)->bind($res);
		}

		return $entity;
	}
}
