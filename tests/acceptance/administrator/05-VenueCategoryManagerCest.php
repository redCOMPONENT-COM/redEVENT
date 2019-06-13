a<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Faker\Generator;
use Faker\Factory;
use Page\Acceptance\Administrator\AbstractPage;
use Step\Acceptance\Administrator\VenueCategoryManagerSteps;
class VenueCategoryManagerCest
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
	protected $categoryVenueName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $categoryVenueName2;

	/**
	 * 05-VenueCategoryManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker         = Factory::create();
		$this->categoryVenueName1 = $this->faker->bothify("Venue Category Name 1 ##??");
		$this->categoryVenueName2 = $this->faker->bothify("Venue Category Name 2 ##??");
	}
	public function _before(\AcceptanceTester $i)
	{
		$i->doAdministratorLogin();
	}

	/**
	 * @param VenueCategoryManagerSteps $I
	 * @throws Exception
	 */
	public function AllCaseVenueCategory(VenueCategoryManagerSteps $I)
	{
		$I->wantToTest('Add a Venue category 1 in redEVENT');
		$I->createVenueCategoryNew(
			array(
				'name' => $this->categoryVenueName1,
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->wantToTest('Add a Venue category 2 in redEVENT');
		$I->createVenueCategoryNew(
			array(
				'name' => $this->categoryVenueName2,
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->searchVenueCategory($this->categoryVenueName1);
		$I->dontSee($this->categoryVenueName2);
		$I->buttonClear($this->categoryVenueName1,$this->categoryVenueName2);
		$I->wantToTest('delete a  venue category 1 in redEVENT');
		$I->deleteVenueCategory($this->categoryVenueName1);
		$I->wantToTest('delete a venue category 2 in redEVENT');
		$I->deleteVenueCategory($this->categoryVenueName2);
	}
}
