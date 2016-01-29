<?php
/**
 * @package     Redeventsync
 * @subpackage  Library
 * @copyright   Redeventsync (C) 2008-2015 Julien Vonthron. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Base view.
 *
 * @package     Redeventsync
 * @subpackage  View
 * @since       3.0
 */
abstract class ResyncViewAdmin extends RViewAdmin
{
	/**
	 * The component title to display in the topbar layout (if using it).
	 * It can be html.
	 *
	 * @var string
	 */
	protected $componentTitle = 'redEVENTSYNC';

	/**
	 * Do we have to display a sidebar ?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = true;

	/**
	 * The sidebar layout name to display.
	 *
	 * @var  boolean
	 */
	protected $sidebarLayout = 'sidebar';

	/**
	 * Do we have to display a topbar ?
	 *
	 * @var  boolean
	 */
	protected $displayTopBar = true;

	/**
	 * The topbar layout name to display.
	 *
	 * @var  boolean
	 */
	protected $topBarLayout = 'topbar';

	/**
	 * Do we have to display a topbar inner layout ?
	 *
	 * @var  boolean
	 */
	protected $displayTopBarInnerLayout = true;

	/**
	 * The topbar inner layout name to display.
	 *
	 * @var  boolean
	 */
	protected $topBarInnerLayout = 'topnav';

	/**
	 * True to display "Back to Joomla" link (only if displayJoomlaMenu = false)
	 *
	 * @var  boolean
	 */
	protected $displayBackToJoomla = false;

	/**
	 * True to display "Version 1.0.x"
	 *
	 * @var  boolean
	 */
	protected $displayComponentVersion = true;

	/**
	 * Redirect to another location
	 *
	 * @var  string
	 */
	protected $logoutReturnUri = 'index.php';

	/**
	 * Show the project switch
	 *
	 * @var  boolean
	 */
	protected $showProjectSwitch = true;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.<br/>
	 *                          name: the name (optional) of the view (defaults to the view class name suffix).<br/>
	 *                          charset: the character set to use for display<br/>
	 *                          escape: the name (optional) of the function to use for escaping strings<br/>
	 *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)<br/>
	 *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name<br/>
	 *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)<br/>
	 *                          layout: the layout (optional) to use to display the view<br/>
	 */
	public function __construct($config = array())
	{
		// If user is Super Admin (or has permission to manage the core component, enables Back2Joomla link)
		if (JFactory::getApplication()->isAdmin())
		{
			$this->displayBackToJoomla = true;
		}

		parent::__construct($config);

		$this->sidebarData = array(
			'active' => strtolower($this->_name),
			'view' => $this
		);
	}
}
