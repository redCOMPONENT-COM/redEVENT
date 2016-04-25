<?php
/**
 * This file is a PHP implementation of rsscal
 *
 * @version   0.8.5
 * @author    Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @copyright copyright (c) 2006 Kjell-Inge Gustafsson kigkonsult
 * @link      www.kigkonsult.se/rsscalCreator/index.php ical@kigkonsult.se
 *
 * License:
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */
// version string, do NOT remove!!
define('RSSCALCALCREATOR_VERSION', 'rsscalCreator 0.8.5');

define('ENCODING', 'UTF-8');
/* only for phpversion 5.x, date management */
if (substr(phpversion(), 0, 1) >= '5')
	date_default_timezone_set('Europe/Stockholm');

/**
 * rCbase class
 *
 * abstract base class
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.8.2 - 2006-12-19
 */
class rCbase
{

	/**
	 * _prepLink
	 *
	 * make valid urls in link or url
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.2 - 2006-12-19
	 *
	 * @param string $link (reference variable)
	 *
	 * @return void
	 */
	function _prepLink(& $link)
	{
		$link = trim($link);
		$pos = strrpos($link, "?");
		if ($pos !== FALSE)
		{
			$str1 = substr($link, 0, $pos);
			$str2 = substr($link, $pos);
			$str2 = str_replace('&amp;', '&', $str2);
			$str3 = explode('&', $str2);
			foreach ($str3 as $six => $str)
			{
				$str4 = explode('=', $str);
				if (2 == count($str4))
					$str3[$six] = $str4[0] . '=' . rawurlencode($str4[1]);
				else
					$str3[$six] = rawurlencode($str4[0]);
			}
			$str2 = implode('&amp;', $str3);
			$link = str_replace(' ', '%20', $str1) . $str2;
		}
	}

	/**
	 * _prepString
	 *
	 * convert any HTML-chars in title, summary, descriptions, dc:*, ev.*
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.2 - 2006-12-19
	 *
	 * @param string $string (reference variable)
	 *
	 * @return void
	 */
	function _prepString(& $string)
	{
		$string = trim($string);
		$string = htmlspecialchars(strip_tags(stripslashes(urldecode($string))));
	}
}


/**
 * rsscalCreator class
 *
 * main, public class
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.8.2 - 2006-12-19
 */
class rsscalCreator extends rCbase
{
	var $version;      // version of rsscal
	var $channel;      // rsscalElement
	var $image;        // rsscalElement
	var $items;        // an array of rsscalElements
	var $textinput;    // rsscalElement

	var $output;       // edited content

	var $directory;    // where to store rss file
	var $filename;     // file name
	var $delimiter;    // path/filename delimiter
	var $newlinechar;  // new line character

	/**
	 * rsscalCreator
	 *
	 * constructor for rsscalCreator object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string title - required for channel element
	 * @param string link - required for channel element
	 * @param string description - required for channel element
	 * @param string version
	 *
	 * @uses   function rsscalElement::rsscalElement
	 * @uses   function setChannelTitle
	 * @uses   function setChannelLink
	 * @uses   function setChannelDescription
	 */
	function __construct($title = null, $link = null, $description = null, $version = null)
	{
		$this->setVersion($version);

		/** set defaults */
		$this->delimiter = '.';
		$this->filename = date('YmdHis') . '.rss';
		$this->delimiter = '/';
		$this->newlinechar = "\n";

		$this->channel = new rsscalElement('channel');
		$this->channel->elementContent = array();
		if ($title)
			$this->setChannelTitle($title);
		if ($link)
			$this->setChannelLink($link);
		if ($description)
			$this->setChannelDescription($description);
		$this->image = null;
		$this->items = array();
		$this->textinput = null;

		$this->output = null;
	}

	/**
	 * addItem
	 *
	 * add item element to rsscalCreator instans
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param object $itemElement
	 *
	 * @return void
	 */
	function addItem($itemElement)
	{
		$this->items[] = $itemElement;
	}

