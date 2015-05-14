<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML class for the search View
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewSearch extends RedeventViewSessionlist
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

		$mainframe = JFactory::getApplication();
		$params = $mainframe->getParams();

		$state = $this->get('state');

		// Add javascript
		JHTML::_('behavior.framework');
		RHelperAsset::load('site/search.js');

		$this->checkDirectRedirect();

		// Are events available?
		$nofilter = 0;

		if (!$this->rows)
		{
			$noevents = 1;

			if (!$this->get('Filter'))
			{
				$nofilter = 1;
			}
		}
		else
		{
			$noevents = 0;
		}

		$this->assign('noevents', $noevents);
		$this->assign('nofilter', $nofilter);

		$print_link = JRoute::_('index.php?option=com_redevent&view=search&tmpl=component&pop=1');
		$this->assignRef('print_link', $print_link);

		if ($state->get('results_type') == 0)
		{
			$this->setLayout('searchevents');
			$allowed = array(
					'title',
					'venue',
					'category',
					'picture',
			);
			$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
			$cols = RedeventHelper::validateColumns($cols, $allowed);
			$this->assign('columns', $cols);
		}

		parent::display($tpl);
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

		$params->def('page_title', $menuItem->title);

		return $params->get('page_title');
	}

	/**
	 * Prepare form action
	 *
	 * @return void
	 */
	protected function prepareAction()
	{
		parent::prepareAction();

		$this->assign('action', RedeventHelperRoute::getSearchRoute());
	}

	/**
	 * Get feed link
	 *
	 * @return void
	 */
	protected function getFeedLink()
	{
		return false;
	}

	/**
	 * Potentially redirect to details if only one result
	 *
	 * @return void
	 */
	protected function checkDirectRedirect()
	{
		$config = RedeventHelper::config();
		$rows = $this->rows;

		if (count($rows) == 1 && $config->get('redirect_search_unique_result_to_details', 0))
		{
			if ($this->get('state')->get('results_type') == 0)
			{
				$route = RedeventHelperRoute::getDetailsRoute($rows[0]->slug);
			}
			else
			{
				$route = RedeventHelperRoute::getDetailsRoute($rows[0]->slug, $rows[0]->xslug);
			}

			JFactory::getApplication()->redirect($route);
		}
	}
}
