<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class AddAVenueCategoryCest
{
	public function addVenueCategory(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add a venue category in redEVENT');
		$I->doAdministratorLogin();
		$I->createVenueCategory(
			array(
				'name' => 'Venue Category 1',
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "Venue Category 1")]');
	}
}
