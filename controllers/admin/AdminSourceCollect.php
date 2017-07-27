<?php


class AdminSourceCollectController extends ModuleAdminController {

    public $date_to;
    public $date_from;
	
	public $orders_collected = null;
	
	public $update = false;
	
    public function __construct() {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'Configuration';
        $this->table = 'configuration';
        
        $this->orders_collected = array();

    	$fields = array(

            'collect_up' => array (
                'type' => 'text',
                'default' => '1',
            ),
            
            'CONTR_LAST_COLLECT' => array(
                'title' => $this->l('Last collection date'),
                'desc' => $this->l(''),
                'hint' => $this->l('Last date when the data has been collected'),
                'type' => 'text',
                'class' => 'fixed-width-xxl',
                'default' => '0',
            ),
            
            'CONTR_DB' => array(
                'title' => $this->l('Collect webshop income data'),
                'desc' => $this->l('If enabled, data webshop (as orders, connections) will be collected.'),
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'bool',
                'default' => '0'
            ),
            
            'CONTR_LAST_COLLECT_GA' => array(
                'title' => $this->l('Last collection date of Google Analytics'),
                'desc' => $this->l(''),
                'hint' => $this->l('Last date when the GA data has been collected'),
                'type' => 'text',
                'class' => 'fixed-width-xxl',
                'default' => '0',
            ),
            'CONTR_GA' => array(
                'title' => $this->l('Collect Google Analytics data'),
                'desc' => $this->l('If enabled, data from Google Analytics will be collected.'),
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'bool',
                'default' => '0'
            ),
            
            'CONTR_LAST_COLLECT_FB' => array(
                'title' => $this->l('Last collection date of Facebook insights data'),
                'desc' => $this->l(''),
                'hint' => $this->l('Last date when the FB insights data has been collected'),
                'type' => 'text',
                'class' => 'fixed-width-xxl',
                'default' => '0',
            ),
            'CONTR_FB' => array(
                'title' => $this->l('Collect Facebook data'),
                'desc' => $this->l('If enabled, advertising data from Facebook will be collected.'),
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'bool',
                'default' => '0'
            ),
            'CONTR_FUNNEL' => array(
                'title' => $this->l('Collect Funnel data'),
                'desc' => $this->l('If enabled, all marketing funnel data will be collected.'),
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'bool',
                'default' => '0'
            ),
             'UPDATE_DATA' => array(
                'title' => $this->l('Update existing data'),
                'desc' => $this->l('If enabled, the existing data will be updated.'),
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'bool',
                'default' => '0'
            ),
            
         /*   'CONTR_DATE_LAST_COLLECT_ADW' => array(
                'title' => $this->l('Last collection date of Adwords insights data'),
                'desc' => $this->l(''),
                'hint' => $this->l('Last date when the Adwords insights data has been collected'),
                'type' => 'text',
                'class' => 'fixed-width-xxl',
                'default' => '0',
            ),
            'CONTR_ADW' => array(
                'title' => $this->l('Collect Adwords data'),
                'desc' => $this->l('If enabled, advertising data from Google Adwords will be collected.'),
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'bool',
                'default' => '0'
            ),    */                    
        );
        
        //$config_last_collect_date = Configuration::get('CONTR_LAST_COLLECT');
        //$last_collect_date =  $config_last_collect_date != '' ? $config_last_collect_date : $this->l('Never');     
       // $fields['DATE_LAST_COLLECT']['type'] = 'disabled';
        //$fields['DATE_LAST_COLLECT']['disabled'] = $last_collect_date;
                    
                    
        //$this->fields_value['DATE_LAST_COLLECT'] = Configuration::get('CONTR_LAST_COLLECT');
        //$last_fb = Configuration::get('CONTR_LAST_COLLECT_FB');
        //$this->fields_value['DATE_LAST_COLLECT_FB'] = isset($last_fb) && $last_fb != '' ? $last_fb : '1970-01-01';
        
    	$this->fields_options = array(
            'general' => array(
                'title' =>    $this->l('Collect marketing and income data'),
                'icon' =>    'icon-cogs',
                'fields' =>    $fields,
                'submit' => array('title' => $this->l('Collect!')),
            ),
        );        
        
             
        parent::__construct();     
    }
    

