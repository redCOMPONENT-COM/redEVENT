<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for textsnippets list
 *
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventViewTextsnippets extends RViewCsv
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		$columns = array(
			'id' => 'id',
			'text_name' => 'text_name',
			'text_description' => 'text_description',
			'text_field' => 'text_field',
			'language' => 'language',
		);

		return $columns;
	}
}
