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
use Step\Acceptance\Administrator\EventManagerSteps;
use Step\Acceptance\Administrator\SessionManagerSteps;
use Page\Acceptance\Administrator\AbstractPage;
class SessionManagerCest
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
	protected $categoryVanueName;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $VanueName;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $categoryName;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $eventName;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $templateName;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $SessionName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $SessionName2;

	/**
	 * VenueCategoryManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker             = Factory::create();
		$this->categoryVanueName = $this->faker->bothify("Category Vanue Name ##??");
		$this->VanueName         = $this->faker->bothify("Vanue Name  ##??");
		$this->categoryName      = $this->faker->bothify("Category Name ##??");
		$this->eventName         = $this->faker->bothify("Event Name  ##??");
		$this->templateName      =  'default template';
		$this->SessionName1       = $this->faker->bothify("Session Name  ##??");
		$this->SessionName2       = $this->faker->bothify("Session Name  ##??");
	}

	/**
	 * @param VanueManagerSteps $i
	 * @throws Exception
	 * @since 3.2.10
	 */
	public function _before(VanueManagerSteps $i)
	{
		$i->doAdministratorRedEventLogin();
	}

	/**
	 * @param VanueManagerSteps $I
	 * @throws Exception
	 */
	public function addVenue(VanueManagerSteps $I)
	{
		$I->wantToTest('Add a venue in redEVENT');

		$I->createVenueNew($this->VanueName,$this->categoryVanueName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}
	/**
	 * @param EventManagerSteps $I
	 * @throws Exception
	 */
	public function addEvent(EventManagerSteps $I)
	{
		$I->wantToTest('Add an event in redEVENT with created template');

		$I->createEventNew($this->eventName,$this->categoryName, $this->templateName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param SessionManagerSteps $I
	 * @throws Exception
	 */
	public function AllCaseSession(SessionManagerSteps $I)
	{
		$I->wantToTest('Add an open date session in redEVENT');
		$I->createSessionNew($this->eventName,$this->VanueName,$this->SessionName1 );
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->wantToTest('Add an open date session in redEVENT');
		$I->createSessionNew($this->eventName,$this->VanueName,$this->SessionName2 );
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->searchSession($this->SessionName1);
		$I->dontSee($this->SessionName2);
		$I->buttonClear($this->SessionName1,$this->SessionName2);
		$I->wantToTest('delete all session 1 in redEVENT');
	}

	/**
	 * @param SessionManagerSteps $client
	 * @throws Exception
	 */
	public function deleteAllSection(SessionManagerSteps $client)
	{
		$client->deleteAllSession();
		$client->dontSee($this->SessionName1);
		$client->dontSee($this->SessionName2);
	}
}
