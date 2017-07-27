<?php


class AdminMarketingController extends ModuleAdminController {
    var $modules = null;
    
    public function __construct() {                
        
        /* ****************
         * DEFINE the data tables to display in the controlling system
            name: name of the module. THIS is also the CLASSNAME of the object where the data is visualized! Otherwise the hook wont work
            displayName
            active: true or false
            hook: executable function name for displaying the data of the module
        ******************/
        $this->modules = array (
	        array('name' => 'MarketingData', 'displayName' => $this->l('All marketing data by source'), 'active' => true),	       
	        array('name' => 'CampaignData', 'displayName' => $this->l('Campaign data by source'), 'active' => true),	       
	        array('name' => 'DisplayFunnelData', 'displayName' => $this->l('Funnel data'), 'active' => true),	       
	        array('name' => 'Coupons', 'displayName' => $this->l('Coupons'), 'active' => true),	       
	        array('name' => 'CouponsToday', 'displayName' => $this->l('CouponsToday'), 'active' => true),	       
	        array('name' => 'ProductbyDate', 'displayName' => $this->l('ProductbyDate'), 'active' => true),	  
	        array('name' => 'ProductSaleSearch', 'displayName' => $this->l('ProductSaleSearch'), 'active' => true),	       

	    );	    
	    
	    $this->name = 'andiocontrolling';
	    $this->bootstrap = true;
	    			
        parent::__construct();			
    }
    
    public function init()
	{
		parent::init();
                
		$this->action = 'view';
		$this->display = 'view';	    
		
		$this->context = Context::getContext();
	
	}

	public function initContent()
	{
		$this->initTabModuleList();
		$this->addToolBarModulesListButton();	    
		$this->toolbar_title = $this->l('Controlling - Marketing tables');
		if ($this->display == 'view')
		{
			// Some controllers use the view action without an object
			if ($this->className)
				$this->loadObject(true);
			$this->content .= $this->renderView();
		}
		
		$this->content .= $this->displayMenu();
		//$this->content .= $this->displayCalendar();
		$this->content .= $this->displayStats();


		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,
		));
	}
	
    public function displayMenu()
	{
	    $this->override_folder = 'administration/';
		$tpl = $this->createTemplate('menu.tpl');
		

		$modules = $this->modules;

		$tpl->assign(array(
			'current' => self::$currentIndex,
			'token' => $this->token,
			'modules' => $modules,
			'current_module_name' => ''
		));

		return $tpl->fetch();
	}	
	
    public function displayCalendar()
	{
		$tpl = $this->createTemplate('calendar.tpl');
		$this->override_path = 'administration';

        $date_from = date('Y-m-d',strtotime(date('Y-01-01'))); 
        $date_to = date('Y-m-d');

		$tpl->assign(array(
			'datepickerFrom' => $date_from,
			'datepickerTo' => $date_to
		));

		return $tpl->fetch();
	}		
	
    /**
     * Creates a template object
     *
     * @param string $tpl_name Template filename
     * @return Smarty_Internal_Template
     */
    public function createTemplate($tpl_name)
    {
        $tpl_path = _MODULE_DIR_.$this->module->name.'/views/templates/admin/'.$this->override_folder.$tpl_name;
       
        if (file_exists($tpl_path) && $this->viewAccess()) {

            return $this->context->smarty->createTemplate($tpl_path, $this->context->smarty);
        } elseif (file_exists($this->getTemplatePath().$this->override_folder.$tpl_name) && $this->viewAccess()) {

            return $this->context->smarty->createTemplate($this->getTemplatePath().$this->override_folder.$tpl_name, $this->context->smarty);
        }

        return parent::createTemplate($tpl_name);
    }	

    
    public function displayStats()
	{
		$tpl = $this->createTemplate('stats.tpl');

        $hook = null;
        $error = null;
        
        $module = null;
        $module_name = Tools::getValue('module');
        if (!isset($module_name)) {
           $module = $this->modules[0];
           $module_name = $module['name'];
        } else {
            foreach ($this->modules as $item) {
                if ($module_name == $item['name']) {
                    $module = $item;
                    break;
                }
            }
        }
        
        
		if ($module_name)
		{
			if ($module['active']) {
			    $class_name = $module['name'];
			    $class_file = _PS_CORE_DIR_.'/modules/'.$this->module->name.'/classes/'.$class_name.'.php';
			    
			    if (file_exists($class_file)) {
			        require_once $class_file;
    			    if (class_exists($class_name)) {
    			        $this->override_folder = '';
    			        $orb_tpl = $this->createTemplate('orb.tpl');
    			        $o = new $class_name($orb_tpl, $this->module->name);

    			        $hook = $o->display();
    			    } else {
    			        //throw new PrestaShopException("Class {$class_name} not exists");
    			        $error = "Class {$class_name} not exists";
    			    }
    			} else {
    			    $error = "Class and file {$class_name} not exists";
    			}
			}
		}

		$tpl->assign(array(
			'module_name' => isset($module_name) ? $module_name : null,
			'hook' => isset($hook) ? $hook : null,
			'error' => isset($error) ? $error : null,
		));

		return $tpl->fetch();
	}	
	
    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(_MODULE_DIR_.$this->module->name.'/js/react-0.12.2.min.js');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/js/orb.min.js');
        //$this->addJS(_MODULE_DIR_.$this->module->name.'/js/orb.main.js');        
        
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/css/orb.css', 'all', 0, true); 
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/css/sajat.css', 'all', 0, true); 
    }
    
    public function postProcess()
    {
        $this->context = Context::getContext();
        
        $this->processDateRange();

    }
    
    public function processDateRange()
    {
        if (Tools::isSubmit('submitDatePicker')) {
            if ((!Validate::isDate($from = Tools::getValue('datepickerFrom')) || !Validate::isDate($to = Tools::getValue('datepickerTo'))) || (strtotime($from) > strtotime($to))) {
                $this->errors[] = Tools::displayError('The specified date is invalid.');
            }
        }
        if (isset($from) && isset($to) && !count($this->errors)) {
            $this->context->employee->stats_date_from = $from;
            $this->context->employee->stats_date_to = $to;
            $this->context->employee->update();
            if (!$this->isXmlHttpRequest()) {
                Tools::redirectAdmin($_SERVER['REQUEST_URI']);
            }
        }
    }
} 