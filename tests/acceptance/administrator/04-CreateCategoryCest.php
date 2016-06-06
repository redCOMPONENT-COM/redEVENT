<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class AddACategoryCest
{
	public function addCategory(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add a category in redEVENT');
		$I->doAdministratorLogin();
		$I->createCategory(
			array(
				'name' => 'Category 1',
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "Category 1")]');
	}
}
