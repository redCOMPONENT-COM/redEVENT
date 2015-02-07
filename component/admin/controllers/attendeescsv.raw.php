<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Categories csv export/import Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerAttendeescsv extends JControllerLegacy
{
	/**
	 * Get csv export file
	 *
	 * @return void
	 */
	public function eventoptions()
	{
		$model = $this->getModel('Attendeescsv');
		$items = $model->getEventsOptions($this->input->getInt('category_id'), $this->input->getInt('venue_id'));

		echo json_encode($items);
		exit;
	}
}
