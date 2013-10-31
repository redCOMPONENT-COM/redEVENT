<?php
// No direct access
defined('_JEXEC') or die;

class translationRe_venueFilter extends translationFilter
{
	public function __construct($contentElement)
	{
		$this->filterNullValue = "-1";
		$this->filterType = "re_venue";
		$this->filterField = $contentElement->getFilter("re_venue");
		parent::__construct($contentElement);
	}

	public function _createFilter()
	{
		if (!$this->filterField) return "";
		$filter = "";

		//since joomla 3.0 filter_value can be '' too not only filterNullValue
		if (isset($this->filter_value) && strlen($this->filter_value) > 0 && $this->filter_value != $this->filterNullValue)
		{
			$db = JFactory::getDBO();
			$filter = " c." . $this->filterField . "=" . $db->escape($this->filter_value, true);
		}
		return $filter;
	}

	function _createfilterHTML()
	{
		if (!$this->filterField) return "";

		$allCategoryOptions = array();

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('id AS value, venue AS text');
		$query->from('#__redevent_venues');
		$query->where('published = 1');
		$query->order('venue');

		$db->setQuery($query);
		$options = $db->loadObjectList();

		if (!FALANG_J30)
		{
			$allOptions[-1] = JHTML::_('select.option', '-1', JText::_('JALL'));
		}
		$options = array_merge($allOptions, $options);

		$field = array();

		if (FALANG_J30)
		{
			$field["title"] = 'Venue';
			$field["position"] = 'sidebar';
			$field["name"] = 're_venue_filter_value';
			$field["type"] = 're_venue';
			$field["options"] = $options;
			$field["html"] = JHTML::_('select.genericlist', $options, 're_venue_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
		}
		else
		{
			$field["title"] = 'Venue';
			$field["html"] = JHTML::_('select.genericlist', $options, 're_venue_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
		}

		return $field;

	}
}
