<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CreatePriceGroupCest
{
	public function createPriceGroup(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add a price group in redEVENT');
		$I->doAdministratorLogin();
		$name = 'A price group';
		$I->createPriceGroup(
			array(
				'name' => $name,
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $name . '")]');
	}
}
