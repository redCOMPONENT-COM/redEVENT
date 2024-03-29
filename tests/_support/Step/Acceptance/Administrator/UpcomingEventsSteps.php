<?php
/**
 * @package     redEVENT
 * @subpackage  Steps
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\AbstractPage;
use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;
use Step\Acceptance\AdminRedevent;

class UpcomingEventsSteps extends AdminRedevent
{
	/**
	 * @param $menuitem
	 * @param $sessionName
	 * @param $eventName
	 * @throws \Exception
	 */
	public  function  checkEventUpcoming($menuitem, $sessionName, $eventName)
	{
		$I = $this;
		$I->doFrontEndLogin("admin","admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->see($eventName);
		$I->see($sessionName);
	}

	/**
	 * @param $menuItem
	 * @param $sessionName
	 * @param $eventName
	 * @param $venues
	 * @throws \Exception
	 */
	public  function  checkViewEventWithCalendar($menuItem, $sessionName, $eventName, $venues)
	{
		$I = $this;
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuItem, 30);
		$I->click($menuItem);
		$I->waitForElement(FrontendJoomlaManagerPage::$calendar, 30);
		$I->waitForText($eventName, 30, FrontendJoomlaManagerPage::$calendarToday);
		$I->click($eventName);
		$I->waitForText($sessionName,30);
		$I->waitForText($venues,30);
	}

	/**
	 * @param $menuitem
	 * @param $sessionName
	 * @param $eventName
	 * @param $venues
	 * @param $category
	 * @throws \Exception
	 */
	public  function  checkEventOnTable($menuitem, $sessionName, $eventName, $venues, $category)
	{
		$I = $this;
		$I->doFrontEndLogin("admin","admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForElement(FrontendJoomlaManagerPage::$tableCategoryEvent, 30);
		$I->waitForText($eventName, 30);
		$I->click(FrontendJoomlaManagerPage::returnLink($eventName, $sessionName));
		$I->waitForText(FrontendJoomlaManagerPage::returnLink($eventName, $sessionName), 30, FrontendJoomlaManagerPage::$titleEvent);
		$I->waitForText($venues, 30, FrontendJoomlaManagerPage::$whereEvent);
		$I->waitForText($category, 30, FrontendJoomlaManagerPage::$categoryEvent);
	}

	/**
	 * @param $menuitem
	 * @param $sessionName
	 * @param $eventName
	 * @param $venues
	 * @param $category
	 * @throws \Exception
	 */
	public  function  checkEventArchive($menuitem, $sessionName, $eventName, $venues, $category)
	{
		$I = $this;
		$I->doFrontEndLogin("admin","admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForElement(FrontendJoomlaManagerPage::$eventList, 30);
		$I->waitForText($eventName, 30);
		$I->click(FrontendJoomlaManagerPage::returnLink($eventName, $sessionName));
		$I->waitForText(FrontendJoomlaManagerPage::returnLink($eventName, $sessionName),30, FrontendJoomlaManagerPage::$titleEvent);
		$I->waitForText($venues, 30, FrontendJoomlaManagerPage::$whereEvent);
		$I->waitForText($category, 30, FrontendJoomlaManagerPage::$categoryEvent);
	}

	/**
	 * @param $menuitem
	 * @param $sessionName
	 * @param $eventName
	 * @param $venues
	 * @param $category
	 * @throws \Exception
	 */
	public function searchEventOnFrontEnd($menuitem, $sessionName, $eventName, $venues, $category)
	{
		$I = $this;
		$I->doFrontEndLogin("admin","admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForText(FrontendJoomlaManagerPage::$titleSearchEvent, 30);
		$I->waitForElement(FrontendJoomlaManagerPage::$tableSearch, 30);
		$I->waitForElement(FrontendJoomlaManagerPage::$fieldSearchFrontEnd, 30);
		$I->fillField(FrontendJoomlaManagerPage::$fieldSearchFrontEnd, $eventName);
		$I->click(FrontendJoomlaManagerPage::$buttonSearchFrontEnd);
		$I->waitForText(FrontendJoomlaManagerPage::returnLink($eventName, $sessionName), 30, FrontendJoomlaManagerPage::$titleOnTable);
		$I->click(FrontendJoomlaManagerPage::returnLink($eventName, $sessionName));
		$I->waitForText(FrontendJoomlaManagerPage::returnLink($eventName, $sessionName), 30, FrontendJoomlaManagerPage::$titleEvent);
		$I->waitForText($venues, 30, FrontendJoomlaManagerPage::$whereEvent);
		$I->waitForText($category, 30, FrontendJoomlaManagerPage::$categoryEvent);
	}
}