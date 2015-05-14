<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT_SITE . '/classes/calendar.class.php';

/**
 * HTML View class for the Calendar View
 *
 * @package  Redevent.Site
 * @since    1.1
 */
class RedeventViewCalendar extends RedeventViewFront
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$this->prepareView();

		$app = JFactory::getApplication();

		// Load tooltips behavior
		JHTML::_('behavior.tooltip');

		$document = JFactory::getDocument();
		$settings = RedeventHelper::config();
		$item = $app->getMenu()->getActive();
		$params = $app->getParams();

		RHelperAsset::load('redeventcalendar.css');
		RHelperAsset::load('calendarview.js');

		$year = $app->input->getInt('yearID', strftime("%Y"));
		$month = $app->input->getInt('monthID', strftime("%m"));

		// Get data from model and set the month
		$model = $this->getModel();
		$model->setDate(mktime(0, 0, 1, $month, 1, $year));

		$rows = $this->get('Data');
		$categories = $this->get('Categories');

		// Init calendar
		$cal = new RECalendar($year, $month, 0, $app->getCfg('offset'));
		$cal->enableMonthNav('index.php?option=com_redevent&view=calendar');
		$cal->setFirstWeekDay(($params->get('week_start', "SU") == 'SU' ? 0 : 1));
		$cal->enableDayLinks(false);

		$this->assignRef('rows', $rows);
		$this->assignRef('categories', $categories);
		$this->assignRef('params', $params);
		$this->assignRef('settings', $settings);
		$this->assignRef('cal', $cal);

		parent::display($tpl);
	}

	/**
	 * Creates a tooltip
	 *
	 * @param   string  $tooltip  The tip string
	 * @param   string  $title    The title of the tooltip
	 * @param   string  $text     The text for the tip
	 * @param   string  $href     An URL that will be used to create the link
	 * @param   string  $class    the class to use for tip.
	 *
	 * @return  string
	 */
	public function caltooltip($tooltip, $title = '', $text = '', $href = '', $class = 'editlinktip hasTip')
	{
		$tooltip = (htmlspecialchars($tooltip));
		$title = (htmlspecialchars($title));

		if ($href)
		{
			$href = JRoute::_($href);
			$style = '';
			$tip = '<span class="' . $class . '" title="' . $title . '" rel="' . $tooltip . '"><a href="' . $href . '">' . $text . '</a></span>';
		}
		else
		{
			$tip = '<span class="' . $class . '" title="' . $title . '" rel="' . $tooltip . '">' . $text . '</span>';
		}

		return $tip;
	}
}
