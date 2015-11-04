<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component events csv export/import Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerEventscsv extends JControllerLegacy
{
	/**
	 * Get csv export file
	 *
	 * @return void
	 */
	public function export()
	{
		$this->input->set('view', 'eventscsv');
		$this->input->set('format', 'csv');

		parent::display();
	}
}
