<?php
/**
 * @package    Redevent.Administrator
 *
 * @copyright  redEVENT (C) 2014 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Organizations list controller class.
 *
 * @package  Redevent.Administrator
 * @since    2.5
 */
class RedeventControllerOrganizations extends FOFController
{
	/**
	 * @var   string  The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_REDEVENT_ORGANIZATIONS';

	/**
	 * Sync with redmember
	 *
	 * @return void
	 */
	public function sync()
	{
		// Load the model
		$model = $this->getThisModel();

		if (!$model->sync())
		{
			$message = $model->getError();
			$msgType = 'error';
		}
		else
		{
			$message = JText::_('COM_REDEVENT_ORGANIZATIONS_SYNC_OK');
			$msgType = '';
		}

		$url = 'index.php?option=com_redevent&view=organizations';
		$this->setRedirect($url, $message, $msgType);
	}
}
