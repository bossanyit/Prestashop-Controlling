<?php


class AdminSourceController extends ModuleAdminController {
    public function __construct() {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'ControllingSource';
        $this->table = 'controlling_source';

        $this->fields_list = array(
            'id_controlling_source'    => array(
                'title' => $this->l('ID'),
                'class' => 'fixed-width-xs'
            ),
            'name'    => array(
                'title' => $this->l('Source name'),
                'type' => 'text',

            ),
            'n1'    => array(
                'title' => $this->l('Ad source'),
                'filter_key' => 'id_ad_source',
                'havingFilter' => true
            ),
            'n2'    => array(
                'title' => $this->l('Reg source'),
                'filter_key' => 'id_reg_source',
                'havingFilter' => true
            ),
            'n3'    => array(
                'title' => $this->l('OTO source'),
                'filter_key' => 'id_oto_source',
                'havingFilter' => true
            ),
            'n4'    => array(
                'title' => $this->l('Order source'),
                'filter_key' => 'id_order_source',
                'havingFilter' => true
            ),  
            'position'    => array(
                'title' => $this->l('Position'),
                'type' => 'int',
            ),   
            'collection_rank'    => array(
                'title' => $this->l('Collection order'),
                'type' => 'int',
            ),                                              
        );
        parent::__construct();     

    }
    
    public function init()
    {
        parent::init();

        if (Tools::isSubmit('addcontrolling_source')) {
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
                a.id_controlling_source, name, position, collection_rank,
                  e1.entry_name as n1, e1.id_entry_type as t1, id_ad_source,
                  e2.entry_name as n2, e2.id_entry_type as t2, id_reg_source,
                  e3.entry_name as n3, e3.id_entry_type as t3, id_oto_source,
                  e4.entry_name as n4, e1.id_entry_type as t4, id_order_source';

        // query: join
        $this->_join = '
			left join ps_controlling_source_entrypoint e1 on e1.id_entry = a.id_ad_source
            left join ps_controlling_source_entrypoint e2 on e2.id_entry = a.id_reg_source
            left join ps_controlling_source_entrypoint e3 on e3.id_entry = a.id_oto_source
            left join ps_controlling_source_entrypoint e4 on e4.id_entry = a.id_order_source';
            
	    $this->_orderBy = 'a.position';
        $this->_use_found_rows = true;

        return parent::renderList();
    }
    
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_source'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new source', null, null, false),
                'icon' => 'process-icon-new'
            );
        }

        parent::initPageHeaderToolbar();
    }        
    
    public function setMedia() {
        parent::setMedia();
        
        $this->context->controller->addCSS(__PS_BASE_URI__.'modules/andiocontrolling/css/source.css');
        $this->context->controller->addJS(__PS_BASE_URI__.'modules/andiocontrolling/js/sourcestep.js');
    }
