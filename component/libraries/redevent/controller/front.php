<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008-2016 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Redevent Component Controller
 *
 * @package  Redevent.library
 * @since    0.9
 */
class RedeventControllerFront extends JControllerLegacy
{
	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  A JController object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// If filter is set, put the filter values as get variable so that the user can go back without warning
		$this->checkfilter();

		$view = $this->input->get('view', '');

		$method = '_display' . ucfirst($view);

		if (method_exists($this, $method))
		{
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

		return parent::display();
	}

	/**
	 * Check if there are post filter, then redirect to get
	 *
	 * @return void
	 */
	protected function checkfilter()
	{
		$post = $_POST;
		$filterInput = JFilterInput::getInstance();

		$uri = JUri::getInstance();

		// Do not modify it if not proper view...
		$myuri = clone $uri;
		$vars = 0;

		foreach ($post as $filter => $v)
		{
			if (is_array($v))
			{
				$v = $filterInput->clean($v, 'array');
			}
			else
			{
				$v = $filterInput->clean($v, 'string');
			}

			switch ($filter)
			{
				case 'filter_category':
				case 'filter_venuecategory':
				case 'filter_order':
				case 'filter_order_Dir':
				case 'filter':
				case 'filter_event':
				case 'filter_type':
				case 'filter_venue':
				case 'filter_date':
				case 'filter_date_from':
				case 'filter_date_to':
				case 'filter_country':
				case 'task':
				case 'layout':
				case 'showfilters':
					if ($v)
					{
						$myuri->setVar($filter, urlencode($v));
						$vars++;
					}
					break;

				case 'filter_multicategory':
				case 'filter_multivenue':
					if (is_array($v) && !empty($v))
					{
						foreach ($v AS $k => $val)
						{
							if ($val)
							{
								$myuri->setVar($filter . "[$k]", urlencode($val));
								$vars++;
							}
						}
					}
					break;

				case 'limit':
					$app = JFactory::getApplication();
					$params = $app->getParams('com_redevent');
					$app->getUserStateFromRequest('com_redevent.limit', 'limit', $params->def('display_num', 0), 'int');
					$vars++;
					break;

				case 'filtercustom':

					foreach ((array) $v as $n => $val)
					{
						if (is_array($val))
						{
							foreach ($val as $k => $sub)
							{
								if ($sub)
								{
									$myuri->setVar("filtercustom[$n][$k]", urlencode($sub));
									$vars++;
								}
							}
						}
						elseif ($val)
						{
							$myuri->setVar("filtercustom[$n]", urlencode($val));
							$vars++;
						}
					}

					break;

				default:
					if (strpos($filter, 'filter_') === 0)
					{
						if ($v)
						{
							$myuri->setVar($filter, urlencode($v));
							$vars++;
						}
					}
			}
		}

		if ($vars)
		{
			switch ($this->input->get('view', ''))
			{
				case 'archive':
				case 'categoryevents':
				case 'day':
				case 'featured':
				case 'search':
				case 'simplelist':
				case 'venueevents':
				case 'venuesmap':
				case 'week':
					$this->setRedirect(JRoute::_($myuri->toString(), false));
					$this->redirect();
			}
		}
	}
}
