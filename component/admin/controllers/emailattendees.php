<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Email attendees Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerEmailattendees extends JControllerLegacy
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('emailall', 'email');
	}

	public function email()
	{
		$this->input->set('view', 'emailattendees');

		parent::display();
	}

	public function send()
	{
		echo '<pre>'; echo print_r('send', true); echo '</pre>'; exit;
	}
}
