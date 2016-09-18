<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Bundle Controller
 *
 * @package  Redevent.Site
 * @since    3.2.0
 */
class RedeventControllerBundle extends JControllerLegacy
{
	/**
	 * Get default session for bundle event
	 *
	 * @return void
	 */
	public function defaultsession()
	{
		try
		{
			$bundeleventid = $this->input->getInt('bundleeventid');

			if (!$entity = RedeventEntityBundleevent::load($bundeleventid))
			{
				throw new InvalidArgumentException('Entity not found');
			}

			if (!$next = $entity->getNext())
			{
				echo new JResponseJson(false);

				return;
			}

			$id = $next->id;

			$label = JText::sprintf(
				'COM_REDEVENT_VIEW_BUNDLE_SESSION_SELECTED_LABEL_S_S_S_D',
				$next->getFormattedStartDate(),
				$next->getVenue()->city,
				$next->getVenue()->country,
				$next->getDurationDays()
			);

			if ($priceGroups = $next->getPricegroups(true))
			{
				$prices = array_map(
					function($pg)
					{
						return array('id' => $pg->id, 'price' => $pg->price);
					},
					array_values($priceGroups)
				);
				$singleprice = count($prices) <= 2;
			}
			else
			{
				$prices = false;
				$singleprice = true;
			}

			echo new JResponseJson(compact('id', 'label', 'prices', 'singleprice'));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	public function getSessions()
	{
		try
		{
			$bundeleventid = $this->input->getInt('bundleeventid');
			$limitstart = $this->input->getInt('limitstart');

			if (!$entity = RedeventEntityBundleevent::load($bundeleventid))
			{
				throw new InvalidArgumentException('Entity not found');
			}

			if (!$sessions = $entity->getSessions())
			{
				echo new JResponseJson(false);

				return;
			}

			$resp = array_map(
				function($session)
				{
					if ($priceGroups = $session->getPricegroups(true))
					{
						$prices = array_map(
							function($pg)
							{
								return $pg->price;
							},
							$priceGroups
						);

						$price = implode(" - ", $prices);
					}
					else
					{
						$price = "";
					}

					if ($session->maxattendees)
					{
						$left = $session->getNumberLeft();
						$placesLeft = $left < 10 ? $left : "10+";
					}
					else
					{
						$placesLeft = "10+";
					}

					$data = new stdclass;
					$data->date = $session->getFormattedStartDate();
					$data->duration = $session->getDurationDays();
					$data->language = $session->language;
					$data->venue = $session->getVenue()->name;
					$data->price = $price;
					$data->places = $placesLeft;

					return $data;
				},
				$sessions
			);

			echo new JResponseJson(array_slice($resp, $limitstart, 5));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}
}
