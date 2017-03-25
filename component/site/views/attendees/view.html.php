<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML Attendees View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewAttendees extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'manageattendees')
		{
			return $this->displayManageAttendees($tpl);
		}

		$mainframe = JFactory::getApplication();

		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		$elsettings = RedeventHelper::config();
		$acl = RedeventUserAcl::getInstance();
		$uri = JFactory::getURI();
		$model = &$this->getModel();

		// Manages attendees
		$manage_attendees = $this->get('ManageAttendees');
		$view_full_attendees = $this->get('ViewAttendees');

		$session = $this->get('Session');
		$registers = $model->getRegisters();
		$register_fields = $model->getFormFields();
		$roles = $this->get('Roles');

		// Get menu information
		$menu = $mainframe->getMenu();
		$item = $menu->getActive();

		if (!$item)
		{
			$item = $menu->getDefault();
		}

		$params = $mainframe->getParams('com_redevent');

		// Check if the id exists
		if (!$session)
		{
			return JError::raiseError(404, JText::sprintf('COM_REDEVENT_Session_not_found'));
		}

		// Print
		$pop = JFactory::getApplication()->input->getBool('pop');

		$params->def('page_title', RedeventHelper::getSessionFullTitle($session) . ' - ' . JText::_('COM_REDEVENT_ATTENDEES'));

		if ($pop)
		{
			$params->set('popup', 1);
		}

		$print_link = JRoute::_('index.php?option=com_redevent&view=attendees&xref=' . $session->slug . '&pop=1&tmpl=component');

		// Pathway
		$pathway = $mainframe->getPathWay();
		$pathway->addItem(JText::_('COM_REDEVENT_ATTENDEES'), JRoute::_('index.php?option=com_redevent&view=attendees&xref=' . $session->slug));

		// Set page title and meta stuff
		$document->setTitle($item->title . ' - ' . RedeventHelper::getSessionFullTitle($session));

		$unreg_check = RedeventHelper::canUnregister($session->xref);

		// Lists
		$lists = array();

		// Call the state object
		$state = $this->get('state');

		// Get the values from the state object that were inserted in the model's construct function
		$lists['order_Dir'] = $state->get('filter_order_Dir');
		$lists['order'] = $state->get('filter_order');

		$this->assignRef('session', $session);
		$this->assignRef('params', $params);
		$this->assignRef('user', $user);
		$this->assignRef('manage_attendees', $manage_attendees);
		$this->assignRef('view_full_attendees', $view_full_attendees);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('registers', $registers);
		$this->assignRef('registersfields', $register_fields);
		$this->assignRef('roles', $roles);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('item', $item);
		$this->assignRef('unreg_check', $unreg_check);
		$this->assignRef('action', JRoute::_('index.php?option=com_redevent&view=attendees&xref=' . $session->slug));
		$this->assignRef('lists', $lists);

		return parent::display($tpl);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function displayManageAttendees($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		$elsettings = RedeventHelper::config();
		$uri = JFactory::getURI();
		$session = $this->get('Session');
		$registers = $this->get('Registers');
		$regcheck = $this->get('ManageAttendees');
		$roles = $this->get('Roles');

		// Get menu information
		$menu = $mainframe->getMenu();
		$item = $menu->getActive();

		if (!$item)
		{
			$item = $menu->getDefault();
		}

		$params = $mainframe->getParams('com_redevent');

		// Check if the session exists
		if (!$session)
		{
			return JError::raiseError(404, JText::sprintf('COM_REDEVENT_Session_not_found'));
		}

		// Check if user has access to the attendees management
		if (!$regcheck)
		{
			$mainframe->redirect('index.php', JText::_('COM_REDEVENT_Only_logged_users_can_access_this_page'), 'error');
		}

		// Add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$params->def('page_title', JText::_('COM_REDEVENT_Manage_attendees'));

		// Pathway
		$pathway = $mainframe->getPathWay();
		$pathway->addItem(
			JText::_('COM_REDEVENT_Manage_attendees')
			. ' - ' . RedeventHelper::getSessionFullTitle($session),
			JRoute::_('index.php?option=com_redevent&view=attendees&layout=manageattendees&id=' . $session->slug)
		);

		// Check user if he can edit
		$manage_attendees = $this->get('ManageAttendees');
		$view_full_attendees = $this->get('ViewAttendees');

		// Add javascript code for cancel button on attendees layout.
		JHTML::_('behavior.framework');
		RHelperAsset::load('site/attendees.js');

		// Set page title and meta stuff
		$document->setTitle(JText::_('COM_REDEVENT_Manage_attendees') . ' - ' . RedeventHelper::getSessionFullTitle($session));

		// Lists
		$lists = array();

		// Call the state object
		$state = $this->get('state');

		// Get the values from the state object that were inserted in the model's construct function
		$lists['order_Dir'] = $state->get('filter_order_Dir');
		$lists['order'] = $state->get('filter_order');

		$this->assignRef('session', $session);
		$this->assignRef('params', $params);
		$this->assignRef('user', $user);
		$this->assignRef('registers', $registers);
		$this->assignRef('roles', $roles);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('item', $item);
		$this->assignRef('manage_attendees', $manage_attendees);
		$this->assignRef('view_full_attendees', $view_full_attendees);
		$this->assign('action', JRoute::_('index.php?option=com_redevent&view=attendees&layout=manageattendees&id=' . $session->slug));
		$this->assign('lists', $lists);

		parent::display($tpl);
	}

	/**
	 * Show roles
	 *
	 * @return void
	 */
	public function showRoles()
	{
		if (file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redmember') && 0)
		{
			$layout = $this->getLayout();
			$this->setLayout('default');
			echo $this->loadTemplate('rmroles');
			$this->setLayout($layout);
		}
		else
		{
			$layout = $this->getLayout();
			$this->setLayout('default');
			echo $this->loadTemplate('roles');
			$this->setLayout($layout);
		}
	}
}
