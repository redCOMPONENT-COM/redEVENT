<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKMINIEVENTS') or die('Restricted access');

class RokMiniEvents_Event
{
    /**
     * @var RokMiniEvents_Date
     */
    private $start;

    /**
     * @var RokMiniEvents_Date
     */
    private $end;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $allDay;

    /**
     * @var string
     */
    private $link = '#';


    public function __construct($start, $end, $title, $desciption = '', $link = '#')
    {
        $this->setStart(new RokMiniEvents_Date($start));
        $this->setEnd(new RokMiniEvents_Date($end));
        $this->setTitle($title);
        $this->setDescription($desciption);
        $this->setLink($link);
    }

    public function setFormats($day, $month, $year, $time)
    {
        if (isset($this->start)) $this->start->setFormats($day, $month, $year, $time);
        if (isset($this->end)) $this->end->setFormats($day, $month, $year, $time);
    }

    public function setOffset($offset)
    {
        if (isset($this->start)) $this->start->setOffset($offset);
        if (isset($this->end)) $this->end->setOffset($offset);
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setEnd(RokMiniEvents_Date $end)
    {
        $this->end = $end;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setStart(RokMiniEvents_Date $start)
    {
        $this->start = $start;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;
    }

    public function isAllDay()
    {
        return $this->allDay;
    }
}
