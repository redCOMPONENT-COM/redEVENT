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
	public function createVenueNew($nameVanue, $nameVanueCategory)
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
	 * @param $nameVenue
	 * @param $nameVenueCategory
	 * @param $descriptionVenueCategory
	 * @param $country
	 * @param $viewOnMap
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function createVenueWithCountry($nameVenue, $nameVenueCategory, $descriptionVenueCategory, $country, $viewOnMap)
	{
		$I = $this;
		$I->createVenueCategoryNew(
			array(
				'name' => $nameVenueCategory,
				'description' => $descriptionVenueCategory
			)
		);

		$I->amOnPage(VanueManagerPage::$URL);
		$I->waitForText(VanueManagerPage::$venueTitle, 30);
		$I->click(VanueManagerPage::$buttonNew);
		$I->waitForText(VanueManagerPage::$venueTitleNew, 30);
		$I->fillField(VanueManagerPage::$fieldName, $nameVenue);
		$I->waitForElement(VanueManagerPage::$categoryVanueSelect, 30);
		$I->selectOptionInChosenByIdUsingJs(VanueManagerPage::$categoryVanueItem, $nameVenueCategory);
		$I->waitForElementVisible(VanueManagerPage::$addressTab, 30);
		$I->click(VanueManagerPage::$addressTab);
		$I->selectOptionInChosenByIdUsingJs(VanueManagerPage::$countryId, $country);
		$I->selectOptionInChosenByIdUsingJs(VanueManagerPage::$viewOnMapId, $viewOnMap);
		$I->click(VanueManagerPage::$buttonSaveClose);
	}

	/**
	 * @param $nameVanue
	 * @throws \Exception
	 */
	public function searchVanue($nameVanue)
	{
		$I = $this;
		$I->search(VanueManagerPage::$URL, $nameVanue);
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
	 * @param $menuitem
	 * @param $eventName
	 * @param $venueName
	 * @param $categoryName
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function checkVenueEventsTableLayout($menuitem, $eventName, $venueName, $categoryName)
	{
		$I = $this;
		$I->doFrontEndLogin("admin", "admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForText($eventName, 30);
		$I->waitForText($venueName, 30);
		$I->waitForText($categoryName, 30);
	}

	/**
	 * @param $menuItem
	 * @param $country
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function checkVenuesMap($menuItem, $country)
	{
		$I = $this;
		$I->doFrontEndLogin();
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuItem, 30);
		$I->click($menuItem);
		$I->waitForElementClickable(FrontendJoomlaManagerPage::$dismissButton, 30);
		$I->click(FrontendJoomlaManagerPage::$dismissButton);
		$I->waitForElement(FrontendJoomlaManagerPage::returnVenuesMap($country));
	}

	/**
	 * @param $nameVenueCategory
	 * @param $nameVenue
	 * @throws \Exception
	 */
	public function deleteVenue($nameVenueCategory, $nameVenue)
	{
		$I = $this;
		$I->delete(VanueManagerPage::$URL,VanueManagerPage::$venueTitle, $nameVenue);
		$I->wantToTest('Delete Venue and Venue Category in redEVENT');
		$I->deleteVenueCategory($nameVenueCategory);
	}
}
