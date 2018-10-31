<?php


namespace Page\Acceptance\Administrator;


class TemplateManagerPage extends AbstractPage
{
    /**
     * Include url of current page
     *
     * @var   string
     * @since 1.0.0
     */
    public static $URL               = 'administrator/index.php?option=com_redevent&view=eventtemplates';

    /**
     * Title of this page.
     * @var   string
     * @since 1.0.0
     */
    public static $Title      = 'Event Templates';

    /**
     * Title of this page new category.
     * @var   string
     * @since 1.0.0
     */
    public static $TitleNew   = "Name";

    /**
     * Locator for meta description
     * @var array
     * @since 1.0.0
     */
    public static $metaDescription                  = '#jform_meta_description';

    /**
     * Locator for field name
     * @var array
     * @since 1.0.0
     */
    public static $metaKeywords                  = '#jform_meta_keywords';

    /**
     * Locator for field name
     * @var array
     * @since 1.0.0
     */
    public static $redFormId                  = 'jform_redform_id';

    /**
     * Locator for  Name item
     * @var string
     */
    public static $tableResult                = '//table[@id=\'table-items\']/tbody/tr[1]/td[4]';

}