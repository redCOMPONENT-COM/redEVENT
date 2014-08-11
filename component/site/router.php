<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license can be read in this package of software in the file license.txt or
 * read on http://redcomponent.com/license.txt
 * Developed by email@recomponent.com - redCOMPONENT.com
 */
function RedEventBuildRoute(&$query)
{
	$segments = array();

	if(isset($query['view']))
	{
	  $view = $query['view'];
		$segments[] = $query['view'];
		unset($query['view']);
	}
	else {
	  $view = '';
	}

	switch ($view) {

	  case 'confirmation':
	    break;
	  case 'editevent':
    	if(isset($query['id']))
    	{
    		$segments[] = $query['id'];
    		unset($query['id']);
    	};
    	if(isset($query['xref']))
    	{
    		$segments[] = $query['xref'];
    		unset($query['xref']);
    	};
	  	break;

		case 'archive':
	  case 'calendar':
	  case 'categoryevents':
	  case 'details':
	  case 'search':
	  case 'upcomingevents':
	  case 'venuecategory':
	  case 'venuesmap':
	  case 'categories':
	  case 'confirmation':
	  case 'myevents':
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
    	if(isset($query['id']))
    	{
    		$segments[] = $query['id'];
    		unset($query['id']);
    	};

    	if(isset($query['task']))
    	{
    		$segments[] = $query['task'];
    		unset($query['task']);
    	};

    	if(isset($query['returnid']))
    	{
    		$segments[] = $query['returnid'];
    		unset($query['returnid']);
    	};
    	break;

	  case 'signup':
    	if(isset($query['subtype']))
    	{
    		$segments[] = $query['subtype'];
    		unset($query['subtype']);
    	};

    	if(isset($query['task']))
    	{
    		$segments[] = $query['task'];
    		unset($query['task']);
    	};

    	if(isset($query['id']))
    	{
    		$segments[] = $query['id'];
    		unset($query['id']);
    	};

    	if(isset($query['xref']))
    	{
    		$segments[] = $query['xref'];
    		unset($query['xref']);
    	};

    	if(isset($query['pg']))
    	{
    		$segments[] = $query['pg'];
    		unset($query['pg']);
    	};
    	break;

	  case 'attendees':
    	if(isset($query['controller']))
    	{
    		$segments[] = $query['controller'];
    		unset($query['controller']);
    	};
    	if(isset($query['task']))
    	{
    		$segments[] = $query['task'];
    		unset($query['task']);
    	};
    	if(isset($query['xref']))
    	{
    		$segments[] = $query['xref'];
    		unset($query['xref']);
    	};
    	break;
	  case 'moreinfo':
    	if(isset($query['xref']))
    	{
    		$segments[] = $query['xref'];
    		unset($query['xref']);
    	}
    	if(isset($query['tmpl']))
    	{
    		unset($query['tmpl']);
    	}
	  	break;
	  case 'week':
    	if(isset($query['week']))
    	{
    		$segments[] = $query['week'];
    		unset($query['week']);
    	}
	  	break;
	}

	return $segments;
}

function RedEventParseRoute($segments)
{
	$vars = array();
	//Handle View and Identifier
	switch($segments[0])
	{
		case 'categoryevents':
		{
			$id = explode(':', $segments[1]);
			$vars['id'] = $id[0];
			$vars['view'] = 'categoryevents';

			$count = count($segments);
			if($count > 2) {
				$vars['task'] = $segments[2];
			}

		} break;

		case 'venue':
		{
			if (isset($segments[1])) {
				$id = explode(':', $segments[1]);
				$vars['id'] = $id[0];
			}
			$vars['view'] = 'venue';
		} break;

		case 'details':
		{
			$vars['view'] = 'details';
      $count = count($segments);
			if ($count > 1)
			{
				$id = explode(':', $segments[1]);
				$vars['id'] = $id[0];
	      if($count > 2) {
	        $vars['task'] = $segments[2];
	      }
			}

		} break;

		case 'venueevents':
		{
			$id = explode(':', $segments[1]);
			$vars['id'] = $id[0];
			$vars['view'] = 'venueevents';
			$count = count($segments);
			if($count > 2) {
				$vars['task'] = $segments[2];
			}

		} break;

		case 'editevent':
		{
			$count = count($segments);

			$vars['view'] = 'editevent';

			if ($count > 1) {
				$vars['id'] = $segments[1];
			}

			if($count > 2) {
				$vars['xref'] = $segments[2];
			}

		} break;

		case 'editvenue':
		{
			$count = count($segments);

			$vars['view'] = 'editvenue';

			if($count > 1) {
				$vars['id'] = $segments[1];
			}
			if($count > 2) {
				$vars['returnid'] = $segments[2];
			}

		} break;

		case 'simplelist':
		{
			$vars['view'] = 'simplelist';

			$count = count($segments);
			if($count == 2) {
				$vars['task'] = $segments[1];
			}

		} break;

		case 'categoriesdetailed':
		{
			$vars['view'] = 'categoriesdetailed';

			$count = count($segments);
			if($count == 2) {
				$vars['task'] = $segments[1];
			}

		} break;

		case 'categories':
		{
			$vars['view'] = 'categories';

			$count = count($segments);
			if($count == 2) {
				$vars['task'] = $segments[1];
			}

		} break;

		case 'venues':
		{
			$vars['view'] = 'venues';

			$count = count($segments);
			if($count == 2) {
				$vars['task'] = $segments[1];
			}

		} break;

		case 'day':
		{
			$vars['view'] = 'day';

			$count = count($segments);
			if($count == 2) {
				$vars['id'] = $segments[1];
			}

		} break;

    case 'upcomingvenueevents':
    {
      $vars['view'] = 'upcomingvenueevents';

      $count = count($segments);
      if($count == 2) {
        $vars['id'] = $segments[1];
      }

    } break;

    case 'attendees':
    {
      $vars['view'] = 'attendees';

      $count = count($segments);
      if($count > 1) {
        $vars['controller'] = $segments[1];
      }
      if($count > 2) {
        $vars['task'] = $segments[2];
      }
      if($count > 3) {
        $vars['xref'] = $segments[3];
      }

    } break;

    case 'signup':
      $vars['view'] = 'signup';

      $count = count($segments);
      if($count > 1) {
        $vars['subtype'] = $segments[1];
      }
      if($count > 2) {
        $vars['task'] = $segments[2];
      }
      if($count > 3) {
        $vars['id'] = $segments[3];
      }
      if($count > 4) {
        $vars['xref'] = $segments[4];
      }
      if($count > 5) {
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

//    case 'cregistration':
//    	$vars['controller'] = $segments[0];
//    	break;

//		default:
//      $vars['view'] = $segments[0];
//
//			break;
	}

	return $vars;
}
