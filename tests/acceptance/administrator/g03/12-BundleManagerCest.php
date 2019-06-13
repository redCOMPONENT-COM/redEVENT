<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Faker\Generator;
use Faker\Factory;
use \Page\Acceptance\Administrator\BundleManagerPage;
use Step\Acceptance\Administrator\BundleManagerSteps;
class BundleManagerCest
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
	protected $bundleName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $bundleName2;

	/**
	 * CategoryManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker       = Factory::create();
		$this->bundleName1 = $this->faker->bothify("A bundle 1 ##??");
		$this->bundleName2 = $this->faker->bothify("A bundle 2 ##??");
	}

	public function _before(\AcceptanceTester $i)
	{
		$i->doAdministratorLogin();
	}

	/**
	 * @param BundleManagerSteps $I
	 * @throws Exception
	 */
	public function createBundle(BundleManagerSteps $I)
	{
		$I->wantToTest('Add a bundle 1 in redEVENT');
		$I->createBundleNew(
			array(
				'name' => $this->bundleName1,
				'description' => '<strong>description</strong> here',
			)
		);

		$I->wantToTest('Add a bundle 2 in redEVENT');
		$I->createBundleNew(
			array(
				'name' => $this->bundleName2,
				'description' => '<strong>description</strong> here',
			)
		);

		$I->searchBundle($this->bundleName1);
		$I->dontSee($this->bundleName2);

		$I-> buttonClear($this->bundleName1,$this->bundleName2);

		$I->wantToTest('Delete a bundle 1 in redEVENT');
		$I->deleteBundle( $this->bundleName1);

		$I->wantToTest('Delete a bundle 2 in redEVENT');
		$I->deleteBundle( $this->bundleName2);
	}
}

