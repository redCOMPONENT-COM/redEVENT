<?php
/**
 * @package     redEVENT
 * @subpackage  Steps
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use Page\Acceptance\Administrator\AbstractPage;
use \Page\Acceptance\Administrator\VanueManagerPage;
use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;

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
	 * @param $menuitem
	 * @param $categoryName
	 * @param $venueName
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function checkVenues($menuitem, $categoryName, $venueName)
	{
		$I = $this;
		$I->doFrontEndLogin("admin", "admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForText($venueName, 30);
		$I->click($venueName);
		$I->waitForText($categoryName, 30);
		$I->waitForText($venueName, 30);
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
