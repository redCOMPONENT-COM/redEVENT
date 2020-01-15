<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use \Page\Acceptance\Administrator\CategoryManagerPage;
use Step\Acceptance\AdminRedevent;

/**
 * Class CategoryManagerSteps
 * @package Step\Acceptance\Administrator
 */
class CategoryManagerSteps extends AdminRedevent
{
	/**
	 * @param $params
	 * @throws \Exception
	 */
	public function createCategoryNew($params)
	{
		$I = $this;
		$I->createItemNew(CategoryManagerPage::$URL,CategoryManagerPage::$categoryTitle,CategoryManagerPage::$categoryTitleNew,$params);
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
		$I->delete(CategoryManagerPage::$URL,CategoryManagerPage::$categoryTitle, $nameCategory);
	}
}
