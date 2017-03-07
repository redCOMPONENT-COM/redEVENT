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
class RdfFieldRedevent_session extends RdfRfieldSelect
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
		return parent::getOptions();
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
