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
	 * @param $sessionname
	 * @param $eventName
	 * @throws \Exception
	 */
	public  function  checkEventUpcoming($menuitem, $sessionname,$eventName,$venues)
	{
		$I = $this;
		$I->doFrontEndLogin("admin","admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title,30,AbstractPage::$H1);
		$I->waitForText($menuitem,30);
		$I->click($menuitem);
		$I->see($eventName);
		$I->see($sessionname);
	}

	/**
	 * @param $menuItem
	 * @param $sessioName
	 * @param $eventName
	 * @param $venues
	 * @throws \Exception
	 */
	public  function  checkViewEventWithCalendar($menuItem, $sessioName,$eventName,$venues)
	{
		$I = $this;
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title,30,AbstractPage::$H1);
		$I->waitForText($menuItem,30);
		$I->click($menuItem);
		$I->waitForText($eventName,30);
		$I->click($eventName);
		$I->waitForText($sessioName,30);
		$I->waitForText($venues,30);
	}
}