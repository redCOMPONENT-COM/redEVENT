<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Page\Acceptance\Administrator;


class redFormManagerPage extends AbstractPage
{
	/**
	 * Include url of Section  page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URLSection                  = 'administrator/index.php?option=com_redform&view=sections';

	/**
	 * Include url of Field page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URLField                    = 'administrator/index.php?option=com_redform&view=fields';

	/**
	 * Include url of Field page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URLForm                     = 'administrator/index.php?option=com_redform&view=forms';

	/**
	 * Title of Section page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $sectionTitle                = "Sections";

	/**
	 * Title of Section page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $sectionTitleNew             = "Name";

	/**
	 * Title of Field  page.
	 * @var   string
	 * @since 1.0.0
	 */

	public static $fieldTitle                  = "Fields";

	/**
	 * Title of Form  page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $formTitle                   = "Forms";

	/**
	 * Title of Form name.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $formTitleNew                = "Form name";

	/**
	 * Title of Form name.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $formFields                  = "Form field";

	/**
	 * Title of this page new category.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $fieldTitleNew               = "Name";

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldName                   = '#jform_name';

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldClass                  = '#jform_class';

	/**
	 * Locator for field description
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldDescription            = 'jform_description';

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $inputField                  = '#jform_field';

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $inputFieldType              = 'jform_fieldtype';

	/**
	 * Locator for field header
	 * @var array
	 * @since 1.0.0
	 */
	public static $inputFieldHeader            = 'jform_field_header';

	/**
	 * Locator for tooltip
	 * @var array
	 * @since 1.0.0
	 */
	public static $tooltip                     = '#jform_tooltip';

	/**
	 * Locator for default
	 * @var array
	 * @since 1.0.0
	 */

	public static $default                     = '#jform_default';
	/**
	 * Locator for default
	 * @var string
	 * @since 1.0.0
	 */
	public static $fieldId                     = 'jform_field_id';

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $inputFormName               = '#jform_formname';

	/**
	 * Field search
	 * @var string
	 */
	public static $fieldSearch                 = '#filter_search_forms';

	/**
	 * Locator for form Tabs
	 * @var array
	 * @since 1.0.0
	 */
	public static $formTabs                    = '//ul[@id="formTabs"]/li/a[normalize-space(text()) = "Fields"]';

	/**
	 * @param $params
	 * @return string
	 */
	public static $valueSection                = '//table[@id=\'sectionList\']/tbody/tr/td[4]';
	/**
	 * @param $params
	 * @return string
	 */
	public static $valueField                  = '//table[@id=\'fieldList\']/tbody/tr/td[3]';

	/**
	 * @param $params
	 * @return string
	 */
	public static $valueForm                   = '//table[@id=\'formList\']/tbody/tr/td[3]';

	/**
	 * @var string
	 */
	public static $formExpiresNo                 = '//label[@for="jform_formexpires0"]';

	/**
	 * @var string
	 */
	public static $fieldPlaceholder = '//input[@id=\'jform_params_placeholder\']';

	/**
	 * @var string
	 */
	public static $fieldTitleH1 ="//h1[contains(text(),'Field')]";
}