<?php
/**
 * @package     Redevent
 * @subpackage  Steps
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use Page\Acceptance\Administrator\AbstractPage;
use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;
use Page\Acceptance\Administrator\SessionManagerPage;
use Step\Acceptance\AdminRedevent;

class SessionManagerSteps extends AdminRedevent
{
	/**
	 * @param $event
	 * @param $venue
	 * @param $nameSession
	 * @throws \Exception
	 */
	public function createSessionNew($event, $venue, $nameSession)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL);
		$I->waitForText(SessionManagerPage::$sessionTitle, 30);
		$I->click(SessionManagerPage::$buttonNew);
		$I->waitForText(SessionManagerPage::$sessionTitleNew, 30);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$eventSelect, $event);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$venueSelect, $venue);

		if (!empty($nameSession))
		{
			$I->fillField(SessionManagerPage::$fieldName, $nameSession);
		}

		$I->click(SessionManagerPage::$buttonSave);
	}

	/**
	 * @param $event
	 * @param $venue
	 * @param $nameSession
	 * @throws \Exception
	 */
	public function createSessionUpcomming($event, $venue, $nameSession)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL);
		$I->waitForText(SessionManagerPage::$sessionTitle, 30);
		$I->click(SessionManagerPage::$buttonNew);
		$I->waitForText(SessionManagerPage::$sessionTitleNew, 30);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$eventSelect, $event);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$venueSelect, $venue);
		$dateNow = date('Y-m-d');
		$date  = date('Y-m-d', strtotime('+1 day', strtotime($dateNow)));
		$endDate  = date('Y-m-d', strtotime('+2 day', strtotime($dateNow)));
		$I->waitForElement(SessionManagerPage::$fieldDate,30);
		$I->fillField(SessionManagerPage::$fieldDate, $date);
		$I->waitForElement(SessionManagerPage::$endDate,30);
		$I->fillField(SessionManagerPage::$endDate, $endDate);

		if (!empty($nameSession))
		{
			$I->fillField(SessionManagerPage::$fieldName, $nameSession);
		}

		$I->click(SessionManagerPage::$buttonSave);
	}

	/**
	 * @param $event
	 * @param $venue
	 * @param $nameSession
	 * @throws \Exception
	 */
	public function createSessionForEvents($event, $venue, $nameSession)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL);
		$I->waitForText(SessionManagerPage::$sessionTitle, 30);
		$I->click(SessionManagerPage::$buttonNew);
		$I->waitForText(SessionManagerPage::$sessionTitleNew, 30);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$eventSelect, $event);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$venueSelect, $venue);
		$dateNow = date('Y-m-d');
		$I->waitForElement(SessionManagerPage::$fieldDate,30);
		$I->fillField(SessionManagerPage::$fieldDate, $dateNow);
		$I->waitForElement(SessionManagerPage::$endDate,30);
		$I->fillField(SessionManagerPage::$endDate, $dateNow);

		if (!empty($nameSession))
		{
			$I->fillField(SessionManagerPage::$fieldName, $nameSession);
		}

		$I->click(SessionManagerPage::$buttonSave);
	}

	/**
	 * @param $event
	 * @param $venue
	 * @param $nameSession
	 * @param $featured
	 * @throws \Exception
	 */
	public function createSessionOfFeaturedEvents($event, $venue, $nameSession, $featured)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL);
		$I->waitForText(SessionManagerPage::$sessionTitle, 30);
		$I->click(SessionManagerPage::$buttonNew);
		$I->waitForText(SessionManagerPage::$sessionTitleNew, 30);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$eventSelect, $event);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$venueSelect, $venue);
		$dateNow = date('Y-m-d');
		$date  = date('Y-m-d', strtotime('+1 day', strtotime($dateNow)));
		$endDate  = date('Y-m-d', strtotime('+2 day', strtotime($dateNow)));
		$I->waitForElement(SessionManagerPage::$fieldDate,30);
		$I->fillField(SessionManagerPage::$fieldDate, $date);
		$I->waitForElement(SessionManagerPage::$endDate,30);
		$I->fillField(SessionManagerPage::$endDate, $endDate);

		if (!empty($nameSession))
		{
			$I->fillField(SessionManagerPage::$fieldName, $nameSession);
		}

		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$featuredSelect, $featured);
		$I->click(SessionManagerPage::$buttonSave);
	}

	/**
	 * @param $event
	 * @param $venue
	 * @param $nameSession
	 * @param $status
	 * @throws \Exception
	 */
	public function createSessionHaveStatus($event, $venue, $nameSession, $status)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL);
		$I->waitForText(SessionManagerPage::$sessionTitle, 30);
		$I->click(SessionManagerPage::$buttonNew);
		$I->waitForText(SessionManagerPage::$sessionTitleNew, 30);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$eventSelect, $event);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$venueSelect, $venue);
		$dateNow = date('Y-m-d');
		$date  = date('Y-m-d', strtotime('+1 day', strtotime($dateNow)));
		$endDate  = date('Y-m-d', strtotime('+2 day', strtotime($dateNow)));
		$I->waitForElement(SessionManagerPage::$fieldDate,30);
		$I->fillField(SessionManagerPage::$fieldDate, $date);
		$I->waitForElement(SessionManagerPage::$endDate,30);
		$I->fillField(SessionManagerPage::$endDate, $endDate);

		if (!empty($nameSession))
		{
			$I->fillField(SessionManagerPage::$fieldName, $nameSession);
		}

		switch ($status)
		{
			case 'Published':
				$I->click(SessionManagerPage::$statusPublished);
				break;

			case 'Unpublished':
				$I->click(SessionManagerPage::$statusUnpublished);
				break;

			case 'Archived':
				$I->click(SessionManagerPage::$statusArchived);
				break;
		}

		$I->click(SessionManagerPage::$buttonSave);
		$I->waitForText(SessionManagerPage::$messageSaveSuccess, 30, SessionManagerPage::$message);
	}

	/**
	 * @param $nameSession
	 * @throws \Exception
	 */
	public function searchSession($nameSession)
	{
		$I = $this;
		$I->search(SessionManagerPage::$URL, $nameSession);
	}
	/**
	 * @param $nameSession
	 * @throws \Exception
	 */
	public function deleteSession($nameSession)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL);
		$I->waitForText(SessionManagerPage::$sessionTitle, 30);
		$I->searchSession($nameSession);
		$I->see($nameSession, SessionManagerPage::$tableResult);
		$I->checkAllResults();
		$I->click(SessionManagerPage::$buttonDelete);
		$I->wantTo('Test with delete category but then cancel');
		$I->cancelPopup();
		$I->wantTo('Test with delete session then accept');
		$I->click(SessionManagerPage::$buttonDelete);
		$I->acceptPopup();

		try
		{
			$I->waitForText(SessionManagerPage::$notificationNoItem, 30);
		} catch (\Exception $e)
		{
			$I->reloadPage();
			$I->waitForText($nameSession, 30);
			$I->checkAllResults();
			$I->click(SessionManagerPage::$buttonDelete);
			$I->acceptPopup();
			$I->waitForText(SessionManagerPage::$notificationNoItem, 30);
		}

		$I->dontSee($nameSession);
	}

	/**
	 * @param $nameSession
	 * @throws \Exception
	 */
	public function publishSession($nameSession)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL);
		$I->waitForText(SessionManagerPage::$sessionTitle, 30);
		$I->waitForElementVisible(SessionManagerPage::$searchTools,30);
		$I->click(SessionManagerPage::$searchTools);
		$I->waitForElement(SessionManagerPage::$filterPublished,30);
		$I->waitForElementVisible(SessionManagerPage::$filterPublished,30);
		$I->selectOptionInChosenById(SessionManagerPage::$filterPublishedID,"All");
		$I->fillField(AbstractPage::$fieldSearch, $nameSession);
		$I->click(AbstractPage::$buttonSearch);
		$I->seeElement(AbstractPage::$tableResult);
		$I->wait(0.5);
		$I->checkAllResults();
		$I->click(AbstractPage::$buttonPublish);
		$I->waitForText(SessionManagerPage::$messagePublishSuccess,30, SessionManagerPage::$message);
	}

	/**
	 * @throws \Exception
	 */
	public function deleteAllSession()
	{
		$client = $this;
		$client->amOnPage(SessionManagerPage::$URL);
		$client->waitForText(SessionManagerPage::$sessionTitle, 30);
		$client->checkAllResults();
		$client->click(SessionManagerPage::$buttonDelete);
		$client->wantTo('Test with delete Session but then cancel');
		$client->cancelPopup();
		$client->wantTo('Test with delete Session then accept');
		$client->click(SessionManagerPage::$buttonDelete);
		$client->acceptPopup();
		$client->waitForElement(SessionManagerPage::$message, 30);
	}

	/**
	 * @param $menuItem
	 * @param $event
	 * @param $venue
	 * @param $nameSession
	 * @throws \Exception
	 */
	public function createSessionFrontend($menuItem, $event, $venue, $nameSession)
	{
		$I = $this;
		$I->doFrontEndLogin("admin","admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title,30,FrontendJoomlaManagerPage::$H1);
		$I->waitForText($menuItem,30);
		$I->click($menuItem);
		$I->waitForText(SessionManagerPage::$sessionTitleNew, 30);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$eventSelect, $event);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$venueSelect, $venue);
		$dateNow = date('Y-m-d');
		$I->waitForElement(SessionManagerPage::$fieldDate,30);
		$I->fillField(SessionManagerPage::$fieldDate, $dateNow);
		$I->waitForElement(SessionManagerPage::$endDate,30);
		$I->fillField(SessionManagerPage::$endDate, $dateNow);

		if (!empty($nameSession))
		{
			$I->fillField(SessionManagerPage::$fieldName, $nameSession);
		}

		$I->click(SessionManagerPage::$buttonSave);
		$I->waitForText(FrontendJoomlaManagerPage::$messageSaveSessionSuccess, 30, SessionManagerPage::$message);
	}

	/**
	 * @param $event
	 * @param $venue
	 * @param $nameSession
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function createSessionForDay($event, $venue, $nameSession)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL);
		$I->waitForText(SessionManagerPage::$sessionTitle, 30);
		$I->click(SessionManagerPage::$buttonNew);
		$I->waitForText(SessionManagerPage::$sessionTitleNew, 30);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$eventSelect, $event);
		$I->selectOptionInChosenByIdUsingJs(SessionManagerPage::$venueSelect, $venue);
		$dateNow = date('Y-m-d');
		$date  = date('Y-m-d', strtotime('+0 day', strtotime($dateNow)));
		$endDate  = date('Y-m-d', strtotime('+1 day', strtotime($dateNow)));
		$I->waitForElement(SessionManagerPage::$fieldDate,30);
		$I->fillField(SessionManagerPage::$fieldDate, $date);
		$I->waitForElement(SessionManagerPage::$endDate,30);
		$I->fillField(SessionManagerPage::$endDate, $endDate);

		if (!empty($nameSession))
		{
			$I->fillField(SessionManagerPage::$fieldName, $nameSession);
		}

		$I->click(SessionManagerPage::$buttonSave);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30);
	}

	/**
	 * @param $menuitem
	 * @param $eventName
	 * @param $categoryName
	 * @param $venueName
	 * @throws \Exception
	 * @since 3.2.9
	 */
	public function checkWeekView($menuitem, $eventName, $categoryName, $venueName)
	{
		$I = $this;
		$I->doFrontEndLogin("admin", "admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForText($eventName, 30);
		$I->waitForText($categoryName, 30);
		$I->waitForText($venueName, 30);
		$dateNow = date('d.m.Y');
		$date  = date('d.m.Y', strtotime('+1 day', strtotime($dateNow)));
		$endDate  = date('d.m.Y', strtotime('+2 day', strtotime($dateNow)));
		$I->waitForText($date, 30);
		$I->waitForText($endDate, 30);
	}
}