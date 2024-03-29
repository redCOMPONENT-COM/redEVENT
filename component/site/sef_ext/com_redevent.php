<?php
/**
 * sh404SEF support for redEVENT
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if (!class_exists('RedEVENTsh404Helper'))
{
	class RedEVENTsh404Helper
	{
		/**
		 * Return pricegroup alias
		 *
		 * @param   int  $id  session price group id
		 *
		 * @return alias or id if empty
		 */
		public static function getPgAlias($id)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('p.alias');
			$query->from('#__redevent_sessions_pricegroups AS sp');
			$query->join('INNER', '#__redevent_pricegroups AS p ON p.id = sp.pricegroup_id');
			$query->where('sp.id = ' . $db->Quote(intval($id)));

			$db->setQuery($query);
			$res = $db->loadResult();

			return ($res ? $res : $id);
		}

		/**
		 * check if submission includes a paid registration
		 *
		 * @param   string  $submit_key  submission submit_key
		 *
		 * @return boolean
		 */
		public static function getIsPaidRegistration($submit_key)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('sp.price');
			$query->from('#__redevent_register AS r');
			$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id =  r.xref');
			$query->join('INNER', '#__redevent_sessions_pricegroups AS sp ON sp.id =  x.id AND r.sessionpricegroup_id');
			$query->where('r.submit_key = ' . $db->Quote($submit_key));
			$query->where('sp.price > 0');

			$db->setQuery($query);
			$res = $db->loadResult();

			return ($res ? true : false);
		}
	}
}

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);

if ($dosef == false)
{
	return;
}
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
require_once 'language.php';
// ------------------  load language file - adjust as needed ----------------------------------------
$shHomePageFlag = false;

$shHomePageFlag = !$shHomePageFlag ? shIsHomepage($string) : $shHomePageFlag;

