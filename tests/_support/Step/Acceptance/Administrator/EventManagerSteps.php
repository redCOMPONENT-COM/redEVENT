<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use \Page\Acceptance\Administrator\EventManagerPage;

class EventManagerSteps  extends CategoryManagerSteps
{
	/**
	 * @param $nameEvent
	 * @param $nameCategory
	 * @throws \Exception
	 */
	public function createEventNew($nameEvent,$nameCategory,$templateName)
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
	 * @throws \Exception
	 */
	public function createEventRegistrations($nameEvent,$nameCategory,$templateName)
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
	 * @param $nameEvent
	 * @throws \Exception
	 */
	public function searchEvent($nameEvent)
	{
		$I = $this;
		$I->search(EventManagerPage::$URL,$nameEvent);
	}

	/**
	 * @param $nameEvent
	 * @throws \Exception
	 */
	public function deleteEvent($nameEvent)
	{
		$I = $this;
		$I->delete(EventManagerPage::$URL,EventManagerPage::$eventTitle,$nameEvent);
	}
}