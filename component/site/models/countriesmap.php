<?php
/**
 * @version 1.1 $Id: view.html.php 407 2007-09-21 16:03:39Z schlu $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component Venuesmap Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelCountriesmap extends JModel
{
	/**
	 * Venues data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to get the Venues
	 *
	 * @access public
	 * @return array
	 */
	function &getData( )
	{
		global $mainframe;

		$menu		=& JSite::getMenu();
		$item    	= $menu->getActive();
		$params		=& $menu->getParams($item->id);

		$elsettings 	=  & redEVENTHelper::config();

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			
      // Get a reference to the global cache object.
      $cache = & JFactory::getCache('redevent');
      $cache->setCaching( 0 );
      
			$this->_data = $cache->call( array( 'RedeventModelCountriesmap', '_getResultList' ), $query );
      
			$countrycoords = $this->getCountrycoordArray();
			
			$k = 0;
			for($i = 0; $i <  count($this->_data); $i++)
			{
				$country =& $this->_data[$i];
								
				$country->flag = ELOutput::getFlag( $country->iso2 );
				$country->flagurl = ELOutput::getFlagUrl( $country->iso2 );
				$country->latitude = $countrycoords[$country->iso2][0];
        $country->longitude = $countrycoords[$country->iso2][1];
				
				//create target link
				$country->targetlink = JRoute::_(JURI::base().'index.php?option=com_redevent&view=countryevents&filter_country='.$country->iso2);
		
				$k = 1 - $k;
			}

		}
		
		return $this->_data;
	}
	
	function _getResultList($query)
	{
		$db = & JFactory::getDBO();
		
		$db->setQuery($query);

		return ($db->loadObjectList());
	}

	/**
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	function _buildQuery()
	{
		//check archive task
		$task 	= JRequest::getVar('task', '', '', 'string');
		if($task == 'archive') {
			$eventstate = ' AND x.published = -1';
		} else {
			$eventstate = ' AND x.published = 1';
		}
		
		//get categories
		$query = 'SELECT c.*, COUNT( x.id ) AS assignedevents,'
				. ' CONCAT_WS(\':\', c.id, c.iso2) as slug'
				. ' FROM #__redevent_countries as c'
				. ' INNER JOIN #__redevent_venues as v ON v.country = c.iso2'
				. ' INNER JOIN #__redevent_event_venue_xref AS x ON x.venueid = v.id'
				. ' WHERE v.published = 1'
        . '   AND v.latitude <> 0 AND v.longitude <> 0 '
				. $eventstate
				. ' GROUP BY c.id'
        . ' ORDER BY c.name'
				;

		return $query;
	}
	
	function getCountrycoordArray()
	{
		$countrycoord = array();
		$countrycoord['AD']= array(42.5  , 1.5);
		$countrycoord['AE']= array(24  , 54);
		$countrycoord['AF']= array(33  , 65);
		$countrycoord['AG']= array(17.05 , -61.8);
		$countrycoord['AI']= array(18.25 , -63.17);
		$countrycoord['AL']= array(41  , 20);
		$countrycoord['AM']= array(40  , 45);
		$countrycoord['AN']= array(12.25 , -68.75);
		$countrycoord['AO']= array(-12.5 , 18.5);
		$countrycoord['AP']= array(35  , 105);
		$countrycoord['AQ']= array(-90 , 0);
		$countrycoord['AR']= array(-34 , -64);
		$countrycoord['AS']= array(-14.33  , -170);
		$countrycoord['AT']= array(47.33 , 13.33);
		$countrycoord['AU']= array(-27 , 133);
		$countrycoord['AW']= array(12.5  , -69.97);
		$countrycoord['AZ']= array(40.5  , 47.5);
		$countrycoord['BA']= array(44  , 18);
		$countrycoord['BB']= array(13.17 , -59.53);
		$countrycoord['BD']= array(24  , 90);
		$countrycoord['BE']= array(50.83 , 4);
		$countrycoord['BF']= array(13  , -2);
		$countrycoord['BG']= array(43  , 25);
		$countrycoord['BH']= array(26  , 50.55);
		$countrycoord['BI']= array(-3.5  , 30);
		$countrycoord['BJ']= array(9.5 , 2.25);
		$countrycoord['BM']= array(32.33 , -64.75);
		$countrycoord['BN']= array(4.5 , 114.67);
		$countrycoord['BO']= array(-17 , -65);
		$countrycoord['BR']= array(-10 , -55);
		$countrycoord['BS']= array(24.25 , -76);
		$countrycoord['BT']= array(27.5  , 90.5);
		$countrycoord['BV']= array(-54.43  , 3.4);
		$countrycoord['BW']= array(-22 , 24);
		$countrycoord['BY']= array(53  , 28);
		$countrycoord['BZ']= array(17.25 , -88.75);
		$countrycoord['CA']= array(60  , -95);
		$countrycoord['CC']= array(-12.5 , 96.83);
		$countrycoord['CD']= array(0 , 25);
		$countrycoord['CF']= array(7 , 21);
		$countrycoord['CG']= array(-1  , 15);
		$countrycoord['CH']= array(47  , 8);
		$countrycoord['CI']= array(8 , -5);
		$countrycoord['CK']= array(-21.23  , -159.77);
		$countrycoord['CL']= array(-30 , -71);
		$countrycoord['CM']= array(6 , 12);
		$countrycoord['CN']= array(35  , 105);
		$countrycoord['CO']= array(4 , -72);
		$countrycoord['CR']= array(10  , -84);
		$countrycoord['CU']= array(21.5  , -80);
		$countrycoord['CV']= array(16  , -24);
		$countrycoord['CX']= array(-10.5 , 105.67);
		$countrycoord['CY']= array(35  , 33);
		$countrycoord['CZ']= array(49.75 , 15.5);
		$countrycoord['DE']= array(51  , 9);
		$countrycoord['DJ']= array(11.5  , 43);
		$countrycoord['DK']= array(56  , 10);
		$countrycoord['DM']= array(15.42 , -61.33);
		$countrycoord['DO']= array(19  , -70.67);
		$countrycoord['DZ']= array(28  , 3);
		$countrycoord['EC']= array(-2  , -77.5);
		$countrycoord['EE']= array(59  , 26);
		$countrycoord['EG']= array(27  , 30);
		$countrycoord['EH']= array(24.5  , -13);
		$countrycoord['ER']= array(15  , 39);
		$countrycoord['ES']= array(40  , -4);
		$countrycoord['ET']= array(8 , 38);
		$countrycoord['EU']= array(47  , 8);
		$countrycoord['FI']= array(64  , 26);
		$countrycoord['FJ']= array(-18 , 175);
		$countrycoord['FK']= array(-51.75  , -59);
		$countrycoord['FM']= array(6.92  , 158.25);
		$countrycoord['FO']= array(62  , -7);
		$countrycoord['FR']= array(46  , 2);
		$countrycoord['GA']= array(-1  , 11.75);
		$countrycoord['GB']= array(54  , -2);
		$countrycoord['GD']= array(12.12 , -61.67);
		$countrycoord['GE']= array(42  , 43.5);
		$countrycoord['GF']= array(4 , -53);
		$countrycoord['GH']= array(8 , -2);
		$countrycoord['GI']= array(36.18 , -5.37);
		$countrycoord['GL']= array(72  , -40);
		$countrycoord['GM']= array(13.47 , -16.57);
		$countrycoord['GN']= array(11  , -10);
		$countrycoord['GP']= array(16.25 , -61.58);
		$countrycoord['GQ']= array(2 , 10);
		$countrycoord['GR']= array(39  , 22);
		$countrycoord['GS']= array(-54.5 , -37);
		$countrycoord['GT']= array(15.5  , -90.25);
		$countrycoord['GU']= array(13.47 , 144.78);
		$countrycoord['GW']= array(12  , -15);
		$countrycoord['GY']= array(5 , -59);
		$countrycoord['HK']= array(22.25 , 114.17);
		$countrycoord['HM']= array(-53.1 , 72.52);
		$countrycoord['HN']= array(15  , -86.5);
		$countrycoord['HR']= array(45.17 , 15.5);
		$countrycoord['HT']= array(19  , -72.42);
		$countrycoord['HU']= array(47  , 20);
		$countrycoord['ID']= array(-5  , 120);
		$countrycoord['IE']= array(53  , -8);
		$countrycoord['IL']= array(31.5  , 34.75);
		$countrycoord['IN']= array(20  , 77);
		$countrycoord['IO']= array(-6  , 71.5);
		$countrycoord['IQ']= array(33  , 44);
		$countrycoord['IR']= array(32  , 53);
		$countrycoord['IS']= array(65  , -18);
		$countrycoord['IT']= array(42.83 , 12.83);
		$countrycoord['JM']= array(18.25 , -77.5);
		$countrycoord['JO']= array(31  , 36);
		$countrycoord['JP']= array(36  , 138);
		$countrycoord['KE']= array(1 , 38);
		$countrycoord['KG']= array(41  , 75);
		$countrycoord['KH']= array(13  , 105);
		$countrycoord['KI']= array(1.42  , 173);
		$countrycoord['KM']= array(-12.17  , 44.25);
		$countrycoord['KN']= array(17.33 , -62.75);
		$countrycoord['KP']= array(40  , 127);
		$countrycoord['KR']= array(37  , 127.5);
		$countrycoord['KW']= array(29.34 , 47.66);
		$countrycoord['KY']= array(19.5  , -80.5);
		$countrycoord['KZ']= array(48  , 68);
		$countrycoord['LA']= array(18  , 105);
		$countrycoord['LB']= array(33.83 , 35.83);
		$countrycoord['LC']= array(13.88 , -61.13);
		$countrycoord['LI']= array(47.17 , 9.53);
		$countrycoord['LK']= array(7 , 81);
		$countrycoord['LR']= array(6.5 , -9.5);
		$countrycoord['LS']= array(-29.5 , 28.5);
		$countrycoord['LT']= array(56  , 24);
		$countrycoord['LU']= array(49.75 , 6.17);
		$countrycoord['LV']= array(57  , 25);
		$countrycoord['LY']= array(25  , 17);
		$countrycoord['MA']= array(32  , -5);
		$countrycoord['MC']= array(43.73 , 7.4);
		$countrycoord['MD']= array(47  , 29);
		$countrycoord['ME']= array(42  , 19);
		$countrycoord['MG']= array(-20 , 47);
		$countrycoord['MH']= array(9 , 168);
		$countrycoord['MK']= array(41.83 , 22);
		$countrycoord['ML']= array(17  , -4);
		$countrycoord['MM']= array(22  , 98);
		$countrycoord['MN']= array(46  , 105);
		$countrycoord['MO']= array(22.17 , 113.55);
		$countrycoord['MP']= array(15.2  , 145.75);
		$countrycoord['MQ']= array(14.67 , -61);
		$countrycoord['MR']= array(20  , -12);
		$countrycoord['MS']= array(16.75 , -62.2);
		$countrycoord['MT']= array(35.83 , 14.58);
		$countrycoord['MU']= array(-20.28  , 57.55);
		$countrycoord['MV']= array(3.25  , 73);
		$countrycoord['MW']= array(-13.5 , 34);
		$countrycoord['MX']= array(23  , -102);
		$countrycoord['MY']= array(2.5 , 112.5);
		$countrycoord['MZ']= array(-18.25  , 35);
		$countrycoord['NA']= array(-22 , 17);
		$countrycoord['NC']= array(-21.5 , 165.5);
		$countrycoord['NE']= array(16  , 8);
		$countrycoord['NF']= array(-29.03  , 167.95);
		$countrycoord['NG']= array(10  , 8);
		$countrycoord['NI']= array(13  , -85);
		$countrycoord['NL']= array(52.5  , 5.75);
		$countrycoord['NO']= array(62  , 10);
		$countrycoord['NP']= array(28  , 84);
		$countrycoord['NR']= array(-0.53 , 166.92);
		$countrycoord['NU']= array(-19.03  , -169.87);
		$countrycoord['NZ']= array(-41 , 174);
		$countrycoord['OM']= array(21  , 57);
		$countrycoord['PA']= array(9 , -80);
		$countrycoord['PE']= array(-10 , -76);
		$countrycoord['PF']= array(-15 , -140);
		$countrycoord['PG']= array(-6  , 147);
		$countrycoord['PH']= array(13  , 122);
		$countrycoord['PK']= array(30  , 70);
		$countrycoord['PL']= array(52  , 20);
		$countrycoord['PM']= array(46.83 , -56.33);
		$countrycoord['PR']= array(18.25 , -66.5);
		$countrycoord['PS']= array(32  , 35.25);
		$countrycoord['PT']= array(39.5  , -8);
		$countrycoord['PW']= array(7.5 , 134.5);
		$countrycoord['PY']= array(-23 , -58);
		$countrycoord['QA']= array(25.5  , 51.25);
		$countrycoord['RE']= array(-21.1 , 55.6);
		$countrycoord['RO']= array(46  , 25);
		$countrycoord['RS']= array(44  , 21);
		$countrycoord['RU']= array(60  , 100);
		$countrycoord['RW']= array(-2  , 30);
		$countrycoord['SA']= array(25  , 45);
		$countrycoord['SB']= array(-8  , 159);
		$countrycoord['SC']= array(-4.58 , 55.67);
		$countrycoord['SD']= array(15  , 30);
		$countrycoord['SE']= array(62  , 15);
		$countrycoord['SG']= array(1.37  , 103.8);
		$countrycoord['SH']= array(-15.93  , -5.7);
		$countrycoord['SI']= array(46  , 15);
		$countrycoord['SJ']= array(78  , 20);
		$countrycoord['SK']= array(48.67 , 19.5);
		$countrycoord['SL']= array(8.5 , -11.5);
		$countrycoord['SM']= array(43.77 , 12.42);
		$countrycoord['SN']= array(14  , -14);
		$countrycoord['SO']= array(10  , 49);
		$countrycoord['SR']= array(4 , -56);
		$countrycoord['ST']= array(1 , 7);
		$countrycoord['SV']= array(13.83 , -88.92);
		$countrycoord['SY']= array(35  , 38);
		$countrycoord['SZ']= array(-26.5 , 31.5);
		$countrycoord['TC']= array(21.75 , -71.58);
		$countrycoord['TD']= array(15  , 19);
		$countrycoord['TF']= array(-43 , 67);
		$countrycoord['TG']= array(8 , 1.17);
		$countrycoord['TH']= array(15  , 100);
		$countrycoord['TJ']= array(39  , 71);
		$countrycoord['TK']= array(-9  , -172);
		$countrycoord['TM']= array(40  , 60);
		$countrycoord['TN']= array(34  , 9);
		$countrycoord['TO']= array(-20 , -175);
		$countrycoord['TR']= array(39  , 35);
		$countrycoord['TT']= array(11  , -61);
		$countrycoord['TV']= array(-8  , 178);
		$countrycoord['TW']= array(23.5  , 121);
		$countrycoord['TZ']= array(-6  , 35);
		$countrycoord['UA']= array(49  , 32);
		$countrycoord['UG']= array(1 , 32);
		$countrycoord['UM']= array(19.28 , 166.6);
		$countrycoord['US']= array(38  , -97);
		$countrycoord['UY']= array(-33 , -56);
		$countrycoord['UZ']= array(41  , 64);
		$countrycoord['VA']= array(41.9  , 12.45);
		$countrycoord['VC']= array(13.25 , -61.2);
		$countrycoord['VE']= array(8 , -66);
		$countrycoord['VG']= array(18.5  , -64.5);
		$countrycoord['VI']= array(18.33 , -64.83);
		$countrycoord['VN']= array(16  , 106);
		$countrycoord['VU']= array(-16 , 167);
		$countrycoord['WF']= array(-13.3 , -176.2);
		$countrycoord['WS']= array(-13.58  , -172.33);
		$countrycoord['YE']= array(15  , 48);
		$countrycoord['YT']= array(-12.83  , 45.17);
		$countrycoord['ZA']= array(-29 , 24);
		$countrycoord['ZM']= array(-15 , 30);
		$countrycoord['ZW']= array(-20 , 30);
		return $countrycoord;
	}
}
?>