	/**
	 * setChannel
	 *
	 * set channel element for rsscalCreator instans
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param object $channelElement
	 *
	 * @return void
	 */
	function setChannel($channelElement)
	{
		$this->channel = $channelElement;
	}

	/**
	 * setChannelAttribute
	 *
	 * set channel attribute for rsscalCreator instans
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param array $resource
	 *
	 * @return void
	 */
	function setChannelAttribute($resource)
	{
		$this->channel->attributes = array($resource);
	}

	/**
	 * setChannelDescription
	 *
	 * set channel element description for rsscalCreator instans
	 * overwrites any previous set description
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.1 - 2006-12-19
	 *
	 * @param string $elementContent
	 * @param array  $attributes
	 *
	 * @return void
	 * @uses   function setChannelElement
	 */
	function setChannelDescription($elementContent, $attributes = null)
	{
		$this->setChannelElement('description', $elementContent, $attributes);
	}

	/**
	 * setChannelLink
	 *
	 * set channel element link for rsscalCreator instans
	 * overwrites any previous set link
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.2 - 2006-12-19
	 *
	 * @param string $elementContent
	 * @param array  $attributes
	 *
	 * @return void
	 * @uses   function setChannelElement
	 */
	function setChannelLink($elementContent, $attributes = null)
	{
		$this->setChannelElement('link', $elementContent, $attributes);
	}

	/**
	 * setChannelTitle
	 *
	 * set channel element title for rsscalCreator instans
	 * overwrites any previous set title
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.1 - 2006-12-19
	 *
	 * @param string $elementContent
	 * @param array  $attributes
	 *
	 * @return void
	 * @uses   function setChannelElement
	 */
	function setChannelTitle($elementContent, $attributes = null)
	{
		$this->setChannelElement('title', $elementContent, $attributes);
	}

	/**
	 * setChannelElement
	 *
	 * set channel element 'elementName' for rsscalCreator instans
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.2 - 2006-12-19
	 *
	 * @param string $elementName
	 * @param string $elementContent
	 * @param array  $attributes
	 *
	 * @return void
	 * @uses   function rsscalElement::rsscalElement
	 */
	function setChannelElement($elementName, $elementContent, $attributes = null)
	{
		if (in_array(strtolower($elementName), array('description', 'title')))
			$this->_prepString($elementContent);
		elseif (in_array(strtolower($elementName), array('docs', 'link')))
			$this->_prepLink($elementContent);
		$match = null;
		foreach ($this->channel->elementContent as $cix => $channelPart)
		{
			if ($channelPart->elementName != strtolower($elementName))
				continue;
			$this->channel->elementContent[$cix]->elementContent = $elementContent;
			$match = $cix;
			break;
		}
		if (!isset($match))
		{
			$this->channel->elementContent[] = new rsscalElement($elementName
				, $elementContent
			);
			$contentKeys = array_keys($this->channel->elementContent);
			$match = end($contentKeys);
		}
		if (is_array($attributes))
		{
			foreach ($attributes as $attrKey => $attrValue)
			{
				$attrKey = (!is_int($attrKey)) ? $attrKey : null;
				if ($attrKey == strtolower('url'))
					$this->_prepLink($attrValue);
				$this->channel->elementContent[$match]->attributes[$attrKey] = $attrValue;
			}
		}
	}

	/**
	 * setImage
	 *
	 * set image element for rsscalCreator instans
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param object $imageElement
	 *
	 * @return void
	 */
	function setImage($imageElement)
	{
		$this->image = $imageElement;
	}

	/**
	 * setTextinput
	 *
	 * set textinput element for rsscalCreator instans
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param object $textinputElement
	 *
	 * @return void
	 */
	function setTextinput($textinputElement)
	{
		$this->textinput = $textinputElement;
	}

	/**
	 * createRSS
	 *
	 * creates formatted output for rsscalCreator instance
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.5 - 2007-01-16
	 *
	 * @param string $version optional, default '1.0'
	 *
	 * @return string
	 * @uses   function rsscalVersion::rsscalVersion
	 * @uses   function rsscalVersion1_0::rsscalVersion1_0
	 */
	function createRSS($version = FALSE)
	{
		if ($version)
			$this->setVersion($version);
		$rsscalVername = 'rsscalVersion' . $this->version;
		if (class_exists($rsscalVername))
			$rsscalVersion = new $rsscalVername($this);
		else
			$rsscalVersion = new rsscalVersion($this);
		$this->output = $rsscalVersion->createOutput($this->newlinechar);

		return $this->output;
	}