    public function postProcess() {

        if ($last_date = Tools::getValue('CONTR_LAST_COLLECT')) {               
            $this->update = Tools::getValue('UPDATE_DATA');  
            
            Logger::addLog("Marketing data collecting start - " . $this->update);             
            
            $this->date_from = $last_date;
            $yesterday = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 day'));
        
            $this->date_to = $yesterday;
            //$collect_fb = Tools::getValue('CONTR_FB');
            /*if ($collect_fb) {
                $this->date_to =  date('Y-m-d', strtotime($this->date_from . ' +7 day'));
            } */               
            if ($this->date_to > $yesterday) {
                $this->date_to = $yesterday;
                if ($this->date_to >= $last_date) {
                    return;                  
                }
            }
            
          
            
            $this->collectDataForAllSources();

            if (Tools::getValue('CONTR_DB')) {
                Configuration::updateValue('CONTR_LAST_COLLECT', $this->date_to);    
            }
            if (Tools::getValue('CONTR_GA')) {
                Configuration::updateValue('CONTR_LAST_COLLECT_GA', $this->date_to);  
            }
            if (Tools::getValue('CONTR_FB')) {
                Configuration::updateValue('CONTR_LAST_COLLECT_FB', $this->date_to);        
            }
        }          
    }
    
    private function collectDataForAllSources() {
        $collect_db = Tools::getValue('CONTR_DB');
        $collect_fb = Tools::getValue('CONTR_FB');
        $collect_ga = Tools::getValue('CONTR_GA');
        $collect_adw = Tools::getValue('CONTR_ADW');
        
        
        ControllingSource::collectAllData($collect_db, $collect_fb, $collect_ga, $collect_adw, $this->date_from, $this->date_to);
        
        /*$sql = 'Select * from ps_controlling_source where (date_end is null OR date_end  < "' . $this->date_to . '" OR date_add >= "' . $this->date_from . '" ) order by collection_rank';
        //Logger::addLog("Sql: " . $sql); 
        $collect_db = Tools::getValue('CONTR_DB');
        $sources = Db::getInstance()->executeS($sql);
        foreach ($sources as $source) {
            $callable = $source['callback'];       
            if (is_callable(array($this, $callable)) ) {
                $collect_db = Tools::getValue('CONTR_DB');
                if ($collect_db) {
                  $this->$callable($source['id_controlling_source'] );
                }
            } else {
                $this->collectAllEntryPoints($source);
            }
        } */
        
        $collect_funnel = Tools::getValue('CONTR_FUNNEL');

        if ($collect_funnel) {
            $funnel_source = new FunnelSource($this->date_from, $this->date_to);
            $funnel_source->collect($this->update);
        }         
        
    }
    
    private function is_data_saved_this_day($date_from, $id_controlling_source) {
        $sql = 'select id_marketing_source from ps_controlling_marketing_source where order_count > 0 and date  = "' . $date_from . '" and id_controlling_source = '.$id_controlling_source;
        $results = Db::getInstance()->executeS($sql); 
        
        return count($results) > 0;
    }
    
    private function collectAllEntryPoints($source) {
        $collect_db = Tools::getValue('CONTR_DB');
        $collect_fb = Tools::getValue('CONTR_FB');
        $collect_ga = Tools::getValue('CONTR_GA');
        $collect_adw = Tools::getValue('CONTR_ADW');

        $controlling_source = new ControllingSource($source['id_controlling_source'], $collect_db, $collect_fb, $collect_ga, $collect_adw, $this->orders_collected);
        $controlling_source->collectData($this->date_from, $this->date_to, $this->update);
    }
    
