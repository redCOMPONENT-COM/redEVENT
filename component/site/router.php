<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Routing class for com_redevent
 *
 * @since  3.2.9
 */
class RedeventRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_redeventcart component
	 *
	 * @param   array  $query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	public function build(&$query)
	{
		$segments = array();

		if (isset($query['view']))
		{
			$view = $query['view'];
			$segments[] = $query['view'];
			unset($query['view']);
		}
		else
		{
			$view = '';
		}

		switch ($view)
		{
			case 'registration':
				if (isset($query['layout']))
				{
					$segments[] = $query['layout'];
					unset($query['layout']);
				}
				break;
			case 'confirmation':
				break;
			case 'editevent':
				if (isset($query['e_id']))
				{
					$segments[] = $query['e_id'];
					unset($query['e_id']);
				}

				if (isset($query['s_id']))
				{
					$segments[] = $query['s_id'];
					unset($query['s_id']);
				}

				break;

			case 'editsession':
				if (isset($query['e_id']))
				{
					$segments[] = $query['e_id'];
					unset($query['e_id']);
				}

				if (isset($query['s_id']))
				{
					$segments[] = $query['s_id'];
					unset($query['s_id']);
				}

				break;

			case 'myevents':
				if (isset($query['controller']))
				{
					$segments[] = $query['controller'];
					unset($query['controller']);
				}

				if (isset($query['task']))
				{
					$segments[] = $query['task'];
					unset($query['task']);
				}

				break;

			case 'details':
				$segments[] = $query['id'];
				unset($query['id']);

				if (isset($query['xref']))
				{
					$segments[] = $query['xref'];
					unset($query['xref']);
				}

				break;

			case 'archive':
			case 'bundle':
			case 'calendar':
			case 'categoryevents':
			case 'search':
			case 'upcomingevents':
			case 'venuecategory':
			case 'venuesmap':
			case 'categories':
			case 'upcomingvenueevents':
			case 'venueevents':
			case 'categoriesdetailed':
			case 'day':
			case 'editvenue':
			case 'payment':
			case 'simplelist':
			case 'featured':
			case 'venue':
			case 'venues':
				if (isset($query['id']))
				{
					$segments[] = $query['id'];
					unset($query['id']);
				}

				if (isset($query['task']))
				{
					$segments[] = $query['task'];
					unset($query['task']);
				}

				if (isset($query['returnid']))
				{
					$segments[] = $query['returnid'];
					unset($query['returnid']);
				}

				break;

			case 'signup':
				if (isset($query['subtype']))
				{
					$segments[] = $query['subtype'];
					unset($query['subtype']);
				}

				if (isset($query['task']))
				{
					$segments[] = $query['task'];
					unset($query['task']);
				}

				if (isset($query['id']))
				{
					$segments[] = $query['id'];
					unset($query['id']);
				}

				if (isset($query['xref']))
				{
					$segments[] = $query['xref'];
					unset($query['xref']);
				}

				if (isset($query['pg']))
				{
					$segments[] = $query['pg'];
					unset($query['pg']);
				}

				break;

			case 'attendees':
				if (isset($query['xref']))
				{
					$segments[] = $query['xref'];
					unset($query['xref']);
				}

				if (isset($query['task']))
				{
					$segments[] = $query['task'];
					unset($query['task']);
				}

				break;

			case 'moreinfo':
				if (isset($query['xref']))
				{
					$segments[] = $query['xref'];
					unset($query['xref']);
				}

				if (isset($query['tmpl']))
				{
					unset($query['tmpl']);
				}

				break;

			case 'week':
				if (isset($query['week']))
				{
					$segments[] = $query['week'];
					unset($query['week']);
				}

				break;
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  $segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 */
	public function parse(&$segments)
	{
		$vars = array();

		// Handle View and Identifier
		switch ($segments[0])
		{
			case 'categoryevents':
				$id = explode(':', $segments[1]);
				$vars['id'] = $id[0];
				$vars['view'] = 'categoryevents';

				$count = count($segments);

				if ($count > 2)
				{
					$vars['task'] = $segments[2];
				}

				break;

			case 'bundle':
				if (isset($segments[1]))
				{
					$id = explode(':', $segments[1]);
					$vars['id'] = $id[0];
				}

				$vars['view'] = 'bundle';

				break;

			case 'venue':
				if (isset($segments[1]))
				{
					$id = explode(':', $segments[1]);
					$vars['id'] = $id[0];
				}

				$vars['view'] = 'venue';

				break;

			case 'details':
				$vars['view'] = 'details';
				$count = count($segments);

				$id = explode(':', $segments[1]);
				$vars['id'] = $id[0];

				if ($count > 2)
				{
					$xref = explode(':', $segments[2]);
					$vars['xref'] = $xref[0];
				}

				break;

			case 'venueevents':
				$id = explode(':', $segments[1]);
				$vars['id'] = $id[0];
				$vars['view'] = 'venueevents';
				$count = count($segments);

				if ($count > 2)
				{
					$vars['task'] = $segments[2];
				}

				break;

			case 'editevent':
				$count = count($segments);

				$vars['view'] = 'editevent';

				if ($count > 1)
				{
					$vars['e_id'] = $segments[1];
				}

				if ($count > 2)
				{
					$vars['s_id'] = $segments[2];
				}

				break;

			case 'editsession':
				$count = count($segments);

				$vars['view'] = 'editsession';

				if ($count > 1)
				{
					$vars['e_id'] = $segments[1];
				}

				if ($count > 2)
				{
					$vars['s_id'] = $segments[2];
				}

				break;

			case 'editvenue':
				$count = count($segments);

				$vars['view'] = 'editvenue';

				if ($count > 1)
				{
					$vars['id'] = $segments[1];
				}

				if ($count > 2)
				{
					$vars['returnid'] = $segments[2];
				}

				break;

			case 'simplelist':
				$vars['view'] = 'simplelist';

				$count = count($segments);

				if ($count == 2)
				{
					$vars['task'] = $segments[1];
				}

				break;

			case 'categoriesdetailed':
				$vars['view'] = 'categoriesdetailed';

				$count = count($segments);

				if ($count == 2)
				{
					$vars['task'] = $segments[1];
				}

				break;

			case 'categories':
				$vars['view'] = 'categories';

				$count = count($segments);

				if ($count == 2)
				{
					$vars['task'] = $segments[1];
				}

				break;

			case 'venues':
				$vars['view'] = 'venues';

				$count = count($segments);

				if ($count == 2)
				{
					$vars['task'] = $segments[1];
				}

				break;

			case 'day':
				$vars['view'] = 'day';

				$count = count($segments);

				if ($count == 2)
				{
					$vars['id'] = $segments[1];
				}

				break;

			case 'upcomingvenueevents':
				$vars['view'] = 'upcomingvenueevents';

				$count = count($segments);

				if ($count == 2)
				{
					$vars['id'] = $segments[1];
				}

				break;

			case 'attendees':
				$vars['view'] = 'attendees';

				$count = count($segments);

				if ($count > 1)
				{
					$vars['xref'] = $segments[1];
				}

				if ($count > 2)
				{
					$vars['task'] = $segments[2];
				}

				break;

			case 'signup':
				$vars['view'] = 'signup';

				$count = count($segments);

				if ($count > 1)
				{
					$vars['subtype'] = $segments[1];
				}

				if ($count > 2)
				{
					$vars['task'] = $segments[2];
				}

				if ($count > 3)
				{
					$vars['id'] = $segments[3];
				}

				if ($count > 4)
				{
					$vars['xref'] = $segments[4];
				}

				if ($count > 5)
				{
					$vars['pg'] = $segments[5];
				}

				break;

			case 'moreinfo':
				$vars['view'] = $segments[0];
				$vars['xref'] = $segments[1];
				$vars['tmpl'] = 'component';
				break;

			case 'week':
				$vars['view'] = $segments[0];
				$vars['week'] = $segments[1];
				break;

			case 'myevents':
				$vars['view'] = $segments[0];

				if (isset($segments[1]))
				{
					$vars['controller'] = $segments[1];
				}

				if (isset($segments[2]))
				{
					$vars['task'] = $segments[2];
				}

				break;

			case 'registration':
				$vars['view'] = $segments[0];

				if (isset($segments[1]))
				{
					$vars['layout'] = $segments[1];
				}
				break;

			case 'archive':
			case 'confirmation':
			case 'calendar':
			case 'payment':
			case 'search':
			case 'upcomingevents':
			case 'venue':
			case 'venuecategory':
			case 'venuesmap':
			case 'myevents':
			case 'featured':
				$vars['view'] = $segments[0];
				break;
		}

		return $vars;
	}
}

/**
 * Build route
 *
 * @param   array  $query  query parts
 *
 * @return array
 */
function redeventBuildRoute(&$query)
{
	$router = new RedeventRouter;

	return $router->build($query);
}

/**
 * Parse segments
 *
 * @param   array  $segments  segments
 *
 * @return array
 */
function redeventParseRoute($segments)
{
	$router = new RedeventRouter;

	return $router->parse($segments);
}
