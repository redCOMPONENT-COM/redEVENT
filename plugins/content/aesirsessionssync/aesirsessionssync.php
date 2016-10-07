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
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

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
		$item = $this->getAesirSessionItem($session->id);

		if (!$item->isValid())
		{
			$data = array(
				'type_id' => $this->params->get('aesir_session_type_id'),
				'template_id' => $this->params->get('aesir_session_template_id'),
				'title'   => JText::sprintf(
					'PLG_AESIRSESSIONSSYNC_ITEM_SESSION_TITLE_FORMAT',
					$session->getEvent()->title,
					$session->getVenue()->name,
					$session->getFormattedStartDate()),
				'access'  => 1,
				'custom_fields' => array(
					'select_redevent_session' => $session->id
				)
			);

			$eventItem = $this->getAesirEventItem($session->eventid);

			if ($eventItem->isValid())
			{
				$data['params'] = array(
					"related_items" => array($eventItem->getId())
				);
			}

			// TODO: remove this workaround when aesir code gets fixed
			$jform = JFactory::getApplication()->input->get('jform', null, 'array');
			$jform['access'] = $this->params->get('session_access');
			JFactory::getApplication()->input->set('jform', $jform);

			$item->save($data);
		}
	}

	/**
	 * Get aesir item session
	 *
	 * @param   int  $sessionId  redEVENT session id
	 *
	 * @return ReditemEntityItem
	 */
	private function getAesirSessionItem($sessionId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('s.*')
			->from('#__reditem_types_session_2 AS s')
			->join('INNER', '#__reditem_items AS i ON i.id = s.id')
			->where('s.select_redevent_session = ' . $sessionId);

		$db->setQuery($query);

		if ($res = $db->loadObject())
		{
			$entity = ReditemEntityItem::getInstance($res->id)->bind($res);
		}
		else
		{
			$entity = ReditemEntityItem::getInstance();
		}

		return $entity;
	}

	/**
	 * Get aesir item session
	 *
	 * @param   int  $eventId  redEVENT event id
	 *
	 * @return ReditemEntityItem
	 */
	private function getAesirEventItem($eventId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__reditem_types_course_1 AS c')
			->join('INNER', '#__reditem_items AS i ON i.id = c.id')
			->where('c.select_redevent_event = ' . $eventId);

		$db->setQuery($query);

		if ($res = $db->loadObject())
		{
			$entity = ReditemEntityItem::getInstance($res->id)->bind($res);
		}
		else
		{
			$entity = ReditemEntityItem::getInstance();
		}

		return $entity;
	}
}
