<?php


class AdminFunnelController extends ModuleAdminController {
    public function __construct() {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'Funnel';
        $this->table = 'controlling_funnel';

        $this->fields_list = array(
            'id_controlling_funnel'    => array(
                'title' => $this->l('ID'),
                'type'  => 'hidden',
                'class' => 'fixed-width-xs'
            ),
            'funnel_name'    => array(
                'title' => $this->l('Funnel name'),
                'type' => 'text',

            ),
            'n1'    => array(
                'title' => $this->l('Step1 source'),
                'filter_key' => 'id_step1_source',
                'havingFilter' => true
            ),
            'n2'    => array(
                'title' => $this->l('Step2 source'),
                'filter_key' => 'id_step2_source',
                'havingFilter' => true
            ),
            'n3'    => array(
                'title' => $this->l('Step3 source'),
                'filter_key' => 'id_step3_source',
                'havingFilter' => true
            ),
            'n4'    => array(
                'title' => $this->l('Cart source'),
                'filter_key' => 'id_cart_source',
                'havingFilter' => true
            ),        
            'n5'    => array(
                'title' => $this->l('Order source'),
                'filter_key' => 'id_order_source',
                'havingFilter' => true
            ),                            
        );
        parent::__construct();     

    }
    
    public function init()
    {
        parent::init();

        if (Tools::isSubmit('addcontrolling_supplier')) {
            $this->lang = false;

            $this->action = 'new';
            $this->display = 'add';
        }
    }    
    
/**
     * AdminController::renderList() override
     * @see AdminController::renderList()
     */
    public function renderList()
    {
        // removes links on rows
        $this->list_no_link = true;

        // adds actions on rows
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('duplicate');

        // query: select
        $this->_select = '
			a.id_controlling_funnel, funnel_name, 
        	fs1.step_name as n1, fs1.funnel_source_type as st1, fs1.funnel_source as s1, fs1.id_funnel_step_source as id1,
        	fs2.step_name as n2, fs2.funnel_source_type as st2, fs2.funnel_source as s2, fs2.id_funnel_step_source as id2,
        	fs3.step_name as n3, fs3.funnel_source_type as st3, fs3.funnel_source as s3, fs3.id_funnel_step_source as id3,
        	fs4.step_name as n4, fs4.funnel_source_type as st4, fs4.funnel_source as s4,  fs4.id_funnel_step_source as id4,
        	fs5.step_name as n5, fs5.funnel_source_type as st5, fs5.funnel_source as s5,  fs5.id_funnel_step_source as id5';

        // query: join
        $this->_join = '
			left join ps_controlling_funnel_step_source fs1 on a.id_step1_source = fs1.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs2 on a.id_step2_source = fs2.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs3 on a.id_step3_source = fs3.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs4 on a.id_cart_source = fs4.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs5 on a.id_order_source = fs5.id_funnel_step_source';
            
	    $this->_orderBy = 'a.position';
        $this->_use_found_rows = true;

        return parent::renderList();
    }
    
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_funnel'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new funnel', null, null, false),
                'icon' => 'process-icon-new'
            );
        }

        parent::initPageHeaderToolbar();
    }        
    
    public function setMedia() {
        parent::setMedia();
        
        $this->context->controller->addCSS(__PS_BASE_URI__.'modules/andiocontrolling/css/funnel.css');
        $this->context->controller->addJS(__PS_BASE_URI__.'modules/andiocontrolling/js/funnelstep.js');
    }
