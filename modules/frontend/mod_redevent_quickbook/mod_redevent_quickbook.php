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

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

// Get helper
require_once 'helper.php';

$data = modRedEventQuickbookHelper::getData($params);

// Check if any results returned
if (!$data)
{
	return;
}

RHelperAsset::load('mod_redevent_quickbook.css', 'mod_redevent_quickbook');
RHelperAsset::load('mod_redevent_quickbook.js', 'mod_redevent_quickbook');

\Joomla\CMS\Factory::getDocument()->addScriptDeclaration($data->pricegroupjs);

$action = "index.php?option=com_redevent&task=registration.register";

if ($params->get('target', 'post') == 'modal')
{
	$action .= "&modal=1";
	JHtml::_('behavior.modal');
	RHelperAsset::load('mod_redevent_quickbook_modal.js', 'mod_redevent_quickbook');
}
else
{
	RHelperAsset::load('mod_redevent_quickbook_post.js', 'mod_redevent_quickbook');
}

$action = JRoute::_($action);

require JModuleHelper::getLayoutPath('mod_redevent_quickbook');
