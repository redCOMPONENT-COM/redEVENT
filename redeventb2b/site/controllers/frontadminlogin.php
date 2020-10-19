<?php
/**
 * @package    Redevent.site
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * B2B login view
 *
 * @package  Redevent.site
 * @since    2.5
 */

/**
 * B2B login controller
 *
 * @package  Redevent.site
 * @since    2.5
 */
class Redeventb2bControllerFrontadminlogin extends JControllerLegacy
{
	/**
	 * Method to log in a user.
	 *
	 * @return void
	 */
	public function login()
	{
		JSession::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		$app = JFactory::getApplication();

		// Populate the data array:
		$data = array();
		$data['return'] = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
		$data['username'] = JRequest::getVar('username', '', 'method', 'username');
		$data['password'] = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);

		// Set the return URL if empty.
		if (empty($data['return']))
		{
			$data['return'] = 'index.php?option=com_redeventb2b&view=frontadmin';
		}

		// Set the return URL in the user state to allow modification by plugins
		$app->setUserState('users.login.form.return', $data['return']);

		// Get the log in options.
		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);
		$options['return'] = $data['return'];

		// Get the log in credentials.
		$credentials = array();
		$credentials['username'] = $data['username'];
		$credentials['password'] = $data['password'];

		// Perform the log in.
		if (true === $app->login($credentials, $options))
		{
			// Success
			$app->setUserState('users.login.form.data', array());
			$app->redirect(JRoute::_($app->getUserState('users.login.form.return'), false));
		}
		else
		{
			// Login failed !
			$data['remember'] = (int) $options['remember'];
			$app->setUserState('users.login.form.data', $data);
			$app->redirect(JRoute::_('index.php?option=com_redeventb2b&view=frontadminlogin', false));
		}
	}

	/**
	 * Method to log out a user.
	 *
	 * @return void
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		$app = JFactory::getApplication();

		// Perform the log in.
		$error = $app->logout();

		// Check if the log out succeeded.
		if (!($error instanceof Exception))
		{
			// Get the return url from the request and validate that it is internal.
			$return = JRequest::getVar('return', '', 'method', 'base64');
			$return = base64_decode($return);

			if (!JURI::isInternal($return))
			{
				$return = '';
			}

			// Redirect the user.
			$app->redirect(JRoute::_($return, false));
		}
		else
		{
			$app->redirect(JRoute::_('index.php?option=com_redevent&view=frontadminlogin', false));
		}
	}
}
