<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Holds the logic for all output related things
 *
 * @package  Redevent.Library
 * @since    0.9
 */
class RedeventHelperOutput
{
	/**
	 * Writes Event submission button
	 *
	 * @param   boolean  $allowed  Access of user
	 * @param   array    $params   needed params
	 *
	 * @return string
	 */
	public static function submitbutton($allowed, &$params)
	{
		$output = '';

		if ($allowed)
		{
			RHtml::_('rbootstrap.tooltip');

			if ($params->get('icons', 1))
			{
				$text = RHelperAsset::load(
					'submitevent.png',
					null,
					array('alt' => JText::_('COM_REDEVENT_DELIVER_NEW_EVENT'))
				);
			}
			else
			{
				$text = JText::_('COM_REDEVENT_DELIVER_NEW_EVENT');
			}

			$link = RedeventHelperRoute::getEditEventRoute();
			$tip = JText::_('COM_REDEVENT_SUBMIT_EVENT_TIP');

			$output = RHtml::tooltip(
				$tip,
				JText::_('COM_REDEVENT_DELIVER_NEW_EVENT'),
				null,
				$text,
				$link
			);
		}

		return $output;
	}

	/**
	 * Return html code for thumbnails view button
	 *
	 * @param   string  $link    link to the view
	 * @param   array   $params  needed params
	 *
	 * @return string
	 */
	public static function thumbbutton($link, &$params)
	{
		if (!$params->get('show_thumb_icon', 1))
		{
			return '';
		}

		JHTML::_('behavior.tooltip');

		if ($params->get('icons', 1))
		{
			$image = RHelperAsset::load(
				'thumbnail.png',
				null,
				array('alt' => JText::_('COM_REDEVENT_EVENTS_THUMBNAILS_LAYOUT'))
			);
		}
		else
		{
			$image = JText::_('COM_REDEVENT_EVENTS_THUMBNAILS_LAYOUT');
		}

		$output = JHtml::link(
			$link,
			$image,
			array(
				'class' => 'hasTooltip',
				'title' => JText::_('COM_REDEVENT_EVENTS_THUMBNAILS_LAYOUT')
			)
		);

		return $output;
	}

	/**
	 * Return html code for list view button
	 *
	 * @param   string  $link    link to the view
	 * @param   array   $params  needed params
	 *
	 * @return string
	 */
	public static function listbutton($link, &$params)
	{
		JHTML::_('behavior.tooltip');

		if ($params->get('icons'))
		{
			$image = RHelperAsset::load(
				'list.png',
				null,
				array('alt' => JText::_('COM_REDEVENT_EVENTS_LIST_LAYOUT'))
			);
		}
		else
		{
			$image = JText::_('COM_REDEVENT_EVENTS_LIST_LAYOUT');
		}

		$output = JHtml::link(
			$link,
			$image,
			array(
				'class' => 'hasTooltip',
				'title' => JText::_('COM_REDEVENT_EVENTS_LIST_LAYOUT')
			)
		);

		return $output;
	}

	/**
	 * returns html code for edit venue button
	 *
	 * @param   string  $id  venue id/slug
	 *
	 * @return html
	 */
	public static function editVenueButton($id)
	{
		$txt = ($id ? JText::_('COM_REDEVENT_EDIT_VENUE') : JText::_('COM_REDEVENT_ADD_VENUE'));
		$link = JRoute::_(RedeventHelperRoute::getEditVenueRoute($id));

		$image = RHelperAsset::load(
			'calendar_edit.png',
			null,
			array('alt' => $txt)
		);

		return JHTML::link($link, $image, array('class' => "editlinktip hasTooltip", 'title' => $txt));
	}

	/**
	 * returns html code for archive button
	 *
	 * @param   array  $params  needed params
	 *
	 * @return string html
	 */
	public static function archivebutton(&$params)
	{
		if (!$params->get('show_gotoarchive_icon', 1))
		{
			return '';
		}

		RHtml::_('rbootstrap.tooltip');

		if ($params->get('icons', 1))
		{
			$image = RHelperAsset::load(
				'archive_front.png',
				null,
				array('alt' => JText::_('COM_REDEVENT_SHOW_ARCHIVE'))
			);
		}
		else
		{
			$image = JText::_('COM_REDEVENT_SHOW_ARCHIVE');
		}

		$tip = JText::_('COM_REDEVENT_SHOW_ARCHIVE_TIP');
		$title = JText::_('COM_REDEVENT_SHOW_ARCHIVE');

		$link = JRoute::_('index.php?option=com_redevent&view=archive');

		$output = RHtml::tooltip($tip, $title, null, $image, $link);

		return $output;
	}

