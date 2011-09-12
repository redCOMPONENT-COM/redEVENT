<?php
/**
 * @package redevent
 * @subpackage mod_redevent_categories
 * @copyright (C) 2011 Redweb.dk
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');

/**
 * RedEvent Categories Module helper
 *
 * @package redevent
 * @subpackage mod_redevent_categories
 * @since		2.0
 */
class modRedEventCategoriesHelper
{	
	public function getList($params)
	{
		$db = &JFactory::getDBO();
		
		$query = self::_buildQuery($params);
		
		$db->setQuery($query);
		$res = $db->loadObjectList();
		
		if ($params->get('mode', 0))
		{ // tree display
			$res = self::_getTree($res);
		}
		
		return $res;
	}
	
	/**
	 * get category tree for selected categories
	 * 
	 * @param array category ids that we want sorted in tree
	 * @return array
	 */
	private function _getTree($selected)
	{		
		$tree = array();
		foreach ($selected as $cat) 
		{
			if ($cat->parent_id == 0) {
				$tree[] = self::_getChildren($cat, $selected);
			}
		}
		return $tree;
	}
	
	/**
	 * recursely look for category children
	 * 
	 * @param object $parent
	 * @param array $cats
	 * @param array allowed category ids
	 */
	private function _getChildren($parent, $cats)
	{
		$parent->children = array();
		foreach ($cats as $k => $c)
		{
			if ($c->parent_id == $parent->id) {
				$parent->children[] = self::_getChildren($c, $cats);
			}
		}
		return $parent;
	}
		
	/**
	* Method to load the Categories
	*
	* @access private
	* @return array
	*/
	private function _buildQuery($params)
	{
		//initialize some vars
		$mainframe = &JFactory::getApplication();
		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');
	
		$acl = &UserAcl::getInstance();
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
	
		//check archive task and ensure that only categories get selected if they contain a published/archived event
		$task 	= JRequest::getVar('task', '', '', 'string');
		$eventstate = array();
		$eventstate[] = 'x.published = 1';
		if ($params->get('display_all_categories', 1)) {
			$eventstate[] = ' x.id IS NULL ';
		}
		$eventstate = ' AND ('.implode(' OR ', $eventstate).')';
	
	
		//get categories
		$query = ' SELECT c.*, SUM(CASE WHEN x.published THEN 1 ELSE 0 END) AS assignedevents, '
		. '   CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
		. ' FROM #__redevent_categories AS c '
		. ' LEFT JOIN #__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt '
		. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = child.id '
		. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id '
	          
		. ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id AND gc.group_id IN ('.$gids.')'
		        
		. ' WHERE child.published = 1 '
		.     $eventstate
		;
	
		if ($params->get('parent', 0)) {
			$query .= ' AND c.parent_id = '. $this->_db->Quote($params->get('parent', 0));
		}
		$query .= ' AND (c.private = 0 OR gc.id IS NOT NULL) ';
	
		$query .= ' GROUP BY c.id ';
	
		return $query;
	}
	
	function printDtCat($category, $depth, $showcount = 1)
	{
		$link = JRoute::_(RedeventHelperRoute::getCategoryeventsRoute($category->slug));
		$txt  = $showcount ? $category->catname.' ('.$category->assignedevents.')' : $category->catname;
		ob_start();
		?>
				<dt class="accordion_toggler_<?php echo $depth; ?>">
					<?php echo JHTML::link($link, $txt); ?>
				</dt>
		    <dd class="sub_accordion accordion_content_<?php echo $depth; ?>">
		    <?php if (count($category->children)): ?>
		    	<dl>
		    	<?php foreach ($category->children as $c) echo self::printDtCat($c, $depth+1, $showcount); ?>
		    	</dl>
		    <?php endif; ?>
		    </dd>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html; 
	}
}