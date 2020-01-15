<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Faker\Generator;
use Faker\Factory;
use Page\Acceptance\Administrator\CustomFieldsManagerPage;
use Step\Acceptance\Administrator\CustomFieldsManagerSteps;

class CustomFieldsManagerCest
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
	protected $CustomFieldName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $CustomFieldName2;

	/**
	 * CustomFieldsManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker            = Factory::create();
		$this->CustomFieldName1 = $this->faker->bothify("some text ##??");
		$this->CustomFieldName2 = $this->faker->bothify("some text for session ##??");
	}

	/**
	 * @param CustomFieldsManagerSteps $i
	 * @throws Exception
	 */
	public function _before(CustomFieldsManagerSteps $i)
	{
		$i->doAdministratorRedEventLogin();
	}

	/**
	 * @param CustomFieldsManagerSteps $I
	 * @throws Exception
	 */
	public function addEventTextField(CustomFieldsManagerSteps $I)
	{
		$I->wantToTest('Add an event custom text field in redEVENT');
		$I->createCustomFieldNew(
			array(
				'name' => $this->CustomFieldName1,
				'object' => 'event',
				'type' => 'Text'
			)
		);
		$I->wantToTest('Search Custom Fields in redEVENT');
		$I->searchCustomField($this->CustomFieldName1);
		$I->see("Event", CustomFieldsManagerPage::$objectResult);
		$I->see("text", CustomFieldsManagerPage::$typeResult);
	}

	/**
	 * @param CustomFieldsManagerSteps $I
	 * @throws Exception
	 */
	public function addSessionTextField(CustomFieldsManagerSteps $I)
	{
		$I->wantToTest('Add an session custom text field in redEVENT');
		$I->createCustomFieldNew(
			array(
				'name' => $this->CustomFieldName2,
				'object' => 'Session',
				'type' => 'Text'
			)
		);
		$I->wantToTest('Search Custom Fields in redEVENT');
		$I->searchCustomField($this->CustomFieldName2);
		$I->see("Session",CustomFieldsManagerPage::$objectResult);
		$I->see("text", CustomFieldsManagerPage::$typeResult);
	}
	/**
	 * @param CustomFieldsManagerSteps $I
	 * @throws Exception
	 */
	public function AllCaseCategory(CustomFieldsManagerSteps $I)
	{
		$I->searchCustomField($this->CustomFieldName1);
		$I->dontSee($this->CustomFieldName2);
		$I->buttonClear($this->CustomFieldName1,$this->CustomFieldName2);
		$I->wantToTest('delete a Custom Field 1 in redEVENT');
		$I->deleteCustomFields($this->CustomFieldName1);
		$I->wantToTest('delete a Custom Field 2 in redEVENT');
		$I->deleteCustomFields($this->CustomFieldName2);
	}
}
