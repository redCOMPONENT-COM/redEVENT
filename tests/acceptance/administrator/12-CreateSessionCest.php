<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CreateSessionCest
{
	public function createSession(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add an open date session in redEVENT');
		$I->doAdministratorLogin();
		$event = 'Event 1';
		$venue = 'Venue 1';
		$I->createSession(
			array(
				'event' => $event,
				'venue' => $venue
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $event . '")]');
		$I->see("Open date", '//*[@id="table-items"]//td//*[contains(., "' . $event . '")]/ancestor::tr/td[5]');
	}
}
