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

class EventsListThumbnailsCest
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
	protected $venueName;

	/**
	 * @var string
	 * @since 3.2.9
	 */
	protected $categoryName;

	/**
	 * @var string
	 * @Since 3.2.9
	 */
	protected $categoryDescription;

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
	 * EventsListThumbnailsCest constructor.
	 * @since 3.2.9
	 */
	public function __construct()
	{
		$this->faker                = Factory::create();
		$this->categoryVenueName    = $this->faker->bothify("Category Venue Name ##??");
		$this->venueName            = $this->faker->bothify("Venue Name ##??");
		$this->categoryName         = $this->faker->bothify("Category Name ##??");
		$this->categoryDescription  = '<p>The description goes here</p>';
		$this->eventName            = $this->faker->bothify("Event Name ##??");
		$this->templateName         = 'default template';
		$this->sessionName          = $this->faker->bothify("Session Name ##??");

		$this->menuItem             = 'Events list thumbnails';
		$this->menuCategory         = 'redEVENT - Component';
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
		$I->wantToTest('Add a venue in redEVENT');
		$I->createVenueNew($this->venueName, $this->categoryVenueName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param EventManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function addEventWithImage(EventManagerSteps $I)
	{
		$I->wantToTest('Add an event in redEVENT with default template and image');
		$I->createEventWithImage($this->eventName, $this->categoryName, $this->categoryDescription, $this->templateName);
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
	 * @param EventManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function checkFrontEndEventsThumbnails(EventManagerSteps $I)
	{
	  $I->wantToTest('Check Events List Thumbnails on front-end');
	  $I->checkEventsThumbnails($this->menuItem, $this->eventName, $this->venueName);
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
	  $I->wantToTest('Delete Venue');
	  $I->deleteVenue($this->categoryVenueName, $this->venueName);
	}
}