<?php
/**
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2005 - 2008 Christoph Lukes
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
?>
<div class="mod_redevent_venues_events">
<?php foreach ($list as $venue): ?>
<div class="venue-name"><?php echo current($venue)->venue; ?></div>
<?php echo JHTML::_('select.genericlist', modRedEventVenuesEventsHelper::getVenuesEventsOptions($venue), 'venue'.current($venue)->venueid, 'class="mod-ve-select"'); ?>
<?php endforeach; ?>
</div>