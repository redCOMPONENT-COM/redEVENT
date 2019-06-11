<?php

/**
 * @package     redEVENT
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Faker\Generator;
use Faker\Factory;
use Step\Acceptance\Administrator\CategoryManagerSteps;
use Step\Acceptance\JoomlaManagerSteps;
use Step\Acceptance\FrontEndManagerSteps;
use Step\Acceptance\Administrator\VenueCategoryManagerSteps;
use Step\Acceptance\Administrator\VanueManagerSteps;

/**
 * Class VenueSubmissionCest
 * @since 3.2.7
 */
class VenueSubmissionCest
{
	/**
	 * @var   Generator
	 * @since 3.2.7
	 */
	protected $faker;

	/**
	 * @var string
	 * @since 3.2.7
	 */
	protected $categoryName;

	/**
	 * @var string
	 * @since 3.2.7
	 */
	protected $venueName;

	/**
	 * @var string
	 * @since 3.2.7
	 */
	protected $menuItem;

	/**
	 * @var string
	 * @since 3.2.7
	 */
	protected $menuCategory;

	/**
	 * @var string
	 * @since 3.2.7
	 */
	protected $menuName;

	/**
	 * @var string
	 * @since 3.2.7
	 */
	protected $username;

	/**
	 * @var string
	 * @since 3.2.7
	 */
	protected $password;

	/**
	 * @var string
	 * @since 3.2.7
	 */
	protected $email;

	/**
	 * VenueSubmissionCest constructor.
	 * @since 3.2.7
	 */
	public function __construct()
	{
		$this->faker = Factory::create();
		$this->email = $this->faker->email;
		$this->username = $this->faker->bothify("Test##");
		$this->password = $this->faker->password;

		$this->venueName = "Venue Demo";
		$this->categoryName = "Category Venue Demo";
		$this->menuName = $this->faker->bothify("Venue submission ##");
		$this->menuCategory      = 'redEVENT - Component';
		$this->menuItem = "Venue submission";
	}

	/**
	 * @param AcceptanceTester $i
	 * @throws Exception
	 * @since 3.2.7
	 */
	public function _before(\AcceptanceTester $i)
	{
		$i->doAdministratorLogin();
	}

	/**
	 * @param CategoryManagerSteps $I
	 * @throws Exception
	 * @since 3.2.7
	 */
	public function createMenuItem(JoomlaManagerSteps $I)
	{
		$I->wantToTest("I want to create menu item");
		$I->createNewMenuItem($this->menuName, $this->menuCategory, $this->menuItem);
	}

	/**
	 * @param VenueCategoryManagerSteps $I
	 * @throws Exception
	 * @since 3.2.7
	 */
	public function createCategoryVenue(VenueCategoryManagerSteps $I)
	{
		$I->wantToTest("I want to create category venue");
		$I->createVenueCategoryNew(
			array(
				'name' => $this->categoryName,
				'description' => ''
			)
		);
	}

	/**
	 * @param VanueManagerSteps $I
	 * @throws Exception
	 * @since 3.2.7
	 */
	public function createVenue(JoomlaManagerSteps $I, $scenario)
	{
		$I->wantToTest("I want to create venue on frontend");
        $I->createUser($this->username, $this->username, $this->password, $this->email);
		$I = new FrontEndManagerSteps($scenario);
		$I->openNewTab();
        $I->doFrontEndLogin($this->username, $this->password);
		$I->checkVenueSubmission($this->menuName, $this->venueName, $this->categoryName);
		$I->closeTab();
	}

	/**
	 * @param VanueManagerSteps $I
	 * @param $scenario
	 * @throws Exception
	 * @since 3.2.7
	 */
	public function deleteAll(VanueManagerSteps $I, $scenario)
	{
		$I->wantToTest("I want to delete venue");
		$I->deleteVenue($this->categoryName, $this->venueName);
		$I = new JoomlaManagerSteps($scenario);
		$I->delNewSuperUser($this->username);
		$I->delNewMenuItem($this->menuName);
	}
}