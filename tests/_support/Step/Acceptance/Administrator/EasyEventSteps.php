<?php
/**
 * @package     redEVENT
 * @subpackage  Steps
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Exception;
use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;
use Page\Acceptance\Administrator\EasyEventPage;
use Page\Acceptance\Administrator\SessionManagerPage;
use Step\Acceptance\AdminRedevent;

/**
 * Class EasyEventSteps
 * @package Step\Acceptance\Administrator
 * @since 3.2.9
 */
class EasyEventSteps extends AdminRedevent
{
	/**
	 * @param $menuItem
	 * @param $event
	 * @param $category
	 * @param $venue
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function createEasyEvent($menuItem, $event, $category, $venue)
	{
		$I = $this;
		$I->doFrontEndLogin("admin","admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title,30,FrontendJoomlaManagerPage::$H1);
		$I->waitForText($menuItem,30);
		$I->click($menuItem);
		$I->waitForText(EasyEventPage::$manageSessionTitle, 30);
		$I->waitForElement(EasyEventPage::$eventTitle, 30);
		$I->fillField(EasyEventPage::$eventTitle, $event);
		$I->selectOptionInChosenByIdUsingJs(EasyEventPage::$category, $category);
		$I->selectOptionInChosenByIdUsingJs(EasyEventPage::$venueId, $venue);
		$dateNow = date('Y-m-d');
		$I->waitForElement(SessionManagerPage::$fieldDate,30);
		$I->fillField(SessionManagerPage::$fieldDate, $dateNow);
		$I->waitForElement(SessionManagerPage::$endDate,30);
		$I->fillField(SessionManagerPage::$endDate, $dateNow);
		$I->click(SessionManagerPage::$buttonSave);
		$I->waitForText(FrontendJoomlaManagerPage::$messageSaveSessionSuccess, 30, SessionManagerPage::$message);
	}
}