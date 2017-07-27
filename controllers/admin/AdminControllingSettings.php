<?php


class AdminControllingSettingsController extends ModuleAdminController {
    public function __construct() {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'Configuration';
        $this->table = 'configuration';
        
        $this->orders_collected = array();

    	$fields = array(

            'send_up' => array (
                'type' => 'hidden',
                'default' => '1',
            ),
            
            'SEND_EMAILS' => array(
                'title' => $this->l('Send automatic emails'),
                'desc' => $this->l(''),
                'hint' => $this->l('Send missing margins, summaries in email'),
                 'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'bool',
                'default' => '0',
            ),
            
            
        );        
        
        $this->fields_options = array(
            'general' => array(
                'title' =>    $this->l('Settings for the Controlling System'),
                'icon' =>    'icon-cogs',
                'fields' =>    $fields,
                'submit' => array('title' => $this->l('Send!')),
            ),
        );
             
        parent::__construct();     

    }

    public function postProcess() {

        if (Tools::getValue('SEND_EMAILS')) {   
            $sql = "SELECT DISTINCT a.id_order, p.id_product, pl.name as product_name , IFNULL( pa.wholesale_price, p.wholesale_price ) as wholesale_price
                    FROM ps_orders a
                    INNER JOIN ps_order_detail od ON a.id_order = od.id_order
                    INNER JOIN ps_product p ON od.product_id = p.id_product
                	inner join ps_product_lang pl on pl.id_product = p.id_product and pl.id_lang = 2
                    LEFT JOIN ps_product_attribute pa ON pa.id_product_attribute = od.product_attribute_id
                    WHERE a.date_add > '2016-03-01'
                having wholesale_price = 0";
            $result = Db::getInstance()->executeS($sql);    
            
        }
    }
} 