<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Modules
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Categories Module helper
 *
 * @package     Redevent.Frontend
 * @subpackage  Modules
 * @since       2.0
 */
class ModRedEventCategoriesHelper
{
	/**
	 * Get items
	 *
	 * @param   JRegistry  $params  plugin params
	 *
	 * @return array
	 */
	public static function getList($params)
	{
		$db = JFactory::getDBO();

		$query = self::_buildQuery($params);

		$db->setQuery($query);
		$res = $db->loadObjectList();

		if ($params->get('mode', 0))
		{
			// Tree display
			$res = self::_getTree($res);
		}

		return $res;
	}

	/**
	 * get category tree for selected categories
	 *
	 * @param   array  $selected  category ids that we want sorted in tree
	 *
	 * @return array
	 */
	private static function _getTree($selected)
	{
		$tree = array();

		foreach ($selected as $cat)
		{
			if ($cat->parent_id == 0)
			{
				$tree[] = self::_getChildren($cat, $selected);
			}
		}

		return $tree;
	}

	/**
	 * recursely look for category children
	 *
	 * @param   object  $parent  parent category
	 * @param   array   $cats    allowed category ids
	 *
	 * @return array
	 */
	private static function _getChildren($parent, $cats)
	{
		$parent->children = array();

		foreach ($cats as $k => $c)
		{
			if ($c->parent_id == $parent->id)
			{
				$parent->children[] = self::_getChildren($c, $cats);
			}
		}

		return $parent;
	}

	/**
	 * Method to load the Categories
	 *
	 * @param   JRegistry  $params  plugin params
	 *
	 * @return array
	 */
	private static function _buildQuery($params)
	{
		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.*, SUM(CASE WHEN x.published THEN 1 ELSE 0 END) AS assignedevents')
			->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug')
			->from('#__redevent_categories AS c')
			->join('LEFT', '#__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt')
			->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.category_id = child.id')
			->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id')
			->join('LEFT', '#__redevent_events AS a ON x.eventid = a.id')
			->where('child.published = 1')
			->group('c.id');

		$eventstate = array();
		$eventstate[] = 'x.published = 1';

		if ($params->get('display_all_categories', 1))
		{
			$eventstate[] = ' x.id IS NULL ';
		}

		$query->where('(' . implode(' OR ', $eventstate) . ')');

		if (JFactory::getApplication()->getLanguageFilter())
		{
			$query->where('(a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR a.language IS NULL)');
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		if ($params->get('parent', 0))
		{
			$query->where('c.parent_id = ' . $db->Quote($params->get('parent', 0)));
		}

		$query->where('(c.access IN (' . $gids . '))');

		switch ($params->get('ordering', 0))
		{
			case 1:
				$query->order('c.name');
				break;
			case 0:
			default:
			$query->order('c.ordering');
		}

		return $query;
	}

	/**
	 * print category with dt/dl structure for accordeon
	 *
	 * @param   object   $category   category object
	 * @param   int      $depth      current depth
	 * @param   boolean  $showcount  show events count
	 * @param   array    $currents   currently 'opened' category
	 *
	 * @return string
	 */
	public static function printDtCat($category, $depth, $showcount = 1, $currents)
	{
		$link = JRoute::_(RedeventHelperRoute::getCategoryeventsRoute($category->slug));
		$txt = $showcount ? $category->name . ' (' . $category->assignedevents . ')' : $category->name;
		$opened_class = in_array($category->id, $currents) ? ' open' : '';

		ob_start();
		?>
		<dt class="accordion_toggler_<?php echo $depth . $opened_class; ?>">
			<?php echo JHTML::link($link, $txt); ?>
		</dt>
		<dd class="sub_accordion accordion_content_<?php echo $depth; ?>">
			<?php if (isset($category->children) && count($category->children)): ?>
				<dl>
					<?php foreach ($category->children as $c) echo self::printDtCat($c, $depth + 1, $showcount, $currents); ?>
				</dl>
			<?php endif; ?>
		</dd>
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * print category with flat structur
	 *
	 * @param   object   $category   category object
	 * @param   boolean  $showcount  show events count
	 * @param   arrray   $currents   currently 'opened' category
	 *
	 * @return string
	 */
	public static function printFlatCat($category, $showcount = 1, $currents)
	{
		$link = JRoute::_(RedeventHelperRoute::getCategoryeventsRoute($category->slug));
		$txt = $showcount ? $category->name . ' (' . $category->assignedevents . ')' : $category->name;
		$opened_class = in_array($category->id, $currents) ? ' open' : '';

		ob_start();
		?>
		<li class="<?php echo $opened_class; ?>"><?php echo JHTML::link($link, $txt); ?></li>
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * return array of ids category parent
	 *
	 * @param   int  $catid  category id
	 *
	 * @return array
	 */
	public static function getParentsCats($catid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('p.id')
			->from('#__redevent_categories AS p')
			->from('#__redevent_categories AS current')
			->where('current.id = ' . $db->Quote($catid))
			->where('p.lft <= current.lft AND p.rgt >= current.rgt');

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}
}