if (!$shHomePageFlag)
{
	// we may have found that this is homepage, so we msut return an empty string

	if (isset($task) && $task == 'createpdfemail')
	{
		$dosef = false;
	}
	elseif (isset($page) && $page == 'print')
	{
		$dosef = false;
	}
	else
	{

		$app = JFactory::getApplication();
		$reParams = $app->getParams('com_redevent');

		/* Get the DB connection */
		$db = JFactory::getDBO();

		$Itemid = isset($Itemid) ? @$Itemid : null;

		if (!empty($Itemid))
		{
			$menu = $app->getMenu();
			$menuparams = $menu->getParams($Itemid);
		}
		else
		{
			$menuparams = null;
		}

		if (!preg_match( '/Itemid=[0-9]+/iu', $string)) { // if no Itemid in non-sef URL
			// V 1.2.4.t moved back here
			if ($sefConfig->shInsertGlobalItemidIfNone && !empty($shCurrentItemid)) {
				$string .= '&Itemid='.$shCurrentItemid; ;  // append current Itemid
				$Itemid = $shCurrentItemid;
				shAddToGETVarsList('Itemid', $Itemid); // V 1.2.4.m
			}

			if ($sefConfig->shInsertTitleIfNoItemid)
				$title[] = $sefConfig->shDefaultMenuItemName ?
					$sefConfig->shDefaultMenuItemName : getMenuTitle($option, (isset($view) ? $view : null), $shCurrentItemid, null, $shLangName );  // V 1.2.4.q added forced language
			$shItemidString = '';
			if ($sefConfig->shAlwaysInsertItemid && (!empty($Itemid) || !empty($shCurrentItemid)))
				$shItemidString = JText::_('COM_SH404SEF_ALWAYS_INSERT_ITEMID_PREFIX').$sefConfig->replacement
					.(empty($Itemid)? $shCurrentItemid :$Itemid);
		} else {  // if Itemid in non-sef URL
			$shItemidString = $sefConfig->shAlwaysInsertItemid ?
				JText::_('COM_SH404SEF_ALWAYS_INSERT_ITEMID_PREFIX').$sefConfig->replacement.$Itemid
				: '';
			if ($sefConfig->shAlwaysInsertMenuTitle){
				//global $Itemid; V 1.2.4.g we want the string option, not current page !
				if ($sefConfig->shDefaultMenuItemName)
					$title[] = $sefConfig->shDefaultMenuItemName;// V 1.2.4.q added force language
				elseif ($menuTitle = getMenuTitle($option, (isset($view) ? $view : null), $Itemid, '',$shLangName )) {
					if ($menuTitle != '/') $title[] = $menuTitle;
				}
			}
		}

		shRemoveFromGETVarsList('option');
		shRemoveFromGETVarsList('lang');

		if (!empty($Itemid))
		{
			shRemoveFromGETVarsList('Itemid');
		}

		// optional removal of limit and limitstart
		if (!empty($limit)) // use empty to test $limit as $limit is not allowed to be zero
			shRemoveFromGETVarsList('limit');
		if (isset($limitstart)) // use isset to test $limitstart, as it can be zero
			shRemoveFromGETVarsList('limitstart');

		/* Set the main title of the component */
		if (isset($view))
		{
			if (isset($sh_LANG[$shLangIso][$view]))
			{
				$title[] = $sh_LANG[$shLangIso][$view];
			}
			else
			{
				$title[] = $view;
			}

			// layout, no replacement
			if (isset($layout))
			{
				$title[] = $layout;
				shRemoveFromGETVarsList('layout');
			}

			if ($view == 'details' || $view == 'signup' || (isset($controller) && $controller == 'registration'))
			{
				if (isset($xref) && $xref)
				{
					$q = "SELECT e.title, v.city, DATE_FORMAT(x.dates, '%Y-%m-%d') AS dates, TIME_FORMAT(x.times, '%H-%i') AS times,
	              CASE WHEN CHAR_LENGTH(v.alias) THEN v.alias ELSE v.id END as venueslug
		            FROM #__redevent_event_venue_xref x
		            LEFT JOIN #__redevent_events e
		            ON e.id = x.eventid
		            LEFT JOIN #__redevent_venues v
		            ON v.id = x.venueid
		            WHERE x.id = " . $db->Quote((int) $xref);
					$db->setQuery($q);
					$storeq = $db->getQuery();
					$details = $db->loadObject();
				}
				else if (isset($id))
				{
					$q = "SELECT e.title
		            FROM  #__redevent_events e
		            WHERE e.id = " . (int) $id;
					$db->setQuery($q);
					$storeq = $db->getQuery();
					$details = $db->loadObject();
				}
				else
				{
					Jerror::raiseWarning(0, 'sh404sef redevent missing event id/xref');
					$dosef = false;
					return;
				}
			}

			switch ($view)
			{
				case 'calendar':
					if ($menuparams && $catId = $menuparams->get('topcat'))
					{
						$q = "SELECT name FROM #__redevent_categories WHERE id = " . $db->Quote((int) $catId);
						$db->setQuery($q);
						$title[] = $db->loadResult();
					}
					break;

				case 'categories':
					if ($menuparams && $menuparams->get('parentcategory', 0))
					{
						$vcat = $menuparams->get('parentcategory', 0);
						$q = "SELECT name FROM #__redevent_categories WHERE id = " . $db->Quote((int) $vcat);
						$db->setQuery($q);
						$title[] = $db->loadResult();
					}
					break;

				case 'categoriesdetailed':
					if ($menuparams && $menuparams->get('parentcategory', 0))
					{
						$vcat = $menuparams->get('parentcategory', 0);
						$q = "SELECT name FROM #__redevent_categories WHERE id = " . $db->Quote((int) $vcat);
						$db->setQuery($q);
						$title[] = $db->loadResult();
					}
					break;

				case 'categoryevents':
					if ($reParams->get('sh404sef_categoryevents_alias', 0) == 1)
					{
						$title = [];
						$q = "SELECT alias FROM #__redevent_categories WHERE id = " . $db->Quote((int) $id);
					}
					else
					{
						$q = "SELECT name FROM #__redevent_categories WHERE id = " . $db->Quote((int) $id);
					}
					$db->setQuery($q);
					$title[] = $db->loadResult();
					//$title[] = $id;
					/* Remove xref so no other course details are added */
					shRemoveFromGETVarsList('id');
					shRemoveFromGETVarsList('xref');
					break;

				case 'day':
					if (isset($id))
					{
						$title[] = $id;
						shRemoveFromGETVarsList('id');
					}
					if ($menuparams)
					{
						$offset = $menuparams->get('days', 0);
						switch ($offset)
						{
							case 0:
								$title[] = $sh_LANG[$shLangIso]['today'];
								break;
							case 1:
								$title[] = $sh_LANG[$shLangIso]['tomorrow'];
								break;
							case -1:
								$title[] = $sh_LANG[$shLangIso]['yesterday'];
								break;
							default:
								$title[] = sprintf($sh_LANG[$shLangIso]['in x days'], $offset);
								break;
						}
					}
					break;

				case 'details':
					if (isset($xref) && $xref)
					{
						if (!$details)
						{ // link to a non existing event
							$dosef = false;
							return;
						}
						$title[] = $xref . '-' . $details->title;
						$title[] = $details->city;
						if ($details->dates == '0000-00-00')
						{
							$title[] = $sh_LANG[$shLangIso]['open-date'];
						}
						else
						{
							$title[] = $details->dates;
						}
						if ($details->times != '00-00') $title[] = $details->times;
						shRemoveFromGETVarsList('xref');
						if (!empty($id))
							shRemoveFromGETVarsList('id');
					}
					else if (isset($id))
					{
						$title[] = $id . '-' . $details->title;
						shRemoveFromGETVarsList('id');
					}
					break;

				case 'editsession':
					if (isset($e_id))
					{
						$title[] = $e_id;
						shRemoveFromGETVarsList('e_id');
					}

					if (isset($s_id))
					{
						$title[] = $s_id;
						shRemoveFromGETVarsList('s_id');
					}

					break;

				case 'signup':
					$title[] = $details->venueslug;

					if ($details->dates == '0000-00-00')
					{
						$title[] = $sh_LANG[$shLangIso]['open-date'];
					}
					else
					{
						$title[] = $details->dates;
					}

					if ($details->times != '00-00')
					{
						$title[] = $details->times;
					}

					$title[] = $xref;

					if (isset($pg))
					{
						$title[] = redEVENTsh404Helper::getPgAlias($pg);
						shRemoveFromGETVarsList('pg');
					}

					shRemoveFromGETVarsList('xref');
					shRemoveFromGETVarsList('id');
					break;

				case 'upcomingvenueevents':
					$q = "SELECT venue FROM #__redevent_venues WHERE id = " . $db->Quote((int) $id);
					$db->setQuery($q);
					$title[] = $db->loadResult();
					/* Remove xref so no other course details are added */
					shRemoveFromGETVarsList('id');
					break;

				case 'venue': // ajax call, no need for sef
					$dosef = false;
					break;

				case 'venuecategory':
					$q = "SELECT name FROM #__redevent_venues_categories WHERE id = " . $db->Quote((int) $id);
					$db->setQuery($q);
					$title[] = $db->loadResult();
					/* Remove xref so no other course details are added */
					shRemoveFromGETVarsList('id');
					break;

				case 'venueevents':
					$q = "SELECT venue FROM #__redevent_venues WHERE id = " . $db->Quote((int) $id);
					$db->setQuery($q);
					$title[] = $db->loadResult();
					/* Remove xref so no other course details are added */
					shRemoveFromGETVarsList('id');
					break;

				case 'venues':
					if ($menuparams && $menuparams->get('categoryid', 0))
					{
						$vcat = $menuparams->get('categoryid', 0);
						$q = "SELECT name FROM #__redevent_venues_categories WHERE id = " . $db->Quote((int) $vcat);
						$db->setQuery($q);
						$title[] = $db->loadResult();
					}
					break;

				default:
					break;
			}
		}

		/* Remove ID field as we no longer need it */
		shRemoveFromGETVarsList('view');

		if (isset($subtype))
		{
			$title[] = $subtype;
			shRemoveFromGETVarsList('subtype');
		}

		if (isset($submit_key))
		{
			if (isset($page))
			{
				switch ($page)
				{
					case 'final':
						$title[] = $action;
						break;
					default:
						if ($page != 'confirmation') $title[] = $page;
						else $title[] = $action;
						break;
				}
				shRemoveFromGETVarsList('page');
				shRemoveFromGETVarsList('action');
			}
			else $title[] = 'submit';
		}

		if (isset($page))
		{
			$title[] = $page;
			shRemoveFromGETVarsList('page');
		}

		if (isset($task))
		{
			if (strtolower($task) == 'confirm' && strtolower($controller == 'registration'))
			{
				$title[] = $xref;
				$title[] = $sh_LANG[$shLangIso]['registration'];
				$title[] = $sh_LANG[$shLangIso]['confirm'];
				if (redEVENTsh404Helper::getIsPaidRegistration($submit_key))
				{
					$title[] = $sh_LANG[$shLangIso]['free'];
				}
				else
				{
					$title[] = $sh_LANG[$shLangIso]['paid'];
				}
				$title[] = $submit_key;
				$title[] = $confirmid;
				shRemoveFromGETVarsList('submit_key');
				shRemoveFromGETVarsList('confirmid');
				shRemoveFromGETVarsList('controller');
				shRemoveFromGETVarsList('xref');
				shRemoveFromGETVarsList('task');
			}
			else
			{
				$title[] = $task;
				shRemoveFromGETVarsList('task');
			}
		}

		if (isset($tpl))
		{
			$title[] = $tpl;
			shRemoveFromGETVarsList('tpl');
		}

		if (isset($pop))
		{
			if ($pop == 1)
			{
				$title[] = 'print';
			}
			shRemoveFromGETVarsList('pop');
		}

		/* Handle the RSS feed */
		if (isset($format))
		{
			if (strtolower($format) == 'feed') $title[] = 'feed';
			shRemoveFromGETVarsList('format');
		}
		if (isset($type))
		{
			$title[] = $type;
			shRemoveFromGETVarsList('type');
		}
	}
	// ------------------  standard plugin finalize function - don't change ---------------------------
	if ($dosef)
	{
		$string = shFinalizePlugin($string, $title, $shAppendString, $shItemidString,
			(isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
			(isset($shLangName) ? @$shLangName : null), (isset($showall) ? @$showall : null));
	}
	// ------------------  standard plugin finalize function - don't change ---------------------------
}
else
{ // this is multipage homepage
	$title[] = '/';
	$string = sef_404::sefGetLocation($string, $title, null, (isset($limit) ? @$limit : null),
		(isset($limitstart) ? @$limitstart : null), (isset($shLangName) ? @$shLangName : null),
		(isset($showall) ? @$showall : null));
}
