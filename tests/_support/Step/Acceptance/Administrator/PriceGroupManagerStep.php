<?php
/**
 * Created by PhpStorm.
 * User: Trang
 * Date: 10/25/2018
 * Time: 1:38 PM
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
		$I->Search(PriceGroupManagerPage::$URL,$PriceGroup);
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
		$I->click(PriceGroupManagerPage::$checkAll);
		$I->click(PriceGroupManagerPage::$buttonDelete);
		$I->wantTo('Test with delete category but then cancel');
		$I->cancelPopup();
		$I->wantTo('Test with delete product then accept');
		$I->click(PriceGroupManagerPage::$buttonDelete);
		$I->acceptPopup();
		$I->waitForText(PriceGroupManagerPage::$messageDeleteProductSuccess, 60, PriceGroupManagerPage::$message);
		$I->dontSee($namePriceGroup);
	}
}