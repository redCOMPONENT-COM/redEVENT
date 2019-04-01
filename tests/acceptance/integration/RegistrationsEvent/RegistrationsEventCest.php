<?php


use Faker\Factory;
use Page\Acceptance\Administrator\AbstractPage;
use Step\Acceptance\Administrator\EventManagerSteps;
use Step\Acceptance\Administrator\TemplateManagerSteps;

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
        $this->eventName         = $this->faker->bothify("Event Name  ##??");
    }
    public function _before(\AcceptanceTester $i)
    {
        $i->doAdministratorLogin();
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
}