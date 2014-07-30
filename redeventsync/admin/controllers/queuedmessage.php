<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

/**
 * controller for Queued message
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncControllerQueuedmessage extends FOFController
{
	/**
	 * Send selected item(s)
	 *
	 * @return  void
	 */
	public function process()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getModel('Sendqueuedmessages');

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$status = $model->process();

		// Redirect
		$url = 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view);

		if (!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}

		return $status;
	}
}
