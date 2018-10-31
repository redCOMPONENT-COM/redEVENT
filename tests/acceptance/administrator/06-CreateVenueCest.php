<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class AddAVenueCest
{
	public function addVenue(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add a venue in redEVENT');
		$I->doAdministratorLogin();
		$I->createVenue(
			array(
				'name' => 'Venue 1',
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "Venue 1")]');
	}
}
