<?php

class redEVENTcustomHelper {
	
	/**
	 * returns a custom field object according to type
	 *
	 * @param string $type
	 * @return object
	 */
  function getCustomField($type)
  {
    require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'customfield'.DS.'customfield.php');
    switch ($type)
    {
      case 'select':
        return new TCustomfieldSelect();
        break;
        
      case 'select_multiple':
        return new TCustomfieldSelectmultiple();
        break;
        
      case 'date':
        return new TCustomfieldDate();
        break;
        
      case 'radio':
        return new TCustomfieldRadio();
        break;
        
      case 'checkbox':
        return new TCustomfieldCheckbox();
        break;
        
      case 'textarea':
        return new TCustomfieldTextarea();
        break;
        
      case 'textbox':
      default:
        return new TCustomfieldTextbox();
        break;
    }
  }
}
?>