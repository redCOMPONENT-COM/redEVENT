<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use \Page\Acceptance\Administrator\VanueManagerPage;
class VanueManagerSteps extends VenueCategoryManagerSteps
{
	/**
	 * @param $nameVanue
	 * @param $nameVanueCategory
	 * @throws \Exception
	 */
	public function createVenueNew($nameVanue,$nameVanueCategory)
	{
		$I = $this;
		$I->createVenueCategoryNew(
			array(
				'name' => $nameVanueCategory,
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->amOnPage(VanueManagerPage::$URL);
		$I->waitForText(VanueManagerPage::$venueTitle, 30);
		$I->click(VanueManagerPage::$buttonNew);
		$I->waitForText(VanueManagerPage::$venueTitleNew, 30);
		$I->fillField(VanueManagerPage::$fieldName, $nameVanue);
		$I->waitForElement(VanueManagerPage::$categoryVanueSelect, 30);
		$I->selectOptionInChosenByIdUsingJs(VanueManagerPage::$categoryVanueItem, $nameVanueCategory);
		$I->click(VanueManagerPage::$buttonSaveClose);
	}

	/**
	 * @param $nameVanue
	 * @throws \Exception
	 */
	public function searchVanue($nameVanue)
	{
		$I = $this;
		$I->search(VanueManagerPage::$URL,$nameVanue);
	}

	/**
	 * @param $nameVenueCategory
	 * @param $nameVanue
	 * @throws \Exception
	 */
	public function deleteVenue($nameVenueCategory,$nameVanue)
	{
		$I = $this;
		$I->delete(VanueManagerPage::$URL,VanueManagerPage::$venueTitle,$nameVanue);
		$I->wantToTest('Delete Vanue  in redEVENT');
		$I->deleteVenueCategory($nameVenueCategory);
	}
}
