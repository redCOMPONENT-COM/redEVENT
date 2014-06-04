<?php
/**
 * @package     Redevent
 * @subpackage  mod_redevent_globase
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

// Register library prefix
JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
JLoader::registerPrefix('RedForm', JPATH_LIBRARIES . '/redform');

require_once JPATH_SITE . '/components/com_redevent/helpers/route.php';
require_once JPATH_SITE . '/components/com_redevent/helpers/helper.php';
require_once JPATH_SITE . '/components/com_redevent/classes/image.class.php';
require_once JPATH_SITE . '/components/com_redevent/classes/useracl.class.php';

$nyhedsbrev = modRedEventGlobaseHelper::getNyhedsbrevOptions($params);

$document = JFactory::getDocument();
$document->addStyleSheet( JURI::base() . '/modules/mod_redevent_globase/mod_redevent_globase.css');
$document->addScript(JURI::base() . '/modules/mod_redevent_globase/mod_redevent_globase.js');

$action = "index.php?option=com_redform&controller=redform&task=save";

if ($params->get('target', 'post') == 'modal')
{
	$action .= "&modal=1&tmpl=component";
	JHtml::_('behavior.modal');
	$document->addScript(JURI::base() . '/modules/mod_redevent_globase/mod_redevent_globase_modal.js');
}
else
{
	$document->addScript(JURI::base() . '/modules/mod_redevent_globase/mod_redevent_globase_post.js');
}

require(JModuleHelper::getLayoutPath('mod_redevent_globase'));
