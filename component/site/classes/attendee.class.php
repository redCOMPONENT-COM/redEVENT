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

defined('_JEXEC') or die('Restricted access');

class REattendee {
	
	protected $_username;
	
	protected $_fullname;
	
	protected $_email;
	
	protected $_id;
	
	public function __construct($id = null)
	{
		if ($id) {
			$this->setId($id);
		}
	}
	
	public function setUsername($name)
	{
		$this->_username = $name;
	}
	
  public function getUsername()
  {
    return $this->_username;
  }
	
	public function setFullname($name)
	{
    $this->_fullname = $name;		
	}
	
  public function getFullname()
  {
    return $this->_fullname; 
  }
	
	public function setEmail($email)
	{
    $this->_email= $email;		
	}
  
  public function getEmail()
  {
    return $this->_email; 
  }

  public function setId($id)
  {
    $this->_id = (int) $id; 
  }
  
  public function getId()
  {
    return $this->_id; 
  }
}
