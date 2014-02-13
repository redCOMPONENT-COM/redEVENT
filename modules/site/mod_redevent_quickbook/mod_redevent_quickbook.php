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
require_once (dirname(__FILE__) . '/helper.php');

require_once JPATH_SITE . '/components/com_redevent/helpers/route.php';
require_once JPATH_SITE . '/components/com_redevent/helpers/helper.php';
require_once JPATH_SITE . '/components/com_redevent/classes/image.class.php';
require_once JPATH_SITE . '/components/com_redevent/classes/useracl.class.php';

$data = modRedEventQuickbookHelper::getData($params);

// Check if any results returned
if (!$data) {
	return;
}

$document = JFactory::getDocument();
$document->addStyleSheet( JURI::base() . '/modules/mod_redevent_quickbook/mod_redevent_quickbook.css');
$document->addScript(JURI::base() . '/modules/mod_redevent_quickbook/mod_redevent_quickbook.js');

$document->addScriptDeclaration($data->pricegroupjs);

$action = "index.php?option=com_redevent&controller=registration&task=register";

if ($params->get('target', 'post') == 'modal')
{
	$action .= "&modal=1&tmpl=component";
	JHtml::_('behavior.modal');
	$document->addScript(JURI::base() . '/modules/mod_redevent_quickbook/mod_redevent_quickbook_modal.js');
}
else
{
	$document->addScript(JURI::base() . '/modules/mod_redevent_quickbook/mod_redevent_quickbook_post.js');
}

require(JModuleHelper::getLayoutPath('mod_redevent_quickbook'));
