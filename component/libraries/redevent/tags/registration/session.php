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
class RedeventTagsRegistrationSession
{
	/**
	 * @var RdfCore
	 */
	private $rfcore;

	/**
	 * @var RedeventEntitySession
	 */
	private $session;

	/**
	 * @var string
	 */
	private $submitKey;

	/**
	 * @var boolean
	 */
	private $isReview = false;

	/**
	 * @var boolean
	 */
	private $isSingle = false;

	/**
	 * @var int
	 */
	private $pricegroupId = 0;

	/**
	 * Constructor
	 *
	 * @param   int  $sessionId  session id
	 */
	public function __construct($sessionId)
	{
		$this->rfcore = RdfCore::getInstance();
		$this->session = RedeventEntitySession::load($sessionId);
	}

	/**
	 * Set pricegroup id
	 *
	 * @param   int  $id  pricegroup id
	 *
	 * @return RedeventTagsRegistrationSession
	 */
	public function setPricegroupId($id)
	{
		$this->pricegroupId = (int) $id;

		return $this;
	}

	/**
	 * Submit key
	 *
	 * @param   string  $key  submit key
	 *
	 * @return RedeventTagsRegistrationSession
	 */
	public function setSubmitKey($key)
	{
		$this->submitKey = $key;

		return $this;
	}

	/**
	 * Return tag html
	 *
	 * @return string
	 */
	public function getHtml()
	{
		try
		{
			$this->checkCanRegister();
			$html = $this->process();
		}
		catch (Exception $e)
		{
			return '<span class="error">' . $e->getMessage() . '</span>';
		}

		return $html;
	}

	/**
	 * Set review state
	 *
	 * @param   boolean  $value  is review
	 *
	 * @return RedeventTagsRegistrationSession
	 */
	public function isReview($value)
	{
		$this->isReview = $value ? true : false;

		return this;
	}

	/**
	 * Set single state
	 *
	 * @param   boolean  $value  force single signup
	 *
	 * @return RedeventTagsRegistrationSession
	 */
	public function isSingle($value)
	{
		$this->isSingle = $value ? true : false;

		return this;
	}

	/**
	 * Do the work
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	private function process()
	{
		$form = $this->getRedformForm();
		$multi = $this->getNumberOfSignup();
		$prices = $this->session->getPricegroups(true);

		$hasReview = $this->session->getEvent()->hasReview();

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
		$html .= $this->rfcore->getFormFields($this->session->getEvent()->redform_id, $this->isReview ? null : $this->submitKey, $multi, $options);
		$html .= '<input type="hidden" name="xref" value="' . $this->session->id . '"/>';
		$html .= '<input type="hidden" name="option" value="com_redevent"/>';
		$html .= '<input type="hidden" name="task" value="registration.register"/>';

		if ($hasReview)
		{
			$html .= '<input type="hidden" name="hasreview" value="1"/>';
		}

		$html .= '<div id="submit_button" style="display: block;" class="submitform' . $form->classname . '">';

		if (empty($this->submitKey))
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
			if ($this->isReview)
			{
				$label = "display review registration form for event " . $this->session->getEvent()->title;
			}
			else
			{
				$label = "display registration form for event " . $this->session->getEvent()->title;
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
	 * Check the user can register to the session
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function checkCanRegister()
	{
		$status = RedeventHelper::canRegister($this->session->id);

		if (!$status->canregister)
		{
			throw new Exception($status->status);
		}
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
		$max = $this->session->getEvent()->max_multi_signup;
		$user = JFactory::getUser();

		if ($max && !$this->isSingle && $user->get('id'))
		{
			$multi = $this->session->getUserNumberOfSignupLeft($user->get('id'));

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
	 * Get redFORM form
	 *
	 * @return RdfEntityForm
	 *
	 * @throws Exception
	 */
	private function getRedformForm()
	{
		$form = $this->session->getEvent()->getForm();

		if (!$form->checkFormStatus())
		{
			throw new RuntimeException($form->getStatusMessage(), 500);
		}

		return $form;
	}

	/**
	 * Check if a pricegroup is already selected
	 *
	 * @param   RedeventEntitySessionpricegroup[]  $sessionPriceGroups  session price groups
	 *
	 * @return RedeventEntitySessionpricegroup|false if not selected
	 */
	private function getSelectedPriceGroup($sessionPriceGroups)
	{
		$isReview = $this->isReview;

		// If a review, we already have sessionpricegroup_id set in user session data
		$sessionPricegroupIds = $isReview ? JFactory::getApplication()->getUserState('spgids' . $this->submitKey) : null;

		if (!empty($sessionPricegroupIds))
		{
			$sessionPricegroupId = (int) $sessionPricegroupIds[0];

			foreach ($sessionPriceGroups as $sessionPriceGroup)
			{
				if ($sessionPriceGroup->id == $sessionPricegroupId)
				{
					return $sessionPriceGroup;
				}
			}
		}

		// Otherwise check if set
		if (count($sessionPriceGroups) == 1)
		{
			return current($sessionPriceGroups);
		}

		if ($this->pricegroupId)
		{
			foreach ($sessionPriceGroups as $sessionPriceGroup)
			{
				if ($sessionPriceGroup->id == $this->pricegroupId)
				{
					return $sessionPriceGroup;
				}
			}
		}

		return false;
	}
}