	/**
	 * getFilename
	 *
	 * get feed file name
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $directory optional default './'
	 * @param string $filename  optional default generated
	 * @param string $delimiter optional, default '/'
	 *
	 * @return array directory, filename, filesize
	 * @uses   function setFilename
	 */
	function getFilename($directory = FALSE, $filename = FALSE, $delimiter = FALSE)
	{
		if ($directory || $filename || $delimiter)
			$this->setFilename($directory, $filename, $delimiter);

		$dirfile = $this->directory . $this->delimiter . $this->filename;
		$result = array($directory, $delimiter, 0);
		if (is_file($dirfile))
		{
			$result[2] = filesize($dirfile);
		}
		return $result;
	}

	/**
	 * _redirectRSS
	 *
	 * redirect rss file to user browser
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.4 - 2007-01-07
	 *
	 * @param string $directory optional default './'
	 * @param string $filename  optional default generated
	 * @param string $delimiter optional, default '/'
	 *
	 * @return redirect
	 * @uses   function setFilename
	 */
	function _redirectRSS($directory = FALSE, $filename = FALSE, $delimiter = FALSE)
	{
		if ($directory || $filename || $delimiter)
			$this->setFilename($directory, $filename, $delimiter);
		elseif (!$this->filename)
			$this->setFilename();
		$dirfile = $this->directory . $this->delimiter . $this->filename;
		header('Content-Type: application/xml; charset=' . ENCODING);
		header('Content-Length: ' . filesize($dirfile));
		header('Content-Disposition: attachment; filename=' . basename($dirfile));
		$fp = fopen($dirfile, 'r');
		fpassthru($fp);
		fclose($fp);
		die();
	}

	/**
	 * returnRSS
	 *
	 * a HTTP redirect header is sent with created and saved file
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $directory optional default './'
	 * @param string $filename  optional default generated
	 * @param string $delimiter optional, default '/'
	 *
	 * @return redirect
	 * @uses   function saveRSS
	 * @uses   function _redirectRSS
	 */
	function returnRSS($directory = FALSE, $filename = FALSE, $delimiter = '/')
	{
		if ($this->saveRSS($directory, $filename, $delimiter))
			$this->_redirectRSS($directory, $filename, $delimiter);
	}

	/**
	 * saveRSS
	 *
	 * create and save edited rsscalCreator content to file
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $directory optional default './'
	 * @param string $filename  optional default generated
	 * @param string $delimiter optional, default '/'
	 *
	 * @return mixed FALSE (file not writeable) /array : directory, filename, filesize
	 * @uses   function setFilename
	 * @uses   function createRSS
	 */
	function saveRss($directory = FALSE, $filename = FALSE, $delimiter = FALSE)
	{
		if ($directory || $filename || $delimiter)
			$this->setFilename($directory, $filename, $delimiter);
		elseif (!$this->filename)
			$this->setFilename();

		$dirfile = $this->directory . $this->delimiter . $this->filename;
		$rssFile = fopen($dirfile, 'w+');
		if ($rssFile)
		{
			$this->output = $this->createRss();
			fputs($rssFile, $this->output);
			fclose($rssFile);
			$filesize = filesize($dirfile);
			return array($this->directory, $this->filename, $filesize);
		}
		else
			return FALSE;
	}

	/**
	 * setFilename
	 *
	 * set feed file name (default date( 'YmdHis' ).'.rss') (create empty file )
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $directory optional default './'
	 * @param string $filename  optional default generated
	 * @param string $delimiter optional, default '/'
	 *
	 * @return bool FALSE if not writable else TRUE
	 */
	function setFilename($directory = FALSE, $filename = FALSE, $delimiter = '/')
	{
		if ($directory)
			$this->directory = $directory;
		if ($filename)
			$this->filename = $filename;
		if ($delimiter)
			$this->delimiter = $delimiter;

		$dirfile = $this->directory . $this->delimiter . $this->filename;
		if (@touch($dirfile))
			return TRUE;
		else
			return FALSE;
	}

