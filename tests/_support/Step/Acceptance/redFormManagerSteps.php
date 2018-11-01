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
		$I->waitForText(redFormManagerPage::$SectionTitle, 30, redFormManagerPage::$H1);
		$I->click(redFormManagerPage::$buttonNew);
		$I->waitForText(redFormManagerPage::$SectionTitleNew, 30, redFormManagerPage::$label);
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
		$I->waitForText(redFormManagerPage::$SectionTitle, 30, redFormManagerPage::$H1);

		if ($I->isElementPresent(redFormManagerPage::returnValueSection($params)))
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
		$I->waitForText(redFormManagerPage::$FieldTitle, 30, redFormManagerPage::$H1);

		if ($I->isElementPresent(redFormManagerPage::returnValueField($params)))
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
		$I->waitForText(redFormManagerPage::$FieldTitle, 30, redFormManagerPage::$H1);
		$I->click(redFormManagerPage::$buttonNew);
		$I->waitForText(redFormManagerPage::$FieldTitleNew, 30, redFormManagerPage::$label);
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
		$I->waitForText(redFormManagerPage::$FormTitle, 30, redFormManagerPage::$H1);

		if ($I->isElementPresent(redFormManagerPage::returnValueForm($params)))
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
		$I->waitForText(redFormManagerPage::$FormTitle, 30, redFormManagerPage::$H1);
		$I->click(redFormManagerPage::$buttonNew);
		$I->waitForText(redFormManagerPage::$FormTitleNew, 30, redFormManagerPage::$label);
		$I->fillField(redFormManagerPage::$inputFormName, $params['name']);

		$I->click(redFormManagerPage::$buttonSaveClose);

		if (!empty($params['fields']))
		{
			$I->waitForText(redFormManagerPage::$messageSaveSuccess, 30, redFormManagerPage::$message);
			$I->click(redFormManagerPage::returnValueForm($params));
			$I->waitForText(redFormManagerPage::$FormTitleNew, 30, redFormManagerPage::$label);

			foreach ($params['fields'] as $fieldName)
			{
				$I->click(redFormManagerPage::$formTabs);

				$I->click(redFormManagerPage::$buttonNew);
				$I->waitForText(redFormManagerPage:: $FormFields, 30, redFormManagerPage::$H1);
				$I->selectOptionInChosenByIdUsingJs(redFormManagerPage:: $fieldId, $fieldName);
				$I->click(redFormManagerPage::$buttonSaveClose);

				$I->waitForText(redFormManagerPage::$messageSaveSuccess, 30, redFormManagerPage::$message);
			}
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
		$I->createRedformSectionIfNotExists(['name' => $params['name']]);
		$I->createRedformFieldIfNotExists(['name' => 'Name', 'fieldtype' => 'Textfield']);
		$I->createRedformFieldIfNotExists(['name' => 'Email', 'fieldtype' => 'E-mail']);
		$I->createRedformFormIfNotExists(['name' => 'Registration', 'fields' => ['Name', 'Email']]);
	}

}