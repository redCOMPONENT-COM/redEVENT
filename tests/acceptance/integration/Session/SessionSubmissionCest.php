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
use Step\Acceptance\Administrator\SessionManagerSteps;
use Step\Acceptance\Administrator\UpcomingEventsSteps;
use Step\Acceptance\Administrator\VanueManagerSteps;
use Step\Acceptance\JoomlaManagerSteps;
class SessionSubmissionCest
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
	protected $SessionName;

	/**
	 * @var string
	 */
	protected $menuItem;

	/**
	 * @var string
	 */
	protected  $menuCategory;

	/**
	 * VenueCategoryManagerCest constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker             = Factory::create();
		$this->categoryVanueName = $this->faker->bothify("Category Vanue Name ##??");
		$this->VanueName         = $this->faker->bothify("Vanue Name ##??");
		$this->categoryName      = $this->faker->bothify("Category Name ##??");
		$this->eventName         = $this->faker->bothify("Event Name ##??");
		$this->templateName      =  'default template';
		$this->SessionName       = $this->faker->bothify("Session Name ##??");

		$this->menuItem          = 'Session submission';
		$this->menuCategory      = 'redEVENT - Component';
	}

	/**
	 * @param AcceptanceTester $i
	 * @throws Exception
	 */
	public function _before(\AcceptanceTester $i)
	{
		$i->doAdministratorLogin();
	}

//	/**
//	 * @param JoomlaManagerSteps $I
//	 * @throws Exception
//	 */
//	public function createMenuItem(JoomlaManagerSteps $I)
//	{
//		$I->wantTo("Create Menu item Session submission in front end");
//		$I->createNewMenuItem($this->menuItem, $this->menuCategory, $this->menuItem);
//	}

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
		$I->wantToTest('Add an event in redEVENT with default template');
		$I->createEventNew($this->eventName,$this->categoryName, $this->templateName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param SessionManagerSteps $I
	 * @throws Exception
	 */
	public function createSession(SessionManagerSteps $I)
	{
		$I->wantToTest('Add session in redEVENT');
		$I->createSessionFrontend($this->menuItem,$this->eventName,$this->VanueName,$this->SessionName);

	}

	/**
	 * @param JoomlaManagerSteps $I
	 * @throws Exception
	 */
	public function checkSession(SessionManagerSteps $I)
	{
		$I->wantTo("Create Menu item Featured events in front end");
		$I->searchSession($this->SessionName);
	}

	/***
	 * @param SessionManagerSteps $I
	 * @param $scenario
	 * @throws Exception
	 */
	public function deleteAll(SessionManagerSteps $I, $scenario)
	{
		$I->wantToTest('Delete session');
		$I ->deleteSession($this->SessionName);
		$I = new EventManagerSteps($scenario);
		$I->wantToTest('Delete events');
		$I->deleteEvent($this->eventName);
		$I->wantToTest('Delete category');
		$I->deleteCategory($this->categoryName);
		$I = new VanueManagerSteps($scenario);
		$I->wantToTest('Delete Venue');
		$I->deleteVenue($this->categoryVanueName,$this->VanueName);
	}
}