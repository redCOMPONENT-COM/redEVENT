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

$document = JFactory::getDocument();

// Google analytics integration
if (JFactory::getApplication()->getParams('com_redform')->get('enable_ga', 0))
{
	if (JFactory::getApplication()->getParams('com_redform')->get('ga_mode', 0))
	{
		$document->addScriptDeclaration(
			'window.addEvent("domready", function() {
				$$(".mod_redevent_search_submit").addEvent("click", function() {
					 _gaq.push(["_trackPageview", "/virtual/moduleEventSearch"]);
				});
			});'
		);
	}
	else
	{
		$document->addScriptDeclaration(
			'window.addEvent("domready", function() {
				$$(".mod_redevent_search_submit").addEvent("click", function() {
					ga("send", "pageview", "/virtual/moduleEventSearch");
				});
			});'
		);
	}
}

JHtml::_('script', 'media/jui/js/jquery.autocomplete.min.js', false, false, false, false, true);
RHelperAsset::load('mod_redevent_search_ajax.js', 'mod_redevent_search');
?>

<form action="<?php echo $action; ?>" method="post" id="redeventsearchform">

	<div class="mod_redevent_search">
		<?php if ($params->get('filter_text', 1)) : ?>
			<div class="rssm_filter_row">
				<span class="rssm_filter">
					<input type="text"
					       name="filter"
					       id="mod-redevent-searchword"
					       size="1" value="<?php echo htmlspecialchars(JFactory::getApplication()->input->get('filter', '', 'string')); ?>"
					       placeholder="<?php echo JText::_('MOD_REDEVENT_SEARCH_SELECT_EVENT') ?>"/>
				</span>
			</div>
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

		<?php if (isset($lists['multiple_categories']) && $params->get('show_filter_multiple_category', 0)): ?>
			<div class="rssm_filter_row">
				<span class="rssm_filter">
					<?php echo $lists['multiple_categories']; ?>
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

	<div class="main-button">
		<button type="submit" class="mod_redevent_search_submit"><?php echo JText::_('MOD_REDEVENT_SEARCH_SEARCH_LABEL'); ?></button>
	</div>
</form>
