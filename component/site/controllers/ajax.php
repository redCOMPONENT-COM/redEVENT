<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component b2b Controller
 *
 * @package  Redevent.Site
 * @since    2.5
 */
class RedeventControllerAjax extends JControllerLegacy
{
	/**
	 * Print person suggestions as json
	 *
	 * @return void
	 */
	public function eventsuggestions()
	{
		$app = JFactory::getApplication();

		$return = array();

		$search       = $app->input->get('q', '', 'string');

		if (strlen($search) > 2)
		{
			$model = $this->getModel('Ajaxeventssuggest', 'RedeventModel');
			$model->setState('filter.text', $search);
			$return = $model->getItems();
		}

		// Use the correct json mime-type
		header('Content-Type: application/json');

		// Send the response.
		echo json_encode($return);
		JFactory::getApplication()->close();
	}
}
