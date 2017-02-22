<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Ibc.Sessionsdump
 *
 * @copyright   Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Class Venuepaidregistrations model
 *
 * @since  3.2.3
 */
class RedeventModelVenuepaidregistrations extends RModelList
{
	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			jimport('joomla.html.pagination');
			$total = $this->getTotal();
			$limit = $this->getState('limit');
			$limitstart = $this->getState('limitstart');

			if ($limitstart > $total)
			{
				$limitstart = floor($total / $limit) * $limit;
				$this->setState('limitstart', $limitstart);
			}

			$this->pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->pagination;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  object  Query object
	 */
	protected function getListQuery()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('r.*')
			->select('c.invoice_id')
			->select('p.gateway, p.data')
			->from('#__redevent_register AS r')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref')
			->join('INNER', '#__rwf_submitters AS s ON r.sid = s.id')
			->join('INNER', '#__rwf_forms AS fo ON fo.id = s.form_id')
			->join('INNER', '#__rwf_payment_request AS pr ON pr.submission_id = s.id')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
			->join('INNER', '#__rwf_cart AS c ON c.id = ci.cart_id')
			->join('INNER', '#__rwf_payment AS p ON p.cart_id = c.id');

		$query->where('p.paid = 1');

		$this->buildWhere($query);

		$query->group('r.id, c.invoice_id');
		$query->order('r.id DESC');

		return $query;
	}

	/**
	 * Add where part from filters
	 *
	 * @param   JDatabaseQuery  &$query  query
	 *
	 * @return JDatabaseQuery
	 */
	private function buildWhere(&$query)
	{
		$venueId = $this->getState('venueId');

		$acl = RedeventUserAcl::getInstance();

		if (!$venueId || !in_array($venueId, $acl->getManagedVenues()))
		{
			throw new LogicException('Access not allowed', 403);
		}

		$query->where('x.venueid = ' . $venueId);
		$query->where('r.confirmed = 1');
		$query->where('r.waitinglist = 0');
		$query->where('r.cancelled = 0');

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   12.2
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');
		$input = JFactory::getApplication()->input;
		$this->setState('venueId', $input->getInt('id'));

		$this->setState('limit', $app->getUserStateFromRequest('com_redevent.limit', 'limit', $params->def('display_num', 0), 'int'));
		$this->setState('limitstart', $app->input->getInt('limitstart', 0));

		parent::populateState();
	}
}

