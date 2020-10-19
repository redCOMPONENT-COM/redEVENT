<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Analytics support
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventAnalytics
{
	/**
	 * Sends a google analytics transaction (including items) through javascript
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return void
	 */
	public function addTransactionJs($submit_key)
	{
		if (!RdfHelperAnalytics::isEnabled())
		{
			return;
		}

		$cartReference = RdfCore::getSubmitkeyCartReference($submit_key);
		$options = $this->buildOptions($submit_key);

		RdfHelperAnalytics::recordTrans($cartReference, $options);
	}

	/**
	 * Sends a google analytics transaction (including items) through measurement protocol
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return true on success
	 */
	public function addTransactionMeasurementProtocol($submit_key)
	{
		$options = $this->buildOptions($submit_key);
		RdfHelperAnalytics::recordTransMeasurementProtocol($submit_key, $options);

		return true;
	}

	/**
	 * Build options
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return array
	 */
	private function buildOptions($submit_key)
	{
		$session = $this->getSession($submit_key);

		$options = array();
		$options['affiliation'] = 'redevent-b2c';
		$options['sku'] = $session->getEvent()->title;
		$options['productname'] = $session->getVenue()->venue . ' - ' . $session->xref . ' ' . $session->getEvent()->title
			. ($session->title ? ' / ' . $session->title : '');

		$cats = array();

		foreach ($session->getEvent()->getCategories() as $c)
		{
			$cats[] = $c->name;
		}

		$options['category'] = implode(', ', $cats);

		return $options;
	}

	/**
	 * Get data from session
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return RedeventEntitySession
	 */
	private function getSession($submit_key)
	{
		$attendees = RedeventEntityAttendee::loadBySubmitKey($submit_key);

		if (!$attendees)
		{
			throw new RuntimeException('No attendees found for key ' . $submit_key);
		}

		return reset($attendees)->getSession();
	}
}
