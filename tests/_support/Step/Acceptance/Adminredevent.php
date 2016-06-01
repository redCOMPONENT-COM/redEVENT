<?php
namespace Step\Acceptance;

class Adminredevent extends \AcceptanceTester
{
	/**
	 * Create a Category
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createCategory($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=categories');
		$I->waitForText('Categories', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "category.add")]']);
		$I->waitForText('Category', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_description', $params['description']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "category.save")]']);
	}

	/**
	 * Create a Venue Category
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createVenueCategory($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=venuescategories');
		$I->waitForText('Venue categories', 30, ['css' => 'h1']);
		$I->click(['xpath' => '//button[contains(@onclick, "venuescategory.add")]']);
		$I->waitForText('Category', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_description', $params['description']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "venuescategory.save")]']);
	}

	/**
	 * Create a Venue
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createVenue($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=venues');
		$I->waitForText('Venues', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "venue.add")]']);
		$I->waitForText('Venue', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_venue'], $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_locdescription', $params['description']);
		}

		$category_name = !empty($params['category_name']) ? $params['category_name'] : 'Venue Category 1';
		$I->waitForElement(['id' => "jform_categories_chzn"], 30);
		$I->selectOptionInChosenByIdUsingJs('jform_categories', $category_name);

		$I->click(['xpath' => '//button[contains(@onclick, "venue.save")]']);
	}
}
