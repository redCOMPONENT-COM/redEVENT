<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$colnames = explode(",", $this->params->get('lists_columns_names', 'date, title, venue, city, category'));
$colnames = array_map('trim', $colnames);
?>
<table class="eventtable" summary="eventlist">

	<thead>
			<tr>
		<?php foreach ($this->columns as $k => $col): ?>
			<?php switch ($col): 
				case 'date': ?>
				<th id="el_date" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_DATE'); ?></th>
				<?php break;?>
				
				<?php case 'title': ?>
				<th id="el_title" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_TITLE'); ?></th>		
				<?php break;?>
				
				<?php case 'venue': ?>
				<th id="el_location" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_VENUE'); ?></th>			
				<?php break;?>
				
				<?php case 'city': ?>
				<th id="el_city" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_CITY'); ?></th>
				<?php break;?>
				
				<?php case 'country': ?>
				<?php case 'countryflag': ?>
				<th id="el_country" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_COUNTRY'); ?></th>
				<?php break;?>
				
				<?php case 'state': ?>
				<th id="el_state" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_STATE'); ?></th>
				<?php break;?>
				
				<?php case 'category': ?>
				<th id="el_category" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_CATEGORY'); ?></th>
				<?php break;?>
				
				<?php case 'picture': ?>
				<th id="el_picture" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_TABLE_HEADER_PICTURE'); ?></th>
				<?php break;?>
				
				<?php case 'places': ?>
        <th id="el_places" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_Places'); ?></th>
				<?php break;?>
				
				<?php case 'price': ?>
				<th id="el_prices" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_PRICE'); ?></th>
				<?php break;?>
				
				<?php case 'credits': ?>
				<th id="el_credits" class="sectiontableheader"><?php echo isset($colnames[$k]) ? $colnames[$k] : JText::_('COM_REDEVENT_CREDITS'); ?></th>
				<?php break;?>
				
				<?php default: ?>
					<?php if (strpos($col, 'custom') === 0): ?>	
						<?php $c = $this->customs[intval(substr($col, 6))]; ?>			
	        	<th id="el_custom_<?php echo $c->id; ?>" class="sectiontableheader re_custom">
	        	<?php echo JHTML::_('grid.sort', isset($colnames[$k]) ? $colnames[$k] : $this->escape($c->name), 'custom'. $c->id, $this->lists['order_Dir'], $this->lists['order'] ); ?>
	        	<?php if ($c->tips && $this->params->get('lists_show_custom_tip', 1)):?>
	        	<?php echo JHTML::tooltip(str_replace("\n", "<br/>", $c->tips), '', 'tooltip.png', '', '', false); ?>
	        	<?php endif; ?>
	        	</th>
					<?php else: ?>		
	        	<th id="el_custom_<?php echo $c->id; ?>" class="sectiontableheader re_custom">
	        	<?php echo $col; ?>
	        	</th>
					<?php endif; ?>
				<?php break;?>
				
				<?php endswitch;?>
    	<?php endforeach;?>
			</tr>
	</thead>
	<tbody>
	<?php
	if (!count($this->rows)) :
		?>
		<tr align="center"><td><?php echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?></td></tr>
		<?php
	else :

	$k = 0;
	foreach ($this->rows as $row) :
		$isover = (redEVENTHelper::isOver($row) ? ' isover' : '');
				
		?>
  	<tr class="sectiontableentry<?php echo ($k + 1) . $this->params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?><?php echo $isover; ?>">

		<?php foreach ($this->columns as $col): ?>
			<?php switch ($col): 
				case 'date': ?>
	    		<td class="re_date">				
			    	<?php echo REOutput::formatEventDateTime($row);	?>
					</td>
				<?php break;?>
				
				<?php case 'title': ?>
					<td class="re_title" itemprop="name"><?php echo $this->escape($row->full_title); ?></td>			
				<?php break;?>
				
				<?php case 'venue': ?>
					<td class="re_location">
						<?php	echo $row->xref ? $this->escape($row->venue) : '-';	?>
					</td>			
				<?php break;?>
				
				<?php case 'city': ?>
					<td class="re_city"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>
				<?php break;?>
				
				<?php case 'country': ?>
					<td class="re_country"><?php echo $row->country ? redEVENTHelperCountries::getShortCountryName($row->country) : ''; ?></td>
				<?php break;?>
				
				<?php case 'countryflag': ?>
					<td class="re_countryflag"><?php echo $row->country ? redEVENTHelperCountries::getCountryFlag($row->country) : ''; ?></td>
				<?php break;?>
				
				<?php case 'state': ?>
					<td class="re_state"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>
				<?php break;?>
				
				<?php case 'category': ?>
				  <td class="re_category">
				  <?php $cats = array();
					      foreach ($row->categories as $cat)
					      {
					      	$cats[] = $this->escape($cat->catname);
					      }
					      echo implode("<br/>", $cats);
					?>
					</td>	
				<?php break;?>
				
				<?php case 'picture': ?>
          <td class="re_places"><?php echo JHTML::image(redEVENTImage::getThumbUrl('events', $row->datimage, intval($this->params->get('lists_picture_size', 30))), $row->title); ?></td>
				<?php break;?>
				
				<?php case 'places': ?>
          <td class="re_places"><?php echo redEVENTHelper::getRemainingPlaces($row); ?></td>
				<?php break;?>
				
				<?php case 'price': ?>
					<td class="re_prices"><?php echo REOutput::formatListPrices($row->prices); ?></td>
				<?php break;?>
				
				<?php case 'credits': ?>
					<td class="re_credits"><?php echo $row->course_credit ? $row->course_credit : '-'; ?></td>
				<?php break;?>
				
				<?php default: ?>
					<?php if (isset($row->$col)):?>
          	<td class="re_customs"><?php echo str_replace("\n", "<br/>", $row->$col); ?></td>
          <?php else: ?>
          	<td class="re_customs"></td>
          <?php endif;?>
				<?php break;?>
				
				<?php endswitch;?>
    <?php endforeach;?>
		</tr>

  	<?php	$k = 1 - $k; ?>
	<?php endforeach; ?>
	<?php endif; ?>

	</tbody>
</table>
