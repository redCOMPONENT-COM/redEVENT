<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Faker\Generator;
use Faker\Factory;
use Step\Acceptance\Administrator\PriceGroupManagerStep;

class PriceGroupManagerCest
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
	protected $namePriceGroup1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $namePriceGroup2;

	/**
	 * PriceGroupManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker           = Factory::create();
		$this->namePriceGroup1 = $this->faker->bothify(" Price GroupName 1 ##??");
		$this->namePriceGroup2 = $this->faker->bothify(" Price GroupName 2 ##??");
	}

	/**
	 * @param PriceGroupManagerStep $i
	 * @throws Exception
	 */
	public function _before(PriceGroupManagerStep $i)
	{
		$i->doAdministratorRedEventLogin();
	}

	/**
	 * @param PriceGroupManagerStep $I
	 * @throws Exception
	 */
	public function allCasePriceGroup(PriceGroupManagerStep $I)
	{
		$I->wantToTest('Add a price group 1 in redEVENT');
		$I->createPriceGroupNew(
			array(
				'name' => $this->namePriceGroup1,
			)
		);
		$I->wantToTest('Add a price group 2 in redEVENT');
		$I->createPriceGroupNew(
			array(
				'name' => $this->namePriceGroup2,
			)
		);
		$I->searchPriceGroup($this->namePriceGroup1);
		$I->dontSee($this->namePriceGroup2);
		$I->buttonClear($this->namePriceGroup1,$this->namePriceGroup2);
		$I->wantToTest('delete a Price Group 1 in redEVENT');
		$I->deletePriceGroup($this->namePriceGroup1);
		$I->wantToTest('delete a Price Group 2 in redEVENT');
		$I->deletePriceGroup($this->namePriceGroup2);
	}
}
