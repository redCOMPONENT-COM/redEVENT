<?php
/**
 * @package     Redeventsync
 * @subpackage  Admin
 * @copyright   Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();

/**
 * Redeventsync Component logs Controller
 *
 * @package     Redeventsync
 * @subpackage  Admin
 * @since       3.0
 */
class RedeventsyncControllerLogs extends RControllerAdmin
{
	/**
	 * Archive old logs
	 *
	 * @return void
	 */
	public function archiveold()
	{
		$model = $this->getModel('logs');
		$affected = $model->archiveOld();

		$this->setRedirect($this->getRedirectToListRoute(), JText::sprintf('COM_REDEVENTSYNC_LOGS_ARCHIVED_OK', $affected));
	}
}
