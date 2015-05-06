<?php
/**
 * @version    1.0 $Id$
 * @package    Joomla
 * @subpackage redEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Categories View
 *
 * @package    Joomla
 * @subpackage redEVENT
 * @since      0.9
 */
class RedeventViewCategories extends RViewSite
{
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$document = JFactory::getDocument();
		$elsettings = RedeventHelper::config();
		$params = $app->getParams();

		$rows = $this->get('Data');
		$total = $this->get('Total');

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

		// Get menu information
		$active = $app->getMenu()->getActive();
		$params = $app->getParams('com_redevent');

		// Request variables
		$limitstart = JRequest::getInt('limitstart');
		$limit = JRequest::getInt('limit', $params->get('cat_num'));
		$task = JRequest::getWord('task');

		$params->def('page_title', $active->title);

		// Pathway
		$pathway = $app->getPathWay();

		if ($task == 'archive')
		{
			$pathway->addItem(JText::_('COM_REDEVENT_ARCHIVE'), JRoute::_(RedeventHelperRoute::getCategoriesRoute(null, 'archive')));
			$pagetitle = $params->get('page_title') . ' - ' . JText::_('COM_REDEVENT_ARCHIVE');
		}
		else
		{
			$pagetitle = $params->get('page_title');
		}

		// Set Page title
		$this->document->setTitle($pagetitle);

		// Get icon settings
		$params->def('icons', $app->getCfg('icons'));

		// Add alternate feed link
		$link = RedeventHelperRoute::getSimpleListRoute();

		// Check if the user has access to the form
		$canCreate = JFactory::getUser()->authorise('re.createevent');

		// Create the pagination object
		jimport('joomla.html.pagination');

		$pageNav = new JPagination($total, $limitstart, $limit);

		$this->assignRef('rows', $rows);
		$this->assignRef('task', $task);
		$this->assignRef('params', $params);
		$this->assignRef('canCreate', $canCreate);
		$this->assignRef('pageNav', $pageNav);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('pagetitle', $pagetitle);

		parent::display($tpl);
	}
}
