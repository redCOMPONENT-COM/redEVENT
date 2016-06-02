<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CreateTagCest
{
	public function addTag(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add a tag in redEVENT');
		$I->doAdministratorLogin();
		$I->createTag(
			array(
				'name' => 'a_tag',
				'description' => 'short tag description',
				'text' => '<p>the tag content goes here</p>'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "a_tag")]');
	}
}