	/**
	 * returns html code for edit button
	 *
	 * @param   int     $id             id of the item to edit
	 * @param   array   $params         parameters
	 * @param   int     $allowedtoedit  allowed to edit
	 * @param   string  $view           view to go to
	 *
	 * @return string html
	 */
	public static function editbutton($id, &$params, $allowedtoedit, $view)
	{
		if ($allowedtoedit)
		{
			RHtml::_('rbootstrap.tooltip');

			switch ($view)
			{
				case 'editevent':
					if ($params->get('icons', 1))
					{
						$image = RHelperAsset::load(
							'calendar_edit.png',
							null,
							array('alt' => JText::_('COM_REDEVENT_EDIT_EVENT'))
						);
					}
					else
					{
						$image = JText::_('COM_REDEVENT_EDIT_EVENT');
					}

					$tip = JText::_('COM_REDEVENT_EDIT_EVENT_TIP');
					$text = JText::_('COM_REDEVENT_EDIT_EVENT');
					$link = JRoute::_(RedeventHelperRoute::getEditEventRoute($id, JFactory::getApplication()->input->getInt('xref')));
					break;

				case 'editvenue':
					if ($params->get('icons', 1))
					{
						$image = RHelperAsset::load(
							'calendar_edit.png',
							null,
							array('alt' => JText::_('COM_REDEVENT_EDIT_VENUE'))
						);
					}
					else
					{
						$image = JText::_('COM_REDEVENT_EDIT_VENUE');
					}

					$tip = JText::_('COM_REDEVENT_EDIT_VENUE_TIP');
					$text = JText::_('COM_REDEVENT_EDIT_VENUE');
					$link = JRoute::_(RedeventHelperRoute::getEditVenueRoute($id));

					break;
			}

			$output = RHtml::tooltip($tip, $text, null, $image, $link);

			return $output;
		}

		return '';
	}

	/**
	 * Creates the attendees edit button
	 *
	 * @param   int  $id  xref id
	 *
	 * @return string html
	 */
	public static function xrefattendeesbutton($id)
	{
		RHtml::_('rbootstrap.tooltip');

		$image = RHelperAsset::load(
			'attendees.png',
			null,
			array('alt' => JText::_('COM_REDEVENT_EDIT_ATTENDEES'))
		);

		$tip = JText::_('COM_REDEVENT_EDIT_ATTENDEES_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_ATTENDEES');
		$link = RedeventHelperRoute::getManageAttendees($id);

		$output = RHtml::tooltip($tip, $text, null, $image, $link);

		return $output;
	}

	/**
	 * returns html code for edit button
	 *
	 * @param   string  $print_link  link
	 * @param   array   $params      parameters
	 *
	 * @return string html
	 */
	public static function printbutton($print_link, &$params)
	{
		if ($params->get('show_print_icon'))
		{
			RHtml::_('rbootstrap.tooltip');

			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

			if ($params->get('icons', 1))
			{
				$image = JHTML::_('image', 'images/printButton.png', JText::_('COM_REDEVENT_Print'), null, true);
			}
			else
			{
				$image = JText::_('COM_REDEVENT_Print');
			}

			if (JFactory::getApplication()->input->getInt('pop'))
			{
				$output = '<a href="#" onclick="window.print();return false;">' . $image . '</a>';
			}
			else
			{
				$tip = JText::_('COM_REDEVENT_PRINT_TIP');
				$text = JText::_('COM_REDEVENT_Print');

				$link = JHtml::link(
					$print_link, $image, array('onclick' => "window.open(this.href,\'win2\',\'' . $status . '\'); return false;")
				);

				$output = RHtml::tooltip($tip, $text, null, $link);
			}

			return $output;
		}

		return '';
	}

	/**
	 * Creates the email button
	 *
	 * @param   object  $slug    item slug
	 * @param   string  $view    view to display
	 * @param   array   $params  parameters
	 *
	 * @return string html
	 */
	public static function mailbutton($slug, $view, $params)
	{
		if ($params->get('show_email_icon'))
		{
			RHtml::_('rbootstrap.tooltip');

			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'host', 'port'));
			$link = $base . JRoute::_('index.php?option=com_redevent&view=' . $view . '&id=' . $slug, false);
			$url = 'index.php?option=com_mailto&tmpl=component&link=' . base64_encode($link);
			$status = 'width=400,height=300,menubar=yes,resizable=yes';

			if ($params->get('icons', 1))
			{
				$image = JHTML::_('image', 'images/emailButton.png', JText::_('COM_REDEVENT_Email'), null, true);
			}
			else
			{
				$image = JText::_('COM_REDEVENT_Email');
			}

			$overlib = JText::_('COM_REDEVENT_EMAIL_TIP');
			$text = JText::_('COM_REDEVENT_Email');

