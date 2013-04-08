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

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * redEVENT Component b2b Controller
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.5
 */
class RedeventControllerFrontadmin extends FOFController
{
	/**
	 * return sessions html table
	 *
	 * @return void
	 */
	public function searchsessions()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'searchsessions';

		$this->display();

		// No debug !
		$app->close();
	}

	/**
	 * return sessions options as JSON
	 *
	 * @return void
	 */
	public function sessionsoptions()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel('Frontadmin', 'RedeventModel');
		$options = $model->getSessionsOptions();

		echo json_encode($options);

		$app->close();
	}

	/**
	 * return booked sessions html table
	 *
	 * @return void
	 */
	public function getbookings()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'bookings';

		$this->display();

		// No debug !
		$app->close();
	}

	/**
	 * return sessions details as JSON
	 *
	 * @return void
	 */
	public function getusers()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel('Frontadmin', 'RedeventModel');
		$options = $model->getUsersOptions();

		echo json_encode($options);

		$app->close();
	}

	/**
	 * return sessions details as JSON
	 *
	 * @return void
	 */
	public function getsession()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel('eventhelper');
		$model->setXref($app->input->get('id'));
		$data  = $model->getData();

		echo json_encode($data);

		$app->close();
	}

	public function getattendees()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'attendees';

		$model = $this->getModel('frontadmin');

		$att = $model->getAttendees($app->input->get('xref', 0, 'int'), $app->input->get('org', 0, 'int'));

		$view = $this->getThisView();
		$view->assignRef('attendees', $att);
		$this->display();

		JFactory::getApplication()->close();
	}

	public function quickbook()
	{
		echo 'quickbook';

		JFactory::getApplication()->close();
	}
}
