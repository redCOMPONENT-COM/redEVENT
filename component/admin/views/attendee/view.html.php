<?php
/**
 * @version     1.0 $Id$
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * View class for the edit attendee screen
 *
 * @package     Joomla
 * @subpackage  redEvent
 * @since       2.0
 */
class RedEventViewAttendee extends RedeventViewAdmin
{
	/**
	 * Display
	 *
	 * @param   string  $tpl  template
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$row = $this->get('data');

		// Make data safe
		JFilterOutput::objectHTMLSafe($row);

		// Create selectlists
		$lists = array();

		// User list
		$lists['user'] = JHTML::_('list.users', 'uid', $row->uid, 1, null, 'name', 0);

		$sessionpricegroups = $this->get('Pricegroups');
		$lists['pricegroup_id'] = RedeventHelper::getRfPricesSelect($sessionpricegroups, $row->sessionpricegroup_id);

		$this->row = $row;
		$this->session = $this->get('Session');
		$this->lists = $lists;
		$this->returnUrl = $app->input->get('return');

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDEVENT_PAGETITLE_EDITATTENDEE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save = RToolbarBuilder::createSaveButton('attendee.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('attendee.save');

		$group->addButton($save)
			->addButton($saveAndClose);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('attendee.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('attendee.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
