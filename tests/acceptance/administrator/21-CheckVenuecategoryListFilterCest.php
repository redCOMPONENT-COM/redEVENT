<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CheckVenuecategoryListFilterCest
{
	public function checkSearchFilterCest(\Step\Acceptance\AdminRedevent $I)
	{
		$I->doAdministratorLogin();

		$I->wantToTest(' that the search filter works');

		$name = uniqid("venuecategory ");

		$I->createVenuecategory(
			array(
				'name' => $name,
				'description' => '<p>The description goes here</p>'
			)
		);

		$second = uniqid("filtered ");

		$I->createVenuecategory(
			array(
				'name' => $second,
				'description' => '<p>The description goes here</p>'
			)
		);

		$I->fillField('#filter_search', $name);
		$I->click(['xpath' => "//button[@type='submit' and @data-original-title='Search']"]);
		$I->waitForElement(['id' => 'table-items']);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $name . '")]');
		$I->dontSeeElement('//*[@id="table-items"]//td//*[contains(., "' . $second . '")]');

		$I->wantToTest(' that the reset button works');
		$I->click(['xpath' => "//button[@type='button' and @data-original-title='Clear']"]);
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $name . '")]');
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $second . '")]');
	}
}
