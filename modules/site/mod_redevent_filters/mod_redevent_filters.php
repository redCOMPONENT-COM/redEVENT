<?php
/**
 * @package     Redevent
 * @subpackage  mod_redevent_quickbook
 * @copyright   (C) 2014 redcomponent.com
 * @license     GNU/GPL, see LICENCE.php
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

// Get helper
require_once dirname(__FILE__) . '/helper.php';

$data = modRedEventFiltersHelper::getData($params);

// Check if any results returned
if (!$data)
{
	return;
}

$model = modRedEventFiltersHelper::getModel();;

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'modules/mod_redevent_filters/mod_redevent_filters.css');
$document->addScript(JURI::base() . 'modules/mod_redevent_filters/mod_redevent_filters.js');

require(JModuleHelper::getLayoutPath('mod_redevent_filters'));