			$output = '<a href="' . JRoute::_($url) . '" class="editlinktip hasTooltip" onclick="window.open(this.href,\'win2\',\''
				. $status . '\'); return false;" title="' . $text . '::' . $overlib . '">' . $image . '</a>';

			return $output;
		}

		return;
	}

	/**
	 * Creates the map button
	 *
	 * @param   object  $data        data
	 * @param   array   $attributes  attributes
	 *
	 * @return string
	 */
	public static function mapicon($data, $attributes = array())
	{
		// Stop if disabled
		if (!$data->map)
		{
			return '';
		}

		if (isset($attributes['class']))
		{
			$attributes['class'] .= ' venuemap';
		}
		else
		{
			$attributes['class'] = 'venuemap';
		}

		$mapLink = JRoute::_('index.php?option=com_redevent&view=venue&layout=gmap&tmpl=component&id=' . ($data->venueid ?: $data->id));

		$output = RedeventLayoutHelper::render('mapicon', array('link' => $mapLink, 'attributes' => $attributes), null, array('client' => 0));

		return $output;
	}

	/**
	 * Return code for venue map
	 *
	 * @param   object  $data        data, must include 'venueid'
	 * @param   array   $attributes  attributes
	 *
	 * @return string
	 */
	public static function map($data, $attributes = array())
	{
		$document = JFactory::getDocument();
		JHTML::_('behavior.framework');

		$document->addScript('https://maps.googleapis.com/maps/api/js?sensor=false');
		RHelperAsset::load('venuemap.js');
		$document->addScriptDeclaration('
			var basepath = "' . JURI::root() . '";
			window.addEvent(\'domready\', function() {
				mymap.initajax(' . ($data->venueid ?: $data->id) . ', "venue-location");
			});
		'
		);
		JText::script("COM_REDEVENT_GET_DIRECTIONS");

		if (isset($attributes['class']))
		{
			$attributes['class'] .= ' venuemap';
		}
		else
		{
			$attributes['class'] = 'venuemap';
		}

		foreach ($attributes as $k => $v)
		{
			$attributes[$k] = $k . '="' . $v . '"';
		}

		$attributes = implode(' ', $attributes);
		$output = '<div id="venue-location" ' . $attributes . '></div>';

		return $output;
	}

	/**
	 * Creates the map button
	 *
	 * @return string html
	 */
	public static function pinpointicon()
	{
		$params = RedeventHelper::config();

		if (!$key = $params->get('googlemapsApiKey'))
		{
			return '';
		}

		RHelperAsset::load('gmapsoverlay.css');

		$document = JFactory::getDocument();
		$document->addScript('https://maps.googleapis.com/maps/api/js?key=' . $params->get('googlemapsApiKey'));

		$document->addScriptDeclaration('var mymapDefaultaddress = "' . $params->get('pinpoint_defaultaddress', 'usa') . '";');

		RHelperAsset::load('pinpoint.js');

		$output = RedeventLayoutHelper::render('pinpoint', null, null, array('client' => 0));

		return $output;
	}

	/**
	 * returns moreinfo link
	 *
	 * @param   string        $xref_slug  xref slug
	 * @param   string        $text       the content of the link tag
	 * @param   unknown_type  $title      the 'title' for the link
	 *
	 * @return string
	 */
	public static function moreInfoIcon($xref_slug, $text = null, $title = null)
	{
		if (!$text)
		{
			$text = JText::_('COM_REDEVENT_DETAILS_MOREINFO_BUTTON_LABEL');
		}

		if (!$title)
		{
			$title = JText::_('COM_REDEVENT_DETAILS_MOREINFO_BUTTON_LABEL');
		}

		JHTML::_('behavior.modal', 'a.moreinfo');
		$link = JRoute::_(
			RedeventHelperRoute::getMoreInfoRoute($xref_slug, array('tmpl' => 'component'))
		);

		$text = '<a class="moreinfo" title="' . $title
			. '" href="' . $link . '" rel="{handler: \'iframe\', size: {x: 400, y: 500}}">'
			. $text
			. ' </a>';

		return $text;
	}

	/**
	 * Formats date
	 *
	 * @param   string  $date  date to format in a format accepted by strtotime
	 * @param   string  $time  time to format in a format accepted by strtotime
	 *
	 * @return string
	 *
	 * @deprecated
	 */
	public static function formatdate($date, $time)
	{
		return RedeventHelperDate::formatdate($date, $time);
	}

	/**
	 * Formats time
	 *
	 * @param   string  $date  date to format in a format accepted by strtotime
	 * @param   string  $time  time to format in a format accepted by strtotime
	 *
	 * @return string
	 *
	 * @deprecated
	 */
	public static function formattime($date, $time)
	{
		return RedeventHelperDate::formattime($date, $time);
	}

	/**
	 * return formatted event date and time (start and end), or false if open date
	 *
	 * @param   object   $event    event data
	 * @param   boolean  $showend  show end
	 *
	 * @return string
	 *
	 * @deprecated
	 */
	public static function formatEventDateTime($event, $showend = null)
	{
		return RedeventHelperDate::formatEventDateTime($event, $showend);
	}

	/**
	 * returns iso date
	 *
	 * @param   string  $date  date to format in a format accepted by strtotime
	 * @param   string  $time  time to format in a format accepted by strtotime
	 *
	 * @return string
	 *
	 * @deprecated
	 */
	public static function getISODate($date, $time)
	{
		return RedeventHelperDate::getISODate($date, $time);
	}

	/**
	 * Returns an array for ical formatting
	 *
	 * @param   string  $date  date to format in a format accepted by strtotime
	 * @param   string  $time  time to format in a format accepted by strtotime
	 *
	 * @return array
	 *
	 * @deprecated
	 */
	public static function getIcalDateArray($date, $time = null)
	{
		return RedeventHelperDate::getIcalDateArray($date, $time);
	}

	/**
	 * Formats time
	 *
	 * @param   string  $price     price to format
	 * @param   string  $currency  currency code
	 *
	 * @return string
	 */
	public static function formatprice($price, $currency = null)
	{
		$settings = RedeventHelper::config();

		if (!$price)
		{
			return JText::_('COM_REDEVENT_EVENT_PRICE_FREE');
		}

		switch ($settings->get('currency_decimals', 'decimals'))
		{
			case 'decimals':
				// Format price
				$formatprice = number_format(
					$price, 2, $settings->get('currency_decimal_separator', ','), $settings->get('currency_thousand_separator', '.')
				);
				break;

			case 'comma':
				// Format price
				$formatprice = number_format(
					$price, 0, $settings->get('currency_decimal_separator', ','), $settings->get('currency_thousand_separator', '.')
				) . ',-';
				break;

			case 'none':
				// Format price
				$formatprice = number_format(
					$price, 0, $settings->get('currency_decimal_separator', ','), $settings->get('currency_thousand_separator', '.')
				);
				break;
		}

		if ($currency)
		{
			return $currency . ' ' . $formatprice;
		}
		else
		{
			return $formatprice;
		}
	}

	/**
	 * Format prices as string separated by separator
	 *
	 * @param   array   $prices     prices
	 * @param   string  $separator  separator
	 *
	 * @return string|void
	 */
	public static function formatPrices($prices, $separator = ' / ')
	{
		if (!is_array($prices))
		{
			return '';
		}

		if (count($prices) == 1)
		{
			return self::formatprice($prices[0]->price, $prices[0]->currency);
		}

		$res = array();

		foreach ($prices as $p)
		{
			$res[] = self::formatprice($p->price, $p->currency);
		}

		return implode($separator, $res);
	}

	/**
	 * Format prices in ul list
	 *
	 * @param   array  $prices  prices
	 *
	 * @return string|void
	 */
	public static function formatListPrices($prices)
	{
		if (!is_array($prices))
		{
			return '';
		}

		if (count($prices) == 1)
		{
			return self::formatprice($prices[0]->price, $prices[0]->currency);
		}

		$res = array();

		foreach ($prices as $p)
		{
			$res[] = '<li>' . $p->name . ' ' . self::formatprice($p->price, $p->currency) . '</li>';
		}

		return '<ul class="price-list">' . implode("\n", $res) . '</ul>';
	}

	/**
	 * Change images from relative to absolute URLs
	 *
	 * @param   string  $imgHtml  image html tag
	 *
	 * @return string
	 */
	public static function ImgRelAbs($imgHtml)
	{
		$find = ("/ src=\"/");
		$replace = " src=\"" . JURI::root();
		$newtext = preg_replace($find, $replace, $imgHtml);

		return str_ireplace(JURI::root() . JURI::root(), JURI::root(), $newtext);
	}

	/**
	 * return the code for tags display
	 *
	 * @param   string  $field  field to use tag for, allows filtering
	 *
	 * @return html
	 */
	public static function getTagsModalLink($field = '')
	{
		return JHTML::link(
			'index.php?option=com_redevent&view=tags&tmpl=component&field=' . $field, JText::_('COM_REDEVENT_TAGS'), 'class="modal-button"'
		);
	}

	/**
	 * return the code for tags display
	 *
	 * @param   JFormField  $field  field to use tag for, allows filtering
	 *
	 * @return html
	 */
	public static function getTagsEditorInsertModal(JFormField $field)
	{
		RHelperAsset::load('editor-insert-tag.js', 'com_redevent');

		$output = RedeventLayoutHelper::render('redevent.modal.tags', compact('field'));

		return $output;
	}
}
