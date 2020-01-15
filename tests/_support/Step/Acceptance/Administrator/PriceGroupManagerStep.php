<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use \Page\Acceptance\Administrator\PriceGroupManagerPage;
use Step\Acceptance\AdminRedevent;

class PriceGroupManagerStep extends AdminRedevent
{
	/**
	 * @param $namePrice
	 * @throws \Exception
	 */
	public function createPriceGroupNew($namePrice)
	{
		$I = $this;
		$I->createItemNew(PriceGroupManagerPage::$URL,PriceGroupManagerPage::$priceGroupTitle,PriceGroupManagerPage::$priceGroupTitleNew,$namePrice);
	}

	/**
	 * @param $PriceGroup
	 * @throws \Exception
	 */
	public function searchPriceGroup($PriceGroup)
	{
		$I = $this;
		$I->search(PriceGroupManagerPage::$URL,$PriceGroup);
	}

	/**
	 * @param $namePriceGroup
	 * @throws \Exception
	 */
	public function deletePriceGroup($namePriceGroup)
	{
		$I = $this;
		$I->amOnPage(PriceGroupManagerPage::$URL);
		$I->waitForText(PriceGroupManagerPage::$priceGroupTitle, 30);
		$I->searchPriceGroup($namePriceGroup);
		$I->see($namePriceGroup, PriceGroupManagerPage::$tableResult);
		$I->checkAllResults();
		$I->click(PriceGroupManagerPage::$buttonDelete);
		$I->wantTo('Test with delete category but then cancel');
		$I->cancelPopup();
		$I->wantTo('Test with delete product then accept');
		$I->click(PriceGroupManagerPage::$buttonDelete);
		$I->acceptPopup();
		$I->waitForText(PriceGroupManagerPage::$notificationNoItem, 60);
		$I->dontSee($namePriceGroup);
	}
}
