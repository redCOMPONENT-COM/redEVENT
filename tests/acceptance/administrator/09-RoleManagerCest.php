<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Faker\Generator;
use Faker\Factory;
use Page\Acceptance\Administrator\AbstractPage;
use Step\Acceptance\Administrator\RoleManagerSteps;
class RoleMangerCest
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
	protected $roleName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $roleName2;

	/**
	 * VenueroleManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker       = Factory::create();
		$this->roleName1 = $this->faker->bothify("Role 1 ##??");
		$this->roleName2 = $this->faker->bothify("Role 2 ##??");
	}

	/**
	 * @param RoleManagerSteps $i
	 * @throws Exception
	 * @since 3.2.10
	 */
	public function _before(RoleManagerSteps $i)
	{
		$i->doAdministratorRedEventLogin();
	}

	/**
	 * @param RoleManagerSteps $I
	 * @throws Exception
	 */
	public function AllCaseRole(RoleManagerSteps $I)
	{
		$I->wantToTest('Add a role 1 in redEVENT');
		$I->createRoleNew(
			array(
				'name' => $this->roleName1,
				'description' => '<strong>description</strong> here'
			)
		);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->wantToTest('Add a role 2 in redEVENT');
		$I->createRoleNew(
			array(
				'name' => $this->roleName2,
				'description' => '<strong>description</strong> here'
			)
		);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->searchRole($this->roleName1);
		$I->dontSee($this->roleName2);
		$I->buttonClear($this->roleName1,$this->roleName2);
		$I->wantToTest('delete a role 1 in redEVENT');
		$I->deleteRole($this->roleName1);
		$I->wantToTest('delete a role 2 in redEVENT');
		$I->deleteRole($this->roleName2);
	}
}
