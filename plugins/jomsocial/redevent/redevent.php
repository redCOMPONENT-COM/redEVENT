<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 *
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

include_once JPATH_BASE . '/components/com_community/libraries/core.php';

/**
 * Class plgCommunityRedevent
 *
 * @since  2.5
 */
class plgCommunityRedevent extends CApplications
{
	var $name 		= "Redevent Application";
	var $_name		= 'redevent';
	var $_path		= '';
	var $_user		= '';
	var $_my		= '';

  function plgCommunityRedevent(& $subject, $config)
  {
    $this->_path	= JPATH_BASE . DS . 'administrator' . DS . 'components' . DS . 'com_redevent';
		$this->_user	= & CFactory::getActiveProfile();
		$this->_my		= & CFactory::getUser();

		parent::__construct($subject, $config);
  }

	/**
	 * Return itemid for Redevent
	 */
  function getItemid()
  {
  	$db =JFactory::getDBO();
  	$Itemid = 0;

  	if ($Itemid < 1)
  	{
  		$db->setQuery("SELECT id FROM #__menu WHERE link LIKE '%index.php?option=com_redevent%' AND published = 1");
  		$Itemid = $db->loadResult();

  		if ($Itemid < 1)
  		{
  			$Itemid = 0;
  		}
  	}

  	return $Itemid;
  }

	function onProfileDisplay()
	{
		// Load language
		JPlugin::loadLanguage( 'plg_js_redevent', JPATH_ADMINISTRATOR );

		// Attach CSS
		$document	=JFactory::getDocument();
		$css		= JURI::base() . 'plugins/community/redevent/style.css';
		$document->addStyleSheet($css);

		$html  		 = "\n".'<!--[if lte IE 6]>'."\n";
		$html 		.= '<link rel="stylesheet" type="text/css" href="'. JURI::base() . 'plugins/community/redevent/styleIE6.css" type="text/css" />'."\n";
		$html 		.= '<![endif]-->'."\n";
		$document->addCustomTag( $html );

		if( !file_exists( $this->_path . DS . 'admin.redevent.php' ) ){
			$el_exist = 0;
			$content = "<div class=\"icon-nopost\"><img src='".JURI::base()."components/com_community/assets/error.gif' alt=\"\" /></div>";
			$content .= "<div class=\"content-nopost\">".JText::_('PLG_JOMSOCIAL_REDEVENT_REDEVENT_NOT_INSTALLED')."</div>";
		}else{
			$user		= & CFactory::getActiveProfile();
			$userName = $user->getDisplayName();
			$userId = $user->id;
			$events	= $this->_getEvents();
			$itemId = $this->getItemid();
			$el_exist = 1;

			$cache =JFactory::getCache('plgCommunityRedevent');
			$cache->setCaching($this->params->get('cache', 1));
			$params = & $this->params;
			$callback = array('plgCommunityRedevent', '_getRedeventHTML');

			$content = $cache->call($callback, $userName, $userId, $el_exist, $events, $params, $itemId);
		}
		return $content;
	}

