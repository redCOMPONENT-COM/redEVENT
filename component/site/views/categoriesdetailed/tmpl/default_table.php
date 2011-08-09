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
?>
<table class="eventtable" width="<?php echo $this->elsettings->tablewidth; ?>" border="0" cellspacing="0" cellpadding="0" summary="eventlist">

	<colgroup>
		<?php foreach ($this->columns as $col): ?>
			<?php switch ($col): 
				case 'date': ?>
				<col width="<?php echo $this->elsettings->datewidth; ?>" class="el_col_date" />
				<?php break;?>
				
				<?php case 'title': ?>
				<col width="<?php echo $this->elsettings->titlewidth; ?>" class="el_col_title" />				
				<?php break;?>
				
				<?php case 'venue': ?>
				<col width="<?php echo $this->elsettings->locationwidth; ?>" class="el_col_venue" />				
				<?php break;?>
				
				<?php case 'city': ?>
				<col width="<?php echo $this->elsettings->citywidth; ?>" class="el_col_city" />
				<?php break;?>
				
				<?php case 'state': ?>
				<col width="<?php echo $this->elsettings->statewidth; ?>" class="el_col_state" />
				<?php break;?>
				
				<?php case 'category': ?>
				<col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_category" />
				<?php break;?>
				
				<?php case 'picture': ?>
				<col width="<?php echo $this->params->get('picture_col_with', 30); ?>" class="el_col_picture" />
				<?php break;?>
				
				<?php case 'places': ?>
				<col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_places" />
				<?php break;?>
				
				<?php case 'price': ?>
      	<col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_price" />
				<?php break;?>
				
				<?php case 'credits': ?>
      	<col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_credits" />
				<?php break;?>
				
				<?php default: ?>
    	  	<col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_customs" />
				<?php break;?>
				
				<?php endswitch;?>
    <?php endforeach;?>
	</colgroup>

	<thead>
			<tr>
		<?php foreach ($this->columns as $col): ?>
			<?php switch ($col): 
				case 'date': ?>
				<th id="el_date" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->datename); ?></th>
				<?php break;?>
				
				<?php case 'title': ?>
				<th id="el_title" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->titlename); ?></th>		
				<?php break;?>
				
				<?php case 'venue': ?>
				<th id="el_location" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->locationname); ?></th>			
				<?php break;?>
				
				<?php case 'city': ?>
				<th id="el_city" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->cityname); ?></th>
				<?php break;?>
				
				<?php case 'state': ?>
				<th id="el_state" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->statename); ?></th>
				<?php break;?>
				
				<?php case 'category': ?>
				<th id="el_category" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->catfroname); ?></th>
				<?php break;?>
				
				<?php case 'picture': ?>
				<th id="el_picture" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_TABLE_HEADER_PICTURE'); ?></th>
				<?php break;?>
				
				<?php case 'places': ?>
        <th id="el_places" class="sectiontableheader" align="left"><?php echo JText::_('Places'); ?></th>
				<?php break;?>
				
				<?php case 'price': ?>
				<th id="el_prices" class="sectiontableheader" align="left"><?php echo $this->params->get('lists_show_prices_label', 'Price'); ?></th>
				<?php break;?>
				
				<?php case 'credits': ?>
				<th id="el_credits" class="sectiontableheader" align="left"><?php echo $this->params->get('lists_show_credits_label', 'Credits'); ?></th>
				<?php break;?>
				
				<?php default: ?>
					<?php if (strpos($col, 'custom') === 0): ?>	
						<?php $c = $this->customs[intval(substr($col, 6))]; ?>			
	        	<th id="el_custom_<?php echo $c->id; ?>" class="sectiontableheader" align="left">
	        	<?php echo $this->escape($c->name); ?>
	        	<?php if ($c->tips && $this->params->get('lists_show_custom_tip', 1)):?>
	        	<?php echo JHTML::tooltip(str_replace("\n", "<br/>", $c->tips), '', 'tooltip.png', '', '', false); ?>
	        	<?php endif; ?>
	        	</th>
					<?php else: ?>		
	        	<th id="el_custom_<?php echo $c->id; ?>" class="sectiontableheader" align="left">
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
	if (!$this->rows) :
		?>
		<tr align="center"><td colspan="<?php echo count($this->columns); ?>"><?php echo JText::_( 'NO EVENTS' ); ?></td></tr>
		<?php
	else :

	$k = 0;
	foreach ($this->rows as $row) :
		$isover = (redEVENTHelper::isOver($row) ? ' isover' : '');
		
		//Link to details
		$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug) );
		
		?>
  	<tr class="sectiontableentry<?php echo ($k + 1) . $this->params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?><?php echo $isover; ?>" >

		<?php foreach ($this->columns as $col): ?>
			<?php switch ($col): 
				case 'date': ?>
	    		<td headers="el_date" align="left">
	    			<?php echo ELOutput::formatEventDateTime($row);	?>
					</td>
				<?php break;?>
				
				<?php case 'title': ?>
					<td headers="el_title" align="left" valign="top"><a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape($row->full_title); ?></a></td>			
				<?php break;?>
				
				<?php case 'venue': ?>
					<td headers="el_location" align="left" valign="top">
						<?php
						if ($this->elsettings->showlinkvenue == 1 ) :
							echo $row->xref != 0 ? "<a href='".JRoute::_( RedeventHelperRoute::getVenueEventsRoute($row->venueslug) )."'>".$this->escape($row->venue)."</a>" : '-';
						else :
							echo $row->xref ? $this->escape($row->venue) : '-';
						endif;
						?>
					</td>			
				<?php break;?>
				
				<?php case 'city': ?>
					<td headers="el_city" align="left" valign="top"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>
				<?php break;?>
				
				<?php case 'state': ?>
					<td headers="el_state" align="left" valign="top"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>
				<?php break;?>
				
				<?php case 'category': ?>
				  <td headers="el_category" align="left" valign="top">
				  <?php $cats = array();
					      foreach ($row->categories as $cat)
					      {
					      	if ($this->elsettings->catlinklist == 1) {
					      		$cats[] = JHTML::link(RedeventHelperRoute::getCategoryEventsRoute($cat->slug), $cat->catname);
					      	}
					      	else {
					      		$cats[] = $this->escape($cat->catname);
					      	}
					      }
					      echo implode("<br/>", $cats);
					?>
					</td>	
				<?php break;?>
				
				<?php case 'picture': ?>
          <td headers="el_places" align="left" valign="top"><?php echo redEVENTImage::modalimage('events', $row->datimage, $row->title, intval($this->params->get('lists_picture_size', 30))); ?></td>
				<?php break;?>
				
				<?php case 'places': ?>
          <td headers="el_places" align="left" valign="top"><?php echo redEVENTHelper::getRemainingPlaces($row); ?></td>
				<?php break;?>
				
				<?php case 'price': ?>
					<td headers="el_prices" align="left" class="re-price"><?php echo ELOutput::formatListPrices($row->prices); ?></td>
				<?php break;?>
				
				<?php case 'credits': ?>
					<td headers="el_credits" align="left"><?php echo $row->course_credit ? $row->course_credit : '-'; ?></td>
				<?php break;?>
				
				<?php default: ?>
					<?php if (isset($row->$col)):?>
          	<td headers="el_customs" align="left" valign="top"><?php echo str_replace("\n", "<br/>", $row->$col); ?></td>
          <?php else: ?>
          	<td headers="el_customs" align="left" valign="top"></td>
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