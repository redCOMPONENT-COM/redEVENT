<?php

/**
 * @package     redEVENT
 * @subpackage  Steps
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Step\Acceptance;

use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;
use Page\Acceptance\Administrator\VanueManagerPage;

class FrontEndManagerSteps extends AdminRedevent
{
	/**
	 * @param $menuitem
	 * @param $sessionname
	 * @param $eventName
	 * @param $venues
	 * @param $category
	 * @throws \Exception
	 */
	public  function  checkEventUpcomingOfVenue($menuitem, $sessionname, $eventName, $venues, $category)
	{
		$I = $this;
		$usepage = new FrontendJoomlaManagerPage();
		$I->amOnPage($usepage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText($usepage::$title, 30, $usepage::$H1);
		$I->waitForText($menuitem, 30);

		$I->click($menuitem);
		$I->waitForText($eventName, 30);
		$I->click($usepage::returnLink($eventName, $sessionname));
		$I->waitForText($usepage::returnLink($eventName, $sessionname), 30, $usepage:: $titleEvent);
		$I->waitForText($venues, 30, $usepage::$whereEvent);
		$I->waitForText($category, 30, $usepage::$categoryEvent);
	}

	/**
	 * @param $menuitem
	 * @param $sessionname
	 * @param $eventName
	 * @param $venues
	 * @param $category
	 * @param $sessionname1
	 * @param $eventName1
	 * @throws \Exception
	 */
	public  function checkEventOfCategoryVenue ($menuitem, $sessionname, $eventName, $venues, $category, $sessionname1, $eventName1)
	{
		$I = $this;
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title,30,FrontendJoomlaManagerPage::$H1);
		$I->waitForText($menuitem,30);
		$I->click($menuitem);
		$I->waitForText(FrontendJoomlaManagerPage::returnLink($eventName, $sessionname),30);
		$I->dontSee(FrontendJoomlaManagerPage::returnLink($eventName1, $sessionname1));
		$I->click(FrontendJoomlaManagerPage::returnLink($eventName, $sessionname));
		$I->waitForText(FrontendJoomlaManagerPage::returnLink($eventName, $sessionname), 30, FrontendJoomlaManagerPage:: $titleEvent);
		$I->waitForText($venues, 30, FrontendJoomlaManagerPage::$whereEvent);
		$I->waitForText($category, 30, FrontendJoomlaManagerPage::$categoryEvent);
	}

	/**
	 * @param $menuItem
	 * @param $sessionName
	 * @param $eventName
	 * @param $venues
	 * @param $category
	 * @throws \Exception
	 */
	public  function checkEventOnFrontEnd($menuItem, $sessionName, $eventName, $venues, $category)
	{
		$I = $this;
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, FrontendJoomlaManagerPage::$H1);
		$I->waitForText($menuItem, 30);
		$I->click($menuItem);
		$I->waitForElement(FrontendJoomlaManagerPage::$event, 30);
		$I->waitForText(FrontendJoomlaManagerPage::returnLink($eventName, $sessionName), 30, FrontendJoomlaManagerPage:: $titleEvent);
		$I->waitForText($venues, 30, FrontendJoomlaManagerPage::$whereEvent);
		$I->waitForText($category, 30, FrontendJoomlaManagerPage::$categoryEvent);
	}

	/**
	 * @param $menuitem
	 * @param $venuename
	 * @param $category
	 * @throws \Exception
	 * @since 3.2.7
	 */
	public function checkVenueSubmission($menuitem, $venuename, $category)
	{
		$I = $this;
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, FrontendJoomlaManagerPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForElementVisible(FrontendJoomlaManagerPage::$componentTitle, 30);
		$I->fillField(VanueManagerPage::$fieldName, $venuename);
		$I->click(VanueManagerPage::$categoryVanueSelect);
		$I->click(FrontendJoomlaManagerPage::xPathCategoryVenues($category));
		$I->click(FrontendJoomlaManagerPage::$buttonSave);
		$I->waitForElementVisible(FrontendJoomlaManagerPage::$messageSuccess, 30);
		$I->waitForText(FrontendJoomlaManagerPage::$messageSaveSessionSuccess, 5, FrontendJoomlaManagerPage::$messageSuccess);
	}
}