<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML venues map View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewVenuesmap extends RedeventViewFront
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

		$document = JFactory::getDocument();
		$elsettings = RedeventHelper::config();

		JHTML::_('behavior.framework');
		$document->addScript('https://maps.google.com/maps/api/js?sensor=false');
		RHelperAsset::load('markermanager.js');
		RHelperAsset::load('site/venuesmap.js');

		// Filters
		$vcat = $this->state->get('vcat');
		$cat = $this->state->get('cat');
		$custom = $this->get('CustomFilters');
		$filter_customs = $mainframe->getUserStateFromRequest('com_redevent.venuesmap.filter_customs',
			'filtercustom', array(), 'array');

		$rows = $this->get('Data');
		$countries = $this->get('Countries');

		// Add needed scripts if the lightbox effect is enabled
		JHTML::_('behavior.modal');

		$print_link = JRoute::_('index.php?view=venues&pop=1&tmpl=component');

		$lists = array();

		// Venues categories
		$vcat_options = RedeventHelper::getVenuesCatOptions(false);
		array_unshift($vcat_options, JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ALL')));
		$lists['venuescats'] = JHTML::_('select.genericlist', $vcat_options, 'filter_venuecategory', '', 'value', 'text', $vcat);

		// Events categories
		$cat_options = RedeventHelper::getEventsCatOptions(false);
		array_unshift($cat_options, JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ALL')));
		$lists['eventscats'] = JHTML::_('select.genericlist', $cat_options, 'filter_category', '', 'value', 'text', $cat);

		$lists['customfilters'] = $custom;

		$ajaxurl = 'index.php?option=com_redevent&view=venue&tmpl=component';

		if ($vcat)
		{
			$ajaxurl .= '&vcat=' . $vcat;
		}

		if ($cat)
		{
			$ajaxurl .= '&cat=' . $cat;
		}

		$this->assignRef('rows', $rows);
		$this->assignRef('countries', $countries);
		$this->assignRef('lists', $lists);
		$this->assign('action', JRoute::_('index.php?option=com_redevent&view=venuesmap'));
		$this->assign('ajaxurl', $ajaxurl);
		$this->assign('filter_customs', $filter_customs);

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

		$params->def('page_title', (isset($menuItem->title) ? $menuItem->title : JText::_('COM_REDEVENT')));

		return $params->get('page_title');
	}
}
