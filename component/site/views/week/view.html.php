<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML events list week View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewWeek extends RedeventViewSessionlist
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

		$application = JFactory::getApplication();
		$params = $application->getParams();

		// Add css file
		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('site/week.css');
		}

		// Pathway
		$pathway = $application->getPathWay();
		$pathway->addItem(JText::sprintf('COM_REDEVENT_WEEK_HEADER', $this->get('weeknumber'), $this->get('year')));

		$this->assign('week', $this->get('week'));
		$this->assign('weeknumber', $this->get('weeknumber'));
		$this->assign('year', $this->get('year'));
		$this->assign('weekdays', $this->get('weekdays'));
		$this->assign('next', $this->get('nextweek'));
		$this->assign('previous', $this->get('previousweek'));

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		array_unique($cols);
		$exclude = array('date', 'time');
		$cols = array_diff($cols, $exclude);

		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns', $cols);
		$start = JComponentHelper::getParams('com_redevent')->get('week_start') == 'MO' ? 1 : 0;
		$this->assign('start', $start);

		return parent::display($tpl);
	}

	/**
	 * Prepare form action
	 *
	 * @return void
	 */
	protected function prepareAction()
	{
		parent::prepareAction();

		$this->assign('action', RedeventHelperRoute::getWeekRoute());
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$app = JFactory::getApplication();
		$menuItem = $app->getMenu()->getActive();
		$params = $app->getParams();

		if ($menuItem)
		{
			$title = $menuItem->title;
		}
		else
		{
			$title = JText::sprintf('COM_REDEVENT_WEEK_HEADER', $this->get('weeknumber'), $this->get('year'));
		}

		$params->def('page_title', $title);

		return $params->get('page_title');
	}

	/**
	 * Sort sessions by dau
	 *
	 * @return array
	 */
	public function sortByDay()
	{
		if (!$this->rows)
		{
			return false;
		}

		$weekdays = $this->get('weekdays');

		foreach ($this->rows as $ev)
		{
			$days[array_search($ev->dates, $weekdays)][] = $ev;
		}

		return $days;
	}

	/**
	 * get day name from number
	 *
	 * @param   int  $number  day number
	 *
	 * @return string
	 */
	public function getDayName($number)
	{
		$days = $this->get('WeekDays');
		$day = $days[$number];

		return date('l, j F Y', strtotime($day));
	}
}
