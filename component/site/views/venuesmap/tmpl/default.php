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
?>

<div id="redevent" class="jlmap">
  <div id="goback"><a href="javascript:history.back()"><?php echo JText::_('COM_REDEVENT_Back'); ?></a></div>
  <h1 class="componentheading">
    <?php echo JText::_('COM_REDEVENT_VENUES_MAP'); ?>
  </h1>

<script type="text/javascript">
var venueurl = '<?php echo JRoute::_($this->ajaxurl, false); ?>';
var countries = new Array;
<?php foreach ((array) $this->countries AS $row) : ?>
<?php 
$obj = new stdclass();
$obj->name = $row->name;
$obj->lat  = $row->latitude;
$obj->lng  = $row->longitude;
$obj->flag = $row->flagurl;
?>
countries.push(<?php echo json_encode($obj); ?>);
<?php endforeach; ?>

var venues = new Array;
<?php foreach ($this->rows AS $row) : ?>
<?php 
$obj = new stdclass();
$obj->id = $row->id;
$obj->name = $row->venue;
$obj->lat  = $row->latitude;
$obj->lng  = $row->longitude;
?>
venues.push(<?php echo json_encode($obj); ?>);
<?php endforeach; ?>

window.addEvent('domready', function() {
	if ($('vcat')) {
		$('vcat').addEvent('change', function() {
		  $('filter').value = 1;
		  $('filterform').submit();
		});
	}
  if ($('cat')) {
	  $('cat').addEvent('change', function() {
	    $('filter').value = 1;
	    $('filterform').submit();
	  });
  }
  
  $$('.customfilter').each(function(element){
    element.addEvent('change', function(){
      $('filter').value = 1;
      $('filterform').submit();
	  });
	});
});
</script>

<?php if ($this->params->get('showintrotext')) : ?>
  <div class="description no_space floattext">
    <?php echo $this->params->get('introtext'); ?>
  </div>
<?php endif; ?>

<?php if ($this->params->get('show_cat_filter', 1) || $this->params->get('show_vcat_filter', 1) || $this->params->get('show_custom_filters', 1)) : ?>
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="filterform">
<div id="red_filter" class="floattext">
    <div class="el_fleft">
    <table>
	    <?php if ($this->params->get('show_vcat_filter', 1)) : ?>
	      <tr>
	        <td>
			      <label for="filter_type"><?php echo JText::_('COM_REDEVENT_FILTER_VENUES_CATEGORY'); ?></label>
			    </td>
			    <td>
			      <?php echo $this->lists['venuescats']; ?>
	        </td>
		    </tr>
	    <?php endif; ?>
	    <?php if ($this->params->get('show_cat_filter', 1)) : ?>
	      <tr>
	        <td>
		      <label for="filter_type"><?php echo JText::_('COM_REDEVENT_FILTER_EVENTS_CATEGORY'); ?></label>
	        </td>
	        <td>
		      <?php echo $this->lists['eventscats']; ?>
	        </td>
	      </tr>
      <?php endif; ?>
      <?php if ($this->params->get('show_custom_filters', 1)) : ?>
	      <?php foreach ((array) $this->lists['customfilters'] as $filter) : ?>
	      <tr>
	        <td>
		      <label for="filter_type"><?php echo $filter->name; ?></label>
	        </td>
	        <td>
		      <?php echo $filter->renderFilter(array('class' =>"customfilter"), isset($this->filter_customs[$filter->id]) ? $this->filter_customs[$filter->id] : null); ?>
	        </td>
	      </tr>
	      <?php endforeach; ?>
      <?php endif; ?>
    </table>
    </div>
</div>
<input type="hidden" name="filter" id="filter" value="0"/>
</form>
<?php endif; ?>


<div id="gmap" style="height: 500px"></div>
</div>