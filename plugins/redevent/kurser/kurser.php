<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('redevent.bootstrap');
jimport('reditem.library');

/**
 * Specific plugin for redEVENT.
 *
 * @package  Redevent.Plugin
 * @since    3.0
 */
class PlgRedeventKurser extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'kurser';

	/**
	 * Dump sessions
	 *
	 * @return void
	 */
	public function onAjaxRemarketingdump()
	{
		$text = "";

		$stdcols = array(
			'Program ID',
			'Program Name',
			'Area of Study',
			'Program Description',
			'Destination URL',
			'School Name',
			'Thumbnail image URL',
			'Address',
			'Location ID',
			'Image URL',
			'Contextual Keyword',
			'Final URL',
			'Tracking Template',
			'Action'
		);

		$text .= RedeventHelper::writecsvrow($stdcols);

		if ($rows = $this->getData())
		{
			foreach ($rows as $row)
			{
				$item = ReditemEntityItem::getInstance($row->item_id);
				$itemData = $item->getDecodedCustomData();
				$address = '';

				if ($row->city)
				{
					$address = $row->city . ', ' . $row->country;
				}

				$new = [
					$row->id,
					mb_strimwidth($row->title, 0, 25, '...', "utf-8"),
					mb_strimwidth(implode(",", $row->categories), 0, 25, '...', "utf-8"),
					mb_strimwidth($row->teaser, 0, 25, '...', "utf-8"),
					'',
					'IBC',
					'',
					$address,
					'',
					'https://kurser.ibc.dk/media/com_reditem/images/customfield/' . $itemData->billede,
					'',
					$item->getLink(),
					JRoute::_($item->getLink('inherit', false), true, -1),
					'',
					'Add'
				];

				$text .= RedeventHelper::writecsvrow($new);
			}
		}

		$doc = JFactory::getDocument();
		$doc->setMimeEncoding('text/csv');
		$date = md5(date('Y-m-d-h-i-s'));
		$title = JFile::makeSafe('kurser_export_' . $date . '.csv');
		header('Content-Disposition: attachment; filename="' . $title . '"');
		echo $text;
	}

	/**
	 * Get data
	 *
	 * @return mixed
	 */
	private function getData()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('e.id, e.title')
			->select('v.venue, v.city, v.country, v.plz as zip, v.street, v.latitude, v.longitude')
			->select('k.id AS item_id, k.teaser, k.billede AS image')
			->from('#__redevent_events AS e')
			->join('INNER', '#__redevent_event_venue_xref AS x On x.eventid = e.id')
			->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid')
			->join('INNER', '#__reditem_types_kursus_1 AS k ON k.event = e.id')
			->where('e.published = 1')
			->where('x.published = 1')
			->where('(x.dates = 0 OR x.dates >= NOW())')
			->order('e.id, v.city');

		$db->setQuery($query);

		$rows = $db->loadObjectList();

		$this->addCategories($rows);

		return $rows;
	}

	private function addCategories(&$sessions)
	{
		$ids = JArrayHelper::getColumn($sessions, 'item_id');
		$ids = array_unique($ids);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('xcat.item_id, c.title')
			->from('#__reditem_categories AS c')
			->join('INNER', '#__reditem_item_category_xref AS xcat ON xcat.category_id = c.id')
			->where('xcat.item_id IN (' . implode(", ", $ids) . ')')
			// Exclude Kurser og uddannelser
			->where('xcat.category_id <> 2');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$eventCategories = [];

		foreach ($rows as $row)
		{
			$eventCategories[$row->item_id][] = $row->title;
		}

		foreach ($sessions as &$session)
		{
			$session->categories = $eventCategories[$session->item_id];
		}
	}
}