    private function collectMediline($id_controlling_source) {
        if ($this->update)  {
            $this->checkExistsingData($id_controlling_source);
        }
        Logger::addLog('Collect data for Mediline');   
        $sql = 'SELECT distinct id_order, total_paid_tax_incl as total, o.date_add FROM ps_orders o
                inner join ps_customer_group g on o.id_customer = g.id_customer
                where id_group = 7
                and o.current_state <> 6 and o.current_state <> 7';
         $sql .= " and o.date_add between  '".$this->date_from."' AND '".$this->date_to."'";      
         //$results = Db::getInstance()->executeS($sql); 
         $controlling_source = new ControllingSource($id_controlling_source, 0, 0, 0, 0, $this->orders_collected);   
         $results = $controlling_source->getOrders($sql);      
                
         if (count($results) >0) {
            $controlling_source->saveCollectedDBData($results);           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $this->date_from."' AND '".$this->date_to);
         }   
    }
    private function collectRetailer($id_controlling_source) {

        Logger::addLog('Collect data for Viszontelado');  
        if ($this->update)  {
            $this->checkExistsingData($id_controlling_source);
        }        
        $sql = 'SELECT distinct id_order, total_paid_tax_incl as total, o.date_add FROM ps_orders o
                inner join ps_customer_group g on o.id_customer = g.id_customer
                where id_group = 5
                and o.current_state <> 6 and o.current_state <> 7';
         $sql .= " and o.date_add between  '".$this->date_from."' AND '".$this->date_to."'";
         //$results = Db::getInstance()->executeS($sql); 
         $controlling_source = new ControllingSource($id_controlling_source, 0, 0, 0, 0, $this->orders_collected);   
         $results = $controlling_source->getOrders($sql);             
         if (count($results) >0) {
            $controlling_source->saveCollectedDBData($results);           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $this->date_from."' AND '".$this->date_to);
         }          
      
    }
    
    private function collectMainCustomer($id_controlling_source) {

        Logger::addLog('Collect data for Torzsvasarlo');       
        if ($this->update)  {
            $this->checkExistsingData($id_controlling_source);
        }
        $sql = 'SELECT distinct id_order, total_paid_tax_incl as total, o.date_add, o.id_customer FROM ps_orders o 
        inner join ps_customer_group g on o.id_customer = g.id_customer and g.id_group <> 5 and g.id_group <>7
        where o.id_customer in         
        (select id_customer from ps_orders o2 where o.id_customer = o2.id_customer having count(o2.id_customer) >= 5)';
        $sql .= " and o.date_add between  '".$this->date_from."' AND '".$this->date_to."'";
      //$results = Db::getInstance()->executeS($sql); 
         $controlling_source = new ControllingSource($id_controlling_source, 0, 0, 0, 0, $this->orders_collected);   
         $results = $controlling_source->getOrders($sql); 
         if (count($results) >0) {
            $controlling_source->saveCollectedDBData($results);           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $this->date_from."' AND '".$this->date_to);
         }
 
    }    
    
    private function collectCustomer($id_controlling_source) {

      Logger::addLog('Collect data for Visszatero vasarol');       
        if ($this->update)  {
            $this->checkExistsingData($id_controlling_source);
        }
      $sql = 'SELECT distinct id_order, total_paid_tax_incl as total, o.date_add,  o.id_customer FROM ps_orders o 
        inner join ps_customer_group g on o.id_customer = g.id_customer and g.id_group <> 5 and g.id_group <>7
        where o.id_customer in         
        (select id_customer from ps_orders o2 where o.id_customer = o2.id_customer having count(o2.id_customer) >= 2 and count(o2.id_customer) < 5)';
        $sql .= " and o.date_add between  '".$this->date_from."' AND '".$this->date_to."'";
      //$results = Db::getInstance()->executeS($sql); 
         $controlling_source = new ControllingSource($id_controlling_source, 0, 0, 0, 0, $this->orders_collected);   
         $results = $controlling_source->getOrders($sql); 
         if (count($results) >0) {
            $controlling_source->saveCollectedDBData($results);           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $this->date_from."' AND '".$this->date_to);
         }   
    }        
    
    private function collectAdhoc($id_controlling_source) {
          Logger::addLog('Collect data for Adhoc -- Nr of collected orders ' .count($this->orders_collected) ); 
          if ($this->update)  {
            $this->checkExistsingData($id_controlling_source);
          }
          ControllingSource::array_sort($this->orders_collected, 'id_order');          
          $order_array = $this->get_order_ids();
        
          $sql = 'SELECT date_format(date_add, "%Y-%m-%d") as date_add, sum(total_paid) as total, avg(total_paid) as kosar, 0 as id_order FROM `ps_orders` o
                    WHERE o.date_add between  "'.$this->date_from.'" AND "'.$this->date_to.'"
                    and id_order not in ('.$order_array.')
                    group by date_add
                    order by date_add';
          
          /*Logger::addLog($sql );                  
          $results = Db::getInstance()->executeS($sql); 
          foreach ($results as $result) {
                Logger::addLog('Adhoc order on ' . $result['day'] . ' sum ' . $result['total'] . ' avg ' . $result['kosar']);
          } */
             unset($this->orders_collected);
             $controlling_source = new ControllingSource($id_controlling_source, 0, 0, 0, 0, $this->orders_collected, false);   
             $results = $controlling_source->getOrders($sql); 
             if (count($results) >0) {
                $controlling_source->saveCollectedDBData($results);           
             } else {
                Logger::addLog("No data for " .$id_controlling_source . " between " . $this->date_from."' AND '".$this->date_to);
             }        
                  
    }    
    
    private function get_order_ids() {
        $ids = '';
        $i = 0;
        foreach($this->orders_collected as $order) {
            if ($i > 0) {
                $ids .= ', ' . $order['id_order'];
            } else {
                $ids .= $order['id_order'];
            }    
            $i++;
        }
        return $ids;
    }
    
    private function checkExistsingData($id_controlling_source) {
        return false;
        $sql = "SELECT order_count FROM `ps_controlling_marketing_source` WHERE id_controlling_source = ".$id_controlling_source." and date between '".$this->date_from."' and '".$this->date_to."' and order_count > 0";
        $count = Db::getInstance()->getValue($sql);      
        if ($count > 0) {
            $sql = "DELETE FROM `ps_controlling_marketing_source` WHERE id_controlling_source = ".$id_controlling_source." and date between '".$this->date_from."' and '".$this->date_to."' and order_count > 0";
            $result = Db::getInstance()->execute($sql);   
        }
    }
} 