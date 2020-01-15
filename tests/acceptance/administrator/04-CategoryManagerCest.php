<?php
/**
 * @package     redEVENT
 * @subpackage  Cests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Faker\Generator;
use Faker\Factory;
use Page\Acceptance\Administrator\AbstractPage;
use Step\Acceptance\Administrator\CategoryManagerSteps;

/**
 * Class CategoryManagerCest
 * @since 3.2.10
 */
class CategoryManagerCest
{
	/**
	 * @var   Generator
	 * @since 1.0.0
	 */
	protected $faker;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $categoryName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $categoryName2;

	/**
	 * 04-CategoryManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker         = Factory::create();
		$this->categoryName1 = $this->faker->bothify("Category Name 1 ##??");
		$this->categoryName2 = $this->faker->bothify("Category Name 2 ##??");
	}

	/**
	 * @param CategoryManagerSteps $i
	 * @throws Exception
	 * @since 3.2.10
	 */
	public function _before(CategoryManagerSteps $i)
	{
		$i->doAdministratorRedEventLogin();
	}

	/**
	 * @param CategoryManagerSteps $I
	 * @throws Exception
	 */
	public function AllCaseCategory(CategoryManagerSteps $I)
	{
		$I->wantToTest('Add a category 1 in redEVENT');
		$I->createCategoryNew(
			array(
				'name' => $this->categoryName1,
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->wantToTest('Add a category 2 in redEVENT');
		$I->createCategoryNew(
			array(
				'name' => $this->categoryName2,
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->searchCategory($this->categoryName1);
		$I->dontSee($this->categoryName2);
		$I->buttonClear($this->categoryName1,$this->categoryName2);
		$I->wantToTest('delete a category 1 in redEVENT');
		$I->deleteCategory($this->categoryName1);
		$I->wantToTest('delete a category 2 in redEVENT');
		$I->deleteCategory($this->categoryName2);
	}
}

