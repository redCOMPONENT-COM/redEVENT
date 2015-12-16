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
$rows = $displayData['rows'];
$layout=JRequest::getVar('view');
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
		'sort' => 'c.catname',
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
);

if(count($rows) > 0)
{?>
	<thead>
	<tr>
		<th class="col-md-2 col-sm-2 col-xs-12 date"><?php echo JText::_('COM_REDEVENT_EVENT_DATE'); ?>
		</th>

		<th class="col-md-4 col-sm-4 col-xs-12 navn"><?php echo JText::_('COM_REDEVENT_EVENT_NAME'); ?>
		</th>

		<th class="col-md-4 col-sm-4 col-xs-12 venue"><?php echo JText::_('COM_REDEVENT_EVENT_WHERE'); ?>
		</th>

		<th class="col-md-1 col-sm-1 col-xs-12 register"><?php echo JText::_('COM_REDEVENT_EVENT_REGISTER'); ?>
		</th>

		
	</tr>
</thead>
<?php }
else
{?>
	<div class="alert alert-info" role="alert">
		<div class="info">
  		<?php  echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?>
  		</div>
	</div>
<?php } ?>
	
	