	/**
	 * setVersion
	 *
	 * set version for rsscalCreator instans
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $version
	 *
	 * @return void
	 */
	function setVersion($version = FALSE)
	{
		if ($version)
		{
			$version = str_replace('.', '_', $version);
			$this->version = $version;
		}
		else
			$this->version = '1_0';
	}

	/**
	 * useCachedRSS
	 *
	 * if recent version of feed file exists (default 3600 sec), an HTTP redirect header is sent
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $directory
	 * @param string $filename
	 * @param string $delimiter
	 * @param int    $timeout optional timeout seconds
	 *
	 * @return redirect
	 * @uses   function _redirectRSS
	 */
	function useCachedRSS($directory, $filename, $delimiter = '/', $timeout = 3600)
	{
		$dirfile = $directory . $delimiter . $filename;
		if ((file_exists($dirfile)) &&
			(time() - filemtime($dirfile) < $timeout)
		)
		{
			$this->_redirectRSS($directory, $filename, $delimiter);
		}
	}
}

/**
 * rsscalElement class
 *
 * class, manages elements
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.8.2 - 2006-12-19
 */
class rsscalElement extends rCbase
{
	var $elementName;      // element property name
	var $elementContent;   // element property content
	var $attributes;       // element property attributes, array: attr-key => attr-value

	/**
	 * rsscalElement
	 *
	 * constructor for rsscalElement object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.2 - 2006-12-19
	 *
	 * @param string $elementName
	 * @param string $elementContent
	 * @param array  $attributes
	 *
	 * @return void
	 */
	function __construct($elementName = null, $elementContent = null, $attributes = null)
	{
		//  $this->elementName    = strtolower( $elementName );
		if (in_array(strtolower($elementName), array('description'
		, 'name'
		, 'title'
		, 'dc:title'
		, 'dc:creator'
		, 'dc:subject'
		, 'dc:description'
		, 'dc:publisher'
		, 'dc:contributor'
		, 'dc:type'
		, 'dc:format'
		, 'dc:identifier'
		, 'dc:source'
		, 'dc:relation'
		, 'dc:coverage'
		, 'dc:rights'
		, 'ev:location'
		, 'ev:type'
		)))
			$this->_prepString($elementContent);
		elseif (in_array(strtolower($elementName), array('comments', 'docs', 'link', 'url')))
			$this->_prepLink($elementContent);
		$this->elementName = $elementName;
		$this->elementContent = $elementContent;
		if ($attributes)
		{
			if (is_array($attributes))
			{
				foreach ($attributes as $attrKey => $attrValue)
				{
					if ($attrKey == strtolower('url'))
						$this->_prepLink($attrValue);
				}
			}
			$this->attributes = $attributes;
		}
		else
			$this->attributes = array();

		// echo get_class ( $this ); echo "<br />\n";// test ###
		// print_r( $this ); echo "<br />\n";
	}

	/** addElement
	 *
	 * add new (optional) element
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $elementName
	 * @param string $elementContent
	 * @param array  $attributes
	 *
	 * @return void
	 * @uses   function rsscalElement::rsscalElement
	 */
	function addElement($elementName, $elementContent, $attributes = null)
	{
		$this->elementContent[] = new rsscalElement($elementName, $elementContent, $attributes);
	}

