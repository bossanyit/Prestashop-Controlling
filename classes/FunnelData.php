<?php


class FunnelData extends ObjectModel {
    public $id;
    
    public $reach  = null;
    
    public $reach_expense  = null;
    
    public $step1_open  = null ;
    public $step1_clicks  = null;
    public $step2_data  = null;
    public $step3_data  = null;
    public $cart_data  = null;
    public $order_data  = 0;
    public $date  = null;
    

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'controlling_funnel_data',
        'primary' => 'id_funnel_data',
        'multilang' => false,
        'fields' => array(
            'id_controlling_funnel' =>                array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),
            'reach' =>                    array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'reach_expense' =>            array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'step1_open' =>               array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'step1_clicks' =>             array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'step2_data' =>               array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'step3_data' =>               array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'cart_data' =>                array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'order_data' =>               array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),
            'date' =>                     array('type' => self::TYPE_DATE,  'validate' => 'isDate', 'required' => true),
        ),
    );

    public function __construct($id_funnel, $date) {
        $sql = sprintf('select id_funnel_data from ps_controlling_funnel_data where id_controlling_funnel = %d and date = "%s"', $id_funnel, $date);
        $id = Db::getInstance()->getValue($sql);
        parent::__construct($id);
    }
    
    
}
?>