<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CheckForWarningsAndNoticesCest
{
	public function runChecks(\AcceptanceTester $I)
	{
		$I->wantToTest(' that there are no Warnings or Notices in redFORM');
		$I->doAdministratorLogin();
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=categories');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=customfields');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=events');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=eventtemplates');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=logs');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=organizations');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=pricegroups');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=registrations');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=roles');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=sessions');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=tags');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=textsnippets');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=tools');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=venues');
		$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=venuescategories');
	}

	public function checkDefaultTemplateCest(\AcceptanceTester $I)
	{
		$I->wantToTest(' that there is a default event template');
		$I->doAdministratorLogin();
		$I->amOnPage('administrator/index.php?option=com_redevent&view=eventtemplates');
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "default template")]');
	}
}
