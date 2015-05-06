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
			echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
		endif;

		echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
	?>
</p>

  <h1 class="componentheading">
    <?php echo JText::_('COM_REDEVENT_Search_events'); ?>
  </h1>


    <?php if ($this->params->get('showintrotext')) : ?>
      <div class="description no_space floattext">
        <?php echo $this->params->get('introtext'); ?>
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

<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">

<div id="el_filter" class="floattext">

  <div class="el_fleft">
		<table>
			<?php if ($this->params->get('show_filter')) : ?>
		  <tr>
		    <td>
			    <label for="filter_type"><?php echo JText::_('COM_REDEVENT_FILTER');  ?></label>
			  </td>
			  <td>
				<?php echo  $this->lists['filter_types']; ?>
	      <input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
	      <button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_GO' ); ?></button>
	      <button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_RESET' ); ?></button>
				</td>
			</tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_country')): ?>
      <tr>
        <td>
          <?php echo '<label for="filter_country">'.JText::_('COM_REDEVENT_Country').'</label>&nbsp;'; ?>
        </td>
        <td>
          <?php echo $this->lists['countries'];?>
        </td>
      </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_state') && (!$this->params->get('show_filter_country') || $this->filter_country)): ?>
      <tr>
        <td>
          <?php echo '<label for="filter_state">'.JText::_('COM_REDEVENT_State').'</label>&nbsp;';?>
        </td>
        <td>
          <?php echo $this->lists['states'];?>
        </td>
      </tr>
  		<?php endif; ?>
	    <?php if ($this->params->get('show_filter_city') && (!$this->params->get('show_filter_country') || $this->filter_country)
	    																								 && (!$this->params->get('show_filter_state')   || $this->filter_state)): ?>
      <tr>
        <td>
          <?php echo '<label for="filter_city">'.JText::_('COM_REDEVENT_City').'</label>&nbsp;';?>
        </td>
        <td>
          <?php echo $this->lists['cities'];?>
        </td>
      </tr>
  		<?php endif; ?>
	    <?php if ($this->params->get('show_filter_venuecategory')): ?>
			<tr>
        <td>
	        <?php echo '<label for="filter_venuecategory">'.JText::_('COM_REDEVENT_Venue_Category').'</label>&nbsp;'; ?>
	      </td>
	      <td>
	        <?php echo $this->lists['vcategories']; ?>
	      </td>
	    </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_venue')): ?>
			<tr>
        <td>
	        <?php echo '<label for="filter_venue">'.JText::_('COM_REDEVENT_Venue').'</label>&nbsp;'; ?>
	      </td>
	      <td>
	        <?php echo $this->lists['venues']; ?>
	      </td>
	    </tr>
    	<?php endif; ?>
	    <?php if (isset($this->lists['categories']) && $this->params->get('show_filter_category')): ?>
			<tr>
        <td>
	        <?php echo '<label for="filter_category">'.JText::_('COM_REDEVENT_Category').'</label>&nbsp;'; ?>
	      </td>
	      <td>
	        <?php echo $this->lists['categories']; ?>
	      </td>
	    </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_date')): ?>
      <tr>
        <td>
          <?php echo '<label for="filter_date">'.JText::_('COM_REDEVENT_Date').'</label>&nbsp;'; ?>
        </td>
        <td>
          <?php echo RedeventHelper::calendar($this->filter_date_from, 'filter_date_from', 'filter_date_from', '%Y-%m-%d', 'OnUpdateDate', 'class="inputbox dynfilter"');?>
           <?php echo JText::_('COM_REDEVENT_DATE_TO'); ?>
          <?php echo RedeventHelper::calendar($this->filter_date_to, 'filter_date_to', 'filter_date_to', '%Y-%m-%d', 'OnUpdateDate', 'class="inputbox dynfilter"');?>
        </td>
      </tr>
    	<?php endif; ?>
	    <?php if ($this->params->get('show_filter_event')): ?>
      <tr>
        <td>
          <?php echo '<label for="filter_event">'.JText::_('COM_REDEVENT_Event').'</label>&nbsp;'; ?>
        </td>
        <td>
          <?php echo $this->lists['events']; ?>
        </td>
      </tr>
    	<?php endif; ?>

    	<?php foreach ($this->customsfilters as $custom): ?>
      <tr>
        <td>
          <?php echo '<label for="filtercustom'.$custom->id.'">'.JText::_($custom->name).'</label>&nbsp;'; ?>
        </td>
        <td>
          <?php echo $custom->renderFilter(array('class' => "inputbox dynfilter"), isset($this->filter_customs[$custom->id]) ? $this->filter_customs[$custom->id] : null); ?>
        </td>
      </tr>
    	<?php endforeach; ?>
    </table>
  </div>

  <?php if ($this->params->get('display_limit_select')) : ?>
	<div class="el_fright">
		<?php	echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
		echo $this->pageNav->getLimitBox();
		?>
	</div>
  <?php endif; ?>

</div>

<?php if ($this->nofilter): ?>
<div class="redevent-search-warning"><?php echo JText::_('COM_REDEVENT_SEARCH_NO_FILTER'); ?></div>
<?php elseif ($this->noevents): ?>
<div class="redevent-search-warning"><?php echo JText::_('COM_REDEVENT_SEARCH_NO_RESULT'); ?></div>
<?php else: ?>
<?php echo $this->loadTemplate('table'); ?>
<?php endif; ?>
<p>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</p>
</form>

<!--footer-->

<!--pagination-->
<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pageNav->get('pages.total') > 1)) : ?>
<div class="pagination">
	<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter">
				<?php echo $this->pageNav->getPagesCounter(); ?>
		</p>

		<?php endif; ?>
	<?php echo $this->pageNav->getPagesLinks(); ?>
</div>
<?php  endif; ?>
<!-- pagination end -->

</div>
