<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Categories Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerCategories extends RControllerAdmin
{
	/**
	 * The method => state map.
	 *
	 * @var  array
	 */
	protected $states = array(
		'publish' => 1,
		'unpublish' => 0,
		'archive' => -1
	);
}