	function _getRedeventHTML($userName, $userId, $el_exist, $events, $params, $itemId)
	{
		$dateformat = $params->get('dateformat', '%d/%m/%Y');

		ob_start();
		// Test if redevent really exists on this environment.
		if($el_exist)
		{
			if( !$events )
			{
				?>
				<div class="icon-nopost">
		            <img src="<?php echo JURI::base(); ?>plugins/community/redevent/favicon.png" alt="" />
		        </div>
		        <div class="content-nopost">
		            <?php echo $userName; ?> <?php echo JText::_('PLG_JOMSOCIAL_REDEVENT_NO_EVENT_JOINED'); ?>
		        </div>
				<?php
			}
			else
			{
			?>
				<div id="community-redevent-wrap">
				  <?php if ($params->get('showattending', 1)): ?>
				  <div class="ctitle"><?php echo JText::_('PLG_JOMSOCIAL_REDEVENT_Going_to') ?></div>
				    <table cellpadding="2" cellspacing="0" border="0" width="100%">

					<?php foreach( $events['isregistered'] as $event ): ?>
					    <tr>
					        <td width="15">
					            <img src="<?php echo JURI::base(); ?>plugins/community/redevent/favicon.png" alt="" />
					        </td>
					        <td valign="top">
					            <a href="<?php echo JRoute::_('index.php?option=com_redevent&view=details&xref=' . $event->xref . '&id=' . $event->slug . '&Itemid=' . $itemId);?>">
									<?php echo $event->title; ?>
								</a>
								<?php if( !empty( $event->venue ) ): ?>
									<?php echo JText::_('PLG_JOMSOCIAL_REDEVENT_AT'); ?>
									<a href="<?php echo JRoute::_( 'index.php?option=com_redevent&view=venueevents&id=' . $event->venueslug . '&Itemid=' . $itemId);?>"><?php echo $event->venue;?></a>
								<?php endif; ?>
							</td>

					        <td width="150" align="right">
							<?php
								$start		= new JDate($event->dates);
								echo $start->toFormat( $dateformat );
								echo ' - ';
								$end		= new JDate($event->enddates);
								echo $end->toFormat( $dateformat );
							?>
					        </td>
						</tr>
					<?php endforeach; ?>
					</table>
					<?php endif; ?>

          <?php if ($params->get('showmanaging', 1)): ?>
            <div class="ctitle"><?php echo JText::_('PLG_JOMSOCIAL_REDEVENT_Created') ?></div>
            <table cellpadding="2" cellspacing="0" border="0" width="100%">

            <?php foreach( $events['manages'] as $event ): ?>
              <tr>
                <td width="15">
                  <img src="<?php echo JURI::base(); ?>plugins/community/redevent/favicon.png" alt="" />
                </td>
                <td valign="top">
                  <a href="<?php echo JRoute::_('index.php?option=com_redevent&view=details&xref=' . $event->xref . '&id=' . $event->slug . '&Itemid=' . $itemId);?>">
                  <?php echo $event->title; ?>
	                </a>
	               <?php if( !empty( $event->venue ) ): ?>
	                 <?php echo JText::_('PLG_JOMSOCIAL_REDEVENT_AT'); ?>
	                 <a href="<?php echo JRoute::_( 'index.php?option=com_redevent&view=venueevents&id=' . $event->venueslug . '&Itemid=' . $itemId);?>"><?php echo $event->venue;?></a>
	               <?php endif; ?>
	              </td>
	              <td width="150" align="right">
	              <?php
	                $start    = new JDate($event->dates);
	                echo $start->toFormat( $dateformat );
	                echo ' - ';
	                $end    = new JDate($event->enddates);
	                echo $end->toFormat( $dateformat );
	              ?>
	              </td>
	            </tr>
            <?php endforeach; ?>
          </table>
          <?php endif; ?>
				</div>
			<?php
			}
		}

		$content	= ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Returns the list of events and its properties for the specific browsed user
	 *
	 * @access private
	 *
	 * returns	Array	An array of object list
	 **/
	function _getEvents()
	{
		$db		=JFactory::getDBO();
		$limit	= '10';

    // events where user is registered
		$query = 'SELECT a.id, x.dates, x.enddates, x.times, x.endtimes, x.id AS xref, a.title, a.created, '
        . ' l.venue, '
        . ' CASE WHEN CHAR_LENGTH( a.alias ) THEN CONCAT_WS(\':\',a.id,a.alias) ELSE a.id END AS slug, '
        . ' CASE WHEN CHAR_LENGTH( l.alias ) THEN CONCAT_WS(\':\',l.id,l.alias) ELSE l.id END AS venueslug '
        . ' FROM ' . $db->nameQuote( '#__redevent_register' ) . ' AS r '
        . ' INNER JOIN #__redevent_event_venue_xref AS x ON r.xref = x.id'
        . ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
        . ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' WHERE r.uid=' . $db->Quote( $this->_user->id ) . ' '
        . ' AND x.published=' . $db->Quote( '1' ) . ' '
        . ' ORDER BY x.dates ASC '
        . ' LIMIT 0,' . $limit;
        ;

		// events where user is registered
//		$query	= 'SELECT *, ' . $db->nameQuote('v.venue') . ','
//				. 'CASE WHEN CHAR_LENGTH( b.alias ) THEN CONCAT_WS(\':\',b.id,b.alias) ELSE b.id END AS slug, '
//        . 'CASE WHEN CHAR_LENGTH( v.alias ) THEN CONCAT_WS(\':\',v.id,v.alias) ELSE v.id END AS venueslug '
//				. 'FROM ' . $db->nameQuote( '#__redevent_register' ) . ' AS a '
//				. 'INNER JOIN ' . $db->nameQuote( '#__redevent_events' ) . ' AS b ON a.event=b.id '
//				. 'LEFT JOIN ' . $db->nameQuote( '#__redevent_venues' ) . ' AS v ON b.locid = v.id '
//				. 'WHERE a.uid=' . $db->Quote( $this->_user->id ) . ' '
//				. 'AND b.published=' . $db->Quote( '1' ) . ' '
//        . 'ORDER BY b.dates ASC '
//				. 'LIMIT 0,' . $limit;

		$db->setQuery( $query );

		$registered = $db->loadObjectList();
    if ($db->getErrorNum()) {
      RedeventError::raiseWarning('0', $db->getErrorMsg());
    }

    // events he created
    $query = 'SELECT a.id, x.dates, x.enddates, x.times, x.endtimes, x.id AS xref, a.title, a.created,'
        . ' l.venue, '
        . ' CASE WHEN CHAR_LENGTH( a.alias ) THEN CONCAT_WS(\':\',a.id,a.alias) ELSE a.id END AS slug, '
        . ' CASE WHEN CHAR_LENGTH( l.alias ) THEN CONCAT_WS(\':\',l.id,l.alias) ELSE l.id END AS venueslug '
        . ' FROM #__redevent_event_venue_xref AS x'
        . ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
        . ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
        . ' WHERE a.created_by=' . $db->Quote( $this->_user->id ) . ' '
        . ' AND x.published=' . $db->Quote( '1' ) . ' '
        . ' ORDER BY x.dates ASC '
        . ' LIMIT 0,' . $limit;
        ;

    $db->setQuery( $query );

    $managed = $db->loadObjectList();
    if ($db->getErrorNum()) {
      require_once JPATH_ADMINISTRATOR . DS . 'components' . DS .  'com_redevent' . 'classes' . DS .  'error.class.php';
    	RedeventError::raiseWarning('0', $db->getErrorMsg());
    }
    // TODO: multiple cats

		return array('isregistered' => $registered, 'manages' => $managed);
	}
}
