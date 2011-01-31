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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<style type="text/css">
.rf_img {min-height:<?php echo $this->config->imagehight;?>px;}
</style>
<div id="redevent" class="rf_thumb">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			echo ELOutput::listbutton( $this->list_link, $this->params );
			echo ELOutput::submitbutton( $this->dellink, $this->params );
			echo ELOutput::archivebutton( $this->params, $this->task );
		endif;

		echo ELOutput::printbutton( $this->print_link, $this->params );
	?>
</p>

<?php if ($this->params->def( 'show_page_title', 1 )) : ?>

    <h1 class="componentheading">
		<?php echo $this->escape($this->pagetitle); ?>
	</h1>

<?php endif; ?>


<?php if ($this->params->get('showintrotext')) : ?>
	<div class="description no_space floattext">
		<?php echo $this->params->get('introtext'); ?>
	</div>
<?php endif; ?>

<!-- filter -->
<form action="<?php echo $this->action; ?>" method="post" id="adminForm">

<?php $toggle = $this->params->get('filter_toggle', 3); ?>
<?php if ($toggle != 1 || $this->params->get('display')) : ?>
<div id="el_filter" class="floattext">
		<?php if ($toggle != 1 || 1) : ?>
			<?php if ($toggle > 1) : ?>
			<div id="filters-toggle"><?php echo JTExt::_('COM_REDEVENT_TOGGLE_FILTERS'); ?></div>
			<?php endif; ?>
			<div class="el_fleft" id="el-events-filters">
				<?php if ($this->params->get('filter_text', 1) && $this->lists['filter_type']): ?>
			<div id="main-filter">
				<?php
				echo '<label for="filter_type">'.JText::_('FILTER').'</label>&nbsp;';
				echo $this->lists['filter_type'].'&nbsp;';
				?>
				<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('EVENTS_FILTER_HINT'); ?>"/>
				<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</div>
			<?php endif; ?>
						
			<?php if ($this->params->get('lists_filter_category', 1)): ?>
			<div id="category-filter"><?php echo $this->lists['categoryfilter']; ?></div>
    	<?php endif; ?>
    	
			<?php if ($this->params->get('lists_filter_venue', 1)): ?>
			<div id="venue-filter"><?php echo $this->lists['venuefilter']; ?></div>
    	<?php endif; ?>
			
			<?php if ($this->customsfilters && count($this->customsfilters)): ?>
    	<?php foreach ($this->customsfilters as $custom): ?>
      <div class="custom-filter" id="filter<?php echo $custom->id; ?>">
      	<?php echo '<label for="filtercustom'.$custom->id.'">'.JText::_($custom->name).'</label>&nbsp;'; ?>
      	<?php echo $custom->renderFilter(array('class' => "inputbox dynfilter"), isset($this->filter_customs[$custom->id]) ? $this->filter_customs[$custom->id] : null); ?>
      </div>
    	<?php endforeach; ?>
    	<?php endif; ?>
		</div>
   	<input type="hidden" id="f-showfilters" name="showfilters" value="<?php echo $toggle == 0 ? '1' : JRequest::getInt('showfilters', $toggle != 3 ? 1 : 0); ?>"/>
		<?php endif; ?>
		
		<?php if ($this->params->get('display')) : ?>
		<div class="el_fright">
			<?php
			echo '<label for="limit">'.JText::_('DISPLAY NUM').'</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
</div>
<?php endif; ?>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>
<!-- filter end -->

<?php echo $this->loadTemplate('items'); ?>

<!--footer-->
<div class="pageslinks">
	<?php echo $this->pageNav->getPagesLinks(); ?>
</div>

<p class="pagescounter">
	<?php echo $this->pageNav->getPagesCounter(); ?>
</p>

<p class="copyright">
	<?php echo ELOutput::footer( ); ?>
</p>
<!--footer end-->

</div>