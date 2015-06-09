<?php
/**
 * @package     RedMEMBER
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

$name       = $displayData['name'];
$fieldcode	= $displayData['fieldcode'];
$value		= $displayData['value'];
$attributes	= $displayData['attributes'];

$class = isset($attributes['class']) ? $attributes['class'] : '';

if ($value && strlen($value) >= 8)
{
	$date = date("Y-m-d", strtotime($value));

	$year = substr($date, 0, 4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);
}
else
{
	$date = '';
	$year = null;
	$month = null;
	$day = null;
}

$el = array();
$el[] = 'type="hidden"';
$el[] = 'id="' . $fieldcode . '"';
$el[] = 'name="cform[date][' . $fieldcode . ']"';
$el[] = 'class="' . $class . '"';
$el[] = 'value="' . $value . '"';

$field = '<input ' . implode(" ", $el) . '/>';

$options = array(JHtml::_('select.option', '', JText::_('COM_REDMEMBER_DAY')));

for ($i = 1; $i < 32; $i++)
{
	$options[] = JHtml::_('select.option', $i, $i);
}

$daySel = JHtml::_(
	'select.genericlist', $options, $fieldcode . '_day',
	array('class' => $class . ' rm_3boxdaypick day_select' ),
	'value', 'text', $day
);

$options = array(JHtml::_('select.option', '', JText::_('COM_REDMEMBER_YEAR')));

for ($i = date('Y'); $i > 1919; $i--)
{
	$options[] = JHtml::_('select.option', $i, $i);
}

$yearSel = JHtml::_('select.genericlist', $options, $fieldcode . '_year',
	array('class' => $class . ' rm_3boxdaypick year_select'),
	'value', 'text', $year
);

$options = array(
	JHtml::_('select.option', '', JText::_('COM_REDMEMBER_MONTH')),
	JHtml::_('select.option', 1, JText::_('COM_REDMEMBER_JANUARY')),
	JHtml::_('select.option', 2, JText::_('COM_REDMEMBER_FEBRUARY')),
	JHtml::_('select.option', 3, JText::_('COM_REDMEMBER_MARCH')),
	JHtml::_('select.option', 4, JText::_('COM_REDMEMBER_APRIL')),
	JHtml::_('select.option', 5, JText::_('COM_REDMEMBER_MAY')),
	JHtml::_('select.option', 6, JText::_('COM_REDMEMBER_JUNE')),
	JHtml::_('select.option', 7, JText::_('COM_REDMEMBER_JULY')),
	JHtml::_('select.option', 8, JText::_('COM_REDMEMBER_AUGUST')),
	JHtml::_('select.option', 9, JText::_('COM_REDMEMBER_SEPTEMBER')),
	JHtml::_('select.option', 10, JText::_('COM_REDMEMBER_OCTOBER')),
	JHtml::_('select.option', 11, JText::_('COM_REDMEMBER_NOVEMBER')),
	JHtml::_('select.option', 12, JText::_('COM_REDMEMBER_DECEMBER'))
);

$monthSel = JHtml::_('select.genericlist', $options, $fieldcode . '_month',
	array('class' => $class . ' rm_3boxdaypick month_select'),
	'value', 'text', $month
);

$spans = array();
$spans[] = '<span class="rm_date_day_select"><label>' . JText::_('COM_REDMEMBER_DAY') . '</label>' . $daySel . '</span>';
$spans[] = '<span class="rm_date_month_select"><label>' . JText::_('COM_REDMEMBER_MONTH') . '</label>' . $monthSel . '</span>';
$spans[] = '<span class="rm_date_year_select"><label>' . JText::_('COM_REDMEMBER_YEAR') . '</label>' . $yearSel . '</span>';
$spans = implode('', $spans);

RHelperAsset::load('threefieldsdate', 'com_redeventb2b');
?>
<div class="form-group">
	<div class="redmember_customfield_date rm_3boxdaypick">
		<?php echo $spans . $field; ?>
	</div>
</div>
