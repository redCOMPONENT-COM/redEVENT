<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class AddAnEventCest
{
	public function addEvent(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add an event in redEVENT');
		$I->doAdministratorLogin();
		$I->createEvent(
			array(
				'name' => 'Event 1',
				'description' => '<p>The description goes here</p>',
				'template' => 'default template'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "Event 1")]');
	}
}
