<?php
/**
 * @package     redEVENT
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Faker\Factory;
use Page\Acceptance\Administrator\AbstractPage;
use Step\Acceptance\Administrator\EasyEventSteps;
use Step\Acceptance\Administrator\EventManagerSteps;
use Step\Acceptance\Administrator\SessionManagerSteps;
use Step\Acceptance\Administrator\VanueManagerSteps;
use Step\Acceptance\JoomlaManagerSteps;

/**
 * Class EasyEventCest
 * @since 3.2.9
 */
class EasyEventCest
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
	 * EasyEventsCest constructor.
	 * @since 3.2.9
	 */
	public function __construct()
	{
		$this->faker             = Factory::create();
		$this->categoryVenueName = $this->faker->bothify("Category Venue Name ##??");
		$this->venueName         = $this->faker->bothify("Venue Name ##??");
		$this->categoryName      = $this->faker->bothify("Category Name ##??");
		$this->eventName         = $this->faker->bothify("Event Name ##??");
		$this->templateName      = 'default template';
		$this->sessionName       = $this->faker->bothify("Session Name ##??");

		$this->menuItem          = 'Easy event';
		$this->menuCategory      = 'redEVENT - Component';
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
	public function addEvent(EventManagerSteps $I)
	{
		$I->wantToTest('Add an event in redEVENT with default template');
		$I->createEventNew($this->eventName, $this->categoryName, $this->templateName);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
	}

	/**
	 * @param JoomlaManagerSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function createMenuItem(JoomlaManagerSteps $I)
	{
		$I->wantTo("Create menu item Easy Events in frontend");
		$I->createNewMenuItem($this->menuItem, $this->menuCategory, $this->menuItem);
	}

	/**
	 * @param EasyEventSteps $I
	 * @throws Exception
	 * @since 3.2.9
	 */
	public function checkFrontEndEasyEvent(EasyEventSteps $I)
	{
		$I->wantToTest('create Easy Event on front-end');
		$I->createEasyEvent($this->menuItem, $this->eventName, $this->categoryName, $this->venueName);
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
		$I->deleteAllSession();
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