<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Faker\Generator;
use Faker\Factory;
use Step\Acceptance\Administrator\EventManagerSteps;
use Page\Acceptance\Administrator\AbstractPage;
class EventManagerCest
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
	 * @var   string
	 * @since 1.0.0
	 */
	protected $EventName1;
	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $EventName2;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $templateName;

	/**
	 * EventManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker       = Factory::create();
		$this->categoryName1 = $this->faker->bothify("Category Name 1 ##??");
		$this->categoryName2 = $this->faker->bothify("Category Name 2 ##??");
		$this->EventName1 = $this->faker->bothify("Event Name 1 ##??");
		$this->EventName2 = $this->faker->bothify("Event Name 2 ##??");
		$this->templateName =  'default template';
	}
	public function _before(\AcceptanceTester $i)
	{
		$i->doAdministratorLogin();
	}

	/**
	 * @param EventManagerSteps $I
	 * @throws Exception
	 */
	public function addEvent(EventManagerSteps $I)
	{
		$I->wantToTest('Add an event 1 in redEVENT with default template');
		$I->createEventNew($this->EventName1,$this->categoryName1, $this->templateName);

		$I->wantToTest('Add an event 2 in redEVENT with default template');
		$I->createEventNew($this->EventName2,$this->categoryName2, $this->templateName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->searchEvent($this->EventName1);
		$I->dontSee($this->EventName2);
		$I->buttonClear($this->EventName1,$this->EventName2);

		$I->wantToTest('Delete Event 1 in redEvent');
		$I->deleteEvent($this->EventName1);

		$I->wantToTest('Delete Event 2 in redEvent');
		$I->deleteEvent($this->EventName2);
	}

//	public function addEventWithDefaultTemplate(\Step\Acceptance\Adminredevent $I)
//	{
//		$I->wantToTest('Add an event in redEVENT with default template');
//		$I->doAdministratorLogin();
//		$name = 'Event default template';
//		$I->createEvent(
//			array(
//				'name' => $name,
//				'description' => '<p>The description goes here</p>',
//				'template_name' => 'default template'
//			)
//		);
//		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
//		$I->seeElement('//*[@id="table-items"]//td//*[contains(., "' . $name . '")]');
//	}
}
