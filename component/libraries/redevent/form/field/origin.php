<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * RedEvent origin form field
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventFormFieldOrigin extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'origin';

	/**
	 * Method to get the origin options.
	 *
	 * @return  array  The option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT origin')
			->from('#__redevent_register')
			->where('origin <> ""')
			->order('origin ASC');

		$db->setQuery($query);
		$res = $db->loadColumn();

		$options = $res ? array_map(
				function($item)
				{
					return array('value' => $item, 'text' => $item);
				},
				$res
			)
			: array();

		return array_merge(parent::getOptions(), $options);
	}
}
