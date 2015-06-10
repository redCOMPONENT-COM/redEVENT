<?php
/**
 * @version       2.0
 * @package       Joomla
 * @subpackage    RedEvent search module
 * @copyright (C) 2011 redCOMPONENT.com
 * @license       GNU/GPL, see LICENCE.php
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

// Google analytics integration
if (JFactory::getApplication()->getParams('com_redform')->get('enable_ga', 0))
{
	$doc = JFactory::getDocument();

	if (JFactory::getApplication()->getParams('com_redform')->get('ga_mode', 0))
	{
		$doc->addScriptDeclaration(
			'window.addEvent("domready", function() {
				$$(".mod_redevent_search_submit").addEvent("click", function() {
					 _gaq.push(["_trackPageview", "/virtual/moduleEventSearch"]);
				});
			});'
		);
	}
	else
	{
		$doc->addScriptDeclaration(
			'window.addEvent("domready", function() {
				$$(".mod_redevent_search_submit").addEvent("click", function() {
					ga("send", "pageview", "/virtual/moduleEventSearch");
				});
			});'
		);
	}
}
?>

<form action="<?php echo $action; ?>" method="post" id="redeventsearchform">

	<div class="mod_redevent_search">
		<?php if ($params->get('filter_text', 1)) : ?>
			<div class="rssm_filter_row">
				<span class="rssm_filter">
					<input type="text" name="filter" value="<?php echo $lists['filter']; ?>" class="inputbox text_filter" id="modres_text_filter"/>
				</span>
			</div>
		<?php endif; ?>

		<?php if ($params->get('show_filter_venue', 0)): ?>
			<div class="rssm_filter_row">
				<label for="filter_type"><?php echo JText::_('MOD_REDEVENT_SEARCH_VENUE_LABEL'); ?></label>
				<span class="rssm_filter">
					<?php echo $lists['venues']; ?>
				</span>
			</div>
		<?php endif; ?>

		<?php if (isset($lists['categories']) && $params->get('show_filter_category', 0)): ?>
			<div class="rssm_filter_row">
				<label for="filter_type"><?php echo JText::_('MOD_REDEVENT_SEARCH_CATEGORY_LABEL'); ?></label>
				<span class="rssm_filter">
					<?php echo $lists['categories']; ?>
				</span>
			</div>
		<?php endif; ?>

		<?php if ($params->get('show_filter_date', 0)): ?>
			<div class="rssm_filter_row">
				<label for="filter_type"><?php echo JText::_('MOD_REDEVENT_SEARCH_DATE_FROM_LABEL'); ?></label>
				<span class="rssm_filter">
					<?php echo RedeventHelper::calendar($filter_date_from, 'filter_date_from', 'rssm_filter_date_from', '%Y-%m-%d', null, 'class="inputbox date-field"'); ?>
				</span>
			</div>
			<div class="rssm_filter_row">
				<label for="filter_type"><?php echo JText::_('MOD_REDEVENT_SEARCH_DATE_TO_LABEL'); ?></label>
				<span class="rssm_filter">
					<?php echo RedeventHelper::calendar($filter_date_to, 'filter_date_to', 'rssm_filter_date_to', '%Y-%m-%d', null, 'class="inputbox date-field"'); ?>
				</span>
			</div>
		<?php endif; ?>

		<?php if ($params->get('show_filter_custom', 0)): ?>
			<?php if ($customsfilters && count($customsfilters)): ?>
				<?php foreach ($customsfilters as $custom): ?>
					<div class="rssm_filter_row">
						<?php echo '<label for="filtercustom' . $custom->id . '">' . JText::_($custom->name) . '</label>&nbsp;'; ?>
						<span class="rssm_filter">
							<?php echo $custom->renderFilter(array('class' => "inputbox"), isset($filter_customs[$custom->id]) ? $filter_customs[$custom->id] : null); ?>
					</span>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endif; ?>

	</div>
	<button type="submit" class="mod_redevent_search_submit"><?php echo JText::_('MOD_REDEVENT_SEARCH_SEARCH_LABEL'); ?></button>
</form>
