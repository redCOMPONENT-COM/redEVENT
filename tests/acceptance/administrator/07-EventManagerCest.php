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
use Step\Acceptance\Administrator\TemplateManagerSteps;
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
	protected $eventName1;
	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $eventName2;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $templateName;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $templateName2;

	/**
	 * EventManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker         = Factory::create();
		$this->categoryName1 = $this->faker->bothify("Category Name 1 ##??");
		$this->categoryName2 = $this->faker->bothify("Category Name 2 ##??");
		$this->eventName1    = $this->faker->bothify("Event Name 1 ##??");
		$this->eventName2    = $this->faker->bothify("Event Name 2 ##??");
		$this->templateName  = 'default template';
		$this->templateName2 = 'template 1';
	}

	/**
	 * @param EventManagerSteps $i
	 * @throws Exception
	 * @since 3.2.10
	 */
	public function _before(EventManagerSteps $i)
	{
		$i->doAdministratorRedEventLogin();
	}

	/**
	 * @param EventManagerSteps $I
	 * @throws Exception
	 */
	public function addEventWithDefaultTemplate(EventManagerSteps $I)
	{
		$I->wantToTest('Add an event 1 in redEVENT with default template');
		$I->createEventNew($this->eventName1,$this->categoryName1, $this->templateName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param TemplateManagerSteps $I
	 */
	public function addTemplate(TemplateManagerSteps $I){
		$I->wantToTest('Add an template in redEVENT');
		$I->createTemplate($this->templateName2);
	}

	/**
	 * @param EventManagerSteps $I
	 * @throws Exception
	 */
	public function addEvent(EventManagerSteps $I)
	{
		$I->wantToTest('Add an event in redEVENT with template');
		$I->createEventNew($this->eventName2,$this->categoryName2, $this->templateName2);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);

		$I->wantToTest('Search Event 1 in redEvent');
		$I->searchEvent($this->eventName1);
		$I->dontSee($this->eventName2);
		$I->buttonClear($this->eventName1,$this->eventName2);

		$I->wantToTest('Delete Event 1 in redEvent');
		$I->deleteEvent($this->eventName1);

		$I->wantToTest('Delete Event 2 in redEvent');
		$I->deleteEvent($this->eventName2);
	}
}
