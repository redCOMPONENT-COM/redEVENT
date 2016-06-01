<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CreateCustomFieldsCest
{
	public function addEventTextField(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add an event custom text field in redEVENT');
		$I->doAdministratorLogin();
		$name = 'some text';
		$I->createCustomField(
			array(
				'name' => $name,
				'object' => 'event',
				'type' => 'Text'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "some text")]');
		$I->see("Event", '//*[@id="table-items"]//td//*[contains(., "' .$name . '")]/ancestor::tr/td[8]');
		$I->see("text", '//*[@id="table-items"]//td//*[contains(., "' .$name . '")]/ancestor::tr/td[9]');
	}

	public function addSessionTextField(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add an session custom text field in redEVENT');
		$I->doAdministratorLogin();
		$name = 'some text for session';
		$I->createCustomField(
			array(
				'name' => $name,
				'object' => 'Session',
				'type' => 'Text'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "some text for session")]');
		$I->see("Session", '//*[@id="table-items"]//td//*[contains(., "' .$name . '")]/ancestor::tr/td[8]');
		$I->see("text", '//*[@id="table-items"]//td//*[contains(., "' .$name . '")]/ancestor::tr/td[9]');
	}
}
