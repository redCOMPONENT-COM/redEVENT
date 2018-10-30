<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use \Page\Acceptance\Administrator\CategoryManagerPage;
use Step\Acceptance\Adminredevent;

/**
 * Class CategoryManagerSteps
 * @package Step\Acceptance\Administrator
 */
class CategoryManagerSteps extends Adminredevent
{
	/**
	 * @param   $params
	 * @throws \Exception
	 */
	public function createCategoryNew($params)
	{
		$I = $this;
		$I->amOnPage(CategoryManagerPage::$URL);
		$I->waitForText(CategoryManagerPage::$categoryTitle, 30);
		$I->click(CategoryManagerPage:: $buttonNew);
		$I->waitForText(CategoryManagerPage::$categoryTitleNew, 30);
		$I->fillField(CategoryManagerPage::$fieldName, $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById(CategoryManagerPage::$fieldDescription, $params['description']);
		}

		$I->click(CategoryManagerPage::$buttonSave);
	}
	/**
	 * @param $nameCategory
	 * @throws \Exception
	 */
	public function searchCategory($nameCategory)
	{
		$I = $this;
		$I->search(CategoryManagerPage::$URL,$nameCategory);
	}

	/**
	 * @param $nameCategory
	 * @throws \Exception
	 */
	public function deleteCategory($nameCategory)
	{
		$I = $this;
		$I->amOnPage(CategoryManagerPage::$URL);
		$I->waitForText(CategoryManagerPage::$categoryTitle, 30);
		$I->search(CategoryManagerPage::$URL,$nameCategory);
		$I->see($nameCategory, CategoryManagerPage::$tableResult);
		$I->click(CategoryManagerPage::$checkAll);
		$I->click(CategoryManagerPage::$buttonDelete);
		$I->wantTo('Test with delete category but then cancel');
		$I->cancelPopup();
		$I->wantTo('Test with delete product then accept');
		$I->click(CategoryManagerPage::$buttonDelete);
		$I->acceptPopup();
		$I->waitForText(CategoryManagerPage::$messageDeleteProductSuccess, 60, CategoryManagerPage::$message);
		$I->dontSee($nameCategory);
	}
}

