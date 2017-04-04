<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 *
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Class Redevent_sessionFieldRedevent_session
 *
 * @since  3.0
 */
class RdfFieldRedevent_Session extends RdfRfieldSelect
{
	/**
	 * @var string
	 */
	protected $type = 'redevent_session';

	/**
	 * @var JRegistry
	 */
	protected $pluginParams;

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$element = RdfLayoutHelper::render(
			'rform.rfield.' . $this->type,
			$this,
			null,
			array('component' => 'com_redform', 'defaultLayoutsPath' => dirname(__DIR__) . '/layouts')
		);

		return $element;
	}

	/**
	 * Return field options (for select, radio, etc...)
	 *
	 * @return mixed
	 */
	public function getOptions()
	{
		$options = parent::getOptions();

		if (!$sessions = $this->getSessions())
		{
			return $sessions;
		}

		$formatValue = $this->getParam('label_format', '[session_id]');
		$formatText = $this->getParam('value_format', '[event_title] - [date format="d-m-Y"]');

		$optionsSessions = array_map(
			function ($session) use ($formatValue, $formatText)
			{
				$tags = new RedeventTags;
				$tags->setXref($session->id);

				$option = new stdClass;
				$option->value = $tags->replaceTags($formatValue, ['extra' => ['[session_id]' => $session->id]]);
				$option->label = $tags->replaceTags($formatText, ['extra' => ['[session_id]' => $session->id]]);
				$option->price = 0;
				$option->sku = "";

				return $option;
			},
			$sessions
		);

		return array_merge($options ?: array(), $optionsSessions);
	}

	/**
	 * Set params from plugin
	 *
	 * @param   JRegistry  $params  params
	 *
	 * @return EventacymailinglistsFieldEventacymailinglists
	 */
	public function setPluginParams(JRegistry $params)
	{
		$this->pluginParams = $params;

		return $this;
	}

	/**
	 * getSessions
	 *
	 * @return RedeventEntitySession[]
	 *
	 * @since 3.2.3
	 */
	private function getSessions()
	{
		$filterState = $this->getParam('session_state');
		$filterCategory = $this->getParam('session_category');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('x.*')
			->from('#__redevent_event_venue_xref AS x')
			->innerJoin('#__redevent_events AS e ON e.id = x.eventid')
			->where('x.published = 1')
			->where('e.published = 1')
			->order('x.dates > 0 DESC, x.dates ASC, x.times ASC');

		if (is_numeric($filterState))
		{
			$query->where('x.published = ' . $filterState);
		}

		if (is_numeric($filterCategory))
		{
			$query->innerJoin('#__redevent_event_category_xref AS xcat ON xcat.event_id = x.eventid');
			$query->where('xcat.category_id = ' . $filterCategory);
		}

		$db->setQuery($query);

		if (!$res = $db->loadObjectList())
		{
			return array();
		}

		return RedeventEntitySession::loadArray($res);
	}
}