	/** createOutput
	 *
	 * edit rsscalElement properties
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $newlinechar
	 *
	 * @return string
	 */
	function createOutput($newlinechar)
	{
		// echo get_class ( $this ); echo "<br />\n";// trdf:resource="http://www.oreilly.com/catalog/progxmlrpc/"est ###
		// print_r( $this ); echo "<br />\n";
		$str = null;
		if (!empty($this->elementName))
			$str .= '<' . $this->elementName;
		if (isset($this->attributes) && (0 < count($this->attributes)))
		{
			foreach ($this->attributes as $attrKey => $attrValue)
				$str .= $newlinechar . ' ' . $attrKey . '="' . $attrValue . '"';
		}
		if (!empty($this->elementName) && empty($this->elementContent))
			$str .= ' />' . $newlinechar;
		else
		{
			if (!empty($this->elementName))
				$str .= '>';
			if (is_array($this->elementContent))
			{
				if (!empty($this->elementName))
					$str .= $newlinechar;
				$endix = count($this->elementContent) - 1;
				foreach ($this->elementContent as $cix => $elementContentPart)
				{ // new group, repeat name
					if (is_array($elementContentPart))
					{
						if ((0 < $cix) && !empty($this->elementName))
							$str .= '<' . $this->elementName . '>' . $newlinechar;
						foreach ($elementContentPart as $elementContentPart2)
						{     // new element within group
							$str .= $elementContentPart2->createOutput($newlinechar);
						}
						if (($cix < $endix) && !empty($this->elementName))
							$str .= '</' . $this->elementName . '>' . $newlinechar;
					}
					else
					{
						$str .= $elementContentPart->createOutput($newlinechar);
					}
				}
			}
			elseif (is_object($this->elementContent))
			{
				$str .= $newlinechar;
				$str .= $this->elementContent->createOutput($newlinechar);
			}
			else
			{
				$str .= $this->elementContent;
			}
			if (!empty($this->elementName))
				$str .= '</' . $this->elementName . '>' . $newlinechar;
		}
		return $str;
	}
}

/**
 * rsscalChannel class
 *
 * class, manage Channel elements
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.6.0 - 2006-11-09
 */
class rsscalchannel extends rsscalElement
{

	/**
	 * rsscalChannel
	 *
	 * constructor for rsscalChannel object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $title       - required for element channel
	 * @param string $link        - required for element channel
	 * @param string $description - required for element channel
	 * @param array  $resource    - optional for element channel, link is used if missing
	 *
	 * @return void
	 * @uses   function rsscalElement::rsscalElement
	 */
	function __construct($title, $link, $description, $resource = null)
	{
		$this->elementName = 'channel';
		$this->elementContent = array();
		$this->elementContent[] = new rsscalElement('title', $title);       // required
		$this->elementContent[] = new rsscalElement('link', $link);        // required
		$this->elementContent[] = new rsscalElement('description', $description); // optional
		if (isset($resource))
			$this->attributes = array($resource);
	}
}

/**
 * rsscalImage class
 *
 * class, manage optional channel element image
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.6.0 - 2006-11-09
 */
class rsscalImage extends rsscalElement
{
	/**
	 * rsscalImage
	 *
	 * constructor for rsscalImage object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $title - required for element image
	 * @param string $url   - required for element image
	 * @param string $link  - required for element image
	 *
	 * @return void
	 * @uses   function rsscalElement::rsscalElement
	 */
	function __construct($title, $url, $link)
	{ // all required
		$this->elementName = 'image';
		$this->elementContent = array(new rsscalElement('title', $title) // required
		, new rsscalElement('url', $url)   // required
		, new rsscalElement('link', $link)  // required
		);
	}
}

/**
 * rsscalItem class
 *
 * class, manages Item elements
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.6.0 - 2006-11-09
 */
class rsscalItem extends rsscalElement
{
	/**
	 * rsscalItem
	 *
	 * constructor for rsscalItem object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $title       - required for element image
	 * @param string $link        - required for element image
	 * @param string $description - optional for element image
	 *
	 * @return void
	 * @uses   function rsscalElement::rsscalElement
	 */
	function __construct($title, $link, $description = null)
	{
		$this->elementName = 'item';
		$this->elementContent = array();
		$this->elementContent[] = new rsscalElement('title', $title);       // required
		$this->elementContent[] = new rsscalElement('link', $link);        // required
		if ($description)
			$this->elementContent[] = new rsscalElement('description', $description); // optional
	}
}

/**
 * rsscalTextinput class
 *
 * class, manages optional channel element textinput
 *
 * rsscalTextinput class
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.6.0 - 2006-11-09
 */
