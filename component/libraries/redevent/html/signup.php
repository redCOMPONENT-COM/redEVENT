<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Html helper for signup
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventHtmlSignup
{
	/**
	 * Return signup image link for feed
	 *
	 * @param   string  $signupType   signup type
	 * @param   object  $sessionData  session data
	 *
	 * @return string html
	 *
	 * @throws LogicException
	 */
	public static function getSignupImageLink($signupType, $sessionData)
	{
		$config = RedeventHelper::config();

		// Default signup url
		$url = RedeventHelperRoute::getSignupRoute($signupType, $sessionData->xslug, $sessionData->slug);

		switch ($signupType)
		{
			case 'email':
				$image = RHelperAsset::load(
					$config->get('signup_email_img', 'email_icon.gif'),
					null,
					array(
						'alt' => JText::_($config->get('signup_email_text')),
						'class' => 'signup-icon'
					)
				);
				$link = JHtml::link($url, $image, array('target' => '_blank'));

				return $link;

			case 'phone':
				$image = RHelperAsset::load(
					$config->get('signup_phone_img', 'phone_icon.gif'),
					null,
					array(
						'alt' => JText::_($config->get('signup_phone_text')),
						'class' => 'signup-icon'
					)
				);
				$link = JHtml::link($url, $image, array('target' => '_blank'));

				return $link;

			case 'external':
				$url = $sessionData->submission_type_external;
				$image = RHelperAsset::load(
					$config->get('signup_external_img', 'external_icon.gif'),
					null,
					array(
						'alt' => JText::_($config->get('signup_external_text')),
						'class' => 'signup-icon'
					)
				);
				$link = JHtml::link($url, $image, array('target' => '_blank'));

				return $link;

			case 'webform':
				if ($sessionData->prices && count($sessionData->prices))
				{
					$text = array();

					foreach ($sessionData->prices as $p)
					{
						if ($p->image)
						{
							$image = JHtml::image(
								$p->image,
								$p->name,
								array(
									'class' => 'signup-icon'
								)
							);
						}
						else
						{
							$image = JHtml::image(
								$config->get('signup_webform_img', 'form_icon.gif'),
								$p->name,
								array(
									'class' => 'signup-icon'
								)
							);
						}

						$url = RedeventHelperRoute::getSignupRoute('webform', $sessionData->slug, $sessionData->xslug, $p->slug);
						$text[] = JHtml::link($url, $image, array('target' => '_blank'));
					}

					return implode(" ", $text);
				}
				else
				{
					$url = $sessionData->submission_type_external;
					$image = RHelperAsset::load(
						$config->get('signup_webform_img', 'form_icon.gif'),
						null,
						array(
							'alt' => JText::_($config->get('signup_webform_text')),
							'class' => 'signup-icon'
						)
					);
					$link = JHtml::link($url, $image, array('target' => '_blank'));

					return $link;
				}

			case 'formaloffer':
				$image = RHelperAsset::load(
					$config->get('signup_formal_offer_img', 'formal_icon.gif'),
					null,
					array(
						'alt' => JText::_($config->get('signup_formal_offer_text')),
						'class' => 'signup-icon')
				);
				$link = JHtml::link($url, $image, array('target' => '_blank'));

				return $link;
		}

		throw new LogicException('Unknown signup type');
	}
}