/**
	 * AdminController::initContent() override
	 * @see AdminController::initContent()
	 */
	public function initContent()
	{
	    if (Tools::isSubmit('updatecontrolling_funnel') ) {
	        $id_controlling_funnel = (int)Tools::getValue('id_controlling_funnel');
	        $funnel = new Funnel($id_controlling_funnel);
	        
	        $source_types = array(
	            '0' => 'Email',
	            '1' => $this->l('FB ad'),
	            '2' => $this->l('Manual'),
	            '3' => $this->l('Adwords ad'),
	            '4' => $this->l('Website'),
	            '5' => $this->l('UTM parameter'),
	            '6' => $this->l('Coupons'));
	            
	        $step1data = $this->getStepSource($funnel->id_step1_source);    
	        $step2data = $this->getStepSource($funnel->id_step2_source);
	        $step3data = $this->getStepSource($funnel->id_step3_source);
	        $step4data = $this->getStepSource($funnel->id_cart_source);
	        $step5data = $this->getStepSource($funnel->id_order_source);
	        
	        if ($step4data->funnel_source_type == 5) {
	            $source = unserialize($step4data->funnel_source);
	            $step4data->funnel_source = $source['metrics'];
	            $step4data->dimensions = $source['dimensions'];
	            $step4data->filter = $source['filter'];
	        } 
	        if ($step5data->funnel_source_type == 5) {
	            $source = unserialize($step5data->funnel_source);
	            $step5data->funnel_source = $source['metrics'];
	            $step5data->dimensions = $source['dimensions'];
	            $step5data->filter = $source['filter'];
	        } 
	        
            // defines the fields of the form to display
    		$this->fields_form[]['form'] = array(
    			'legend' => array(
    				'title' => $this->l('Define the funnel steps'),
    				'image' => '../img/admin/cms.gif'
    			)
    		);
    		
    		// loads languages
    		$this->getlanguages();

    		// sets up the helper
    		$helper = new HelperForm();
    		$helper->submit_action = 'submit';
    		$helper->currentIndex = self::$currentIndex;
    		$helper->toolbar_btn = $this->toolbar_btn;
    		$helper->toolbar_scroll = false;
    		$helper->token = $this->token;
    		$helper->id = null; // no display standard hidden field in the form
    		$helper->languages = $this->_languages;
    		$helper->default_form_language = $this->default_form_language;
    		$helper->allow_employee_form_lang = $this->allow_employee_form_lang;
    		//$helper->title = $this->l('Definie the funnel steps');
    
    		$helper->override_folder = 'funnelstep/';
    		// assigns our content
		    $helper->tpl_vars['id_controlling_funnel'] = $id_controlling_funnel;
		    $helper->tpl_vars['funnel_name'] = $funnel->funnel_name;
		    $helper->tpl_vars['date_add'] = $funnel->date_add;
		    $helper->tpl_vars['position'] = $funnel->position;
		    $helper->tpl_vars['source_types'] = $source_types;
		    $helper->tpl_vars['step1data'] = $step1data;
		    $helper->tpl_vars['step2data'] = $step2data;
		    $helper->tpl_vars['step3data'] = $step3data;
		    $helper->tpl_vars['step4data'] = $step4data;
		    $helper->tpl_vars['step5data'] = $step5data;
		    $helper->base_tpl_form = 'form.tpl';
		
    		// generates the form to display
    		$content = $helper->generateForm($this->fields_form);
    
    		$this->context->smarty->assign(array(
    			'content' => $content,
    			'url_post' => self::$currentIndex.'&token='.$this->token,
    		));
    		//$this->display();
    		//$this->tpl_form_vars['id_order'] = $id_ordunneler;
    
    	} elseif (Tools::isSubmit('duplicatecontrolling_funnel') ) {
    	    //Logger::addLog('duplicate ' . Tools::getValue('id_controlling_funnel'));
    	    $id_controlling_funnel = (int)Tools::getValue('id_controlling_funnel');
    	    $funnel_old = new Funnel($id_controlling_funnel);
    	    $funnel_new = new Funnel();
    	    $funnel_new->funnel_name = $funnel_old->funnel_name . ' duplicate'; 
    	    $funnel_new->position = $funnel_old->position;
    	    $funnel_new->date_add = date('Y-m-d');
    	    $funnel_new->display = 1;
    	    $funnel_new->id_step1_source = $this->duplicateFunnelSourceStep($funnel_old->id_step1_source);
    	    $funnel_new->id_step2_source = $this->duplicateFunnelSourceStep($funnel_old->id_step2_source);
    	    $funnel_new->id_step3_source = $this->duplicateFunnelSourceStep($funnel_old->id_step3_source);
    	    $funnel_new->id_cart_source = $this->duplicateFunnelSourceStep($funnel_old->id_cart_source);
    	    $funnel_new->id_order_source = $this->duplicateFunnelSourceStep($funnel_old->id_order_source);
    	    $funnel_new->save();
    	    parent::initContent();   
    	    
    	} else {
		// call parent initcontent to render standard form content
		    parent::initContent();    	
		}
	}
	
	private function duplicateFunnelSourceStep($id_funnel_step_source) {
	    $step_old = new FunnelSourceStep($id_funnel_step_source);
	    $step_new = new FunnelSourceStep(0);
	    $step_new->step_name = $step_old->step_name;
	    $step_new->funnel_source_type = $step_old->funnel_source_type;
	    $step_new->funnel_source = $step_old->funnel_source;
	    $step_new->save();
	    $id = Db::getInstance()->Insert_ID();
	    return $id;
	}
	
	private function getStepSource($id_funnel_step_source) {
	    if (!isset($id_funnel_step_source) || $id_funnel_step_source == '') {
	        $id_funnel_step_source = '';
	    }
	    $funnel_source = new FunnelSourceStep($id_funnel_step_source);
	    return $funnel_source;
	}    
	
	/**
	 * AdminController::postProcess() override
	 * @see AdminController::postProcess()
	 */
	public function postProcess()
	{
	    //if (Tools::isSubmit('updatecontrolling_funnel') ) {
	    if (Tools::isSubmit('submitupdatecontrolling_funnel') ) {
	        $id_step1_source = 0;
	        $id_step2_source = 0;
	        $id_step3_source = 0;
	        $id_step4_source = 0;
	        $id_step5_source = 0;
	        
	        $id_controlling_funnel = Tools::getValue('id_controlling_funnel');
            Logger::addLog('AdminFunnel saving... source ' . $id_controlling_funnel);   	    
            $funnel = new Funnel($id_controlling_funnel);
            
            
            // save step sources
            $id = Tools::getValue('id_step1_source');
            //Logger::addLog( 'AdminFunnel source step id: (' . $id .") name " . Tools::getValue('step1_name'));  
            $source_step1 = new FunnelSourceStep($id); 
            $source_step1->step_name = Tools::getIsset('step1_name') ? Tools::getValue('step1_name')  : '';
            $source_step1->funnel_source_type = Tools::getIsset('step1_source_type') ? Tools::getValue('step1_source_type')  : 0;
            $source_step1->funnel_source = Tools::getIsset('step1_funnel_source') ? Tools::getValue('step1_funnel_source')  : '';
            if ($source_step1->step_name != '') {
                $source_step1->save();
                $id_step1_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;    
                //Logger::addLog('New AdminFunnel source step 1 id: ' . $id_step1_source . "(" . $id .")");  
            }
            
            // save step sources
            $id = Tools::getValue('id_step2_source');
            //Logger::addLog( 'AdminFunnel source step id: (' . $id .") name " . Tools::getValue('step2_name'));  
            $source_step2 = new FunnelSourceStep($id); 
            $source_step2->step_name = Tools::getIsset('step2_name') ? Tools::getValue('step2_name')  : '';
            $source_step2->funnel_source_type = Tools::getIsset('step2_source_type') ? Tools::getValue('step2_source_type')  : 0;
            $source_step2->funnel_source = Tools::getIsset('step2_funnel_source') ? Tools::getValue('step2_funnel_source')  : '';
            if ($source_step2->step_name != '') {
                $source_step2->save();
                $id_step2_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;                
                //Logger::addLog('New AdminFunnel source step 2 id: ' . $id_step2_source . "(" . $id .")");  
            }
            
            // save step sources
            $id = Tools::getValue('id_step3_source');
            Logger::addLog( 'AdminFunnel source step id: (' . $id .") name " . Tools::getValue('step3_name'));  
            $source_step3 = new FunnelSourceStep($id); 
            $source_step3->step_name = Tools::getIsset('step3_name') ? Tools::getValue('step3_name')  : '';
            $source_step3->funnel_source_type = Tools::getIsset('step3_source_type') ? Tools::getValue('step3_source_type')  : 0;
            $source_step3->funnel_source = Tools::getIsset('step3_funnel_source') ? Tools::getValue('step3_funnel_source')  : '';
            if ($source_step3->step_name != '') {
                $source_step3->save();
                $id_step3_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;                
                //Logger::addLog('New AdminFunnel source step 2 id: ' . $id_step3_source . "(" . $id .")");  
            }
            
            $id = Tools::getValue('id_step4_source');
            Logger::addLog( 'AdminFunnel source step id: (' . $id .") name " . Tools::getValue('step4_name'));  
            $source_step4 = new FunnelSourceStep($id); 
            $source_step4->step_name = Tools::getIsset('step4_name') ? Tools::getValue('step4_name')  : '';
            $source_step4->funnel_source_type = Tools::getIsset('step4_source_type') ? Tools::getValue('step4_source_type')  : 0;
            if ($source_step4->funnel_source_type == 5 || $source_step4->funnel_source_type == 4) {
                $source_step4->funnel_source = $this->getSerializedUtmParams('step4_funnel_source', 'step4_dimension', 'step4_filter');
            } else {
                $source_step4->funnel_source = Tools::getIsset('step4_funnel_source') ? Tools::getValue('step4_funnel_source')  : '';
            }
            if ($source_step4->step_name != '') {
                $source_step4->save();
                $id_step4_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;                
                //Logger::addLog('New AdminFunnel source step 2 id: ' . $id_step4_source . "(" . $id .")");  
            }
            
            $id = Tools::getValue('id_step5_source');
            Logger::addLog( 'AdminFunnel source step id: (' . $id .") name " . Tools::getValue('step5_name'));  
            $source_step5 = new FunnelSourceStep($id); 
            $source_step5->step_name = Tools::getIsset('step5_name') ? Tools::getValue('step5_name')  : '';
            $source_step5->funnel_source_type = Tools::getIsset('step5_source_type') ? Tools::getValue('step5_source_type')  : 0;
            if ($source_step5->funnel_source_type == 5 || $source_step5->funnel_source_type == 4) {
                $source_step5->funnel_source = $this->getSerializedUtmParams('step5_funnel_source', 'step5_dimension', 'step5_filter');
            } else {
                $source_step5->funnel_source = Tools::getIsset('step5_funnel_source') ? Tools::getValue('step5_funnel_source')  : '';
            }
            if ($source_step5->step_name != '') {
                $source_step5->save();
                $id_step5_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;                
                //Logger::addLog('New AdminFunnel source step 2 id: ' . $id_step5_source . "(" . $id .")");  
            }
                        
            
            //save funnel data  
            $funnel->id_step1_source = $id_step1_source;  
            $funnel->id_step2_source = $id_step2_source; 
            $funnel->id_step3_source = $id_step3_source; 
            $funnel->id_cart_source = $id_step4_source; 
            $funnel->id_order_source = $id_step5_source; 
            $funnel->position = Tools::getValue('position');
            $funnel->funnel_name = Tools::getValue('funnel_name');
            $funnel->save();

	    }
	    parent::postProcess();
    }	
    
    private function getSerializedUtmParams($elem_metrics, $elem_dimensions, $elem_filter) {
        $metrics = Tools::getIsset($elem_metrics) ? Tools::getValue($elem_metrics)  : '';
        $dimensions = Tools::getIsset($elem_dimensions) ? Tools::getValue($elem_dimensions)  : '';
        $filter = Tools::getIsset($elem_filter) ? Tools::getValue($elem_filter)  : '';
        
        $source = array(
            'metrics' => $metrics,
            'dimensions' => $dimensions,
            'filter' => $filter);
            
        $serialized_source = serialize($source);
        return $serialized_source;    
    }
} 