class rsscalTextinput extends rsscalElement
{
	/**
	 * rsscalTextinput
	 *
	 * constructor for rsscalTextinput object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $title       - required for element textinput
	 * @param string $description - required for element textinput
	 * @param string $name        - required for element textinput
	 * @param string $link        - required for element textinput
	 *
	 * @return void
	 * @uses   function rsscalElement::rsscalElement
	 */
	function __construct($title, $description, $name, $link)
	{
		$this->elementName = 'textinput';
		$this->elementContent = array(new rsscalElement('title', $title)
		, new rsscalElement('description', $description)
		, new rsscalElement('name', $name)
		, new rsscalElement('link', $link)
		);
	}
}

/**
 * rsscalVersion class
 *
 * class, manages unknown rsscal version
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.6.0 - 2006-11-09
 */
class rsscalVersion
{
	var $version;
	var $content;     // anonymous rsscalElement
	var $channel;     // rsscalElement
	var $image;       // rsscalElement
	var $items;       // array, rsscalElements
	var $textinput;   // rsscalElement
	var $newlinechar;

	/**
	 * rsscalVersion
	 *
	 * constructor for rsscalVersion object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.3 - 2006-12-19
	 *
	 * @param object $rsscalCreator
	 *
	 * @return void
	 */
	function __construct($rsscalCreator)
	{
		$this->version = $rsscalCreator->version;
		$this->content = null;
		$this->channel = $rsscalCreator->channel;
		if (isset($this->channel->attributes))
			unset($this->channel->attributes);
		foreach ($this->channel->elementContent as $cix => $channelPart)
		{
			if (!in_array(strtolower($channelPart->elementName)
				, array('title', 'link', 'description'))
			)
				unset($this->channel->elementContent[$cix]);
			else
				unset($this->channel->elementContent[$cix]->attributes);
		}
		$this->image = $rsscalCreator->image;
		if (isset($this->image))
		{
			foreach ($this->image->elementContent as $cix => $imagePart)
			{
				if (!in_array(strtolower($imagePart->elementName)
					, array('title', 'url', 'link'))
				)
					unset($this->image->elementContent[$cix]);
				else
					unset($this->image->elementContent[$cix]->attributes);
			}
		}
		$this->items = $rsscalCreator->items;
		foreach ($this->items as $ix => $item)
		{
			if (!empty($item->elementContent))
			{
				foreach ($item->elementContent as $ix2 => $element)
				{
					if (!in_array(strtolower($element->elementName)
						, array('title', 'link', 'description'))
					)
						unset($this->items[$ix]->elementContent[$ix2]);
					else
						unset($this->items[$ix]->elementContent[$ix2]->attributes);
				}
			}
		}
		$this->textinput = $rsscalCreator->textinput;
		if (isset($this->textinput))
		{
			foreach ($this->textinput->elementContent as $tiix => $textinputPart)
			{
				if (!in_array(strtolower($textinputPart->elementName)
					, array('title', 'link', 'name', 'description'))
				)
					unset($this->textinput->elementContent[$tiix]);
				else
					unset($this->textinput->elementContent[$tiix]->attributes);
			}
		}
		$this->newlinechar = $rsscalCreator->newlinechar;
	}

