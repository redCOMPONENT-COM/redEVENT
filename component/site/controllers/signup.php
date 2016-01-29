<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * EventList Component Events Controller
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventControllerSignup extends RedeventControllerFront
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();
		$this->registerTask('signup', 'display');
		$this->registerTask('sendsignupemail', 'display');
		$this->registerTask('manageredit', 'edit');
	}

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function createpdfemail()
	{
		parent::display();
	}

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function edit()
	{
		$this->input->set('layout', 'edit');

		parent::display();
	}
}
