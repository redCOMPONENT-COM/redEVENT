<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Holds the logic for all output related things
 *
 * @package Joomla
 * @subpackage redEVENT
 */
class REOutput {

	/**
	* Writes footer. Official copyright! Do not remove!
	* @since 0.9
	*/
	function footer( )
	{
	}

	/**
	* Writes Event submission button
	*
	* @author Christoph Lukes
	* @since 0.9
	*
	* @param int $dellink Access of user
	* @param array $params needed params
	* @param string $view the view the user will redirected to
	**/
	function submitbutton( $dellink, &$params )
	{
		if ($dellink == 1) {

			JHTML::_('behavior.tooltip');

			if ( $params->get('icons', 1) ) {
				$image = JHTML::_('image', 'components/com_redevent/assets/images/submitevent.png', JText::_('COM_REDEVENT_DELIVER_NEW_EVENT' ));
			} else {
				$image = JText::_('COM_REDEVENT_DELIVER_NEW_EVENT' );
			}

			$link 		= RedeventHelperRoute::getEditEventRoute();
			$overlib 	= JText::_('COM_REDEVENT_SUBMIT_EVENT_TIP' );
			$output		= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.JText::_('COM_REDEVENT_DELIVER_NEW_EVENT' ).'::'.$overlib.'">'.$image.'</a>';

			return $output;
		}

		return;
	}

	/**
	* Writes Event submission button
	*
	* @author Christoph Lukes
	* @since 0.9
	*
	* @param int $dellink Access of user
	* @param array $params needed params
	* @param string $view the view the user will redirected to
	**/
	function thumbbutton( $link, &$params )
	{
    if (!$params->get( 'show_thumb_icon', 1)) {
      return '';
    }

		JHTML::_('behavior.tooltip');

		if ( $params->get('icons', 1) ) {
			$image = JHTML::_('image', 'components/com_redevent/assets/images/thumbnail.png', JText::_('COM_REDEVENT_EVENTS_THUMBNAILS_LAYOUT' ));
		} else {
			$image = JText::_('COM_REDEVENT_EVENTS_THUMBNAILS_LAYOUT' );
		}
		$output		= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.JText::_('COM_REDEVENT_EVENTS_THUMBNAILS_LAYOUT' ).'::">'.$image.'</a>';

		return $output;
	}

	/**
	* Writes Event submission button
	*
	* @author Christoph Lukes
	* @since 0.9
	*
	* @param int $dellink Access of user
	* @param array $params needed params
	* @param string $view the view the user will redirected to
	**/
	function listbutton( $link, &$params )
	{
		JHTML::_('behavior.tooltip');

		if ( $params->get('icons') ) {
			$image = JHTML::_('image', 'components/com_redevent/assets/images/list.png', JText::_('COM_REDEVENT_EVENTS_LIST_LAYOUT' ));
		} else {
			$image = JText::_('COM_REDEVENT_EVENTS_LIST_LAYOUT' );
		}
		$output		= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.JText::_('COM_REDEVENT_EVENTS_LIST_LAYOUT' ).'::">'.$image.'</a>';

		return $output;
	}

	/**
	 * returns html code for edit venue button
	 *
	 * @param $id venue id/slug
	 * @return html
	 */
	function editVenueButton($id)
	{
		$txt   =  ($id ? JText::_('COM_REDEVENT_EDIT_VENUE' ) : JText::_('COM_REDEVENT_ADD_VENUE' ));
		$link  = JRoute::_(RedeventHelperRoute::getEditVenueRoute($id));
		$image = JHTML::image('components/com_redevent/assets/images/calendar_edit.png', $txt);
		return JHTML::link($link, $image, array( 'class' => "editlinktip hasTip", 'title' => $txt.'::'));
	}

