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
defined('_JEXEC') or die('Restricted access');
$i = 0;
?>
<dl class="mod_re_cats_accordion">
	<?php foreach ($list as $cat): ?>
	<?php echo modRedEventCategoriesHelper::printDtCat($cat, 1, $params->get('show_count', 1)); ?>
	<?php endforeach; ?>
</dl>