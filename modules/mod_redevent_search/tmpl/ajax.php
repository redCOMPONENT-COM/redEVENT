<?php
/**
 * @version 2.0
 * @package Joomla
 * @subpackage RedEvent search module
 * @copyright (C) 2011 redCOMPONENT.com
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
defined('_JEXEC') or die('Restricted access');

$document =& JFactory::getDocument();
// Add Javascript
$document->addScriptDeclaration("window.MOD_REDEVENT_SEARCH_SELECT_EVENT = '" . JText::_('MOD_REDEVENT_SEARCH_SELECT_EVENT') . "';");
JHTML::_('behavior.modal');

?>
<form accept-charset="UTF-8" action="<?php echo $action; ?>" method="get" id="redeventsearchform">

  <div class="mod_redevent_search">
			<?php if ($params->get('filter_text', 1)) : ?>

			<?php
				$value = $lists['filter'];
			?>

		  <div class="rssm_filter_row">
		  	<label for="filter_type" style="display: none;"><?php echo $lists['filter_types']; ?></label>
		  	<span class="rssm_filter">
	      	<input type="text" id="filter_text" name="filter" value="<?php echo $value;?>" class="inputbox text_filter" placeholder="<?php echo JText::_('MOD_REDEVENT_SEARCH_SELECT_EVENT') ?>"/>
	      </span>
			</div>
			<div id="selction-ajax"></div>
    	<?php endif; ?>

	    <?php if ($params->get('show_filter_venue', 0)): ?>
		  <div class="rssm_filter_row">
		  	<span class="rssm_filter">
	        <?php echo $lists['venues']; ?>
				</span>
			</div>
    	<?php endif; ?>

	    <?php if (isset($lists['categories']) && $params->get('show_filter_category', 0)): ?>
		  <div class="rssm_filter_row">
		  	<span class="rssm_filter">
	        <?php echo $lists['categories']; ?>
				</span>
			</div>
    	<?php endif; ?>

	    <?php if ($params->get('show_filter_date', 0)): ?>
		  <div class="rssm_filter_row">
		  	<label for="filter_type"><?php echo JText::_('MOD_REDEVENT_SEARCH_DATE_FROM_LABEL');  ?></label>
		  	<span class="rssm_filter">
          <?php echo redEVENTHelper::calendar($filter_date_from, 'filter_date_from', 'rssm_filter_date_from', '%Y-%m-%d', null, 'class="inputbox date-field"');?>
				</span>
			</div>
		  <div class="rssm_filter_row">
		  	<label for="filter_type"><?php echo JText::_('MOD_REDEVENT_SEARCH_DATE_TO_LABEL');  ?></label>
		  	<span class="rssm_filter">
          <?php echo redEVENTHelper::calendar($filter_date_to, 'filter_date_to', 'rssm_filter_date_to', '%Y-%m-%d', null, 'class="inputbox date-field"');?>
				</span>
			</div>
    	<?php endif; ?>

	    <?php if ($params->get('show_filter_custom', 0)): ?>
				<?php if ($customsfilters && count($customsfilters)): ?>
	    	<?php foreach ($customsfilters as $custom): ?>
	      <div class="rssm_filter_row">
	      	<?php echo '<label for="filtercustom'.$custom->id.'">'.JText::_($custom->name).'</label>&nbsp;'; ?>
		  		<span class="rssm_filter">
	      		<?php echo $custom->renderFilter(array('class' => "inputbox"), isset($filter_customs[$custom->id]) ? $filter_customs[$custom->id] : null); ?>
					</span>
	      </div>
	    	<?php endforeach; ?>
	    	<?php endif; ?>
	    <?php endif; ?>

  </div>

  <div class="main-button">
	  	<div class="green-left"></div>
	  	<div class="green-center">
	  		<button type="submit" ><?php echo JText::_( 'MOD_REDEVENT_SEARCH_SEARCH_LABEL' ); ?></button>
	  	</div>
	  	<div class="green-right"></div>
  	</div>
  	<div class="cleared"></div>
</form>
<link href="modules/mod_redevent_search/lib/content/styles.css" rel="stylesheet" />

<script type="text/javascript" src="modules/mod_redevent_search/lib/scripts/jquery.mockjax.js"></script>
<script type="text/javascript" src="modules/mod_redevent_search/lib/scripts/jquery.autocomplete.js"></script>
<script type="text/javascript" src="modules/mod_redevent_search/lib/scripts/demo.js"></script>