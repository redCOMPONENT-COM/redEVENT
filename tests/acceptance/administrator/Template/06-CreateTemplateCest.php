<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CreateTemplateCest
{
	public function addTemplate(\Step\Acceptance\Adminredevent $I)
	{
		$I->wantToTest('Add a template in redEVENT');
		$I->doAdministratorLogin();
		$I->createMinimalRegistrationForm(['name' => 'Registration']);
		$name = "Template 1";
		$I->createTemplate(
			array(
				'name' => $name,
				'meta_description' => 'This is the meta description of the event [event_title], session at [venue]',
				'meta_keywords' => 'some keywords, [event_title], [venue]',
				'redform' => 'Registration'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $name . '")]');
	}
}
