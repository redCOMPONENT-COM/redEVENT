<?php
namespace Step\Acceptance;

class Adminredevent extends \AcceptanceTester
{
	/**
	 * Create a Bundle
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createBundle($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=bundles');
		$I->waitForText('Bundles', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "bundle.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_description', $params['description']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "bundle.save")]']);
	}

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
	 * Create a session
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createSession($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=sessions');
		$I->waitForText('Sessions', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "session.add")]']);
		$I->waitForText('Evennt', 30, ['css' => 'label']);

		$event = isset($params['event']) ? $params['event'] : 'Event 1';
		$I->selectOptionInChosenByIdUsingJs('jform_eventid', $event);

		$venue = isset($params['venue']) ? $params['venue'] : 'Venue 1';
		$I->selectOptionInChosenByIdUsingJs('jform_venueid', $venue);

		if (!empty($params['title']))
		{
			$I->fillField(['id' => 'jform_title'], $params['title']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "session.save")]']);
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
	 * Create a template
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createTemplate($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=eventtemplates');
		$I->waitForText('Event Templates', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "eventtemplate.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		if (!empty($params['meta_description']))
		{
			$I->fillField(['id' => 'jform_meta_description'], $params['meta_description']);
		}

		if (!empty($params['meta_keywords']))
		{
			$I->fillField(['id' => 'jform_meta_keywords'], $params['meta_keywords']);
		}

		if (!empty($params['redform']))
		{
			$I->click(['xpath' => '//*[@id="eventTab"]/li/a[*/text() = "Registration"]']);
			$I->selectOptionInChosenByIdUsingJs('jform_redform_id', $params['redform']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "eventtemplate.save")]']);
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
		$I->amOnPage('administrator/index.php?option=com_redform&view=sections');
		$I->waitForText('Sections', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "section.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		if (!empty($params['class']))
		{
			$I->fillField(['id' => 'jform_class'], $params['class']);
		}

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_description', $params['description']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "section.save")]']);
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
		$I->amOnPage('administrator/index.php?option=com_redform&view=sections');
		$I->waitForText('Sections', 30, ['css' => 'H1']);

		if ($I->isElementPresent('//*[@id="table-items"]//td//*[contains(., "' . $params['name'] . '")]'))
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
		$I->amOnPage('administrator/index.php?option=com_redform&view=fields');
		$I->waitForText('Fields', 30, ['css' => 'H1']);

		if ($I->isElementPresent('//*[@id="fieldList"]//td//*[contains(., "' . $params['name'] . '")]'))
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
		$I->amOnPage('administrator/index.php?option=com_redform&view=fields');
		$I->waitForText('Fields', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "field.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_field'], $params['name']);
		$I->selectOptionInChosenByIdUsingJs('jform_fieldtype', $params['fieldtype']);

		if (isset($params['field_header']))
		{
			$I->fillField(['id' => 'jform_field_header'], $params['field_header']);
		}

		if (isset($params['tooltip']))
		{
			$I->fillField(['id' => 'jform_tooltip'], $params['tooltip']);
		}

		if (isset($params['default']))
		{
			$I->fillField(['id' => 'jform_default'], $params['default']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "field.save")]']);
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
		$I->amOnPage('administrator/index.php?option=com_redform&view=forms');
		$I->waitForText('Forms', 30, ['css' => 'H1']);

		if ($I->isElementPresent('//*[@id="formList"]//td//*[contains(., "' . $params['name'] . '")]'))
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
		$I->amOnPage('administrator/index.php?option=com_redform&view=forms');
		$I->waitForText('Forms', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "form.add")]']);
		$I->waitForText('Form name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_formname'], $params['name']);

		$I->click(['xpath' => '//button[contains(@onclick, "form.save")]']);

		if (!empty($params['fields']))
		{
			$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
			$I->click('//*[@id="formList"]//td//*[contains(., "' . $params['name'] . '")]');
			$I->waitForText('Form name', 30, ['css' => 'label']);

			foreach ($params['fields'] as $fieldName)
			{
				$I->click(['xpath' => '//*[@id="formTabs"]/li/a[normalize-space(text()) = "Fields"]']);

				$I->click(['xpath' => '//button[contains(@onclick, "formfield.add")]']);
				$I->waitForText('Form field', 30, ['css' => 'h1']);
				$I->selectOptionInChosenByIdUsingJs('jform_field_id', $fieldName);
				$I->click(['xpath' => '//button[contains(@onclick, "formfield.save")]']);

				$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
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

	protected function isElementPresent($element)
	{
		$I = $this;

		try
		{
			$I->seeElement($element);
		}
		catch (\PHPUnit_Framework_AssertionFailedError $f)
		{
			return false;
		}

		return true;
	}
}
