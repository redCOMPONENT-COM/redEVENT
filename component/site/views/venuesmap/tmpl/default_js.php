<?php
/**
 * @version 1.1 $Id: default.php 668 2008-05-12 14:32:13Z schlu $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<div id="redevent" class="jlmap">
  <div id="goback"><a href="javascript:history.back()"><?php echo JText::_('Back'); ?></a></div>
  <h1 class="componentheading">
    <?php echo JText::_('Locations'); ?>
  </h1>

<?php if ($this->elsettings->gmapkey): ?>

<script type="text/javascript">
<!--
var venueurl = '<?php echo JRoute::_('index.php?option=com_redevent&view=venue&format=raw', false); ?>';
var countries = new Array;
<?php foreach ((array) $this->countries AS $row) : ?>
countries.push({'name':'<?php echo addslashes($row->name); ?>','lat':'<?php echo $row->latitude; ?>','lng':'<?php echo $row->longitude; ?>','flag':'<?php echo $row->flagurl; ?>','targetlink':'<?php echo $row->targetlink; ?>','assignedevents':'<?php echo $row->assignedevents; ?>'});
<?php endforeach; ?>

var venues = new Array;
<?php foreach ($this->rows AS $row) : ?>
venues.push({'id':'<?php echo $row->id; ?>','name':'<?php echo addslashes($row->venue); ?>','lat':'<?php echo $row->latitude; ?>','lng':'<?php echo $row->longitude; ?>'});
<?php endforeach; ?>
-->
</script>
<div id="gmap" style="height: 500px"></div>
<?php endif; ?>
</div>