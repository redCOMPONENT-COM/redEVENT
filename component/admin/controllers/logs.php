<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Logs Controller
 *
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventControllerLogs extends RControllerAdmin
{
	/**
	 * Clears log file
	 *
	 * @return void
	 */
	public function clearlog()
	{
		RedeventHelperLog::clear();
		$msg = JText::_('COM_REDEVENT_LOG_CLEARED');
		$this->setRedirect('index.php?option=com_redevent&view=logs', $msg);
		$this->redirect();
	}
}
