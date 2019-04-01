<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
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
	public function createSessionNew($event,$venue,$nameSession)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL );
		$I->waitForText(SessionManagerPage:: $sessionTitle, 30);
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
	public function createSessionUpcomming($event,$venue,$nameSession)
	{
		$I = $this;
		$I->amOnPage(SessionManagerPage::$URL );
		$I->waitForText(SessionManagerPage:: $sessionTitle, 30);
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
	 * @param $nameSession
	 * @throws \Exception
	 */
	public function searchSession($nameSession)
	{
		$I = $this;
		$I->search(SessionManagerPage::$URL,$nameSession);
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
		$I->waitForText(SessionManagerPage::$messageDeleteProductSuccess, 60, SessionManagerPage::$message);
		$I->dontSee($nameSession);
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
        $client->wantTo('Test with delete category but then cancel');
        $client->cancelPopup();
        $client->wantTo('Test with delete product then accept');
        $client->click(SessionManagerPage::$buttonDelete);
        $client->acceptPopup();
        $client->waitForElement(SessionManagerPage::$message, 30);
    }
}