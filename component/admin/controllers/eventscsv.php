<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Events csv export/import Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerEventscsv extends RControllerForm
{
	public function edit($key = null, $urlVar = null)
	{
		$this->setRedirect('index.php?option=com_redevent&view=eventscsv');
	}
}
