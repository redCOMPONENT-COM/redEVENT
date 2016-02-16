<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');


/**
 * HTML base frontend view
 *
 * @package  Redevent.Library
 * @since    3.0
 */
abstract class RedeventViewFront extends RViewSite
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

		parent::display($tpl);
	}

	/**
	 * Prepare the view
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$config = RedeventHelper::config();
		$params = $app->getParams();

		// Add css file
		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		// Get variables
		$task = $app->input->getWord('task');

		// Title
		$pagetitle = $this->getTitle();
		$document->setTitle($pagetitle);
		$document->setMetadata('keywords', $params->get('page_title'));

		if ($app->input->getBool('pop'))
		{
			// If printpopup set true
			$params->set('popup', 1);
			$this->setLayout('print');
		}

		$params->def('print', !$app->getCfg('hidePrint'));
		$print_link = JRoute::_('index.php?option=com_redevent&view=' . $this->getName() . '&tmpl=component&pop=1');

		$this->assignRef('task', $task);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('params', $params);
		$this->assignRef('pagetitle', $pagetitle);
		$this->assignRef('config', $config);
		$this->assign('state', $this->get('state'));
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
	 * Add feed links
	 *
	 * @return void
	 */
	protected function addFeedLinks()
	{
		if (!$link = $this->getFeedLink())
		{
			return;
		}

		$document = JFactory::getDocument();

		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);

		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
	}
}