	/**
	* Writes Archivebutton
	*
	* @author Christoph Lukes
	* @since 0.9
	*
	* @param array $params needed params
	* @return string html
	*/
	function archivebutton( &$params )
	{
    if (!$params->get( 'show_gotoarchive_icon', 1)) {
      return '';
    }

		JHTML::_('behavior.tooltip');

		if ( $params->get('icons', 1) ) {
			$image = JHTML::_('image', 'components/com_redevent/assets/images/archive_front.png', JText::_('COM_REDEVENT_SHOW_ARCHIVE' ));
		} else {
			$image = JText::_('COM_REDEVENT_SHOW_ARCHIVE' );
		}
		$overlib 	= JText::_('COM_REDEVENT_SHOW_ARCHIVE_TIP' );
		$title 		= JText::_('COM_REDEVENT_SHOW_ARCHIVE' );

		$link		= JRoute::_('index.php?option=com_redevent&view=archive');

		$output = '<a href="'.$link.'" class="editlinktip hasTip" title="'.$title.'::'.$overlib.'">'.$image.'</a>';

		return $output;
	}

	/**
	 * display a button for current events
	 *
	 * @param array $params
	 * @param string $link
	 * @return html
	 */
	public static function currentbutton( &$params, $link)
	{
		return '';
	}

	/**
	 * Creates the edit button
	 *
	 * @param int $Itemid
	 * @param int $id
	 * @param array $params
	 * @param int $allowedtoedit
	 * @param string $view
	 * @since 0.9
	 */
	function editbutton( $Itemid, $id, &$params, $allowedtoedit, $view)
	{

		if ( $allowedtoedit )
		{

			JHTML::_('behavior.tooltip');

			switch ($view)
			{
				case 'editevent':
					if ( $params->get('icons', 1) ) {
						$image = JHTML::_('image', 'components/com_redevent/assets/images/calendar_edit.png', JText::_('COM_REDEVENT_EDIT_EVENT' ));
					} else {
						$image = JText::_('COM_REDEVENT_EDIT_EVENT' );
					}
					$overlib = JText::_('COM_REDEVENT_EDIT_EVENT_TIP' );
					$text = JText::_('COM_REDEVENT_EDIT_EVENT' );
					$link  = JRoute::_(RedeventHelperRoute::getEditEventRoute($id, JRequest::getInt('xref')));
					break;

				case 'editvenue':
					if ( $params->get('icons', 1) ) {
						$image = JHTML::_('image', 'components/com_redevent/assets/images/calendar_edit.png', JText::_('COM_REDEVENT_EDIT_EVENT' ));
					} else {
						$image = JText::_('COM_REDEVENT_EDIT_VENUE' );
					}
					$overlib = JText::_('COM_REDEVENT_EDIT_VENUE_TIP' );
					$text = JText::_('COM_REDEVENT_EDIT_VENUE' );
					$link  = JRoute::_(RedeventHelperRoute::getEditVenueRoute($id));
					break;
			}

			$output	= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

			return $output;
		}

		return;
	}

	/**
	 * Creates the attendees edit button
	 *
	 * @param int xref id
	 * @since 2.0
	 */
	function xrefattendeesbutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image', 'components/com_redevent/assets/images/attendees.png', JText::_('COM_REDEVENT_EDIT_ATTENDEES' ));

		$overlib = JText::_('COM_REDEVENT_EDIT_ATTENDEES_TIP' );
		$text = JText::_('COM_REDEVENT_EDIT_ATTENDEES' );
//		$link 	= 'index.php?option=com_redevent&view=details&layout=manageattendees&xref='. $id;
		$link = RedeventHelperRoute::getManageAttendees($id);
		$output	= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

