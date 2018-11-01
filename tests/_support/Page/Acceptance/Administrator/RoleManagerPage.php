<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

class RoleManagerPage extends AbstractPage
{
    /**
     * Include url of current page
     *
     * @var   string
     * @since 1.0.0
     */
    public static $URL = 'administrator/index.php?option=com_redevent&view=roles';

    /**
     * Title of this page.
     * @var   string
     * @since 1.0.0
     */
    public static $roleTitle      = "Roles";

    /**
     * Title  of this page new Custom Roles
     * @var   string
     * @since 1.0.0
     */
    public static $roleTitleNew   = "Add/edit role";

    /**
     * @var string
     * @since 1.0.0
     */
    public static $tableResult = '//table[@id=\'table-items\']/tbody/tr[1]/td[4]';
}