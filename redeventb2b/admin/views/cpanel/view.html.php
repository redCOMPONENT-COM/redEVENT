<?php
/**
 * @package     Redcomponent.redeventb2b
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013-2015 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Class Redeventb2bViewCpanel
 *
 * @since  2.5
 */
class Redeventb2bViewCpanel extends Redeventb2bViewAdmin
{
	/**
	 * Hide sidebar in cPanel
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display the control panel
	 *
	 * @param   string  $tpl  The template file to use
	 *
	 * @return   string
	 *
	 * @since   2.0
	 */
	public function display($tpl = null)
	{
		$this->user = JFactory::getUser();

		parent::display($tpl);
	}
}
