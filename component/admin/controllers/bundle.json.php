<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component bundle Controller
 *
 * @package  Redevent.admin
 * @since    3.2.0
 */
class RedeventControllerBundle extends RControllerForm
{
	/**
	 * Return bundle events
	 *
	 * @return void
	 */
	public function events()
	{
		$bundleId = $this->input->getInt('id');

		$model = $this->getModel();

		$events = $model->getEvents($bundleId);

		echo json_encode($events);
	}
}
