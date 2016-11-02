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
			$bundleeventid = $this->input->getInt('bundleeventid');

			if (!$entity = RedeventEntityBundleevent::load($bundleeventid))
			{
				throw new InvalidArgumentException('Entity not found');
			}

			if (!$next = $entity->getNext())
			{
				echo new JResponseJson("");

				return;
			}

			echo new JResponseJson($this->getSessionData($next));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	/**
	 * Get bundle sessions
	 *
	 * @return void
	 */
	public function sessions()
	{
		try
		{
			$bundleeventid = $this->input->getInt('bundleeventid');
			$limitstart = $this->input->getInt('limitstart');

			if (!$entity = RedeventEntityBundleevent::load($bundleeventid))
			{
				throw new InvalidArgumentException('Entity not found');
			}

			if (!$sessions = $entity->getSessions())
			{
				echo new JResponseJson("");

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

					$data = new stdclass;
					$data->id = $session->id;
					$data->date = $session->getFormattedStartDate();
					$data->duration = $session->getDurationDays();
					$data->language = $session->language;
					$data->venue = $session->getVenue()->name;
					$data->price = $price;
					$data->left = $session->getNumberLeft();
					$data->maxattendees = $session->maxattendees;
					$data->full = $session->isFull();

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

	/**
	 * Get session
	 *
	 * @return void
	 */
	public function session()
	{
		try
		{
			$id = $this->input->getInt('id');

			if (!$session = RedeventEntitySession::load($id))
			{
				throw new InvalidArgumentException('Entity not found');
			}

			echo new JResponseJson($this->getSessionData($session));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	/**
	 * Get session data from entity
	 *
	 * @param   RedeventEntitySession  $session  entity
	 *
	 * @return array
	 */
	private function getSessionData(RedeventEntitySession $session)
	{
		$id = $session->id;

		$label = JText::sprintf(
			'COM_REDEVENT_VIEW_BUNDLE_SESSION_SELECTED_LABEL_S_S_S_D',
			$session->getFormattedStartDate(),
			$session->getVenue()->city,
			$session->getVenue()->country,
			$session->getDurationDays()
		);

		if ($priceGroups = $session->getPricegroups(true))
		{
			$prices = array_map(
				function($pg)
				{
					return array('id' => $pg->id, 'price' => $pg->price, 'currency' => $pg->currency);
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

		$hasLimit = $session->maxattendees;
		$left = $session->getNumberLeft();

		return compact('id', 'label', 'prices', 'singleprice', 'hasLimit', 'left');
	}
}
