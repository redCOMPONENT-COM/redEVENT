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
    public static $URLSection               = 'administrator/index.php?option=com_redform&view=sections';

    /**
     * Include url of Field page
     *
     * @var   string
     * @since 1.0.0
     */
    public static $URLField                 = 'administrator/index.php?option=com_redform&view=fields';

    /**
     * Include url of Field page
     *
     * @var   string
     * @since 1.0.0
     */
    public static $URLForm                  = 'administrator/index.php?option=com_redform&view=forms';

    /**
     * Title of Section page.
     * @var   string
     * @since 1.0.0
     */
    public static $SectionTitle             = "Sections";

    /**
     * Title of Section page.
     * @var   string
     * @since 1.0.0
     */
    public static $SectionTitleNew          = "Name";

    /**
     * Title of Field  page.
     * @var   string
     * @since 1.0.0
     */
    
    public static $FieldTitle               = "Fields";

    /**
     * Title of Form  page.
     * @var   string
     * @since 1.0.0
     */
    public static $FormTitle                = "Forms";

    /**
     * Title of Form name.
     * @var   string
     * @since 1.0.0
     */
    public static $FormTitleNew             = "Form name";

    /**
     * Title of Form name.
     * @var   string
     * @since 1.0.0
     */
    public static $FormFields               = "Form field";

    /**
     * Title of this page new category.
     * @var   string
     * @since 1.0.0
     */
    public static $FieldTitleNew            = "Name";

    /**
     * Locator for field name
     * @var array
     * @since 1.0.0
     */
    public static $fieldName                = '#jform_name';

    /**
     * Locator for field name
     * @var array
     * @since 1.0.0
     */
    public static $fieldClass               ='#jform_class';

    /**
     * Locator for field description
     * @var array
     * @since 1.0.0
     */
    public static $fieldDescription         = 'jform_description';

    /**
     * Locator for field name
     * @var array
     * @since 1.0.0
     */
    public static $inputField               = '#jform_field';

    /**
     * Locator for field name
     * @var array
     * @since 1.0.0
     */
    public static $inputFieldType           = 'jform_fieldtype';

    /**
     * Locator for field header
     * @var array
     * @since 1.0.0
     */
    public static $inputFieldHeader         = 'jform_field_header';

    /**
     * Locator for tooltip
     * @var array
     * @since 1.0.0
     */
    public static $tooltip                  = '#jform_tooltip';

    /**
     * Locator for default
     * @var array
     * @since 1.0.0
     */

    public static $default                  = '#jform_default';
    /**
     * Locator for default
     * @var string
     * @since 1.0.0
     */
    public static $fieldId                  = 'jform_field_id';

    /**
     * Locator for field name
     * @var array
     * @since 1.0.0
     */
    public static $inputFormName            = '#jform_formname';

    /**
     * Locator for form Tabs
     * @var array
     * @since 1.0.0
     */
    public static $formTabs                 = '//*[@id="formTabs"]/li/a[normalize-space(text()) = "Fields"]';
    public static function returnValueSection($params)
    {
        $path = '//*[@id="sectionList"]//td//*[contains(., "' . $params['name'] . '")]';

        return $path;
    }

    public static function returnValueField($params)
    {
        $path = '//*[@id="fieldList"]//td//*[contains(., "' . $params['name'] . '")]';

        return $path;
    }

    public static function returnValueForm($params)
    {
        $path ='//*[@id="formList"]//td//*[contains(., "' . $params['name'] . '")]';

        return $path;
    }
}