<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component session Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerSession extends RedeventControllerForm
{
	/**
	 * Get a session details
	 *
	 * @return void
	 */
	public function item()
	{
		$id = $this->input->getInt('id');
		$session = RedeventEntitySession::load($id);

		if (!$session->isValid())
		{
			$response = array('error' => 'Error retrieving item');
		}
		else
		{
			$response = array(
				'id' => $session->id,
				'formatted_start_date' => $session->getFormattedStartDate(),
				'venue' => $session->getVenue()->venue,
			);
		}

		echo json_encode($response);
	}
}
