<?php
/**
 * @package     redEVENT
 * @subpackage  Steps
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\AbstractPage;
use \Page\Acceptance\Administrator\EventManagerPage;
use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;

class EventManagerSteps  extends CategoryManagerSteps
{
	/**
	 * @param $nameEvent
	 * @param $nameCategory
	 * @param $templateName
	 * @throws \Exception
	 */
	public function createEventNew($nameEvent, $nameCategory, $templateName)
	{
		$I = $this;
		$I ->createCategoryNew(array(
			'name' => $nameCategory,
			'description' => '<p>The description goes here</p>'
		));
		$I->amOnPage(EventManagerPage::$URL);
		$I->waitForText(EventManagerPage::$eventTitle, 30);
		$I->click(EventManagerPage::$buttonNew);
		$I->waitForText(EventManagerPage::$eventTitleNew, 30);
		$I->fillField(EventManagerPage::$fieldName, $nameEvent);
		$I->waitForElement(EventManagerPage::$categorySelect, 30);
		$I->selectOptionInChosenByIdUsingJs(EventManagerPage::$categoryItem, $nameCategory);
		$I->waitForElement(EventManagerPage::$templateSelect, 30);
		$I->selectOptionInChosenByIdUsingJs(EventManagerPage::$templateItem, $templateName);
		$I->click(EventManagerPage::$buttonSaveClose);
	}

	/**
	 * @param $nameEvent
	 * @param $nameCategory
	 * @param $descriptionCategory
	 * @param $templateName
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function createEventWithImage($nameEvent, $nameCategory, $descriptionCategory, $templateName)
	{
		$I = $this;
		$I ->createCategoryNew(array(
			'name' => $nameCategory,
			'description' => $descriptionCategory
		));

		$I->amOnPage(EventManagerPage::$URL);
		$I->waitForText(EventManagerPage::$eventTitle, 30);
		$I->click(EventManagerPage::$buttonNew);
		$I->waitForText(EventManagerPage::$eventTitleNew, 30);
		$I->fillField(EventManagerPage::$fieldName, $nameEvent);
		$I->waitForElement(EventManagerPage::$categorySelect, 30);
		$I->selectOptionInChosenByIdUsingJs(EventManagerPage::$categoryItem, $nameCategory);
		$I->waitForElement(EventManagerPage::$templateSelect, 30);
		$I->selectOptionInChosenByIdUsingJs(EventManagerPage::$templateItem, $templateName);
		$I->click(EventManagerPage::$chooseImage);
		//This is where I have problems.
		$I->fillField(FrontendJoomlaManagerPage::$imageUrlId, FrontendJoomlaManagerPage::$joomlaImage);
		$I->click(EventManagerPage::$insertButton);
		//
		$I->waitForText(EventManagerPage::$eventTitleNew, 30);
		$I->click(EventManagerPage::$buttonSaveClose);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param $nameEvent
	 * @param $nameCategory
	 * @param $templateName
	 * @throws \Exception
	 */
	public function createEventRegistrations($nameEvent, $nameCategory, $templateName)
	{
		$I = $this;
		$I ->createCategoryNew(array(
			'name' => $nameCategory,
			'description' => '<p>The description goes here</p>'
		));
		$I->amOnPage(EventManagerPage::$URL);
		$I->waitForText(EventManagerPage::$eventTitle, 30);
		$I->click(EventManagerPage::$buttonNew);
		$I->waitForText(EventManagerPage::$eventTitleNew, 30);
		$I->fillField(EventManagerPage::$fieldName, $nameEvent);
		$I->waitForElement(EventManagerPage::$categorySelect, 30);
		$I->selectOptionInChosenByIdUsingJs(EventManagerPage::$categoryItem, $nameCategory);
		$I->waitForElement(EventManagerPage::$templateSelect, 30);
		$I->selectOptionInChosenByIdUsingJs(EventManagerPage::$templateItem, $templateName);
		$I->click(EventManagerPage::$tabRegistration);
		$I->waitForElement(EventManagerPage::$enableRegistrationYes,30);
		$I->click(EventManagerPage::$enableRegistrationYes);
		$I->click(EventManagerPage::$buttonSaveClose);
	}

	/**
	 * @param $menuitem
	 * @param $eventName
	 * @param $categoryName
	 * @param $venueName
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function checkEvents($menuitem, $eventName, $categoryName, $venueName)
	{
		$I = $this;
		$I->doFrontEndLogin("admin", "admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForText(EventManagerPage::$managedSessions, 30);
		$I->waitForText(EventManagerPage::$managedEvents, 30);
		$I->waitForText(EventManagerPage::$managedVenues, 30);
		$I->waitForText($eventName, 30);
		$I->waitForText($categoryName, 30);
		$I->waitForText($venueName, 30);
	}

	/**
	 * @param $menuitem
	 * @param $eventName
	 * @param $venueName
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function checkEventsThumbnails($menuitem, $eventName, $venueName)
	{
		$I = $this;
		$I->doFrontEndLogin("admin", "admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForElement(FrontendJoomlaManagerPage::$eventImageThumbnails, 30);
		$I->waitForText($eventName, 30);
		$I->waitForText($venueName, 30);
	}

	/**
	 * @param $nameEvent
	 * @throws \Exception
	 */
	public function searchEvent($nameEvent)
	{
		$I = $this;
		$I->search(EventManagerPage::$URL, $nameEvent);
	}

	/**
	 * @param $nameEvent
	 * @throws \Exception
	 */
	public function deleteEvent($nameEvent)
	{
		$I = $this;
		$I->delete(EventManagerPage::$URL,EventManagerPage::$eventTitle, $nameEvent);
	}
}