	/**
	 * createOutput
	 *
	 * edit rsscalVersion properties
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.6.0 - 2006-11-09
	 *
	 * @param string $newlinechar
	 *
	 * @return string
	 * @uses   function rsscalElement::rsscalElement
	 */
	function createOutput($newlinechar = FALSE)
	{
		if ($newlinechar)
			$this->newlinechar = $newlinechar;
		$output = '<?xml version="1.0" encoding="' . ENCODING . '"?>' . $this->newlinechar;
		if (!isset($this->content))
		{
			$this->content = new rsscalElement('rdf:RDF');
			$this->content->attributes['xmlns:rdf'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
			$this->content->attributes['xmlns'] = 'http://purl.org/rss/1.0/';
		}
		$content = array();
		$content[] = $this->channel;
		if (isset($this->image))
			$content[] = $this->image;
		foreach ($this->items as $ix => $item)
			$content[] = $item;
		if (isset($this->textinput))
			$content[] = $this->textinput;
		$this->content->elementContent = new rsscalElement(null, $content);
		$output .= $this->content->createOutput($this->newlinechar);
		return $output;
	}
}

/**
 * rsscalVersion1_0 class
 *
 * class, manages rsscal version 1.0
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.6.0 - 2006-11-09
 */
class rsscalVersion1_0 extends rsscalVersion
{
	/**
	 * rsscalVersion1_0
	 *
	 * constructor for rsscalVersion1_0 object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.3 - 2006-12-19
	 *
	 * @param object $rsscalCreator
	 *
	 * @return void
	 * @uses   function rsscalElement::rsscalElement
	 */
	function __construct($rsscalCreator)
	{
		$this->version = $rsscalCreator->version;
		$this->content = new rsscalElement('rdf:RDF');
		$this->content->attributes['xmlns:rdf'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
		$this->content->attributes['xmlns:ev'] = 'http://purl.org/rss/1.0/modules/event/';
		$this->content->attributes['xmlns:dc'] = 'http://purl.org/dc/elements/1.1/';
		$this->content->attributes['xmlns'] = 'http://purl.org/rss/1.0/';
		$this->channel = $rsscalCreator->channel;
		// echo "1 : ";   print_r( $this->channel ); echo "<br />\n"; // test ###
		if (0 >= count($this->channel->attributes))
		{
			foreach ($this->channel->elementContent as $channelElement)
			{
				// print_r( $channelElement ); echo "<br />\n"; // test ###
				if ('link' != strtolower($channelElement->elementName))
					continue;
				$this->channel->attributes = array('rdf:about' => $channelElement->elementContent);
				break;
			}
		}
		else
		{
			$this->channel->attributes = array('rdf:about' => reset($rsscalCreator->channel->attributes));
		}
		foreach ($this->channel->elementContent as $ccix => $channelElement)
		{
			if (in_array(strtolower($channelElement->elementName)
				, array('title', 'link', 'description')))
				unset($this->channel->elementContent[$ccix]->attributes);
		}
		if (isset($rsscalCreator->image) && is_object($rsscalCreator->image))
		{
			$this->image = $rsscalCreator->image;
			foreach ($this->image->elementContent as $iix => $imageElement)
			{
				if (in_array(strtolower($imageElement->elementName)
					, array('title', 'url', 'link')))
					unset($this->image->elementContent[$iix]->attributes);
				if ('link' != strtolower($imageElement->elementName))
					continue;
				$this->channel->elementContent[] =
					new rsscalElement('image', null, array('rdf:resource' => $imageElement->elementContent));
				$this->image->attributes['rdf:about'] = $imageElement->elementContent;
				break;
			}
		}
		$this->items = $rsscalCreator->items;
		$list = new rsscalElement();
		foreach ($this->items as $ix => $item)
		{
			if (!empty($item->elementContent))
			{
				foreach ($item->elementContent as $ix2 => $element)
				{
					if (in_array(strtolower($element->elementName)
						, array('title', 'link', 'description')))
						unset($this->items[$ix]->elementContent[$ix2]->attributes);
					if ('link' != strtolower($element->elementName))
						continue;
					$list->elementContent[] = new rsscalElement('rdf:li'
						, null
						, array('rdf:resource' => $element->elementContent));
					$this->items[$ix]->attributes['rdf:about'] = $element->elementContent;
					break;
				}
			}
		}
		if (0 < count($list->elementContent))
		{
			$rdf_Seq = new rsscalElement ('rdf:Seq', $list);
			$this->channel->elementContent[] = new rsscalElement ('items', $rdf_Seq);
		}
		if (isset($rsscalCreator->textinput) && is_object($rsscalCreator->textinput))
		{
			$this->textinput = $rsscalCreator->textinput;
			foreach ($this->textinput->elementContent as $tiix => $textinputElement)
			{
				if (in_array(strtolower($textinputElement->elementName)
					, array('title', 'link', 'name', 'description')))
					unset($this->textinput->elementContent[$tiix]->attributes);
				if ('link' != strtolower($textinputElement->elementName))
					continue;
				$this->channel->elementContent[] =
					new rsscalElement('textinput', null, array('rdf:resource' => $textinputElement->elementContent));
				$this->textinput->attributes['rdf:about'] = $textinputElement->elementContent;
				break;
			}
		}
		$this->newlinechar = $rsscalCreator->newlinechar;
	}
}

/**
 * rsscalVersion2_0 class
 *
 * class, manages rsscal version 2.0
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  0.8.3 - 2006-12-19
 */
class rsscalVersion2_0 extends rsscalVersion
{
	/**
	 * rsscalVersion2_0 RSS
	 *
	 * constructor for rsscalVersion2_0 object
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.3 - 2006-12-19
	 *
	 * @param object $rsscalCreator
	 *
	 * @return void
	 * @uses   function rsscalElement::rsscalElement
	 */
	function __construct($rsscalCreator)
	{
		$this->content = new rsscalElement('rss');
		$version = str_replace('_', '.', $rsscalCreator->version);
		$this->content->attributes['version'] = $version;
		$this->content->attributes['xmlns:ev'] = 'http://purl.org/rss/1.0/modules/event/';
		$this->content->attributes['xmlns:dc'] = 'http://purl.org/dc/elements/1.1/';
		$this->channel = $rsscalCreator->channel;
		foreach ($this->channel->elementContent as $ccix => $channelElement)
		{
			if (in_array(strtolower($channelElement->elementName)
				, array('title', 'link', 'description')))
				unset($this->channel->elementContent[$ccix]->attributes);
			if (strtolower($channelElement->elementName) == 'generator')
				unset($this->channel->elementContent[$ccix]);
		}
		$this->channel->elementContent[] = new rsscalElement('generator', RSSCALCALCREATOR_VERSION);
		if (isset($rsscalCreator->image) && is_object($rsscalCreator->image))
		{
			$this->image = $rsscalCreator->image;
			foreach ($this->image->elementContent as $iix => $imageElement)
			{
				if (in_array(strtolower($imageElement->elementName)
					, array('title', 'url', 'link')))
					unset($this->image->elementContent[$iix]->attributes);
			}
		}
		if (isset($rsscalCreator->textinput) && is_object($rsscalCreator->textinput))
		{
			$this->textinput = $rsscalCreator->textinput;
			foreach ($this->textinput->elementContent as $tiix => $textinputElement)
			{
				if (in_array(strtolower($textinputElement->elementName)
					, array('title', 'link', 'name', 'description')))
					unset($this->textinput->elementContent[$tiix]->attributes);
			}
		}
		$this->items = $rsscalCreator->items;
		foreach ($this->items as $ix => $item)
		{
			if (!empty($item->elementContent))
			{
				foreach ($item->elementContent as $ix2 => $element)
				{
					if (in_array(strtolower($element->elementName)
						, array('title', 'link', 'description')))
						unset($this->items[$ix]->elementContent[$ix2]->attributes);
				}
			}
		}
		$this->newlinechar = $rsscalCreator->newlinechar;
	}

	/**
	 * createOutput
	 *
	 * edit rsscalVersion 2.0 properties
	 *
	 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
	 * @since  0.8.3 - 2006-12-19
	 *
	 * @param string $newlinechar
	 *
	 * @return string
	 * @uses   function rsscalElement::rsscalElement
	 */
	function createOutput($newlinechar = FALSE)
	{
		if ($newlinechar)
			$this->newlinechar = $newlinechar;
		$output = '<?xml version="1.0" encoding="' . ENCODING . '"?>' . $this->newlinechar;
		if (isset($this->image))
			$this->channel->elementContent[] = $this->image;
		if (isset($this->textinput))
			$this->channel->elementContent[] = $this->textinput;
		foreach ($this->items as $ix => $item)
			$this->channel->elementContent[] = $item;
		$this->content->elementContent = $this->channel;
		$output .= $this->content->createOutput($this->newlinechar);
		return $output;
	}
}
