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

/**
 * HTML View class for the frontend admin View
 *
 * @package     Joomla
 * @subpackage  redevent
 * @since       2.0
*/
class RedeventViewAdmin extends JView
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
		$acl        = UserACl::getInstance();

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

		parent::display($tpl);
	}
}
