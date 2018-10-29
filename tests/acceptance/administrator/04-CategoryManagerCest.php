<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Faker\Generator;
use Faker\Factory;
use Step\Acceptance\Administrator\CategoryManagerSteps;
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
        $this->faker       = Factory::create();
        $this->categoryName1 = $this->faker->bothify("Category Name 1 ##??");
        $this->categoryName2 = $this->faker->bothify("Category Name 2  ##??");
    }
    public function _before(\AcceptanceTester $i)
    {
        $i->doAdministratorLogin();
    }

    /**
     * @param CategoryManagerSteps $I
     * @throws Exception
     */
    public function AllCaseCategory(CategoryManagerSteps $I)
    {
        $I->wantToTest('Add a category 1 in redEVENT');
        $I->createCategoryNew($this->categoryName1);
        $I->wantToTest('Add a category 2 in redEVENT');
        $I->createCategoryNew($this->categoryName2);
        $I->SearchCategory($this->categoryName1);
        $I->dontSee($this->categoryName2);
        $I->buttonClear($this->categoryName1,$this->categoryName2);
        $I->wantToTest('delete a category 2 in redEVENT');
        $I->deleteCategory($this->categoryName1);
    }
}

