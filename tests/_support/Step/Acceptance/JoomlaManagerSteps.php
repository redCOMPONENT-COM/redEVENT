<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use \Page\Acceptance\JoomlaManagerPage;
class JoomlaManagerSteps extends AdminRedevent
{
	/**
	 * @param $nameExtensions
	 * @throws \Exception
	 */
	public function uninstallExtension($nameExtensions)
	{
		$I = $this;
		$I->amOnPage(JoomlaManagerPage::$URLUninstall);
		$I->waitForText(JoomlaManagerPage::$extensionsTitle, 30, JoomlaManagerPage::$H1);
		$I->fillField(JoomlaManagerPage::$fieldSearch, $nameExtensions);
		$I->click(JoomlaManagerPage::$buttonSearch);
		$I->waitForElement(JoomlaManagerPage::$manageList);
		$I->click(JoomlaManagerPage::$checkbox);
		$I->click(JoomlaManagerPage::$buttonUninstall);
		$I->acceptPopup();
		$I->waitForText(JoomlaManagerPage::$messageUninstall, 30, JoomlaManagerPage::$message);
		$I->see(JoomlaManagerPage::$messageUninstall, JoomlaManagerPage::$message);
		$I->fillField(JoomlaManagerPage::$fieldSearch, $nameExtensions);
		$I->click(JoomlaManagerPage::$buttonSearch);
		$I->waitForText(JoomlaManagerPage::$messageFailedSearch, 30, JoomlaManagerPage::$messageFailed);
		$I->see(JoomlaManagerPage::$messageFailedSearch,JoomlaManagerPage::$messageFailed);
	}
}