<?php
/**
 * @package     redEVENT
 * @subpackage  Cests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AdminRedevent;

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class CheckForWarningsAndNoticesCest
{
	/**
	 * @param AdminRedevent $I
	 * @throws Exception
	 * @since 3.2.10
	 */
	public function runChecks(AdminRedevent $I)
	{
		$I->wantToTest(' that there are no Warnings or Notices in redFORM');
		$I->doAdministratorRedEventLogin();
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

	/**
	 * @param AdminRedevent $I
	 * @throws Exception
	 * @since 3.2.10
	 */
	public function checkDefaultTemplateCest(AdminRedevent $I)
	{
		$I->wantToTest(' that there is a default event template');
		$I->doAdministratorRedEventLogin();
		$I->amOnPage('administrator/index.php?option=com_redevent&view=eventtemplates');
		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "default template")]');
	}
}
