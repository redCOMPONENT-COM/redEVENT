<?php
/**
 * @package    Redevent.site
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * B2B login view
 *
 * @package  Redevent.site
 * @since    2.5
 */
class Redeventb2bViewFrontadminlogin extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$app->input->set('tmpl', 'component');

		$user = JFactory::getUser();

		$frontadminRoute = JRoute::_(Redeventb2bHelperRoute::getFrontadminRoute());

		if ($user->get('id'))
		{
			$app->redirect($frontadminRoute);

			return;
		}

		$params = $app->getParams();
		$document  = JFactory::getDocument();

		// Add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
			$document->addStyleSheet($this->baseurl . '/media/com_redevent/css/redevent-b2b.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$this->return = base64_encode($frontadminRoute);
		$this->params = $app->getParams();

		parent::display($tpl);
	}
}
