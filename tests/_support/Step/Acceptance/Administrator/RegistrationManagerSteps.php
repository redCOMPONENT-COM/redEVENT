<?php

/**
 * @package     redEVENT
 * @subpackage  Steps
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;
use Page\Acceptance\Administrator\RegistrationManagerPage;
use Step\Acceptance\AdminRedevent;

class RegistrationManagerSteps extends AdminRedevent
{
	/**
	 * @param $menuitem
	 * @param $sessionname
	 * @param $eventName
	 * @param $nameUser
	 * @param $emailUser
	 * @param $placeholderUser
	 * @param $placeholderEmail
	 * @throws \Exception
	 */
	public  function  checkRegistrationEvents($menuitem, $sessionname,$eventName,$nameUser, $emailUser,$placeholderUser, $placeholderEmail)
	{
		$I = $this;
		$I->amOnPage(FrontendJoomlaManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(FrontendJoomlaManagerPage::$title,30,FrontendJoomlaManagerPage::$H1);
		$I->waitForText($menuitem,30);
		$I->click($menuitem);
		$I->see($eventName);
		$I->click(FrontendJoomlaManagerPage::returnLink($eventName, $sessionname));
		$I->waitForElement(FrontendJoomlaManagerPage::$imagesRegistration,30);
		$I->click(FrontendJoomlaManagerPage::$imagesRegistration);
		$I->fillField(FrontendJoomlaManagerPage:: returnInput($placeholderUser),$nameUser);
		$I->fillField(FrontendJoomlaManagerPage:: returnInput($placeholderEmail),$emailUser);
		$I->click(FrontendJoomlaManagerPage:: $submit);
	}

	/**
	 * @param $nameEvent
	 * @param $nameUser
	 * @param $email
	 * @throws \Exception
	 */
	public  function  checkRegistrationBackend($nameEvent, $nameUser, $email)
	{
		$I = $this;
		$I->amOnPage(RegistrationManagerPage::$URL);
		$I->checkForPhpNoticesOrWarningsOrExceptions();
		$I->waitForText(RegistrationManagerPage::$title,30,RegistrationManagerPage::$H1);
		$I->click($nameEvent);
		$I->waitForText(RegistrationManagerPage::$titleAttendees,30, RegistrationManagerPage::$H1);
		$I->checkAllResults();
		$I->click(RegistrationManagerPage::$buttonEdit);
		$I->scrollTo(RegistrationManagerPage::$xpathAnswers);
		$I->waitForElement(RegistrationManagerPage:: returnValue($nameUser));
		$I->waitForElement(RegistrationManagerPage:: returnValue($email));
	}
}