<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Faker\Generator;
use Faker\Factory;
use Step\Acceptance\Administrator\VanueManagerSteps;
use Page\Acceptance\Administrator\AbstractPage;
class VenueManagerCest
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
	protected $categoryVanueName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $vanueName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $categoryVanueName2;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $vanueName2;

	/**
	 * VenueCategoryManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker       = Factory::create();
		$this->categoryVanueName1 = $this->faker->bothify("Category Vanue Name 1 ##??");
		$this->vanueName1 = $this->faker->bothify("Vanue Name 1 ##??");
		$this->categoryVanueName2 = $this->faker->bothify("Category Vanue Name 2 ##??");
		$this->vanueName2 = $this->faker->bothify("Vanue Name 2 ##??");
	}
	public function _before(\AcceptanceTester $i)
	{
		$i->doAdministratorLogin();
	}

	/**
	 * @param VanueManagerSteps $I
	 * @throws Exception
	 */
	public function addVenue(VanueManagerSteps $I)
	{
		$I->wantToTest('Add a venue 1 in redEVENT');
		$I->createVenueNew($this->vanueName1,$this->categoryVanueName1);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->wantToTest('Add a venue 2 in redEVENT');
		$I->createVenueNew($this->vanueName2,$this->categoryVanueName2);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->wantToTest('Search Vanue  in redEVENT');
		$I->searchVanue($this->vanueName1);
		$I->dontSee($this->vanueName2);
		$I->buttonClear($this->vanueName1,$this->vanueName2);
		$I->wantToTest('delete a category 1 in redEVENT');
		$I->deleteVenue($this->categoryVanueName1,$this->vanueName1);
		$I->wantToTest('delete a category 2 in redEVENT');
		$I->deleteVenue($this->categoryVanueName2,$this->vanueName2);
	}
}
