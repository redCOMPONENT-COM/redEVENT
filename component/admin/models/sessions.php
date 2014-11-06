<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component sessions Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelSessions extends JModel
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_sessions';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'sessions_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configs
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'title', 'obj.title',
				'published', 'obj.published',
				'id', 'obj.id',
				'language', 'obj.language',
			);
		}

		parent::__construct($config);
	}

  /**
   * Method to get List data
   *
   * @access public
   * @return array
   */
  function getData()
  {
    // Lets load the content if it doesn't already exist
    if (empty($this->_data))
    {
      $query = $this->_buildQuery();
      $pagination = $this->getPagination();
      $res = $this->_getList($query, $pagination->limitstart, $pagination->limit);

      if (!$res) {
   	  	echo $this->_db->getErrorMsg();
   	  	return false;
  		}

  		$this->_data = $res;
  		$this->_addAttendeesStats();
    }
    return $this->_data;
  }

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT obj.*, 0 AS checked_out, '
		  . ' e.title AS event_title, e.checked_out as event_checked_out, e.registra, '
		  . ' v.venue, v.checked_out as venue_checked_out '
			. ' FROM #__redevent_event_venue_xref AS obj '
			. ' INNER JOIN #__redevent_events AS e ON obj.eventid = e.id '
		  . ' LEFT JOIN #__redevent_venues AS v ON v.id = obj.venueid '
		  ;

		$query .= $where;
		$query .= $orderby;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter_order		  = $this->getState('filter_order');
		$filter_order_Dir	= $this->getState('filter_order_Dir');

		if ($filter_order == 'obj.dates'){
			$orderby 	= ' ORDER BY obj.dates '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , obj.dates ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$search				= $this->getState('search');

		$where = array();

		if ($this->_eventid) {
			$where[] = ' obj.eventid = '. $this->_eventid;
		}

		if ($search)
		{
			$where[] = '(LOWER(e.title) LIKE ' . $this->_db->Quote('%' . $search . '%') . ' OR '
				. ' LOWER(obj.title) LIKE ' . $this->_db->Quote('%'.$search.'%') . ' OR '
				. ' LOWER(obj.session_code) LIKE ' . $this->_db->Quote('%'.$search.'%') . ')';
		}

		switch ($this->getState('filter_state'))
		{
			case 'unpublished':
				$where[] = ' obj.published = 0 ';
				break;

			case 'published':
				$where[] = ' obj.published = 1 ';
				break;

			case 'archived':
				$where[] = ' obj.published = -1 ';
				break;

			case 'notarchived':
				$where[] = ' obj.published >= 0 ';
				break;
		}

		switch ($this->getState('filter_featured'))
		{
			case 'featured':
				$where[] = ' obj.featured = 1 ';
				break;

			case 'unfeatured':
				$where[] = ' obj.featured = 0 ';
				break;
		}

		if ($this->getState('venueid'))
		{
			$where[] = ' obj.venueid = '.$this->getState('venueid');
		}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}

  /**
   * adds attendees stats to session
   *
   * @return boolean true on success
   */
  private function _addAttendeesStats()
  {
  	if (!count($this->_data)) {
  		return false;
  	}

  	$ids = array();
		foreach ($this->_data as $session)
		{
			$ids[] = $session->id;
		}

		$query = ' SELECT x.id, COUNT(*) AS total, SUM(r.waitinglist) AS waiting, SUM(1-r.waitinglist) AS attending '
		       . ' FROM #__redevent_event_venue_xref AS x'
		       . ' LEFT JOIN #__redevent_register AS r ON x.id = r.xref '
		       . ' WHERE x.id IN ('.implode(', ', $ids).')'
		       . ' AND r.confirmed = 1 '
		       . ' AND r.cancelled = 0 '
		       . ' GROUP BY r.xref ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList('id');

		$noreg = new stdclass();
		$noreg->total = 0;
		$noreg->waiting = 0;
		$noreg->attending = 0;

		foreach ($this->_data as &$session)
		{
			if (isset($res[$session->id]))
			{
				$session->attendees = $res[$session->id];
			}
			else {
				$session->attendees = $noreg;
			}
		}
		return true;
  }

	/**
	 * Method to (un)feature
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function featured($cid = array(), $featured = 1)
	{
		$user = JFactory::getUser();

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__redevent_event_venue_xref'
				. ' SET featured = ' . (int) $featured
				. ' WHERE id IN ('. $cids .')'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}
}
