<?php

class ControllingMarketingSource extends ObjectModel {
    public $id;
    
    public $id_controlling_source;
    
    public $ad_expense;
    
    public $reach;
    
    public $visits;
    
    public $ctr;
    
    public $subscription_count;
    
    public $oto_count;
    public $oto_sum;
    public $oto_avg;
    
    public $order_count;
    
    public $avg_cart;
    
    public $income_sum;
    
    public $margin_sum;
    
    public $covert;
    
    /** @var string Object creation date */
    public $date;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'controlling_marketing_source',
        'primary' => 'id_marketing_source',
        'multilang' => false,
        'fields' => array(
            'id_controlling_source' =>    array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),
            'ad_expense' =>               array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'visits' =>                   array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'ctr' =>                      array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'subscription_count' =>       array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'oto_count' =>                array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'oto_sum' =>                  array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'oto_avg' =>                  array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'order_count' =>              array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'avg_cart' =>                 array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'income_sum' =>               array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'margin_sum' =>               array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'covert' =>                   array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'date' =>                     array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
    
    public function __construct($date = null, $id_controlling_source = null) {
        $result = $this->get_controlling_marketing_source_day($date, $id_controlling_source);
        $id = 0;
        foreach ($result as $row) {
            $id = $row['id_marketing_source'];
            break;
        }
                
        if ($id > 0) {
         
            parent::__construct($id);
        } else {
            parent::__construct();
        } 
        $this->id_controlling_source = $id_controlling_source;
        $this->date = $date;
    }
  
   
    private function get_controlling_marketing_source_day($date, $id_controlling_source) {
        if ($id_controlling_source == '' || $date == '') {
            return array();
        }
        $sql = 'select id_marketing_source from ps_controlling_marketing_source where id_controlling_source = '.$id_controlling_source.' and date ="' . $date . '"';      
        return Db::getInstance()->executeS($sql);    
   } 
}
?>