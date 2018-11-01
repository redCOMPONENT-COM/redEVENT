<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

class CustomFieldsManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL = 'administrator/index.php?option=com_redevent&view=customfields';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $customFieldsTitle ='Custom fields';

	/**
	 * Title  of this page new Custom fields
	 * @var   string
	 * @since 1.0.0
	 */
	public static $customFieldsTitleNew= "Add/edit custom field - redEVENT";

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldName = '#jform_name';

	/**
	 * Locator for field tag
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldTag ='#jform_tag';

	/**
	 * Locator for field object
	 * @var string
	 * @since 1.0.0
	 */
	public static $fieldObject ='jform_object_key';

	/**
	 * Locator for field type
	 * @var string
	 * @since 1.0.0
	 */
	public static $fieldType ='jform_type';

	/**
	 * Button Save
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public static $buttonSave ='//button[contains(@onclick, "customfield.save")]';

	/**
	 * Locator for object on table
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public static $objectResult ='//table[@id=\'table-items\']/tbody/tr[1]/td[7]';

	/**
	 * Locator for type on table
	 *
	 * @var string
	 * @since 1.0.0s
	 */
	public static $typeResult ='//table[@id=\'table-items\']/tbody/tr[1]/td[8]';
}