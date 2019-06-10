<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 6/10/2019
 * Time: 3:14 PM
 */
use Faker\Generator;
use Faker\Factory;
use Step\Acceptance\Administrator\CategoryManagerSteps;
use Step\Acceptance\JoomlaManagerSteps;
use Step\Acceptance\FrontEndManagerSteps;
use Step\Acceptance\Administrator\VenueCategoryManagerSteps;
use Step\Acceptance\Administrator\VanueManagerSteps;

class VenueSubmissionCest
{
    /**
     * @var   Generator
     * @since 1.0.0
     */
    protected $faker;

    /**
     * @var string
     */
    protected $categoryName;

    /**
     * @var string
     */
    protected $venueName;

    /**
     * @var
     */
    protected $menuItem;

    /**
     * @var string
     */
    protected $menuCategory;

    /**
     * @var
     */
    protected $menuName;

    /**
     * @var
     */
    protected $username;

    /**
     * @var
     */
    protected $password;

    /**
     * @var
     */
    protected $email;


    public function __construct()
    {
        $this->faker         = Factory::create();

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
     */
    public function _before(\AcceptanceTester $i)
    {
        $i->doAdministratorLogin();
    }

    /**
     * @param CategoryManagerSteps $I
     * @throws Exception
     */
    public function createMenuItem(JoomlaManagerSteps $I)
    {
        $I->wantToTest("I want to create menu item");
        $I->createNewMenuItem($this->menuName, $this->menuCategory, $this->menuItem);
    }

    /**
     * @param VenueCategoryManagerSteps $I
     * @throws Exception
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
     */
    public function createVenue(JoomlaManagerSteps $I, $scenario)
    {
        $I->wantToTest("I want to create venue on frontend");
        $I->createNewSuperuser($this->username, $this->username, $this->password, $this->email);
        $I = new FrontEndManagerSteps($scenario);
        $I->openNewTab();
        $I->loginFrontend($this->username, $this->password);
        $I->checkVenueSubmission($this->menuName, $this->venueName, $this->categoryName);
        $I->closeTab();
    }

    /**
     * @param VanueManagerSteps $I
     * @param $scenario
     * @throws Exception
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