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
class Redeventb2bControllerFrontadmin extends FOFController
{
	/**
	 * ajax update user
	 *
	 * @return void
	 */
	public function update_user()
	{
		$app = JFactory::getApplication();

		$dataForm = $app->input->get('jform', array(), 'array');
		$dataCustom = $app->input->get('cform', array(), 'array');

		$rmId = isset($dataForm['id']) && $dataForm['id'] ? (int) $dataForm['id'] : 0;

		$rmUser = RedmemberApi::getUserByRmid($rmId);

		$resp = new stdClass;

		try
		{
			if (!$orgId = $app->input->getInt('orgId'))
			{
				RedeventHelperLog::simpleLog('Create user B2b missing organization');
				throw new InvalidArgumentException('Missing organization id');
			}

			$currentOrgs = $rmUser->getOrganizations();

			if (!isset($currentOrgs[$orgId]))
			{
				$currentOrgs[$orgId] = array('organization_id' => $orgId, 'level' => 1);
			}

			$rmUser->setOrganizations($currentOrgs);

			// Remove type from posted fields
			$customDataClean = array();

			foreach ($dataCustom as $type => $fieldValues)
			{
				foreach ($fieldValues as $fieldcode => $value)
				{
					$customDataClean[$fieldcode] = $value;
				}
			}

			$rmUser->bind($dataForm, $customDataClean);

			$rmUser->save();
			$resp->status = 1;
		}
		catch (Exception $e)
		{
			$resp->status = 0;
			$resp->error  = JText::_('COM_USERS_USER_SAVE_FAILED') . ': ' . $e->getMessage();
		}

		if ($this->input->get('format') == 'json')
		{
			echo json_encode($resp);
			$app->close();
		}
		else
		{
			$app->input->set('orgId', $orgId);

			if ($resp->status)
			{
				$app->input->set('uid', $user->get('id'));
				$app->input->set('uname', $user->get('name'));

				if ($app->input->get('modal'))
				{
					$this->closemodalmember();
				}
				else
				{
					$app->enqueueMessage(Jtext::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_SAVED'));
					$this->editmember();
				}
			}
			else
			{
				$app->enqueueMessage($resp->error, 'error');
				$this->editmember();
			}
		}
	}
}
