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
<table class="eventtable" summary="eventlist">
	<thead>
		<tr>
		<?php foreach ($this->columns as $k => $col): ?>
			<?php switch ($col):
				case 'title': ?>
				<th id="el_title" class="sectiontableheader 123"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_TITLE'), 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php break;?>

				<?php case 'venue': ?>
				<th id="el_location" class="sectiontableheader"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_VENUE'), 'l.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th id="el_info" class="sectiontableheader"><?php echo JText::_('COM_REDEVENT_INFO'); ?></th>
				<?php break;?>

				<?php case 'category': ?>
				<th id="el_category" class="sectiontableheader"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_CATEGORY'), 'c.catname', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php break;?>

				<?php case 'picture': ?>
				<th id="el_picture" class="sectiontableheader"><?php echo JText::_('COM_REDEVENT_TABLE_HEADER_PICTURE'); ?></th>
				<?php break;?>

				<?php default: ?>
					<?php if (strpos($col, 'custom') === 0): ?>
						<?php $c = $this->customs[intval(substr($col, 6))]; ?>
			        	<th id="el_custom_<?php echo $c->id; ?>" class="sectiontableheader re_custom">
			        	<?php echo JHTML::_('grid.sort', $this->escape($c->name), 'custom'. $c->id, $this->lists['order_Dir'], $this->lists['order'] ); ?>
			        	<?php if ($c->tips && $this->params->get('lists_show_custom_tip', 1)):?>
			        	<?php echo JHTML::tooltip(str_replace("\n", "<br/>", $c->tips), '', 'tooltip.png', '', '', false); ?>
			        	<?php endif; ?>
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
		//Link to details
		$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug) );
		?>
  	<tr class="sectiontableentry<?php echo ($k + 1) . $this->params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?>"
  	    itemscope itemtype="http://schema.org/Event">

		<?php foreach ($this->columns as $col): ?>
			<?php switch ($col):

					case 'title': ?>
					<td class="re_title" itemprop="name"><a href="<?php echo $detaillink ; ?>" itemprop="url"><?php echo $this->escape($row->full_title); ?></a></td>
				<?php break;?>

				<?php case 'venue': ?>
					<td class="re_location">
						<?php
						$venues = array();
						foreach ($row->sessions as $s):
							if ($this->params->get('showlinkvenue',1) == 1 ) :
								$venues[$s->venue] = $s->xref != 0 ? JHTML::link(JRoute::_( RedeventHelperRoute::getVenueEventsRoute($s->venueslug) ), $this->escape($s->venue), 'itemprop="url"') : '-';
							else :
								$venues[$s->venue] = $s->xref ? $this->escape($s->venue) : '-';
							endif;
						endforeach;
						ksort($venues);
						echo implode("<br>", $venues);
						?>
						</td>
						<td class="m-info"><a class="m-info-link" href="<?php echo $detaillink ; ?>" itemprop="url"><?php echo JText::_('MORE_INFO'); ?></a></td>
				<?php break;?>

				<?php case 'category': ?>
				  <td class="re_category">
				  <?php $cats = array();
					      foreach ($row->categories as $cat)
					      {
					      	if ($this->params->get('catlinklist', 1) == 1) {
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
          <td class="re_places" itemprop="image"><?php echo redEVENTImage::modalimage($row->datimage, $row->title, intval($this->params->get('lists_picture_size', 30))); ?></td>
				<?php break;?>

				<?php default: ?>
					<?php if (isset($row->$col)):?>
          	<td class="re_customs"><?php echo str_replace("\n", "<br/>", $row->$col); ?></td>
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