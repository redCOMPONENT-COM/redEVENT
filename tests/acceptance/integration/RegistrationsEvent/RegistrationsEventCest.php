<?php
/**
 * @package     redEVENT
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Faker\Factory;
use Page\Acceptance\Administrator\AbstractPage;
use Step\Acceptance\Administrator\EventManagerSteps;
use Step\Acceptance\Administrator\RegistrationManagerSteps;
use Step\Acceptance\Administrator\SessionManagerSteps;
use Step\Acceptance\Administrator\TemplateManagerSteps;
use Step\Acceptance\Administrator\VanueManagerSteps;
use Step\Acceptance\JoomlaManagerSteps;

class RegistrationsEventCest
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
	protected $nameTemplate;

	/**
	 * 14-TemplateManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker         = Factory::create();
		$this->nameTemplate = $this->faker->bothify("Template Name 1 ##??");
		$this->categoryName      = $this->faker->bothify("Category Name ##??");
		$this->eventName         = $this->faker->bothify("Event Name ##??");
		$this->categoryVanueName = $this->faker->bothify("Category Vanue Name ##??");
		$this->VanueName         = $this->faker->bothify("Vanue Name  ##??");
		$this->SessionName       = $this->faker->bothify("SessionName ##??");

		$this->nameUser          = $this->faker->bothify("User Name  ##??");
		$this->emailUser         = "test@test" . rand() . ".com";

		$this->fieldUser         = "Name";
		$this->fieldEmail        = "Email";
		$this->menuItem          = 'Events list table';
		$this->menuCategory      = 'redEVENT - Component';
	}

	public function _before(\AcceptanceTester $i)
	{
		$i->doAdministratorLogin();
	}


	/**
	 * @param JoomlaManagerSteps $I
	 * @throws Exception
	 */
	public function createMenuItem(JoomlaManagerSteps $I)
	{
		$I->wantTo("Create Menu item Upcoming events in front end");
		$I->createNewMenuItem($this->menuItem, $this->menuCategory, $this->menuItem);
	}

	/**
	 * @param TemplateManagerSteps $I
	 * @throws Exception
	 */
	public function createTemplate(TemplateManagerSteps $I)
	{
		$I->wantToTest('Add a template 1 in redEVENT');
		$I->createMinimalRegistrationForm(['name' => 'Registration']);
		$I->createTemplateRegistration(
			array(
				'name' =>$this->nameTemplate,
				'meta_description' => 'This is the meta description of the event [event_title], session at [venue]',
				'meta_keywords' => 'some keywords, [event_title], [venue]',
				'redform' => 'Registration'
			)
		);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param EventManagerSteps $I
	 * @throws Exception
	 */
	public function addEvent(EventManagerSteps $I)
	{
		$I->wantToTest('Add an event in redEVENT with created template');

		$I->createEventRegistrations($this->eventName,$this->categoryName, $this->nameTemplate);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param VanueManagerSteps $I
	 * @throws Exception
	 */
	public function addVenue(VanueManagerSteps $I)
	{
		$I->wantToTest('Add a venue in redEVENT');

		$I->createVenueNew($this->VanueName, $this->categoryVanueName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param SessionManagerSteps $I
	 * @throws Exception
	 */
	public function createSession(SessionManagerSteps $I)
	{
		$I->wantToTest('Add session in redEVENT');
		$I->createSessionUpcomming($this->eventName,$this->VanueName,$this->SessionName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}


	/**
	 * @param RegistrationManagerSteps $I
	 * @throws Exception
	 */
	public function CheckFrontEnd(RegistrationManagerSteps $I)
	{
		$I->wantToTest('Check Registrations event on front-end');
		$I->checkRegistrationEvents($this->menuItem,$this->SessionName,$this->eventName,$this->nameUser,$this->emailUser,$this->fieldUser,$this->fieldEmail);
		$I->wantToTest('Check Registrations event on administrator');
		$I->checkRegistrationBackend($this->eventName,$this->nameUser,$this->emailUser);
	}
}