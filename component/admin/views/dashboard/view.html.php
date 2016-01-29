<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for the redEVENT home screen
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewDashboard extends RedeventViewAdmin
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

		// Get stats
		$this->version = $this->get('Version');
		$this->eventsStats = $this->get('EventsStats');
		$this->venuesStats = $this->get('VenuesStats');
		$this->categoriesStats = $this->get('CategoriesStats');

		parent::display($tpl);
	}
}
