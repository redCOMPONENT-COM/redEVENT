<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license can be read in this package of software in the file license.txt or
 * read on http://redcomponent.com/license.txt
 * Developed by email@recomponent.com - redCOMPONENT.com
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Redevent Component Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
*/
class RedeventControllerFront extends JControllerLegacy
{
	/**
	 * Display the view
	 *
	 * @since 0.9
	 */
	function display()
	{
		// if filter is set, put the filter values as get variable so that the user can go back without warning
		if ($this->_checkfilter()) { // a redirect was set in the filter function
			return;
		}

		$view = JRequest::getVar('view', '');

		$method = '_display'.ucfirst($view);
		if (method_exists($this, $method)) {
			return $this->$method();
		}

		$input = JFactory::getApplication()->input;

		// Default list layout for certain views
		switch ($view)
		{
			case 'categoryevents':
			case 'simplelist':
			case 'venueevents':
				if (!$input->get('layout'))
				{
					$input->def('layout', JFactory::getApplication()->getParams('com_redevent')->get('default_list_layout', 'table'));
				}
				break;

			case 'frontadmin':
				if (!$input->get('tmpl'))
				{
					$input->set('tmpl', 'component');
				}
				break;
		}

		parent::display();
	}

	function _checkfilter()
	{
		$app = & JFactory::getApplication();

		$post = JRequest::get('post');
		$uri  = Jfactory::getUri();

		$myuri = clone($uri); // do not modify it if not proper view...
		$vars = 0;
		foreach ($post as $filter => $v)
		{
			switch ($filter)
			{
				case 'filter_category':
				case 'filter_multicategory':
				case 'filter_venuecategory':
				case 'filter_order':
				case 'filter_order_Dir':
				case 'filter':
				case 'filter_type':
				case 'filter_venue':
				case 'filter_multivenue':
				case 'layout':
				case 'task':
					if ($v)
					{
						$myuri->setVar($filter, $v);
						$vars++;
					}
					break;
				case 'filtercustom':
					$filt = array();
					foreach ((array) $v as $n => $val)
					{
						if (is_array($val))
						{
							//							echo '<pre>';print_r($val); echo '</pre>';exit;
							$r = array();
							foreach ($val as $sub) {
								if ($sub) $r[] = $sub;
							}
							$myuri->setVar("filtercustom[$n]", $r);
						}
						else {
							if ($val) $filt[$n] = $val;
						}
					}
					if (count($filt)) {
						//						echo '<pre>';print_r($filt); echo '</pre>';exit;
						$myuri->setVar($filter, $filt);
						$vars++;
					}
					break;
			}
		}

		if ($vars)
		{
			switch (JRequest::getVar('view', ''))
			{
				case 'categoryevents':
				case 'venueevents':
				case 'simplelist':
				case 'venuesmap':
				case 'search':
					$this->setRedirect(JRoute::_($myuri->toString(), false));
					break;
			}
		}
	}

}
