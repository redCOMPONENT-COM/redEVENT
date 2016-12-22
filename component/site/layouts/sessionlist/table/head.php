<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$params = $displayData['params'];
$columns = $displayData['columns'];
$customs = $displayData['customs'];

$order = JArrayHelper::getValue($displayData, 'order');
$orderDir = JArrayHelper::getValue($displayData, 'orderDir');

$colnames = explode(",", $params->get('lists_columns_names', 'date, title, venue, city, category'));
$colnames = array_map('trim', $colnames);
$print = JArrayHelper::getValue($displayData, 'print', 0);
$sorting = JArrayHelper::getValue($displayData, 'sorting', 1) && !$print;

$allColumns = array(
	'date' => array(
		'title' => JText::_('COM_REDEVENT_DATE'),
		'sort' => 'x.dates',
		'id' => 'el_date'
	),
	'enddate' => array(
		'title' => JText::_('COM_REDEVENT_ENDDATE'),
		'sort' => 'x.enddate',
	),
	'title' => array(
		'title' => JText::_('COM_REDEVENT_TITLE'),
		'sort' => 'a.title',
		'id' => 'el_title'
	),
	'venue' => array(
		'title' => JText::_('COM_REDEVENT_VENUE'),
		'sort' => 'l.venue',
	),
	'city' => array(
		'title' => JText::_('COM_REDEVENT_CITY'),
		'sort' => 'l.city',
		'id' => 'el_city'
	),
	'country' => array(
		'title' => JText::_('COM_REDEVENT_COUNTRY'),
		'sort' => 'l.country',
		'id' => 'el_country'
	),
	'countryflag' => array(
		'title' => JText::_('COM_REDEVENT_COUNTRY'),
		'sort' => 'l.country',
		'id' => 'el_country'
	),
	'state' => array(
		'title' => JText::_('COM_REDEVENT_STATE'),
		'sort' => 'l.state',
		'id' => 'el_state'
	),
	'category' => array(
		'title' => JText::_('COM_REDEVENT_CATEGORY'),
		'sort' => 'c.name',
		'id' => 'el_category'
	),
	'picture' => array(
		'title' => JText::_('COM_REDEVENT_TABLE_HEADER_PICTURE'),
		'id' => 'el_picture'
	),
	'places' => array(
		'title' => JText::_('COM_REDEVENT_Places'),
		'id' => 'el_places'
	),
	'price' => array(
		'title' => JText::_('COM_REDEVENT_PRICE'),
		'id' => 'el_prices'
	),
	'credits' => array(
		'title' => JText::_('COM_REDEVENT_CREDITS'),
		'id' => 'el_credits'
	),
	'session_code' => array(
		'title' => JText::_('COM_REDEVENT_TABLE_HEADER_SESSION_CODE'),
		'id' => 'el_session_code',
		'sort' => 'x.session_code'
	),
	'registration' => array(
		'title' => JText::_('COM_REDEVENT_TABLE_HEADER_REGISTRATION'),
		'id' => 'el_registration'
	),
);
?>
<thead>
	<tr>
		<?php foreach ($columns as $k => $col): ?>

			<?php if (isset($allColumns[$col])): ?>

				<th class="sectiontableheader" <?php echo isset($allColumns[$col]['id']) ? 'id="' . $allColumns[$col]['id'] . '"' : ''; ?>>
					<?php if (JArrayHelper::getValue($allColumns[$col], 'sort', 0) && $sorting): ?>
						<?php echo JHTML::_('grid.sort', JArrayHelper::getValue($colnames, $k, $allColumns[$col]['title']), $allColumns[$col]['sort'], $orderDir, $order); ?>
					<?php else: ?>
						<?php echo JArrayHelper::getValue($colnames, $k, $allColumns[$col]['title']); ?>
					<?php endif; ?>
				</th>

			<?php elseif (strpos($col, 'custom') === 0): ?>
				<?php $c = $customs[intval(substr($col, 6))]; ?>

				<th id="el_custom_<?php echo $c->id; ?>" class="sectiontableheader re_custom">
					<?php if ($sorting): ?>
						<?php echo JHTML::_('grid.sort', isset($colnames[$k]) ? $colnames[$k] : $this->escape($c->name), 'custom'. $c->id, $orderDir, $order); ?>
					<?php else: ?>
						<?php echo isset($colnames[$k]) ? $colnames[$k] : $this->escape($c->name); ?>
					<?php endif; ?>

					<?php if (!$print && $c->tips && $params->get('lists_show_custom_tip', 1)): ?>
						<?php echo JHTML::tooltip(str_replace("\n", "<br/>", $c->tips), '', 'tooltip.png', '', '', false); ?>
					<?php endif; ?>
				</th>

			<?php else: ?>

				<th class="sectiontableheader re_col">
					<?php echo JArrayHelper::getValue($colnames, $k, $col); ?>
				</th>

			<?php endif; ?>

		<?php endforeach;?>
	</tr>
</thead>
