<?php
/**
 * @package     redEVENT
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Faker\Factory;
use Page\Acceptance\Administrator\AbstractPage;
use Step\Acceptance\Administrator\EventManagerSteps;
use Step\Acceptance\Administrator\SessionManagerSteps;
use Step\Acceptance\Administrator\VanueManagerSteps;
use Step\Acceptance\JoomlaManagerSteps;

class VenuesMapCest
{
	/**
	 * @var Generator
	 * @since 3.2.9
	 */
	protected $faker;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $categoryVenueName;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $categoryVenueDescription;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $venueName;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $viewOnMap;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $venueCountryId;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $venueCountry;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $categoryName;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $eventName;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $templateName;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $sessionName;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $menuItem;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected  $menuCategory;

	/**
	 * VenuesMapCest constructor.
	 * @since 3.2.9
	 */
	public function __construct()
	{
		$this->faker                    = Factory::create();
		$this->categoryVenueName        = $this->faker->bothify("Category Venue Name ##??");
		$this->categoryVenueDescription = '<p>The description goes here</p>';
		$this->venueName                = $this->faker->bothify("Venue Name ##??");
		$this->viewOnMap                = 'Yes';
		$this->venueCountryId           = 'DK - Denmark';
		$this->venueCountry             = 'Denmark';
		$this->categoryName             = $this->faker->bothify("Category Name ##??");
		$this->eventName                = $this->faker->bothify("Event Name ##??");
		$this->templateName             = 'default template';
		$this->sessionName              = $this->faker->bothify("Session Name ##??");

		$this->menuItem                 = 'Venues map';
		$this->menuCategory             = 'redEVENT - Component';
	}

	/**
	 * @param VanueManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function _before(VanueManagerSteps $I)
	{
		$I->doAdministratorRedEventLogin();
	}

	/**
	 * @param VanueManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function addVenue(VanueManagerSteps $I)
	{
		$I->wantToTest('Add a venue in redEVENT with country');
		$I->createVenueWithCountry($this->venueName, $this->categoryVenueName, $this->categoryVenueDescription, $this->venueCountryId, $this->viewOnMap);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param EventManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function addEvent(EventManagerSteps $I)
	{
		$I->wantToTest('Add an event in redEVENT with default template');
		$I->createEventNew($this->eventName, $this->categoryName, $this->templateName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param SessionManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function createSession(SessionManagerSteps $I)
	{
		$I->wantToTest('Add session in redEVENT');
		$I->createSessionNew($this->eventName, $this->venueName, $this->sessionName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param JoomlaManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function createMenuItem(JoomlaManagerSteps $I)
	{
		$I->wantTo("Create menu item featured events in frontend");
		$I->createNewMenuItem($this->menuItem, $this->menuCategory, $this->menuItem);
	}

	/**
	 * @param VanueManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function checkFrontEndVenuesMap(VanueManagerSteps $I)
	{
		$I->wantToTest('Check Venues Map on front-end');
		$I->checkVenuesMap($this->menuItem, $this->venueCountry);
	}

	/**
	 * @param SessionManagerSteps $I
	 * @param $scenario
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function deleteAll(SessionManagerSteps $I, $scenario)
	{
		$I->wantToTest('Delete session');
		$I ->deleteSession($this->sessionName);
		$I = new EventManagerSteps($scenario);
		$I->wantToTest('Delete events');
		$I->deleteEvent($this->eventName);
		$I->wantToTest('Delete category');
		$I->deleteCategory($this->categoryName);
		$I = new VanueManagerSteps($scenario);
		$I->wantToTest('Delete Venue and Venue Category');
		$I->deleteVenue($this->categoryVenueName, $this->venueName);
	}
}