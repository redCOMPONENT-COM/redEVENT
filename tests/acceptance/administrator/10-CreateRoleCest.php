<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CreateRoleCest
{
	public function createRole(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add a role in redEVENT');
		$I->doAdministratorLogin();
		$name = 'A role';
		$I->createRole(
			array(
				'name' => $name,
				'description' => '<strong>description</strong> here',
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $name . '")]');
	}
}
