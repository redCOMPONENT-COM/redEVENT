<?php
/**
 * Created by PhpStorm.
 * User: Trang
 * Date: 10/31/2018
 * Time: 3:55 PM
 */

namespace Step\Acceptance\Administrator;
use \Page\Acceptance\Administrator\TemplateManagerPage;
use Step\Acceptance\redFormManagerSteps;

class TemplateManagerSteps extends redFormManagerSteps
{
    /**
     * Create a template
     *
     * @param   array  $params  parameters
     *
     * @return void
     */
    public function createTemplate($params)
    {
        $I = $this;
        $I->amOnPage(TemplateManagerPage::$URL);
        $I->waitForText(TemplateManagerPage::$Title, 30, ['css' => 'H1']);
        $I->click(TemplateManagerPage::$buttonNew);
        $I->waitForText(TemplateManagerPage::$TitleNew, 30, ['css' => 'label']);
        $I->fillField(TemplateManagerPage::$fieldName, $params['name']);

        if (!empty($params['meta_description']))
        {
            $I->fillField(TemplateManagerPage::$metaDescription, $params['meta_description']);
        }

        if (!empty($params['meta_keywords']))
        {
            $I->fillField(TemplateManagerPage::$metaKeywords, $params['meta_keywords']);
        }

        if (!empty($params['redform']))
        {
            $I->click(['xpath' => '//*[@id="eventTab"]/li/a[*/text() = "Registration"]']);
            $I->selectOptionInChosenByIdUsingJs(TemplateManagerPage::$redFormId, $params['redform']);
        }

        $I->click(TemplateManagerPage::$buttonSaveClose);
    }

    /**
     * @param $nameTemplate
     * @throws \Exception
     */
    public function searchTemplate($nameTemplate)
    {
        $I = $this;
        $I->amOnPage(TemplateManagerPage::$URL);
        $I->fillField(TemplateManagerPage::$fieldSearch, $nameTemplate);
        $I->click(TemplateManagerPage::$buttonSearch);
        $I->seeElement(TemplateManagerPage::$tableResult);
    }

    /**
     * @param $nameTemplate
     * @throws \Exception
     */
    public function deleteTemplate($nameTemplate)
    {
        $I = $this;
        $I->amOnPage(TemplateManagerPage::$URL);
        $I->waitForText(TemplateManagerPage::$Title, 120);
        $I->searchTemplate($nameTemplate);
        $I->see($nameTemplate, TemplateManagerPage::$tableResult);
        $I->click(TemplateManagerPage::$checkAll);
        $I->click(TemplateManagerPage::$buttonDelete);
        $I->wantTo('Test with delete category but then cancel');
        $I->cancelPopup();
        $I->wantTo('Test with delete product then accept');
        $I->click(TemplateManagerPage::$buttonDelete);
        $I->acceptPopup();
        $I->waitForText(TemplateManagerPage::$messageDeleteProductSuccess, 120, TemplateManagerPage::$message);
        $I->dontSee($nameTemplate);
    }
}