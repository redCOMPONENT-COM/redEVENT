<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace Step\Acceptance\Administrator;
use \Page\Acceptance\Administrator\RoleManagerPage;
use Step\Acceptance\AdminRedevent;

class RoleManagerSteps extends AdminRedevent
{
	/**
	 * @param $params
	 * @throws \Exception
	 */
	public function createRoleNew($params)
	{
		$I = $this;
		$I->createItemNew(RoleManagerPage::$URL,RoleManagerPage::$roleTitle,RoleManagerPage::$roleTitleNew,$params);
	}

	/**
	 * @param $nameRole
	 * @throws \Exception
	 */
	public function searchRole($nameRole)
	{
		$I = $this;
		$I->search(RoleManagerPage::$URL,$nameRole);
	}

	/**
	 * @param $nameRole
	 * @throws \Exception
	 */

	public function deleteRole($nameRole)
	{
		$I = $this;
		$I->amOnPage(RoleManagerPage::$URL);
		$I->waitForText(RoleManagerPage::$roleTitle, 30);
		$I->searchRole($nameRole);
		$I->see($nameRole, RoleManagerPage::$tableResult);
		$I->checkAllResults();
		$I->click(RoleManagerPage::$buttonDelete);
		$I->wantTo('Test with delete Role but then cancel');
		$I->cancelPopup();
		$I->wantTo('Test with delete Role then accept');
		$I->click(RoleManagerPage::$buttonDelete);
		$I->acceptPopup();
		$I->waitForText(RoleManagerPage::$notificationNoItem, 60);
		$I->dontSee($nameRole);
	}
}