		return $output;
	}

	/**
	 * Creates the print button
	 *
	 * @param string $print_link
	 * @param array $params
	 * @since 0.9
	 */
	function printbutton( $print_link, &$params )
	{
		if ($params->get( 'show_print_icon' )) {

			JHTML::_('behavior.tooltip');

			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

			// checks template image directory for image, if non found default are loaded
			if ( $params->get( 'icons', 1 ) ) {
				$image = JHTML::_('image', 'images/printButton.png', JText::_('COM_REDEVENT_Print'), null, true);
			} else {
				$image = JText::_('COM_REDEVENT_Print' );
			}

			if (JRequest::getInt('pop')) {
				//button in popup
				$output = '<a href="#" onclick="window.print();return false;">'.$image.'</a>';
			} else {
				//button in view
				$overlib = JText::_('COM_REDEVENT_PRINT_TIP' );
				$text = JText::_('COM_REDEVENT_Print' );

				$output	= '<a href="'. JRoute::_($print_link) .'" class="editlinktip hasTip" onclick="window.open(this.href,\'win2\',\''.$status.'\'); return false;" title="'.$text.'::'.$overlib.'">'.$image.'</a>';
			}

			return $output;
		}
		return;
	}

	/**
	 * Creates the email button
	 *
	 * @param object $slug
	 * @param array $params
	 * @since 0.9
	 */
	function mailbutton($slug, $view, $params)
	{
		if ($params->get('show_email_icon')) {

			JHTML::_('behavior.tooltip');
			$uri    =& JURI::getInstance();
			$base  	= $uri->toString( array('scheme', 'host', 'port'));
			$link 	= $base.JRoute::_( 'index.php?option=com_redevent&view='.$view.'&id='.$slug, false );
			$url	= 'index.php?option=com_mailto&tmpl=component&link='.base64_encode( $link );
			$status = 'width=400,height=300,menubar=yes,resizable=yes';

			if ($params->get('icons', 1)) 	{
				$image = JHTML::_('image', 'images/emailButton.png', JText::_('COM_REDEVENT_Email' ), null, true);
			} else {
				$image = JText::_('COM_REDEVENT_Email' );
			}

			$overlib = JText::_('COM_REDEVENT_EMAIL_TIP' );
			$text = JText::_('COM_REDEVENT_Email' );

			$output	= '<a href="'. JRoute::_($url) .'" class="editlinktip hasTip" onclick="window.open(this.href,\'win2\',\''.$status.'\'); return false;" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

			return $output;
		}
		return;
	}

	/**
	 * Creates the map button
	 *
	 * @param obj $data
	 * @param obj $settings
	 *
	 * @since 0.9
	 */
	function mapicon($data, $attributes = array())
	{
		$settings = & redEVENTHelper::config();

		//Link to map
		$mapimage = JHTML::image(JURI::root().'components/com_redevent/assets/images/mapsicon.png', JText::_('COM_REDEVENT_MAP' ) );

		//set var
		$output 	= null;

		//stop if disabled
		if (!$data->map) {
			return $output;
		}

		$data->country = JString::strtoupper($data->country);

		if (isset($attributes['class'])) {
			$attributes['class'] .= ' venuemap';
		}
		else {
			$attributes['class'] = 'venuemap';
		}
		$attributes['handler'] = 'iframe';

		JHTML::_('behavior.modal', 'a.venuemap');

		foreach ($attributes as $k => $v) {
			$attributes[$k] = $k.'="'.$v.'"';
		}
		$attributes = implode(' ', $attributes);
		$output = '<a title="'.JText::_('COM_REDEVENT_MAP' ).'" rel="{handler:\'iframe\'}" href="'.JRoute::_('index.php?option=com_redevent&view=venue&layout=gmap&tmpl=component&id='.$data->venueid).'"'.$attributes.'>'.$mapimage.'</a>';

		return $output;
	}

	function map($data, $attributes = array())
	{
		$output = '';
		$document 	= & JFactory::getDocument();
		JHTML::_('behavior.mootools');

		$document->addScript('http://maps.google.com/maps/api/js?sensor=false');
		$document->addScript(JURI::root().'/components/com_redevent/assets/js/venuemap.js');
		$document->addScriptDeclaration('
			var basepath = "'.JURI::root().'";
			window.addEvent(\'domready\', function() {
				mymap.initajax('.$data->venueid.', "venue-location");
			});
		');
		JText::script("COM_REDEVENT_GET_DIRECTIONS");
		if (isset($attributes['class'])) {
			$attributes['class'] .= ' venuemap';
		}
		else {
			$attributes['class'] = 'venuemap';
		}
		foreach ($attributes as $k => $v) {
			$attributes[$k] = $k.'="'.$v.'"';
		}
		$attributes = implode(' ', $attributes);
		$output		= '<div id="venue-location" '.$attributes.'></div>';
		return $output;
	}

	/**
	 * Creates the map button
	 *
	 * @param obj $data
	 * @param obj $settings
	 *
	 * @since 0.9
	 */
	function pinpointicon($data, $attributes = array())
	{
		JHTML::_('behavior.framework');
		$params = JComponentHelper::getParams('com_redevent');
		$document 	= & JFactory::getDocument();
		$document->addScript('http://maps.google.com/maps/api/js?sensor=false');
		FOFTemplateUtils::addJS('media://com_redevent/js/pinpoint.js');
		JText::script("COM_REDEVENT_APPLY");
		JText::script("COM_REDEVENT_CLOSE");
		$document->addScriptDeclaration('mymap.defaultaddress = "'.$params->get('pinpoint_defaultaddress', 'usa').'";');
		FOFTemplateUtils::addCSS('media://com_redevent/js/css/gmapsoverlay.css');

		//Link to map
		$mapimage = JHTML::image(JURI::root().'components/com_redevent/assets/images/marker.png', JText::_( 'COM_REDEVENT_PINPOINTLOCATION_ALT' ), array('class' => 'pinpoint'));

		$data->country = JString::strtoupper($data->country);

		if (isset($attributes['class'])) {
			$attributes['class'] .= ' venuemap';
		}
		else {
			$attributes['class'] = 'venuemap';
		}

		foreach ($attributes as $k => $v) {
			$attributes[$k] = $k.'="'.$v.'"';
		}
		$attributes = implode(' ', $attributes);
		$output = '<span title="'.JText::_('COM_REDEVENT_MAP' ).'" '.$attributes.'>'.$mapimage.'</span>';

		return $output;
	}

  /**
   * returns moreinfo link
   *
   * @param string $text the content of the link tag
   * @param unknown_type $title the 'title' for the link
   * @return string
   */
  public static function moreInfoIcon($xref_slug, $text = null, $title = null)
  {
  	if (!$text) {
  		$text = JText::_('COM_REDEVENT_DETAILS_MOREINFO_BUTTON_LABEL');
  	}
  	if (!$title) {
  		$title = JText::_('COM_REDEVENT_DETAILS_MOREINFO_BUTTON_LABEL');
  	}
		JHTML::_('behavior.modal', 'a.moreinfo');
		$link = JRoute::_(RedeventHelperRoute::getMoreInfoRoute($xref_slug,
		                                                        array('tmpl' =>'component')));
		$text = '<a class="moreinfo" title="'.$title
		      .  '" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 400, y: 500}}">'
		      . $text
		      . ' </a>'
		      ;
		return $text;
  }

	/**
	 * Creates the flyer
	 *
	 * @param obj $data
	 * @param obj $settings
	 * @param array $image
	 * @param string $type
	 *
	 * @since 0.9
	 */
	function flyer( $data, $image, $type = 'venue' )
	{
		$settings = & redEVENTHelper::config();

		//define the environment based on the type
		if ($type == 'event') {
			$folder		= 'events';
			$imagefile	= $data->datimage;
			$info		= $data->title;
		} else {
			$folder 	= 'venues';
			$imagefile	= $data->locimage;
			$info		= $data->venue;
		}

		//do we have an image?
		if (empty($imagefile)) {

			//nothing to do
			return;

		} else {

			jimport('joomla.filesystem.file');

			//does a thumbnail exist?
			if (JFile::exists(JPATH_SITE.DS.'images'.DS.'redevent'.DS.$folder.DS.'small'.DS.$imagefile)) {

				if ($settings->get('lightbox') == 0) {

					$url		= '#';
					$attributes	= 'class="modal" onclick="window.open(\''.JURI::root().'/'.$image['original'].'\',\'Popup\',\'width='.$image['width'].',height='.$image['height'].',location=no,menubar=no,scrollbars=no,status=no,toolbar=no,resizable=no\')"';

				} else {

					JHTML::_('behavior.modal');

					$url		= JURI::root().'/'.$image['original'];
					$attributes	= 'class="modal" title="'.$info.'"';

				}

				$icon	= '<img src="'.JURI::root().'/'.$image['thumb'].'" width="'.$image['thumbwidth'].'" height="'.$image['thumbheight'].'" alt="'.$info.'" title="'.JText::_('COM_REDEVENT_CLICK_TO_ENLARGE' ).'" />';
				$output	= '<a href="'.$url.'" '.$attributes.'>'.$icon.'</a>';

			//No thumbnail? Then take the in the settings specified values for the original
			} else {

				$output	= '<img class="modal" src="'.JURI::root().'/'.$image['original'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.$info.'" />';

			}
		}

		return $output;
	}

	/**
	 * Creates the country flag
	 *
	 * @param string $country
	 *
	 * @since 0.9
	 */
	function getFlag($country)
	{
        $country = JString::strtolower($country);

        jimport('joomla.filesystem.file');

        if (JFile::exists(JPATH_COMPONENT_SITE.DS.'assets'.DS.'images'.DS.'flags'.DS.$country.'.gif')) {
        	$countryimg = '<img src="'.JURI::base(true).'/components/com_redevent/assets/images/flags/'.$country.'.gif" alt="'.JText::_('COM_REDEVENT_COUNTRY' ).': '.$country.'" width="16" height="11" />';

        	return $countryimg;
        }

        return null;
	}

  /**
   * Creates the country flag
   *
   * @param string $country
   *
   * @since 0.9
   */
  function getFlagUrl($country)
  {
        $country = JString::strtolower($country);

        jimport('joomla.filesystem.file');

        if (JFile::exists(JPATH_COMPONENT_SITE.DS.'assets'.DS.'images'.DS.'flags'.DS.$country.'.gif')) {
          return JURI::base(true).'/components/com_redevent/assets/images/flags/'.$country.'.gif';
        }

        return null;
  }

	/**
	 * Formats date
	 *
	 * @param string $date
	 * @param string $time
	 *
	 * @return string $formatdate
	 *
	 * @since 0.9
	 */
	function formatdate($date, $time)
	{
		$settings = & redEVENTHelper::config();

		if(!redEVENTHelper::isValidDate($date)) {
			return JText::_('COM_REDEVENT_OPEN_DATE');
		}

		if(!$time) {
			$time = '00:00:00';
		}

		//Format date
		$formatdate = strftime( $settings->get('formatdate', '%d.%m.%Y'), strtotime( $date.' '.$time ));

		return $formatdate;
	}

	/**
	 * Formats time
	 *
	 * @param string $date
	 * @param string $time
	 *
	 * @return string $formattime
	 *
	 * @since 0.9
	 */
	public static function formattime($date, $time)
	{
		$settings = & redEVENTHelper::config();

		if(!$time) {
			return;
		}

		//Format time
		$formattime = strftime( $settings->get('formattime', '%H:%M'), strtotime( $date.' '.$time ));
		return $formattime;
	}

	/**
	 * return formatted event date and time (start and end), or false if open date
	 *
	 * @param object $event
	 * @return string or false for open date
	 */
	function formatEventDateTime($event, $showend = true)
	{
		if (!redEVENTHelper::isValidDate($event->dates)) { // open dates
			$date = '<span class="event-date open-date">'.JText::_('COM_REDEVENT_OPEN_DATE').'</span>';
			return $date;
		}
		$settings = & redEVENTHelper::config();

		// is this a full day(s) event ?
		$allday = '00:00:00' == $event->times && '00:00:00' == $event->endtimes;

		$date = '<span class="event-date">';
		$date .= '<span class="event-start">';
		$date .= '<span class="event-day">'.self::formatdate($event->dates, $event->times).'</span>';
		if (!$allday && $settings->get('lists_show_time', 0) == 1) {
			$date .= ' <span class="event-time">'.self::formattime($event->dates, $event->times).'</span>';
		}
		$date .= '</span>';

		if ($allday)
		{
			if ($showend && redEVENTHelper::isValidDate($event->enddates))
			{
				if ( strtotime($event->enddates. ' -1 day') != strtotime($event->dates)
				    && strtotime($event->enddates) != strtotime($event->dates) ) // all day is written as midnight to midnight, so remove last day
				{
					$date .= ' <span class="event-end"><span class="event-day">'.self::formatdate(strftime('%Y-%m-%d', strtotime($event->enddates. ' -1 day')), $event->endtimes).'</span></span>';
				}
			}
		}
		else if ($showend)
		{
			if (redEVENTHelper::isValidDate($event->enddates) && strtotime($event->enddates) != strtotime($event->dates))
			{
				$date .= ' <span class="event-end"><span class="event-day">'.self::formatdate($event->enddates, $event->endtimes).'</span>';
				if ($settings->get('lists_show_time', 0) == 1) {
					$date .= ' <span class="event-time">'.self::formattime($event->dates, $event->endtimes).'</span>';
				}
				$date .= '</span>';
			}
			else if ($settings->get('lists_show_time', 0) == 1)
			{
				$date .= ' <span class="event-time">'.self::formattime($event->dates, $event->endtimes).'</span>';
			}
		}
		$date .= '</span>';

		return $date;
	}

	/**
	 * returns iso date
	 *
	 * @param string $date
	 * @param string $time
	 * @return string
	 */
	function getISODate($date, $time)
	{
		$txt = '';
		if ($date && strtotime($date)) {
			$txt = $date;
		}
		else {
			return false;
		}
		if ($time) {
			$txt .= 'T'.$time;
		}
		return $txt;
	}

	/**
	 * Returns an array for ical formatting
	 * @param string date
	 * @param string time
	 * @return array
	 */
	function getIcalDateArray($date, $time = null)
	{
		if ($time) {
			$sec = strtotime($date. ' ' .$time);
		}
		else {
			$sec = strtotime($date);
		}
		if (!$sec) {
			return false;
		}

		//Format date
		$parsed = strftime('%Y-%m-%d %H:%M:%S', $sec);

		$date = array( 'year'  => (int) substr($parsed, 0, 4),
		               'month' => (int) substr($parsed, 5, 2),
		               'day'   => (int) substr($parsed, 8, 2) );

		//Format time
		if (substr($parsed, 11, 8) != '00:00:00')
		{
			$date['hour'] = substr($parsed, 11, 2);
			$date['min'] = substr($parsed, 14, 2);
			$date['sec'] = substr($parsed, 17, 2);
		}
		return $date;
	}

	/**
	 * Formats time
	 *
	 * @param string $date
	 * @param string $time
	 *
	 * @return string $formattime
	 *
	 * @since 0.9
	 */
	function formatprice($price, $currency = null)
	{
		$settings = & redEVENTHelper::config();

		if(!$price) {
			return JText::_('COM_REDEVENT_EVENT_PRICE_FREE');
		}

		switch ($settings->get('currency_decimals', 'decimals')) {
			case 'decimals':
				//Format price
				$formatprice = number_format($price, 2, $settings->get('currency_decimal_separator', ','), $settings->get('currency_thousand_separator', '.'));
				break;
			case 'comma':
				//Format price
				$formatprice = number_format($price, 0, $settings->get('currency_decimal_separator', ','), $settings->get('currency_thousand_separator', '.')).',-';
				break;
			case 'none':
				//Format price
				$formatprice = number_format($price, 0, $settings->get('currency_decimal_separator', ','), $settings->get('currency_thousand_separator', '.'));
				break;
		}
		if ($currency) {
			return $currency. ' ' .$formatprice;
		}
		else {
			return $formatprice;
		}
	}

	function formatPrices($prices)
	{
		if (!is_array($prices)) {
			return;
		}

		if (count($prices) == 1) {
			return self::formatprice($prices[0]->price, $prices[0]->currency);
		}
		$res = array();
		foreach ($prices as $p)
		{
			$res[] = self::formatprice($p->price, $p->currency);
		}
		return implode(' / ', $res);
	}

	function formatListPrices($prices)
	{
		if (!is_array($prices)) {
			return;
		}

		if (count($prices) == 1) {
			return self::formatprice($prices[0]->price, $prices[0]->currency);
		}
		$res = array();
		foreach ($prices as $p)
		{
			$res[] = $p->name.' '.self::formatprice($p->price, $p->currency);
		}
		return implode('<br/>', $res);
	}

	/**
	  * Change images from relative to absolute URLs
	  */
	public function ImgRelAbs($text) {
		$find = ("/ src=\"/");
		$replace = " src=\"".JURI::root();
		$newtext = preg_replace($find, $replace, $text);
		return str_ireplace(JURI::root().JURI::root(), JURI::root(), $newtext);
	}
}
