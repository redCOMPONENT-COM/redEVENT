<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use \Page\Acceptance\Administrator\BundleManagerPage;
use Step\Acceptance\AdminRedevent;

class BundleManagerSteps extends AdminRedevent
{
	/**
	 * @param $bundleName
	 * @throws \Exception
	 */
	public function createBundleNew($bundleName)
	{
		$I = $this;
		$I->createItemNew(BundleManagerPage::$URL,BundleManagerPage::$bundleTitle,BundleManagerPage::$bundleTitleNew,$bundleName);
	}

	/**
	 * @param $bundleName
	 * @throws \Exception
	 */
	public function searchBundle($bundleName)
	{
		$I = $this;
		$I->search(BundleManagerPage::$URL,$bundleName);
	}

	/**
	 * @param $bundleName
	 * @throws \Exception
	 */
	public function deleteBundle($bundleName)
	{
		$I = $this;
		$I->amOnPage(BundleManagerPage::$URL);
		$I->waitForText(BundleManagerPage::$bundleTitle, 30);
		$I->searchBundle($bundleName);
		$I->see($bundleName, BundleManagerPage::$tableResult);
		$I->checkAllResults();
		$I->click(BundleManagerPage::$buttonDelete);
		$I->waitForText(BundleManagerPage::$notificationNoItem, 120);
		$I->dontSee($bundleName);
	}
}
