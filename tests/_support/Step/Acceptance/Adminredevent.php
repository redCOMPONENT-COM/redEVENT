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
	 * Create a Tag
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createCustomField($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=customfields');
		$I->waitForText('Custom fields', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "customfield.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);
		$I->fillField(['id' => 'jform_tag'], isset($params['tag']) ? $params['tag'] : $params['name']);

		$object = isset($params['object']) ? $params['object'] : 'event';
		$type = isset($params['type']) ? $params['type'] : 'text';

		$I->selectOptionInChosenByIdUsingJs('jform_object_key', $object);
		$I->selectOptionInChosenByIdUsingJs('jform_type', $type);

		$I->click(['xpath' => '//button[contains(@onclick, "customfield.save")]']);
	}

	/**
	 * Create an Event
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createEvent($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=events');
		$I->waitForText('Events', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "event.add")]']);
		$I->waitForText('title', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_title'], $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_datdescription', $params['description']);
		}

		$category_name = !empty($params['category_name']) ? $params['category_name'] : 'Category 1';
		$I->waitForElement(['id' => "jform_categories_chzn"], 30);
		$I->selectOptionInChosenByIdUsingJs('jform_categories', $category_name);

		$template_name = !empty($params['template_name']) ? $params['template_name'] : 'default template';
		$I->waitForElement(['id' => "jform_template_id"], 30);
		$I->selectOptionInChosenByIdUsingJs('jform_template_id', $template_name);

		$I->click(['xpath' => '//button[contains(@onclick, "event.save")]']);
	}

	/**
	 * Create a price group
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createPriceGroup($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=pricegroups');
		$I->waitForText('Price groups', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "pricegroup.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		$I->click(['xpath' => '//button[contains(@onclick, "pricegroup.save")]']);
	}

	/**
	 * Create a Role
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createRole($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=roles');
		$I->waitForText('Roles', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "role.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_description', $params['description']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "role.save")]']);
	}

	/**
	 * Create a Tag
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createTag($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=textsnippets');
		$I->waitForText('Text library', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "textsnippet.add")]']);
		$I->waitForText('Tag name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_text_name'], $params['name']);

		if (!empty($params['description']))
		{
			$I->fillField(['id' => 'jform_text_description'], $params['description']);
		}

		if (!empty($params['text']))
		{
			$I->fillTinyMceEditorById('jform_text_field', $params['text']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "textsnippet.save")]']);
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
}
