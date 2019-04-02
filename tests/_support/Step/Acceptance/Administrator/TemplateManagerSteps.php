<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
	public function createTemplateNew($params)
	{
		$I = $this;
		$I->amOnPage(TemplateManagerPage::$URL);
		$I->waitForText(TemplateManagerPage::$title, 30, TemplateManagerPage::$H1);
		$I->click(TemplateManagerPage::$buttonNew);
		$I->waitForText(TemplateManagerPage::$titleNew, 30, TemplateManagerPage::$label);
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
			$I->click('Registration');
			$I->selectOptionInChosenByIdUsingJs(TemplateManagerPage::$redFormId, $params['redform']);
		}

		$I->click(TemplateManagerPage::$buttonSaveClose);
	}

	/**
	 * Create a template
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createTemplateRegistration($params)
	{
		$I = $this;
		$I->amOnPage(TemplateManagerPage::$URL);
		$I->waitForText(TemplateManagerPage::$title, 30, TemplateManagerPage::$H1);
		$I->click(TemplateManagerPage::$buttonNew);
		$I->waitForText(TemplateManagerPage::$titleNew, 30, TemplateManagerPage::$label);
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
			$I->click('Registration');
			$I->selectOptionInChosenByIdUsingJs(TemplateManagerPage::$redFormId, $params['redform']);
		}
		if (!empty($params['redform']))
		{
			$I->click('Registrations types');
			$I->waitForElement(TemplateManagerPage::$enabled,30);
			$I->click(TemplateManagerPage::$enabled);
			$I->fillTinyMceEditorById(TemplateManagerPage::$editorTypeWebForm,"[redform]");
		}
		$I->click(TemplateManagerPage::$buttonSaveClose);
	}

	/**
	 * Create a template
	 *
	 * @param   string  $nameTemplate
	 *
	 * @return void
	 */
	public function createTemplate($nameTemplate)
	{
		$I = $this;
		$I->amOnPage(TemplateManagerPage::$URL);
		$I->waitForText(TemplateManagerPage::$title, 30, TemplateManagerPage::$H1);
		$I->click(TemplateManagerPage::$buttonNew);
		$I->waitForText(TemplateManagerPage::$titleNew, 30, TemplateManagerPage::$label);
		$I->fillField(TemplateManagerPage::$fieldName, $nameTemplate);
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
		$I->waitForText(TemplateManagerPage::$title, 120);
		$I->searchTemplate($nameTemplate);
		$I->see($nameTemplate, TemplateManagerPage::$tableResult);
		$I->checkAllResults();
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