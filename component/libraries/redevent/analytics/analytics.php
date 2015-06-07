<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
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
	 * @return true on success
	 */
	public function addTransactionJs($submit_key)
	{
		$options = $this->buildOptions($submit_key);
		RdfHelperAnalytics::recordTrans($submit_key, $options);
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
		$details = $this->getSessionDetails($submit_key);

		$options = array();
		$options['affiliation'] = 'redevent-b2c';
		$options['sku'] = $details->title;
		$options['productname'] = $details->venue . ' - ' . $details->xref . ' ' . $details->title
			. ($details->session_title ? ' / ' . $details->session_title : '');

		$cats = array();

		foreach ($details->categories as $c)
		{
			$cats[] = $c->catname;
		}

		$options['category'] = implode(', ', $cats);

		return $options;
	}

	/**
	 * Get data from session
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return mixed
	 */
	private function getSessionDetails($submit_key)
	{
		$attendeeRow = RTable::getAdminInstance('attendee');
		$attendeeRow->load(array('submit_key' => $submit_key));

		$model = RModel::getFrontInstance('Eventhelper');
		$model->setXref($attendeeRow->xref);

		$eventInfo = $model->getData();

		return $eventInfo;
	}
}
