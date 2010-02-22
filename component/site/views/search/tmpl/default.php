<?php
/**
 * @version 1.0 $Id: simplelist.php 1403 2009-11-03 12:41:02Z julien $
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
<div id="redevent" class="el_eventlist">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			echo ELOutput::submitbutton( $this->dellink, $this->params );
			echo ELOutput::archivebutton( $this->params, $this->task );
		endif;

		echo ELOutput::printbutton( $this->print_link, $this->params );
	?>
</p>

  <h1 class="componentheading">
    <?php echo JText::_('Search events'); ?>
  </h1>


    <?php if ($this->params->get('search_showintrotext')) : ?>
      <div class="description no_space floattext">
        <?php echo $this->params->get('search_introtext'); ?>
      </div>
    <?php endif; ?>

<!--table-->
<script type="text/javascript">

	function tableOrdering( order, dir, view )
	{
		var form = document.getElementById("adminForm");

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		form.submit( view );
	}
</script>

<form action="<?php echo $this->action; ?>" method="post" id="adminForm">

<div id="el_filter" class="floattext">
  <div class="el_fleft">
		<table>
			<?php if ($this->params->get('show_filter')) : ?>
		  <tr>
		    <td>
			    <label for="filter_type"><?php echo JText::_('FILTER');  ?></label>
			  </td>
			  <td>			
				<?php echo  $this->lists['filter_types']; ?>
	      <input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
	      <button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_( 'GO' ); ?></button>
	      <button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_( 'RESET' ); ?></button>
				</td>
			</tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_country')): ?>
      <tr>
        <td>
          <?php echo '<label for="country">'.JText::_('Country').'</label>&nbsp;'; ?>
        </td>
        <td>
          <?php echo $this->lists['countries'];?>
        </td>
      </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_city') && (!$this->params->get('show_filter_country') || $this->filter_country)): ?>
      <tr>
        <td>
          <?php echo '<label for="city">'.JText::_('City').'</label>&nbsp;';?>
        </td>
        <td>
          <?php echo $this->lists['cities'];?>
        </td>
      </tr>
  		<?php endif; ?>
	    <?php if ($this->params->get('show_filter_venuecategory')): ?>
			<tr>
        <td>
	        <?php echo '<label for="filter_venuecategory">'.JText::_('Venue Category').'</label>&nbsp;'; ?>
	      </td>
	      <td>
	        <?php echo $this->lists['vcategories']; ?>
	      </td>
	    </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_venue')): ?>
			<tr>
        <td>
	        <?php echo '<label for="filter_venue">'.JText::_('Venue').'</label>&nbsp;'; ?>
	      </td>
	      <td>
	        <?php echo $this->lists['venues']; ?>
	      </td>
	    </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_category')): ?>
			<tr>
        <td>
	        <?php echo '<label for="filter_category">'.JText::_('Category').'</label>&nbsp;'; ?>
	      </td>
	      <td>
	        <?php echo $this->lists['categories']; ?>
	      </td>
	    </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_date')): ?>
      <tr>
        <td>
          <?php echo '<label for="filter_date">'.JText::_('Date').'</label>&nbsp;'; ?>
        </td>
        <td>
          <?php echo JHTML::_('calendar', $this->filter_date, 'filter_date', 'filter_date', '%Y-%m-%d', 'class="inputbox dynfilter"');?>
        </td>
      </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_event')): ?>
      <tr>
        <td>
          <?php echo '<label for="filter_event">'.JText::_('Date').'</label>&nbsp;'; ?>
        </td>
        <td>
          <?php echo $this->lists['events']; ?>
        </td>
      </tr>
    	<?php endif; ?>
    </table>	
  </div>
  <?php if ($this->params->get('display')) : ?>
	<div class="el_fright">
	<?php	echo '<label for="limit">'.JText::_('DISPLAY NUM').'</label>&nbsp;';
	echo $this->pageNav->getLimitBox();
	?>
  <?php endif; ?>
	</div>
</div>

<?php echo $this->loadTemplate('table'); ?>

<p>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</p>
</form>

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

</div>