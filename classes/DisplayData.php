<?php

abstract class DisplayData {
	public $fields = null;
	public $fields_start = null;
	public $data = null;
	public $control_label1 = null;
	public $control_value1 = null;
	public $control_label2 = null;
	public $control_value2 = null;
	public $description = null;
	
	public $tpl;
	public $module_name;
	
    public function __construct($tpl, $module_name) {
        $this->tpl = $tpl;
        $this->module_name = $module_name;        
    }
	
	public function display() {

	    //$tpl = $this->getTemplatePath('orb.tpl');
	    
	    if ($this->fields == null) {
	        $error = $this->l('Please define fields for displaying', 'andiocontrolling');
	    }

	    if ($this->fields_start == null) {
	        $error = $this->l('Please define columns, rows and data fields for displaying', 'andiocontrolling');
	    }
	    		
	    if ($this->data == null) {
	        $error = $this->l('Please create the data rows', 'andiocontrolling');
	    }
	    
	    if ($this->control_label1 == null) {
	        $this->control_label1 = '';
	        $this->control_value1 = '';
	    }
	    
	    if ($this->control_label2 == null) {
	        $this->control_label2 = '';
	        $this->control_value2 = '';
	    }	    
	    
	    if ($this->description == null) {
	    		$this->description = '';
	    	}
	    		
   		$this->tpl->assign(array(
			'data' => $this->data,
			'fields' => $this->fields,
			'fields_start' => $this->fields_start,
			'control_label1' => $this->control_label1,
			'control_value1' => $this->control_value1,
			'control_label2' => $this->control_label2,
			'control_value2' => $this->control_value2,
			'description' => $this->description,
			'error' => isset($error) ? $error : null,
		));

		return $this->tpl->fetch();
		
	}
	
	private function getTemplatePath($tpl_name) {
	    $tpl_path = _MODULE_DIR_.$this->module_name.'/views/templates/admin/'.$tpl_name;
	    $tpl = $this->smarty->createTemplate($tpl_path, $this->smarty);    
	    return $tpl;
	}
	
	
    /**
     * Non-static method which uses AdminController::translate()
     *
     * @param string  $string Term or expression in english
     * @param string|null $class Name of the class
     * @param bool $addslashes If set to true, the return value will pass through addslashes(). Otherwise, stripslashes().
     * @param bool $htmlentities If set to true(default), the return value will pass through htmlentities($string, ENT_QUOTES, 'utf-8')
     * @return string The translation if available, or the english default text.
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true) {
        if ($class === null || $class == 'AdminTab') {
            $class = substr(get_class($this), 0, -10);
        } elseif (strtolower(substr($class, -10)) == 'controller') {
            /* classname has changed, from AdminXXX to AdminXXXController, so we remove 10 characters and we keep same keys */
            $class = substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
?>