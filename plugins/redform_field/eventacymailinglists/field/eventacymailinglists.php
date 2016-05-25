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
 * Class EventacymailinglistsFieldEventacymailinglists
 *
 * @since  3.0
 */
class EventacymailinglistsFieldEventacymailinglists extends RdfRfieldCheckbox
{
	/**
	 * @var string
	 */
	protected $type = 'eventacymailinglists';

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
		if (!include_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php')
		{
			JFactory::getApplication()->enqueueMessage('This code can not work without the AcyMailing Component');

			return false;
		}

		$listClass = acymailing_get('class.list');

		$allLists = $listClass->getLists();

		return array_map(
			function ($element)
			{
				return (object) array('value' => $element->listid, 'text' => $element->name);
			}, $allLists
		);
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	public function lookupDefaultValue()
	{
		$params = $this->form->getRenderOptions();

		if (!isset($params['eventId']))
		{
			return;
		}

		$event = RedeventEntityEvent::load($params['eventId']);

		$customField = $this->pluginParams->get('newslettercustom');

		if (!$newsletters = $event->{'custom' . $customField})
		{
			return array();
		}

		$values = explode("\n", $newsletters);
		$this->value = array_map('trim', $values);

		return $this->value;
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
}