/**
	 * AdminController::initContent() override
	 * @see AdminController::initContent()
	 */
	public function initContent()
	{
	    if (Tools::isSubmit('updatecontrolling_source') ) {
	        $id_controlling_source = (int)Tools::getValue('id_controlling_source');
	        $source = new ControllingSource($id_controlling_source, null, null, null, null, $this);
	        
	        $source_types = Db::getInstance()->executeS('select * from ps_controlling_source_entry_type');
	            
	        $step1data = $this->getSourceEntry($source->id_ad_source);    
	        $step2data = $this->getSourceEntry($source->id_reg_source);
	        $step3data = $this->getSourceEntry($source->id_oto_source);
	        $step4data = $this->getSourceEntry($source->id_order_source);
	        
	        if ($step2data->id_entry_type == 9 || $step2data->id_entry_type == 12) {   
	            $entry = unserialize($step2data->entry_name);
	            $step2data->entry_name = $entry['metrics'];
	            $step2data->dimensions = $entry['dimensions'];
	            $step2data->filter = $entry['filter'];
	        } 
	        
	        if ($step3data->id_entry_type == 10) {   
	            $entry = unserialize($step3data->entry_name);
	            $step3data->entry_name = $entry['coupons'];
	            $step3data->referer = $entry['referer'];
	            $step3data->uri = $entry['uri']; 
	            $step3data->products = $entry['products']; 
	        }
	        
	        if ($step4data->id_entry_type == 10) {   
	            $entry = unserialize($step4data->entry_name);
	            $step4data->entry_name = $entry['coupons'];
	            $step4data->referer = $entry['referer'];
	            $step4data->uri = $entry['uri']; 
	            $step4data->products = $entry['products']; 
	        } 
	        
            // defines the fields of the form to display
    		$this->fields_form[]['form'] = array(
    			'legend' => array(
    				'title' => $this->l('Define the source steps'),
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
    
    		$helper->override_folder = 'source/';
    		// assigns our content
		    $helper->tpl_vars['id_controlling_source'] = $id_controlling_source;
		    $helper->tpl_vars['source_name'] = $source->name;
		    $helper->tpl_vars['date_add'] = $source->date_add;
		    $helper->tpl_vars['position'] = $source->position;
		    $helper->tpl_vars['collection_rank'] = $source->collection_rank;
		    $helper->tpl_vars['source_types'] = $source_types;
		    $helper->tpl_vars['step1data'] = $step1data;
		    $helper->tpl_vars['step2data'] = $step2data;
		    $helper->tpl_vars['step3data'] = $step3data;
		    $helper->tpl_vars['step4data'] = $step4data;

		    $helper->base_tpl_form = 'form.tpl';
		
    		// generates the form to display
    		$content = $helper->generateForm($this->fields_form);
    
    		$this->context->smarty->assign(array(
    			'content' => $content,
    			'url_post' => self::$currentIndex.'&token='.$this->token,
    		));
    		//$this->display();
    		//$this->tpl_form_vars['id_order'] = $id_ordunneler;
    
    	} elseif (Tools::isSubmit('duplicatecontrolling_source') ) {
    	    $id_controlling_source = (int)Tools::getValue('id_controlling_source');
    	    $source_old = new ControllingSource($id_controlling_source, null, null, null, null, $this);
	        $source_new = new ControllingSource(0, null, null, null, null, $this);
	        $source_new->name = $source_old->name . ' duplicate';
	        $source_new->position = $source_old->position;
	        $source_new->collection_rank = $source_old->collection_rank;
	        $source_new->date_add = date('Y-m-d');
	        $source_new->id_ad_source = $this->duplicateControllingSource($source_old->id_ad_source);
	        $source_new->id_reg_source = $this->duplicateControllingSource($source_old->id_reg_source);
	        $source_new->id_oto_source = $this->duplicateControllingSource($source_old->id_oto_source);
	        $source_new->id_order_source = $this->duplicateControllingSource($source_old->id_order_source);
	        $source_new->save();
	        parent::initContent();    	
    	} else {
		// call parent initcontent to render standard form content
		    parent::initContent();    	
		}
	}
	
	private function duplicateControllingSource($id_entry) {
	    $source_entry_old = new SourceEntryPoint($id_entry);
	    $source_entry_new = new SourceEntryPoint(0);
	    $source_entry_new->entry_name = $source_entry_old->entry_name;
	    $source_entry_new->id_entry_type = $source_entry_old->id_entry_type;
	    $source_entry_new->save();
	    $id = Db::getInstance()->Insert_ID();
	    return $id;
	}
	
	private function getSourceEntry($id_step_source) {
	    $entry = new SourceEntryPoint($id_step_source);

	    return $entry;
	}    
	
	/**
	 * AdminController::postProcess() override
	 * @see AdminController::postProcess()
	 */
	public function postProcess()
	{
	    if (Tools::isSubmit('submitupdatecontrolling_source') ) {
	        $id_step1_source = 0;
	        $id_step2_source = 0;
	        $id_step3_source = 0;
	        $id_step4_source = 0;

	        
	        $id_controlling_source = Tools::getValue('id_controlling_source');
            Logger::addLog('AdminSource saving... source ' . $id_controlling_source);   	    
            $source = new ControllingSource($id_controlling_source, null, null, null, null, $this);
            
            
            // save step sources
            $id = Tools::getValue('id_step1_source');
            //Logger::addLog( 'AdminFunnel source step id: (' . $id .") name " . Tools::getValue('step1_name'));  
            $source_step1 = new SourceEntryPoint($id); 
            $source_step1->id_entry_type = Tools::getIsset('step1_source_type') ? Tools::getValue('step1_source_type')  : 0;
            $source_step1->entry_name = Tools::getIsset('step1_source') ? Tools::getValue('step1_source')  : '';
            if ($source_step1->entry_name != '') {
                $source_step1->save();
                $id_step1_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;    
            }
            
            // save step sources
            $id = Tools::getValue('id_step2_source');
            $source_step2 = new SourceEntryPoint($id); 
            $source_step2->id_entry_type = Tools::getIsset('step2_source_type') ? Tools::getValue('step2_source_type')  : 0;          
            if ($source_step2->id_entry_type == 9 || $source_step2->id_entry_type == 12 ) {
                $source_step2->entry_name = $this->getSerializedUtmParams('step2_source', 'step2_dimensions', 'step2_filter');
            } else {
                $source_step2->entry_name = Tools::getIsset('step2_source') ? Tools::getValue('step2_source')  : '';
            }
            if ($source_step2->entry_name != '') {
                $source_step2->save();
                $id_step2_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;    
            }
            
            $id = Tools::getValue('id_step3_source');
            $source_step3 = new SourceEntryPoint($id); 
            $source_step3->id_entry_type = Tools::getIsset('step3_source_type') ? Tools::getValue('step3_source_type')  : 0;
            //$source_step3->entry_name = Tools::getIsset('step3_source') ? Tools::getValue('step3_source')  : '';
            if ($source_step3->id_entry_type == 10 ) {
                $source_step3->entry_name = $this->getSerializedDbParams('step3_source', 'step3_referer', 'step3_uri', 'step3_products');
            } else {
                $source_step3->entry_name = Tools::getIsset('step3_source') ? Tools::getValue('step3_source')  : '';
            }
            if ($source_step3->entry_name != '') {
                $source_step3->save();
                $id_step3_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;    
            }            
            
            $id = Tools::getValue('id_step4_source');
            $source_step4 = new SourceEntryPoint($id); 
            $source_step4->id_entry_type = Tools::getIsset('step4_source_type') ? Tools::getValue('step4_source_type')  : 0;
            //$source_step4->entry_name = Tools::getIsset('step4_source') ? Tools::getValue('step4_source')  : '';
            
            if ($source_step4->id_entry_type == 10 ) {
                $source_step4->entry_name = $this->getSerializedDbParams('step4_source', 'step4_referer', 'step4_uri', 'step4_products');
            } else {
                $source_step4->entry_name = Tools::getIsset('step4_source') ? Tools::getValue('step4_source')  : '';
            }
            
            if ($source_step4->entry_name != '') {
                $source_step4->save();
                $id_step4_source = ($id == 0) ? Db::getInstance()->Insert_ID() : $id;    
            }            
                        
            
            //save funnel data  
            $source->id_ad_source = $id_step1_source;  
            $source->id_reg_source = $id_step2_source; 
            $source->id_oto_source = $id_step3_source; 
            $source->id_order_source = $id_step4_source; 
            $source->position = Tools::getValue('position');
            $source->collection_rank = Tools::getValue('collection_rank');
            $source->name = Tools::getValue('source_name');
            $source->update();

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
    
    private function getSerializedDbParams($elem_coupons, $elem_referer, $elem_uri, $elem_products) {
        $metrics = Tools::getIsset($elem_coupons) ? Tools::getValue($elem_coupons)  : '';
        $dimensions = Tools::getIsset($elem_referer) ? Tools::getValue($elem_referer)  : '';
        $filter = Tools::getIsset($elem_uri) ? Tools::getValue($elem_uri)  : '';
        $products = Tools::getIsset($elem_products) ? Tools::getValue($elem_products)  : '';
        
        $source = array(
            'coupons' => $metrics,
            'referer' => $dimensions,
            'uri' => $filter,
            'products' => $products); 
            
        $serialized_source = serialize($source);
        return $serialized_source;    
    }
} 