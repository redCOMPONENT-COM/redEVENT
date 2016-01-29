<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component attendees Controller
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventControllerAttendees extends RedeventControllerFront
{
	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function exportattendees()
	{
		$this->input->set('view', 'attendees');
		$this->input->set('layout', 'exportattendees');

		parent::display();
	}
}
