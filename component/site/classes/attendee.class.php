<?php

class REattendee {
	
	protected $_username;
	
	protected $_fullname;
	
	protected $_email;
	
	protected $_answer_id;
	
	public function __construct($answer_id = null)
	{
		if ($answer_id) {
			$this->setAnswerId($answer_id);
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

  public function setAnswerId($answer_id)
  {
    $this->_answer_id = (int) $answer_id; 
  }
  
  public function getAnswerId()
  {
    return $this->_answer_id; 
  }
}
?>