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
		$I->wantToTest('Add an event in redEVENT with created template');
		$I->doAdministratorLogin();
		$name = 'Event 1';
		$I->createEvent(
			array(
				'name' => $name,
				'description' => '<p>The description goes here</p>',
				'template_name' => 'Template 1'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $name . '")]');
	}

	public function addEventWithDefaultTemplate(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add an event in redEVENT with default template');
		$I->doAdministratorLogin();
		$name = 'Event default template';
		$I->createEvent(
			array(
				'name' => $name,
				'description' => '<p>The description goes here</p>',
				'template_name' => 'default template'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $name . '")]');
	}
}
