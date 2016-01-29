<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Handles redform tag
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventTagsFormForm
{
	/**
	 * @var RdfCore
	 */
	private $rfcore;

	/**
	 * @var RModel
	 */
	private $model;

	/**
	 * @var JInput
	 */
	private $input;

	/**
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param   RModel  $dataModel  data model for session details
	 */
	public function __construct($dataModel)
	{
		$this->rfcore = RdfCore::getInstance();
		$this->model = $dataModel;
		$this->input = JFactory::getApplication()->input;
		$this->db = JFactory::getDbo();
	}

	/**
	 * Return tag html
	 *
	 * @param   boolean  $hasReview  has review
	 *
	 * @return string
	 */
	public function getHtml($hasReview = false)
	{
		try
		{
			$html = $this->process($hasReview);
		}
		catch (Exception $e)
		{
			return '<span class="error">' . $e->getMessage() . '</span>';
		}

		return $html;
	}

	/**
	 * Do the work
	 *
	 * @param   boolean  $hasReview  has review
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	protected function process($hasReview)
	{
		$isReview = $this->input->get('task') == 'review';
		$submit_key = $this->input->get('submit_key');

		$form = $this->getRedformForm();
		$multi = $this->getNumberOfSignup();
		$prices = $this->model->getPrices();

		$options = array('extrafields' => array());

		// Multiple pricegroup handling
		if (count($prices))
		{
			$selectedPricegroup = $this->getSelectedPriceGroup($prices);

			// We add one field per signup
			for ($index = 1; $index < $multi + 1; $index++)
			{
				$field = new RedeventRfieldSessionprice;
				$field->setOptions($prices);
				$field->setFormIndex($index);

				if ($selectedPricegroup)
				{
					$field->setValue($selectedPricegroup->id);
				}

				$options['extrafields'][$index] = array($field);
			}

			$currency = $selectedPricegroup ? $selectedPricegroup->currency : current($prices)->currency;
			$options['currency'] = $currency;

			if (RedeventHelper::config()->get('payBeforeConfirm'))
			{
				$options['selectPaymentGateway'] = 1;
			}
		}

		$html = '<form action="' . JRoute::_('index.php') . '" class="redform-validate" method="post" name="redform" enctype="multipart/form-data">';
		$html .= $this->rfcore->getFormFields($this->model->getData()->redform_id, $isReview ? null : $submit_key, $multi, $options);
		$html .= '<input type="hidden" name="xref" value="' . $this->model->getData()->xref . '"/>';
		$html .= '<input type="hidden" name="option" value="com_redevent"/>';
		$html .= '<input type="hidden" name="task" value="registration.register"/>';

		if ($hasReview)
		{
			$html .= '<input type="hidden" name="hasreview" value="1"/>';
		}

		$html .= '<div id="submit_button" style="display: block;" class="submitform' . $form->classname . '">';

		if (empty($submit_key))
		{
			$html .= '<input type="submit" id="regularsubmit" name="submit" value="' . JText::_('COM_REDEVENT_Submit') . '" />';
		}
		else
		{
			$html .= '<input type="submit" id="redformsubmit" name="submit" value="' . JText::_('COM_REDEVENT_Confirm') . '" />';
			$html .= '<input type="submit" id="redformcancel" name="cancel" value="' . JText::_('COM_REDEVENT_Cancel') . '" />';
		}

		$html .= '</div>';
		$html .= '</form>';

		if (RdfHelperAnalytics::isEnabled())
		{
			if ($isReview)
			{
				$label = "display review registration form for event " . $this->model->getData()->title;
			}
			else
			{
				$label = "display registration form for event " . $this->model->getData()->title;
			}

			$event = new stdclass;
			$event->category = 'registration form';
			$event->action = 'display';
			$event->label = $label;
			$event->value = null;
			RdfHelperAnalytics::trackEvent($event);
		}

		return $html;
	}

	/**
	 * Get number of signup to display
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	private function getNumberOfSignup()
	{
		// Multiple signup ?
		$single = $this->input->getInt('single', 0);
		$max = $this->model->getData()->max_multi_signup;

		if ($max && !$single && JFactory::getUser()->get('id'))
		{
			// We must substract current registrations of this user !
			$nbregs = $this->getUserActiveRegistrationsCount();
			$multi = $max - $nbregs;

			if ($multi < 1)
			{
				throw new Exception(JText::_('COM_REDEVENT_USER_MAX_REGISTRATION_REACHED'), 0);
			}
		}
		else
		{
			// Single signup
			$multi = 1;
		}

		return $multi;
	}

	/**
	 * return current number of registrations for current user to this event
	 *
	 * @return int
	 */
	private function getUserActiveRegistrationsCount()
	{
		$user = JFactory::getUser();

		if (!$user)
		{
			JError::raiseError(403, 'NO_AUTH');
		}

		$query = $this->db->getQuery(true);

		$query->select('COUNT(id)')
			->from('#__redevent_register')
			->where('uid = ' . $user->get('id'))
			->where('cancelled = 0')
			->where('xref = ' . $this->model->getData()->xref);

		$this->db->setQuery($query);
		$res = $this->db->loadResult();

		return $res;
	}

	/**
	 * Get redFORM form
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	private function getRedformForm()
	{
		if (!$this->rfcore->getFormStatus($this->model->getData()->redform_id))
		{
			throw new Exception($this->rfcore->getError(), 500);
		}

		return $this->rfcore->getForm($this->model->getData()->redform_id);
	}

	/**
	 * Check if a pricegroup is already selected
	 *
	 * @param   array  $sessionPriceGroups  session price groups
	 *
	 * @return mixed
	 */
	private function getSelectedPriceGroup($sessionPriceGroups)
	{
		$isReview = $this->input->get('task') == 'review';
		$submit_key = $this->input->get('submit_key');

		$selectedPricegroup = false;

		// If a review, we already have pricegroup_ida set in user session data
		$pricegroupIds = $isReview ? JFactory::getApplication()->getUserState('spgids' . $submit_key) : null;

		if (!empty($pricegroupIds))
		{
			$pricegroupId = intval($pricegroupIds[0]);
		}
		else
		{
			$pricegroupId = $this->input->getInt('pg', 0);
		}

		if (count($sessionPriceGroups) == 1)
		{
			$selectedPricegroup = current($sessionPriceGroups);
		}
		elseif ($pricegroupId)
		{
			foreach ($sessionPriceGroups as $price)
			{
				if ($price->id == $pricegroupId)
				{
					$selectedPricegroup = $price;
					break;
				}
			}
		}

		return $selectedPricegroup;
	}
}
