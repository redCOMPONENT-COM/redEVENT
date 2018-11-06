<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \Step\Acceptance\JoomlaManagerSteps;
class UninstallExtensionCept
{
    protected $SessionName1;

    /**
     * @var   string
     * @since 1.0.0
     */
    protected $nameRedEventComponent;

    /**
     * VenueCategoryManagerCest constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->nameRedEventComponent = 'redEVENT - Component';

    }

    public function _before(\AcceptanceTester $i)
    {
        $i->doAdministratorLogin();
    }

    /**
     * @param JoomlaManagerSteps $I
     * @throws Exception
     */
    public function uninstall( JoomlaManagerSteps $I )
    {
        $I->wantTo('Uninstall redEVENT Extension');
        $I->uninstallExtension($this->nameRedEventComponent);
    }

}