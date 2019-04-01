<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Step\Acceptance;

use \Page\Acceptance\Administrator\redFormManagerPage;
class redFormManagerSteps extends AdminRedevent
{
	/**
	 * Create a section
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createRedformSection($params)
	{
		$I = $this;
		$I->amOnPage(redFormManagerPage::$URLSection);
		$I->waitForText(redFormManagerPage::$sectionTitle, 30, redFormManagerPage::$H1);
		$I->click(redFormManagerPage::$buttonNew);
		$I->waitForText(redFormManagerPage::$sectionTitleNew, 30, redFormManagerPage::$label);
		$I->fillField(redFormManagerPage::$fieldName, $params['name']);

		if (!empty($params['class']))
		{
			$I->fillField(redFormManagerPage::$fieldClass, $params['class']);
		}

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById(redFormManagerPage::$fieldDescription, $params['description']);
		}

		$I->click('Save & Close');
	}

	/**
	 * Create a section
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createRedformSectionIfNotExists($params)
	{
		$I = $this;
		$I->amOnPage(redFormManagerPage::$URLSection);
		$I->waitForText(redFormManagerPage::$sectionTitle, 30, redFormManagerPage::$H1);

		if ($I->isElementPresent($params))
		{
			return;
		}

		$I->createRedformSection($params);
	}

	/**
	 * Create a section
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createRedformFieldIfNotExists($params)
	{
		$I = $this;
		$I->amOnPage(redFormManagerPage::$URLField);
		$I->waitForText(redFormManagerPage::$fieldTitle, 30, redFormManagerPage::$H1);

		if ($I->isElementPresent($params))
		{
			return;
		}

		$I->createRedformField($params);
	}

	/**
	 * Create a field
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createRedformField($params)
	{
		$I = $this;
		$I->amOnPage(redFormManagerPage::$URLField);
		$I->waitForText(redFormManagerPage::$fieldTitle, 30, redFormManagerPage::$H1);
		$I->click(redFormManagerPage::$buttonNew);
		$I->waitForText(redFormManagerPage::$fieldTitleNew, 30, redFormManagerPage::$label);
		$I->fillField(redFormManagerPage::$inputField, $params['name']);
		$I->selectOptionInChosenById(redFormManagerPage::$inputFieldType, $params['fieldtype']);

		if (isset($params['field_header']))
		{
			$I->fillField(redFormManagerPage::$inputFieldHeader, $params['field_header']);
		}

		if (isset($params['tooltip']))
		{
			$I->fillField(redFormManagerPage::$tooltip, $params['tooltip']);
		}

		if (isset($params['default']))
		{
			$I->fillField(redFormManagerPage::$default, $params['default']);
		}

		$I->click(redFormManagerPage::$buttonSaveClose);
	}

	/**
	 * Create a Form if doesn't exist
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createRedformFormIfNotExists($params)
	{
		$I = $this;
		$I->amOnPage(redFormManagerPage::$URLForm);
		$I->waitForText(redFormManagerPage::$formTitle, 30, redFormManagerPage::$H1);

		if ($I->isElementPresent($params))
		{
			return;
		}

		$I->createRedformForm($params);
	}

	/**
	 * Create a form
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createRedformForm($params)
	{
		$I = $this;
		$I->amOnPage(redFormManagerPage::$URLForm);
		$I->waitForText(redFormManagerPage::$formTitle, 30, redFormManagerPage::$H1);
		$I->click(redFormManagerPage::$buttonNew);
		$I->waitForText(redFormManagerPage::$formTitleNew, 30, redFormManagerPage::$label);
		$I->fillField(redFormManagerPage::$inputFormName, $params['name']);
        $I->click('//label[@for="jform_formexpires0"]');
		$I->click(redFormManagerPage::$buttonSaveClose);

		if (!empty($params['fields']))
		{
			$I->waitForText(redFormManagerPage::$messageSaveSuccess, 30, redFormManagerPage::$message);
			$I->amOnPage(redFormManagerPage::$URLForm);
			$I->fillField(redFormManagerPage::$fieldSearch, $params['name']);
			$I->click(redFormManagerPage::$buttonSearch);
			$I->seeElement(redFormManagerPage::$valueForm);
			$I->click(redFormManagerPage::$valueForm);
			$I->waitForText(redFormManagerPage::$formTitleNew, 30, redFormManagerPage::$label);

			foreach ($params['fields'] as $fieldName)
			{
				$I->click(redFormManagerPage::$formTabs);

				$I->click(redFormManagerPage::$buttonNew);
				$I->waitForText(redFormManagerPage:: $formFields, 30, redFormManagerPage::$H1);
				$I->selectOptionInChosenByIdUsingJs(redFormManagerPage:: $fieldId, $fieldName);
				$I->click(redFormManagerPage::$buttonSaveClose);

				$I->waitForText(redFormManagerPage::$messageSaveSuccess, 30, redFormManagerPage::$message);
			}
		}
	}

    /**
     * @param $params
     * @return bool
     */
	protected function isElementPresent($params)
	{
		$I = $this;

		try
		{
			$I->See($params['name']);
			return true;
		}
		catch (\PHPUnit_Framework_AssertionFailedError $f)
		{
			return false;
		}
	}

	/**
	 * Create a form
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createMinimalRegistrationForm($params)
	{
		$I = $this;
		$I->createRedformSection(['name' => $params['name']]);
		$I->createRedformField(['name' => 'Name', 'fieldtype' => 'Textfield']);
		$I->createRedformField(['name' => 'Email', 'fieldtype' => 'E-mail']);
		$I->createRedformForm(['name' => 'Registration', 'fields' => ['Name', 'Email']]);
	}
}
