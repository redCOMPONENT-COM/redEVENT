<?php
/**
 * @version     2.5
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 - 2010 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('');


/**
 * Josetta! category translation Plugin helper
 *
 * @package     Josetta
 * @subpackage  josetta.JosettaReVenueCategoryHelper
 * @since       2.5
 */
abstract class JosettaReCategoryHelper
{
	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 */
	protected static $_categoriesOptionsPerLanguage = array();

	protected static $_categoriesDataPerLanguage = array();

	/**
	 * Returns a select list from the redevent categories
	 *
	 * @param   array  $config  An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 *
	 * @return  array
	 */
	public static function getCategoryOptionsPerLanguage($config = array('filter.published' => array(0, 1), 'filter.languages' => array()))
	{
		$hash = md5(serialize($config));

		if (!isset(self::$_categoriesOptionsPerLanguage[$hash]))
		{
			$config = (array) $config;

			// Read categories from db
			$items = self::getCategoriesPerLanguage($config);

			// B/C compat.
			foreach ($items as &$item)
			{
				$item->title = $item->name;
			}

			// Indent cat list, for easier reading
			$items = self::indentCategories($items);

			self::$_categoriesOptionsPerLanguage[$hash] = array();

			foreach ($items as &$item)
			{
				self::$_categoriesOptionsPerLanguage[$hash][] = JHtml::_('select.option', $item->id, str_replace('<sup>|_</sup>', '', $item->treename));
			}
		}

		return self::$_categoriesOptionsPerLanguage[$hash];
	}

	/**
	 * Returns raw k2 categories
	 *
	 * @param   array  $config  An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 *
	 * @return  array
	 */
	public static function getCategoriesPerLanguage( $config = array('filter.published' => array(0, 1), 'filter.languages' => array()), $index = null)
	{
		$hash = md5(serialize($config));

		if (!isset(self::$_categoriesDataPerLanguage[$hash]))
		{
			$config = (array) $config;
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('c.*');
			$query->from('#__redevent_venues_categories as c');

			if (!empty($config['filter.published']))
			{
				$query->where('c.published in (' . ShlDbHelper::arrayToIntvalList($config['filter.published']) . ')');
			}

			if (!empty($config['filter.languages']))
			{
				$query->where('c.language in (' . ShlDbHelper::arrayToQuotedList($config['filter.languages']) . ')');
			}

			$db->setQuery($query);
			$items = $db->loadObjectList($index);

			foreach ($items as &$item)
			{
				$item->title = $item->name;
			}

			self::$_categoriesDataPerLanguage[$hash] = $items;
		}

		return self::$_categoriesDataPerLanguage[$hash];
	}

	public static function indentCategories(& $rows, $root = 0)
	{
		$children = array ();

		if (count($rows))
		{
			foreach ($rows as $v)
			{
				$pt = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : array ();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$categories = JHTML::_('menu.treerecurse', $root, '', array (), $children);

		return $categories;
	}
}
