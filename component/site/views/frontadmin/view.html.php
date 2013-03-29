<?php
/**
 * @version     2.5
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.view');

require_once JPATH_SITE . '/components/com_redevent/views/myevents/view.html.php';

/**
 * HTML View class for the frontend admin View
 *
 * @package     Joomla
 * @subpackage  redevent
 * @since       2.0
*/
class RedeventViewFrontadmin extends JView
{
	/**
	 * Creates the View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 *
	 * @since 2.5
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'searchsessions')
		{
			return $this->displaySearchSessions($tpl);
		}

		if ($this->getLayout() == 'bookings')
		{
			return $this->displayBookings($tpl);
		}

		JHTML::_('behavior.framework');

		// Load Akeeba Strapper
		include_once JPATH_ROOT.'/media/akeeba_strapper/strapper.php';
		AkeebaStrapper::bootstrap();

		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();

		if (!$user->get('id'))
		{
			$mainframe->redirect('index.php', JText::_('COM_REDEVENT_Only_logged_users_can_access_this_page'), 'error');
		}

		// Initialize variables
		$document   = JFactory::getDocument();
		$elsettings = redEVENTHelper::config();
		$pathway    = $mainframe->getPathWay();
		$params     = $mainframe->getParams();
		$uri        = JFactory::getURI();

		$menu = JSite::getMenu();
		$item = $menu->getActive();

		// Add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet($this->baseurl . '/components/com_redevent/assets/css/redevent.css');
			$document->addStyleSheet($this->baseurl . '/media/com_redevent/css/redevent-b2b.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		FOFTemplateUtils::addJS('media://com_redevent/js/b2b.js');

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$useracl = UserAcl::getInstance();

		$state = $this->get('state');

		// Events filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_EVENT')));
		$this->events_options     = array_merge($options, $this->get('EventsOptions'));

		// Sessions filter
		JText::script("COM_REDEVENT_SESSION");
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_SESSION')));
		$this->sessions_options   = array_merge($options, $this->get('SessionsOptions'));

		// Venues filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_VENUE')));
		$this->venues_options     = array_merge($options, $this->get('VenuesOptions'));

		// Categories filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_CATEGORY')));
		$this->categories_options = array_merge($options, $this->get('CategoriesOptions'));

		// Organizations filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_FRONTEND_ADMIN_ORGANIZATION')));
		$this->organizations_options = array_merge($options, $this->get('OrganizationsOptions'));

		// Users filter
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_SELECT_USER");
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECT_USER')));
		$this->users_options = array_merge($options, $this->get('UsersOptions'));

		$this->filter_from        = $state->get('filter_from');
		$this->filter_to          = $state->get('filter_to');

		$this->order_Dir = $state->get('filter_order');
		$this->order     = $state->get('filter_order_Dir');

		$this->useracl = $useracl;
		$this->params  = $params;
		$this->state   = $state;

		$this->sessions = $this->get('Sessions');

		$this->organization = $this->get('Organization');
		$this->bookings   = $this->get('OrganizationBookings');

		parent::display($tpl);
	}

	protected function displaySearchSessions($tpl = null)
	{
		$useracl = UserAcl::getInstance();
		$params = JFactory::getApplication()->getParams('com_redevent');
		$state = $this->get('state');

		$this->order_Dir = $state->get('filter_order');
		$this->order     = $state->get('filter_order_Dir');

		$this->params  = $params;
		$this->state   = $state;

		$this->useracl = $useracl;
		$this->sessions = $this->get('Sessions');
		$this->params  = $params;

		parent::display($tpl);
	}

	protected function displayBookings($tpl = null)
	{
		$useracl = UserAcl::getInstance();
		$params = JFactory::getApplication()->getParams('com_redevent');
		$state = $this->get('state');

		$this->order_Dir = $state->get('filter_order');
		$this->order     = $state->get('filter_order_Dir');

		$this->params  = $params;
		$this->state   = $state;

		$this->useracl = $useracl;
		$this->bookings = $this->get('Bookings');
		$this->params  = $params;

		$this->organization = $this->get('Organization');

		parent::display($tpl);
	}
}
