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
		<col width="<?php echo $this->elsettings->datewidth; ?>" class="el_col_date" />
		<?php if ($this->elsettings->showtitle == 1) : ?>
			<col width="<?php echo $this->elsettings->titlewidth; ?>" class="el_col_title" />
		<?php endif; ?>
		<?php if ($this->elsettings->showlocate == 1) :	?>
			<col width="<?php echo $this->elsettings->locationwidth; ?>" class="el_col_venue" />
		<?php endif; ?>
		<?php if ($this->elsettings->showcity == 1) :	?>
			<col width="<?php echo $this->elsettings->citywidth; ?>" class="el_col_city" />
		<?php endif; ?>
		<?php if ($this->elsettings->showstate == 1) :	?>
			<col width="<?php echo $this->elsettings->statewidth; ?>" class="el_col_state" />
		<?php endif; ?>
		<?php if ($this->elsettings->showcat == 1) :	?>
			<col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_category" />
		<?php endif; ?>
    <?php if ($this->params->get('display_placesleft', 0)) :  ?>
      <col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_places" />
    <?php endif; ?>
    <?php if ($this->params->get('lists_show_price', 0)) :  ?>
      <col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_price" />
    <?php endif; ?>
    <?php if ($this->params->get('lists_show_credits', 0)) :  ?>
      <col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_credits" />
    <?php endif; ?>
    <?php foreach ($this->customs AS $c): ?>
      <col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_customs" />
    <?php endforeach;?>
	</colgroup>
	
	<thead>
			<tr>
				<th id="el_date_cat<?php echo $this->categoryid; ?>" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->datename); ?></th>
				<?php
				if ($this->elsettings->showtitle == 1) :
				?>
				<th id="el_title_cat<?php echo $this->categoryid; ?>" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->titlename); ?></th>
				<?php
				endif;
				if ($this->elsettings->showlocate == 1) :
				?>
				<th id="el_location_cat<?php echo $this->categoryid; ?>" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->locationname); ?></th>
				<?php
				endif;
				if ($this->elsettings->showcity == 1) :
				?>
				<th id="el_city_cat<?php echo $this->categoryid; ?>" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->cityname); ?></th>
				<?php
				endif;
				if ($this->elsettings->showstate == 1) :
				?>
				<th id="el_state_cat<?php echo $this->categoryid; ?>" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->statename); ?></th>
				<?php
				endif;
				if ($this->elsettings->showcat == 1) :
				?>
				<th id="el_category_cat<?php echo $this->categoryid; ?>" class="sectiontableheader" align="left"><?php echo $this->escape($this->elsettings->catfroname); ?></th>
				<?php
				endif;
        if ($this->params->get('display_placesleft', 0)) :
        ?>
        <th id="el_places" class="sectiontableheader" align="left"><?php echo JText::_('Places'); ?></th>
        <?php
        endif;
				?>
				
				<?php if ($this->params->get('lists_show_prices', 0)): ?>        
				<th id="el_prices" class="sectiontableheader" align="left"><?php echo $this->params->get('lists_show_prices_label', 'Price'); ?></th>
				<?php endif; ?>
				<?php if ($this->params->get('lists_show_credits', 0)): ?>        
				<th id="el_credits" class="sectiontableheader" align="left"><?php echo $this->params->get('lists_show_credits_label', 'Credits'); ?></th>
				<?php endif; ?>
				
		    <?php foreach ($this->customs AS $c): ?>
        	<th id="el_places_<?php echo $c->id; ?>" class="sectiontableheader" align="left">
        	<?php echo $c->name; ?>
        	<?php if ($c->tips && $this->params->get('lists_show_custom_tip', 1)):?>
        	<?php echo JHTML::tooltip(str_replace("\n", "<br/>", $c->tips), '', 'tooltip.png', '', '', false); ?>
        	<?php endif; ?>
        	</th>
		    <?php endforeach;?>
			</tr>
	</thead>

	<tbody>
		<?php
		if (!$this->rows) :
		?>
		<tr class="no_events"><td><?php echo JText::_( 'NO EVENTS' ); ?></td></tr>
		<?php
		else :

		$k = 0;
		foreach ($this->rows as $row) :
			$isover = (redEVENTHelper::isOver($row) ? ' isover' : '');
			?>
  			<tr class="sectiontableentry<?php echo ($k + 1) . $this->params->get( 'pageclass_sfx' ) . ($row->featured ? ' featured' : ''); ?><?php echo $isover; ?>" >
    			<td headers="el_date_cat<?php echo $this->categoryid; ?>" align="left">
    					<?php echo ELOutput::formatEventDateTime($row);	?>
				</td>
				<?php
				//Link to details
				$detaillink = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->id, $row->xslug));
				//title
				if (($this->elsettings->showtitle == 1 ) && ($this->elsettings->showdetails == 1) ) :
				?>
				<td headers="el_title_cat<?php echo $this->categoryid; ?>" align="left" valign="top"><a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape($row->full_title); ?></a></td>
				<?php
				endif;
				if (( $this->elsettings->showtitle == 1 ) && ($this->elsettings->showdetails == 0) ) :
				?>
				<td headers="el_title_cat<?php echo $this->categoryid; ?>" align="left" valign="top"><?php echo $this->escape($row->full_title); ?></td>
				<?php
				endif;

				if ($this->elsettings->showlocate == 1) :
				?>
					<td headers="el_location_cat<?php echo $this->categoryid; ?>" align="left" valign="top">
				<?php
					if ($this->elsettings->showlinkvenue == 1 ) :
							echo $row->venueid != 0 ? "<a href='".JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>" : '-';
						else :
							echo $row->venueid ? $this->escape($row->venue) : '-';
						endif;
				?>
					</td>
				<?php
				endif;
				if ($this->elsettings->showcity == 1) :
				?>
					<td headers="el_city_cat<?php echo $this->categoryid; ?>" align="left" valign="top"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>
				<?php
				endif;

				if ($this->elsettings->showstate == 1) :
				?>
					<td headers="el_state_cat<?php echo $this->categoryid; ?>" align="left" valign="top"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>
				<?php
				endif;

				
        if ($this->elsettings->showcat == 1) : ?>
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
        <?php endif; 

        if ($this->params->get('display_placesleft', 0)) :
        ?>

          <td headers="el_places" align="left" valign="top"><?php echo redEVENTHelper::getRemainingPlaces($row); ?></td>

        <?php endif; ?>
        
				<?php if ($this->params->get('lists_show_prices', 0)): ?>        
					<td headers="el_prices" align="left" class="re-price"><?php echo ELOutput::formatListPrices($row->prices); ?></td>
				<?php endif; ?>
				<?php if ($this->params->get('lists_show_credits', 0)): ?>        
					<td headers="el_credits" align="left"><?php echo $row->course_credit ? $row->course_credit : '-'; ?></td>
				<?php endif; ?>
        
        <!-- custom fields -->
		    <?php foreach ($this->customs AS $c): ?>
		    <?php $property = 'custom'.$c->id; ?>
          <td headers="el_customs" align="left" valign="top"><?php echo str_replace("\n", "<br/>", $row->$property); ?></td>
		    <?php endforeach;?>
        <!-- custom fields end-->
			</tr>
  			<?php
				$k = 1 - $k;
			endforeach;
			endif;
			?>
	</tbody>
</table>