<?php
/**
 * @version 1.1 $Id: default.php 668 2008-05-12 14:32:13Z schlu $
 * @package Joomla
 * @subpackage redEVENT
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
$row = & $this->row;

JHTML::_('behavior.framework');
?>
<script type="text/javascript">
var basepath = "<?php echo JURI::root(); ?>";
window.addEvent('domready', function() {
	mymap.initajax(<?php echo $this->row->id; ?>, "venue-location");
});
</script>

<div id="venue-location" class="venue-full-map"></div>

