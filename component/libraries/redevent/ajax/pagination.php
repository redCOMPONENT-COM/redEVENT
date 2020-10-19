<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.pagination');

/**
 * override pagination for ajax display
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventAjaxPagination extends RPagination
{
	/**
	 * Constructor.
	 *
	 * @param   integer  $total       The total number of items.
	 * @param   integer  $limitstart  The offset of the item to start at.
	 * @param   integer  $limit       The number of items to display per page.
	 * @param   string   $prefix      The prefix used for request variables.
	 *
	 * @since   1.5
	 */
	public function __construct($total, $limitstart, $limit, $prefix = '')
	{
		parent::__construct($total, $limitstart, $limit, $prefix);
		RHelperAsset::load('ajaxnav.js', 'com_redevent');
	}

	/**
	 * Prevent from using default pagination.php override from frontend template...
	 *
	 * @return  string  Pagination page list string.
	 *
	 * @since   1.0
	 */
	public function getPagesLinks()
	{
		$app = JFactory::getApplication();

		// Build the page navigation list.
		$data = $this->_buildDataObject();

		$list = array();
		$list['prefix'] = $this->prefix;
		$list['formName'] = $this->formName;

		$itemOverride = false;
		$listOverride = false;

		// Build the select list
		if ($data->all->base !== null)
		{
			$list['all']['active'] = true;
			$list['all']['data'] = ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		}
		else
		{
			$list['all']['active'] = false;
			$list['all']['data'] = ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}

		if ($data->start->base !== null)
		{
			$list['start']['active'] = true;
			$list['start']['data'] = ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
		}
		else
		{
			$list['start']['active'] = false;
			$list['start']['data'] = ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}

		if ($data->previous->base !== null)
		{
			$list['previous']['active'] = true;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		}
		else
		{
			$list['previous']['active'] = false;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		// Make sure it exists
		$list['pages'] = array();

		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null)
			{
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			}
			else
			{
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}

		if ($data->next->base !== null)
		{
			$list['next']['active'] = true;
			$list['next']['data'] = ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
		}
		else
		{
			$list['next']['active'] = false;
			$list['next']['data'] = ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}

		if ($data->end->base !== null)
		{
			$list['end']['active'] = true;
			$list['end']['data'] = ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
		}
		else
		{
			$list['end']['active'] = false;
			$list['end']['data'] = ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if ($this->total > $this->limit)
		{
			return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Method to create an active pagination link to the item
	 *
	 * @param   RPaginationObject  $item  The object with which to make an active link.
	 *
	 * @return  string  HTML link
	 *
	 * @since   1.0
	 */
	protected function _item_active(RPaginationObject $item)
	{
		return RedeventLayoutHelper::render('reajaxpagination.item.active', $item);
	}

	/**
	 * Method to create an inactive pagination string
	 *
	 * @param   RPaginationObject  $item  The item to be processed
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function _item_inactive(RPaginationObject $item)
	{
		return RedeventLayoutHelper::render('reajaxpagination.item.inactive', $item);
	}
}
