<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;


class AbstractPage
{
	/**
	 * Locator for H1
	 * @var array
	 * @since 1.0.0
	 */
	public static $H1                         = ['css' => 'H1'];

	/**
	 * Locator for label
	 * @var array
	 * @since 1.0.0
	 */
	public static $label                      = ['css' => 'label'];

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldName                   = '#jform_name';

	/**
	 * Locator for field description
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldDescription            = 'jform_description';

	/**
	 * Locator for message
	 * @var string
	 */
	public static $message                     = '#system-message-container';

	/**
	 * Button new
	 * @var string
	 */
	public static $buttonNew                   = 'New';

	/**
	 * Button edit
	 * @var string
	 */
	public static $buttonEdit                  = 'Edit';

	/**
	 * Button Import/Export
	 * @var string
	 */
	public static $buttonImportExport          = 'Import/Export';

	/**
	 * Button Publish
	 * @var string
	 */
	public static $buttonPublish               = 'Publish';

	/**
	 * Button unpublish
	 * @var string
	 */
	public static $buttonUnpublish             = 'Unpublish';

	/**
	 * Button Delete
	 * @var string
	 */
	public static $buttonDelete                = 'Delete';

	/**
	 * Button Save
	 * @var string
	 */
	public static $buttonSave                  = 'Save';

	/**
	 * Button Save & Close
	 * @var string
	 */
	public static $buttonSaveClose             = 'Save & Close';

	/**
	 * Button Save & New
	 * @var string
	 */
	public static $buttonSaveNew               = 'Save & New';

	/**
	 * Button Save as Copy
	 * @var string
	 */
	public static $buttonSaveCopy              = 'Save as Copy';

	/**
	 * Button Cancel
	 * @var string
	 */
	public static $buttonCancel                = 'Cancel';

	/**
	 * Button Clear
	 * @var string
	 */
	public static $buttonClear                 = 'Clear';

	/**
	 * Message Save Success
	 * @var string
	 */
	public static $messageSaveSuccess          = 'Item saved.';

	/**
	 * Message delete success
	 * @var string
	 */
	public static $messageDeleteProductSuccess = '1 item(s) deleted';

	/**
	 * Button search
	 * @var string
	 */
	public static $buttonSearch                = '//button[@type=\'submit\' and @data-original-title=\'Search\']';

	/**
	 * Field search
	 * @var string
	 */
	public static $fieldSearch                 = '#filter_search';

	/**
	 * Locator for  Name item
	 * @var string
	 */
	public static $tableResult                = '//table[@id=\'table-items\']/tbody/tr[1]/td[5]';

    /**
     * @var string
     */
    public static $toggleEditor = "//div[@id='typewebform']//a[@xpath='1']";
}
