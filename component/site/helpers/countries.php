<?php
/**
 * @version 1.0 $Id: image.class.php 298 2009-06-24 07:42:35Z julien $
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class redEVENTHelperCountries
{
	/**
	 * return countries array indexed by iso3 code
	 *
	 * @return array
	 */
	public static function getCountries()
	{
		$country["AFG"]= Array( "iso2" => "AF", "name" => "Afghanistan, Islamic Republic ");
		$country["ALA"]= Array( "iso2" => "AX", "name" => "Åland Islands");
		$country["ALB"]= Array( "iso2" => "AL", "name" => "Albania, Republic of");
		$country["DZA"]= Array( "iso2" => "DZ", "name" => "Algeria, People's Democratic Republic");
		$country["ASM"]= Array( "iso2" => "AS", "name" => "American Samoa");
		$country["AND"]= Array( "iso2" => "AD", "name" => "Andorra, Principality of");
		$country["AGO"]= Array( "iso2" => "AO", "name" => "Angola, Republic of");
		$country["AIA"]= Array( "iso2" => "AI", "name" => "Anguilla");
		$country["ATA"]= Array( "iso2" => "AQ", "name" => "Antarctica (the territory Sout");
		$country["ATG"]= Array( "iso2" => "AG", "name" => "Antigua and Barbuda");
		$country["ARG"]= Array( "iso2" => "AR", "name" => "Argentina, Argentine Republic");
		$country["ARM"]= Array( "iso2" => "AM", "name" => "Armenia, Republic of");
		$country["ABW"]= Array( "iso2" => "AW", "name" => "Aruba");
		$country["AUS"]= Array( "iso2" => "AU", "name" => "Australia, Commonwealth of");
		$country["AUT"]= Array( "iso2" => "AT", "name" => "Austria, Republic of");
		$country["AZE"]= Array( "iso2" => "AZ", "name" => "Azerbaijan, Republic of");
		$country["BHS"]= Array( "iso2" => "BS", "name" => "Bahamas, Commonwealth of the");
		$country["BHR"]= Array( "iso2" => "BH", "name" => "Bahrain, Kingdom of");
		$country["BGD"]= Array( "iso2" => "BD", "name" => "Bangladesh, People's Republic ");
		$country["BRB"]= Array( "iso2" => "BB", "name" => "Barbados");
		$country["BLR"]= Array( "iso2" => "BY", "name" => "Belarus, Republic of");
		$country["BEL"]= Array( "iso2" => "BE", "name" => "Belgium, Kingdom of");
		$country["BLZ"]= Array( "iso2" => "BZ", "name" => "Belize");
		$country["BEN"]= Array( "iso2" => "BJ", "name" => "Benin, Republic of");
		$country["BMU"]= Array( "iso2" => "BM", "name" => "Bermuda");
		$country["BTN"]= Array( "iso2" => "BT", "name" => "Bhutan, Kingdom of");
		$country["BOL"]= Array( "iso2" => "BO", "name" => "Bolivia, Republic of");
		$country["BIH"]= Array( "iso2" => "BA", "name" => "Bosnia and Herzegovina");
		$country["BWA"]= Array( "iso2" => "BW", "name" => "Botswana, Republic of");
		$country["BVT"]= Array( "iso2" => "BV", "name" => "Bouvet Island (Bouvetoya)");
		$country["BRA"]= Array( "iso2" => "BR", "name" => "Brazil, Federative Republic of");
		$country["IOT"]= Array( "iso2" => "IO", "name" => "British Indian Ocean Territory");
		$country["VGB"]= Array( "iso2" => "VG", "name" => "British Virgin Islands");
		$country["BRN"]= Array( "iso2" => "BN", "name" => "Brunei Darussalam");
		$country["BGR"]= Array( "iso2" => "BG", "name" => "Bulgaria, Republic of");
		$country["BFA"]= Array( "iso2" => "BF", "name" => "Burkina Faso");
		$country["BDI"]= Array( "iso2" => "BI", "name" => "Burundi, Republic of");
		$country["KHM"]= Array( "iso2" => "KH", "name" => "Cambodia, Kingdom of");
		$country["CMR"]= Array( "iso2" => "CM", "name" => "Cameroon, Republic of");
		$country["CAN"]= Array( "iso2" => "CA", "name" => "Canada");
		$country["CPV"]= Array( "iso2" => "CV", "name" => "Cape Verde, Republic of");
		$country["CYM"]= Array( "iso2" => "KY", "name" => "Cayman Islands");
		$country["CAF"]= Array( "iso2" => "CF", "name" => "Central African Republic");
		$country["TCD"]= Array( "iso2" => "TD", "name" => "Chad, Republic of");
		$country["CHL"]= Array( "iso2" => "CL", "name" => "Chile, Republic of");
		$country["CHN"]= Array( "iso2" => "CN", "name" => "China, People's Republic of");
		$country["CXR"]= Array( "iso2" => "CX", "name" => "Christmas Island");
		$country["CCK"]= Array( "iso2" => "CC", "name" => "Cocos (Keeling) Islands");
		$country["COL"]= Array( "iso2" => "CO", "name" => "Colombia, Republic of");
		$country["COM"]= Array( "iso2" => "KM", "name" => "Comoros, Union of the");
		$country["COD"]= Array( "iso2" => "CD", "name" => "Congo, Democratic Republic of ");
		$country["COG"]= Array( "iso2" => "CG", "name" => "Congo, Republic of the");
		$country["COK"]= Array( "iso2" => "CK", "name" => "Cook Islands");
		$country["CRI"]= Array( "iso2" => "CR", "name" => "Costa Rica, Republic of");
		$country["CIV"]= Array( "iso2" => "CI", "name" => "Cote d'Ivoire, Republic of");
		$country["HRV"]= Array( "iso2" => "HR", "name" => "Croatia, Republic of");
		$country["CUB"]= Array( "iso2" => "CU", "name" => "Cuba, Republic of");
		$country["CYP"]= Array( "iso2" => "CY", "name" => "Cyprus, Republic of");
		$country["CZE"]= Array( "iso2" => "CZ", "name" => "Czech Republic");
		$country["DNK"]= Array( "iso2" => "DK", "name" => "Denmark, Kingdom of");
		$country["DJI"]= Array( "iso2" => "DJ", "name" => "Djibouti, Republic of");
		$country["DMA"]= Array( "iso2" => "DM", "name" => "Dominica, Commonwealth of");
		$country["DOM"]= Array( "iso2" => "DO", "name" => "Dominican Republic");
		$country["ECU"]= Array( "iso2" => "EC", "name" => "Ecuador, Republic of");
		$country["EGY"]= Array( "iso2" => "EG", "name" => "Egypt, Arab Republic of");
		$country["SLV"]= Array( "iso2" => "SV", "name" => "El Salvador, Republic of");
		$country["GNQ"]= Array( "iso2" => "GQ", "name" => "Equatorial Guinea, Republic of");
		$country["ERI"]= Array( "iso2" => "ER", "name" => "Eritrea, State of");
		$country["EST"]= Array( "iso2" => "EE", "name" => "Estonia, Republic of");
		$country["ETH"]= Array( "iso2" => "ET", "name" => "Ethiopia, Federal Democratic R");
		$country["FRO"]= Array( "iso2" => "FO", "name" => "Faroe Islands");
		$country["FLK"]= Array( "iso2" => "FK", "name" => "Falkland Islands (Malvinas)");
		$country["FJI"]= Array( "iso2" => "FJ", "name" => "Fiji, Republic of the Fiji Isl");
		$country["FIN"]= Array( "iso2" => "FI", "name" => "Finland, Republic of");
		$country["FRA"]= Array( "iso2" => "FR", "name" => "France, French Republic");
		$country["GUF"]= Array( "iso2" => "GF", "name" => "French Guiana");
		$country["PYF"]= Array( "iso2" => "PF", "name" => "French Polynesia");
		$country["ATF"]= Array( "iso2" => "TF", "name" => "French Southern Territories");
		$country["GAB"]= Array( "iso2" => "GA", "name" => "Gabon, Gabonese Republic");
		$country["GMB"]= Array( "iso2" => "GM", "name" => "Gambia, Republic of the");
		$country["GEO"]= Array( "iso2" => "GE", "name" => "Georgia");
		$country["DEU"]= Array( "iso2" => "DE", "name" => "Germany, Federal Republic of");
		$country["GHA"]= Array( "iso2" => "GH", "name" => "Ghana, Republic of");
		$country["GIB"]= Array( "iso2" => "GI", "name" => "Gibraltar");
		$country["GRC"]= Array( "iso2" => "GR", "name" => "Greece, Hellenic Republic");
		$country["GRL"]= Array( "iso2" => "GL", "name" => "Greenland");
		$country["GRD"]= Array( "iso2" => "GD", "name" => "Grenada");
		$country["GLP"]= Array( "iso2" => "GP", "name" => "Guadeloupe");
		$country["GUM"]= Array( "iso2" => "GU", "name" => "Guam");
		$country["GTM"]= Array( "iso2" => "GT", "name" => "Guatemala, Republic of");
		$country["GGY"]= Array( "iso2" => "GG", "name" => "Guernsey, Bailiwick of");
		$country["GIN"]= Array( "iso2" => "GN", "name" => "Guinea, Republic of");
		$country["GNB"]= Array( "iso2" => "GW", "name" => "Guinea-Bissau, Republic of");
		$country["GUY"]= Array( "iso2" => "GY", "name" => "Guyana, Co-operative Republic ");
		$country["HTI"]= Array( "iso2" => "HT", "name" => "Haiti, Republic of");
		$country["HMD"]= Array( "iso2" => "HM", "name" => "Heard Island and McDonald Isla");
		$country["VAT"]= Array( "iso2" => "VA", "name" => "Holy See (Vatican City State)");
		$country["HND"]= Array( "iso2" => "HN", "name" => "Honduras, Republic of");
		$country["HKG"]= Array( "iso2" => "HK", "name" => "Hong Kong, Special Administrat");
		$country["HUN"]= Array( "iso2" => "HU", "name" => "Hungary, Republic of");
		$country["ISL"]= Array( "iso2" => "IS", "name" => "Iceland, Republic of");
		$country["IND"]= Array( "iso2" => "IN", "name" => "India, Republic of");
		$country["IDN"]= Array( "iso2" => "ID", "name" => "Indonesia, Republic of");
		$country["IRN"]= Array( "iso2" => "IR", "name" => "Iran, Islamic Republic of");
		$country["IRQ"]= Array( "iso2" => "IQ", "name" => "Iraq, Republic of");
		$country["IRL"]= Array( "iso2" => "IE", "name" => "Ireland");
		$country["IMN"]= Array( "iso2" => "IM", "name" => "Isle of Man");
		$country["ISR"]= Array( "iso2" => "IL", "name" => "Israel, State of");
		$country["ITA"]= Array( "iso2" => "IT", "name" => "Italy, Italian Republic");
		$country["JAM"]= Array( "iso2" => "JM", "name" => "Jamaica");
		$country["JPN"]= Array( "iso2" => "JP", "name" => "Japan");
		$country["JEY"]= Array( "iso2" => "JE", "name" => "Jersey, Bailiwick of");
		$country["JOR"]= Array( "iso2" => "JO", "name" => "Jordan, Hashemite Kingdom of");
		$country["KAZ"]= Array( "iso2" => "KZ", "name" => "Kazakhstan, Republic of");
		$country["KEN"]= Array( "iso2" => "KE", "name" => "Kenya, Republic of");
		$country["KIR"]= Array( "iso2" => "KI", "name" => "Kiribati, Republic of");
		$country["PRK"]= Array( "iso2" => "KP", "name" => "Korea, Democratic People's Rep");
		$country["KOR"]= Array( "iso2" => "KR", "name" => "Korea, Republic of");
		$country["KWT"]= Array( "iso2" => "KW", "name" => "Kuwait, State of");
		$country["KGZ"]= Array( "iso2" => "KG", "name" => "Kyrgyz Republic");
		$country["LAO"]= Array( "iso2" => "LA", "name" => "Lao People's Democratic Republ");
		$country["LVA"]= Array( "iso2" => "LV", "name" => "Latvia, Republic of");
		$country["LBN"]= Array( "iso2" => "LB", "name" => "Lebanon, Lebanese Republic");
		$country["LSO"]= Array( "iso2" => "LS", "name" => "Lesotho, Kingdom of");
		$country["LBR"]= Array( "iso2" => "LR", "name" => "Liberia, Republic of");
		$country["LBY"]= Array( "iso2" => "LY", "name" => "Libyan Arab Jamahiriya");
		$country["LIE"]= Array( "iso2" => "LI", "name" => "Liechtenstein, Principality of");
		$country["LTU"]= Array( "iso2" => "LT", "name" => "Lithuania, Republic of");
		$country["LUX"]= Array( "iso2" => "LU", "name" => "Luxembourg, Grand Duchy of");
		$country["MAC"]= Array( "iso2" => "MO", "name" => "Macao, Special Administrative ");
		$country["MKD"]= Array( "iso2" => "MK", "name" => "Macedonia, the former Yugoslav");
		$country["MDG"]= Array( "iso2" => "MG", "name" => "Madagascar, Republic of");
		$country["MWI"]= Array( "iso2" => "MW", "name" => "Malawi, Republic of");
		$country["MYS"]= Array( "iso2" => "MY", "name" => "Malaysia");
		$country["MDV"]= Array( "iso2" => "MV", "name" => "Maldives, Republic of");
		$country["MLI"]= Array( "iso2" => "ML", "name" => "Mali, Republic of");
		$country["MLT"]= Array( "iso2" => "MT", "name" => "Malta, Republic of");
		$country["MHL"]= Array( "iso2" => "MH", "name" => "Marshall Islands, Republic of ");
		$country["MTQ"]= Array( "iso2" => "MQ", "name" => "Martinique");
		$country["MRT"]= Array( "iso2" => "MR", "name" => "Mauritania, Islamic Republic o");
		$country["MUS"]= Array( "iso2" => "MU", "name" => "Mauritius, Republic of");
		$country["MYT"]= Array( "iso2" => "YT", "name" => "Mayotte");
		$country["MEX"]= Array( "iso2" => "MX", "name" => "Mexico, United Mexican States");
		$country["FSM"]= Array( "iso2" => "FM", "name" => "Micronesia, Federated States o");
		$country["MDA"]= Array( "iso2" => "MD", "name" => "Moldova, Republic of");
		$country["MCO"]= Array( "iso2" => "MC", "name" => "Monaco, Principality of");
		$country["MNG"]= Array( "iso2" => "MN", "name" => "Mongolia");
		$country["MNE"]= Array( "iso2" => "ME", "name" => "Montenegro, Republic of");
		$country["MSR"]= Array( "iso2" => "MS", "name" => "Montserrat");
		$country["MAR"]= Array( "iso2" => "MA", "name" => "Morocco, Kingdom of");
		$country["MOZ"]= Array( "iso2" => "MZ", "name" => "Mozambique, Republic of");
		$country["MMR"]= Array( "iso2" => "MM", "name" => "Myanmar, Union of");
		$country["NAM"]= Array( "iso2" => "NA", "name" => "Namibia, Republic of");
		$country["NRU"]= Array( "iso2" => "NR", "name" => "Nauru, Republic of");
		$country["NPL"]= Array( "iso2" => "NP", "name" => "Nepal, State of");
		$country["ANT"]= Array( "iso2" => "AN", "name" => "Netherlands Antilles");
		$country["NLD"]= Array( "iso2" => "NL", "name" => "Netherlands, Kingdom of the");
		$country["NCL"]= Array( "iso2" => "NC", "name" => "New Caledonia");
		$country["NZL"]= Array( "iso2" => "NZ", "name" => "New Zealand");
		$country["NIC"]= Array( "iso2" => "NI", "name" => "Nicaragua, Republic of");
		$country["NER"]= Array( "iso2" => "NE", "name" => "Niger, Republic of");
		$country["NGA"]= Array( "iso2" => "NG", "name" => "Nigeria, Federal Republic of");
		$country["NIU"]= Array( "iso2" => "NU", "name" => "Niue");
		$country["NFK"]= Array( "iso2" => "NF", "name" => "Norfolk Island");
		$country["MNP"]= Array( "iso2" => "MP", "name" => "Northern Mariana Islands, Comm");
		$country["NOR"]= Array( "iso2" => "NO", "name" => "Norway, Kingdom of");
		$country["OMN"]= Array( "iso2" => "OM", "name" => "Oman, Sultanate of");
		$country["PAK"]= Array( "iso2" => "PK", "name" => "Pakistan, Islamic Republic of");
		$country["PLW"]= Array( "iso2" => "PW", "name" => "Palau, Republic of");
		$country["PSE"]= Array( "iso2" => "PS", "name" => "Palestinian Territory, Occupie");
		$country["PAN"]= Array( "iso2" => "PA", "name" => "Panama, Republic of");
		$country["PNG"]= Array( "iso2" => "PG", "name" => "Papua New Guinea, Independent ");
		$country["PRY"]= Array( "iso2" => "PY", "name" => "Paraguay, Republic of");
		$country["PER"]= Array( "iso2" => "PE", "name" => "Peru, Republic of");
		$country["PHL"]= Array( "iso2" => "PH", "name" => "Philippines, Republic of the");
		$country["PCN"]= Array( "iso2" => "PN", "name" => "Pitcairn Islands");
		$country["POL"]= Array( "iso2" => "PL", "name" => "Poland, Republic of");
		$country["PRT"]= Array( "iso2" => "PT", "name" => "Portugal, Portuguese Republic");
		$country["PRI"]= Array( "iso2" => "PR", "name" => "Puerto Rico, Commonwealth of");
		$country["QAT"]= Array( "iso2" => "QA", "name" => "Qatar, State of");
		$country["REU"]= Array( "iso2" => "RE", "name" => "Reunion");
		$country["ROU"]= Array( "iso2" => "RO", "name" => "Romania");
		$country["RUS"]= Array( "iso2" => "RU", "name" => "Russian Federation");
		$country["RWA"]= Array( "iso2" => "RW", "name" => "Rwanda, Republic of");
		$country["BLM"]= Array( "iso2" => "BL", "name" => "Saint Barthelemy");
		$country["SHN"]= Array( "iso2" => "SH", "name" => "Saint Helena");
		$country["KNA"]= Array( "iso2" => "KN", "name" => "Saint Kitts and Nevis, Federat");
		$country["LCA"]= Array( "iso2" => "LC", "name" => "Saint Lucia");
		$country["MAF"]= Array( "iso2" => "MF", "name" => "Saint Martin");
		$country["SPM"]= Array( "iso2" => "PM", "name" => "Saint Pierre and Miquelon");
		$country["VCT"]= Array( "iso2" => "VC", "name" => "Saint Vincent and the Grenadin");
		$country["WSM"]= Array( "iso2" => "WS", "name" => "Samoa, Independent State of");
		$country["SMR"]= Array( "iso2" => "SM", "name" => "San Marino, Republic of");
		$country["STP"]= Array( "iso2" => "ST", "name" => "Sao Tome and Principe, Democra");
		$country["SAU"]= Array( "iso2" => "SA", "name" => "Saudi Arabia, Kingdom of");
		$country["SEN"]= Array( "iso2" => "SN", "name" => "Senegal, Republic of");
		$country["SRB"]= Array( "iso2" => "RS", "name" => "Serbia, Republic of");
		$country["SYC"]= Array( "iso2" => "SC", "name" => "Seychelles, Republic of");
		$country["SLE"]= Array( "iso2" => "SL", "name" => "Sierra Leone, Republic of");
		$country["SGP"]= Array( "iso2" => "SG", "name" => "Singapore, Republic of");
		$country["SVK"]= Array( "iso2" => "SK", "name" => "Slovakia (Slovak Republic)");
		$country["SVN"]= Array( "iso2" => "SI", "name" => "Slovenia, Republic of");
		$country["SLB"]= Array( "iso2" => "SB", "name" => "Solomon Islands");
		$country["SOM"]= Array( "iso2" => "SO", "name" => "Somalia, Somali Republic");
		$country["ZAF"]= Array( "iso2" => "ZA", "name" => "South Africa, Republic of");
		$country["SGS"]= Array( "iso2" => "GS", "name" => "South Georgia and the South Sa");
		$country["ESP"]= Array( "iso2" => "ES", "name" => "Spain, Kingdom of");
		$country["LKA"]= Array( "iso2" => "LK", "name" => "Sri Lanka, Democratic Socialis");
		$country["SDN"]= Array( "iso2" => "SD", "name" => "Sudan, Republic of");
		$country["SUR"]= Array( "iso2" => "SR", "name" => "Suriname, Republic of");
		$country["SJM"]= Array( "iso2" => "SJ", "name" => "Svalbard & Jan Mayen Islands");
		$country["SWZ"]= Array( "iso2" => "SZ", "name" => "Swaziland, Kingdom of");
		$country["SWE"]= Array( "iso2" => "SE", "name" => "Sweden, Kingdom of");
		$country["CHE"]= Array( "iso2" => "CH", "name" => "Switzerland, Swiss Confederati");
		$country["SYR"]= Array( "iso2" => "SY", "name" => "Syrian Arab Republic");
		$country["TWN"]= Array( "iso2" => "TW", "name" => "Taiwan");
		$country["TJK"]= Array( "iso2" => "TJ", "name" => "Tajikistan, Republic of");
		$country["TZA"]= Array( "iso2" => "TZ", "name" => "Tanzania, United Republic of");
		$country["THA"]= Array( "iso2" => "TH", "name" => "Thailand, Kingdom of");
		$country["TLS"]= Array( "iso2" => "TL", "name" => "Timor-Leste, Democratic Republ");
		$country["TGO"]= Array( "iso2" => "TG", "name" => "Togo, Togolese Republic");
		$country["TKL"]= Array( "iso2" => "TK", "name" => "Tokelau");
		$country["TON"]= Array( "iso2" => "TO", "name" => "Tonga, Kingdom of");
		$country["TTO"]= Array( "iso2" => "TT", "name" => "Trinidad and Tobago, Republic ");
		$country["TUN"]= Array( "iso2" => "TN", "name" => "Tunisia, Tunisian Republic");
		$country["TUR"]= Array( "iso2" => "TR", "name" => "Turkey, Republic of");
		$country["TKM"]= Array( "iso2" => "TM", "name" => "Turkmenistan");
		$country["TCA"]= Array( "iso2" => "TC", "name" => "Turks and Caicos Islands");
		$country["TUV"]= Array( "iso2" => "TV", "name" => "Tuvalu");
		$country["UGA"]= Array( "iso2" => "UG", "name" => "Uganda, Republic of");
		$country["UKR"]= Array( "iso2" => "UA", "name" => "Ukraine");
		$country["ARE"]= Array( "iso2" => "AE", "name" => "United Arab Emirates");
		$country["GBR"]= Array( "iso2" => "GB", "name" => "United Kingdom of Great Britain");
		$country["USA"]= Array( "iso2" => "US", "name" => "United States of America");
		$country["UMI"]= Array( "iso2" => "UM", "name" => "United States Minor Outlying I");
		$country["VIR"]= Array( "iso2" => "VI", "name" => "United States Virgin Islands");
		$country["URY"]= Array( "iso2" => "UY", "name" => "Uruguay, Eastern Republic of");
		$country["UZB"]= Array( "iso2" => "UZ", "name" => "Uzbekistan, Republic of");
		$country["VUT"]= Array( "iso2" => "VU", "name" => "Vanuatu, Republic of");
		$country["VEN"]= Array( "iso2" => "VE", "name" => "Venezuela, Bolivarian Republic");
		$country["VNM"]= Array( "iso2" => "VN", "name" => "Vietnam, Socialist Republic of");
		$country["WLF"]= Array( "iso2" => "WF", "name" => "Wallis and Futuna");
		$country["ESH"]= Array( "iso2" => "EH", "name" => "Western Sahara");
		$country["YEM"]= Array( "iso2" => "YE", "name" => "Yemen");
		$country["ZMB"]= Array( "iso2" => "ZM", "name" => "Zambia, Republic of");
		$country["ZWE"]= Array( "iso2" => "ZW", "name" => "Zimbabwe, Republic of");
		$country["0"]= Array( "iso2" => "0", "name" => "NO VALID COUNTRY");

		return $country;
	}


	public static function getCountrycoordArray()
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

	public static function getCountryOptions($value_tag = 'value', $text_tag = 'text', $add_select = false)
	{
		$countries = self::getCountries();
		$options = array();
		if ($add_select) {
			$options[] = JHTML::_('select.option', '', JText::_('COM_REDEVENT_SELECT_COUNTRY'));
		}
		foreach ($countries AS $k => $c)
		{
			$name = explode(',', $c['name']);
			$options[] = JHTML::_('select.option', $c['iso2'], JText::_($name[0]), $value_tag, $text_tag);
		}
		return $options;
	}

	public static function convertIso2to3($iso_code_2)
	{
		foreach (self::getCountries() as $iso3 => $c)
		{
			if ($d['iso2'] == $iso_code_2)
			{
				return $iso3;
			}
		}

		return false;
	}

	public static function convertIso3to2($iso_code_3)
	{
		$countries = self::getCountries();

		if (isset($countries[$iso_code_3]))
		{
			return $countries[$iso_code_3]['iso2'];
		}

		return false;
	}

	/**
	 * return flag url from iso code
	 *
	 * @param $iso_code
	 */
	function getIsoFlag($iso_code)
	{
		if (strlen($iso_code) == 3)
		{
			$iso_code = self::convertIso3to2($iso_code);
		}
		if ($iso_code)
		{
			$path = JURI::root().'components/com_redevent/assets/images/flags/'.strtolower($iso_code).'.gif';
			return $path;
		}
		else
		{
			return null;
		}
	}

	/**
	 * example: echo self::getCountryFlag($country);
	 *
	 * @param string: an iso3 country code, e.g AUT
	 * @param string: additional html attributes for the img tag
	 * @return string: html code for the flag image
	 */
	public static function getCountryFlag($countrycode, $attributes = '')
	{
		$src = self::getIsoFlag($countrycode);

		if (!$src)
		{
			return '';
		}

		$html = '<img src="' . $src . '" alt="' . self::getCountryName($countrycode) . '" ';
		$html .= 'title="' . self::getCountryName($countrycode) . '" ' . $attributes . ' />';

		return $html;
	}

	/**
	 * @param string: an iso country code, e.g AUT
	 * @return string: a country name
	 */
	public static function getCountryName($iso)
	{
		if (strlen($iso) == 2)
		{
			$iso = self::convertIso2to3($iso);
		}

		$countries = self::getCountries();

		if (isset($countries[$iso]['name']))
		{
			$c = explode(',', $countries[$iso]['name']);
			return JText::_($c[0]);
		}

		return false;
	}

	/**
	 * @param string: an iso3 country code, e.g AUT
	 * @return string: a country full name
	 */
	public static function getCountryFullName($iso)
	{
		if (strlen($iso) == 2)
		{
			$iso = self::convertIso2to3($iso);
		}

		$countries = self::getCountries();

		if (isset($countries[$iso]['name']))
		{
			return JText::_($countries[$iso]['name']);
		}

		return false;
	}

	/**
	 * @param string: an iso3 country code, e.g AUT
	 * @return string: a country name, short form
	 */
	public static function getShortCountryName($iso)
	{
		if (strlen($iso) == 2)
		{
			$iso = self::convertIso2to3($iso);
		}

		$full = self::getCountryName($iso);

		if (empty($full))
		{
			return false;
		}

		$parts = explode(',', $full);

		return JText::_($parts[0]);
	}

	/**
	 * get country latitude
	 *
	 * @param string $iso code (iso2 or iso3)
	 * @return float or false
	 */
	public static function getLatitude($iso)
	{
		if (strlen($iso) == 3)
		{
			$iso = self::convertIso3to2($iso);
		}

		$coords = self::getCountrycoordArray();

		if (isset($coords[$iso]))
		{
			return $coords[$iso][0];
		}
		else
		{
			JError::raiseWarning(500, JText::_('COM_REDEVENT_COUNTRIES_ERROR_COUNTRY_NOT_FOUND'));
			return false;
		}
	}

	/**
	 * get country longitude
	 *
	 * @param string $iso code (iso2 or iso3)
	 * @return float or false
	 */
	public static function getLongitude($iso)
	{
		if (strlen($iso) == 3)
		{
			$iso = self::convertIso3to2($iso);
		}

		$coords = self::getCountrycoordArray();

		if (isset($coords[$iso]))
		{
			return $coords[$iso][1];
		}
		else
		{
			JError::raiseWarning(500, JText::_('COM_REDEVENT_COUNTRIES_ERROR_COUNTRY_NOT_FOUND'));
			return false;
		}
	}
}
