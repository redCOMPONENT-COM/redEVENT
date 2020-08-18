<?php
/**
 * @package     redEVENT
 * @subpackage  Steps
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Exception;
use Page\Acceptance\Administrator\AbstractPage;
use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;
use Step\Acceptance\AdminRedevent;

/**
 * Class DaySteps
 * @package Step\Acceptance\Administrator
 * @since 3.2.9
 */
class DaySteps extends AdminRedevent
{
	/**
	 * @param $menuitem
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function checkDayMissing($menuitem)
	{
		$I = $this;
		$I->doFrontEndLogin("admin", "admin");
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title, 30, AbstractPage::$H1);
		$I->waitForText($menuitem, 30);
		$I->click($menuitem);
		$I->waitForText(FrontendJoomlaManagerPage::$dayMissing, 30);
	}

	/**
	 * @param $menuitem
	 * @param $eventName
	 * @param $venueName
	 * @param $categoryName
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function checkDay($menuitem, $eventName, $venueName, $categoryName)
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
}