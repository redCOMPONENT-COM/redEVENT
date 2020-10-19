<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Modules
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load redEVENT library
$redeventLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';

if (!file_exists($redeventLoader))
{
	throw new Exception(JText::_('COM_REDEVENT_INIT_FAILED'), 404);
}

include_once $redeventLoader;

RedeventBootstrap::bootstrap();

require_once dirname(__FILE__) . '/helper.php';

// Include mootools tooltip
JHTML::_('behavior.tooltip');

RHelperAsset::load('mod_redevent_calendar.js', 'mod_redevent_calendar');
RHelperAsset::load('mod_redevent_calendar.css', 'mod_redevent_calendar');

$app = JFactory::getApplication();

// Parameters
$day_name_length = $params->get('day_name_length', '2');
$first_day = $params->get('first_day', '1');
$Year_length = $params->get('Year_length', '1');
$Month_length = $params->get('Month_length', '0');
$Month_offset = $params->get('Month_offset', '0');
$Show_Tooltips = $params->get('Show_Tooltips', '1');
$Remember = $params->get('Remember', '1');
$CalTooltipsTitle = $params->get('recal_tooltips_title', 'Events');
$show_weeknb = $params->get('show_week_number', 1);
$week_nb_format = $first_day ? '%W' : '%U';

// Get switch trigger
$req_month = JRequest::getVar('re_mcal_month', '', 'request', 'int');
$req_year = JRequest::getVar('re_mcal_year', '', 'request', 'int');

// Remember which month / year is selected. Don't jump back to tday on page change
if ($Remember == 1)
{
	if ($req_month == 0)
	{
		$req_month = $app->getUserState("redeventcalmonth");
		$req_year = $app->getUserState("redeventcalyear");
	}
	else
	{
		$app->setUserState("redeventcalmonth", $req_month);
		$app->setUserState("redeventcalyear", $req_year);
	}
}

// Set now
$config = JFactory::getConfig();
$tzoffset = $config->get('config.offset');
$time = time() + $tzoffset * 60 * 60;
$today_month = date('m', $time);
$today_year = date('Y', $time);
$today = date('j', $time);

if ($req_month == 0)
{
	$req_month = $today_month + $Month_offset;
}

if ($req_year == 0)
{
	$req_year = $today_year;
}

if ($req_month > 12)
{
	$req_month = $req_month - 12;
	$req_year++;
}

// Setting the previous and next month numbers
$prev_month_year = $req_year;
$next_month_year = $req_year;

$prev_month = $req_month - 1;

if ($prev_month < 1)
{
	$prev_month = 12;
	$prev_month_year--;
}

$next_month = $req_month + 1;

if ($next_month > 12)
{
	$next_month = 1;
	$next_month_year++;
}

// Requested URL
$uri = JURI::getInstance();

// Link for previous month
$prev = clone $uri;
$prev->setVar('re_mcal_month', $prev_month);
$prev->setVar('re_mcal_year', $prev_month_year);
$prev_link = $prev->toString();

// Link for next month
$next = clone $uri;
$next->setVar('re_mcal_month', $next_month);
$next->setVar('re_mcal_year', $next_month_year);
$next_link = $next->toString();

$days = Modredeventcalhelper::getdays($req_year, $req_month, $params);

$day_names = array(
	Jtext::_('SUNDAY'),
	Jtext::_('MONDAY'),
	Jtext::_('TUESDAY'),
	Jtext::_('WEDNESDAY'),
	Jtext::_('THURSDAY'),
	Jtext::_('FRIDAY'),
	Jtext::_('SATURDAY'),
	Jtext::_('SUNDAY'),
);
$day_names_short = array(
	Jtext::_('SUN'),
	Jtext::_('MON'),
	Jtext::_('TUE'),
	Jtext::_('WED'),
	Jtext::_('THU'),
	Jtext::_('FRI'),
	Jtext::_('SAT'),
	Jtext::_('SUN'),
);

if ($first_day)
{
	array_shift($day_names);
	array_shift($day_names_short);
}
else
{
	array_pop($day_names);
	array_pop($day_names_short);
}

$month_names = array(
	JText::_('JANUARY'),
	JText::_('FEBRUARY'),
	JText::_('MARCH'),
	JText::_('APRIL'),
	JText::_('MAY'),
	JText::_('JUNE'),
	JText::_('JULY'),
	JText::_('AUGUST'),
	JText::_('SEPTEMBER'),
	JText::_('OCTOBER'),
	JText::_('NOVEMBER'),
	JText::_('DECEMBER'),
);

$month_names_short = array(
	JText::_('JANUARY_SHORT'),
	JText::_('FEBRUARY_SHORT'),
	JText::_('MARCH_SHORT'),
	JText::_('APRIL_SHORT'),
	JText::_('MAY_SHORT'),
	JText::_('JUNE_SHORT'),
	JText::_('JULY_SHORT'),
	JText::_('AUGUST_SHORT'),
	JText::_('SEPTEMBER_SHORT'),
	JText::_('OCTOBER_SHORT'),
	JText::_('NOVEMBER_SHORT'),
	JText::_('DECEMBER_SHORT'),
);

require JModuleHelper::getLayoutPath('mod_redevent_calendar');
