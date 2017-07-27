<?php


class Funnel extends ObjectModel {
    public $id;
    public $id_source  = null;
    public $funnel_name  = null;
    
    public $id_step1_source  = null;
    public $id_step2_source  = null;
    public $id_step3_source  = null;
    public $id_cart_source  = null;
    public $id_order_source  = null;

    public $date_add  = null;
    public $position  = null;
    

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'controlling_funnel',
        'primary' => 'id_controlling_funnel',
        'multilang' => false,
        'fields' => array(
            'id_source' =>                array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'funnel_name' =>              array('type' => self::TYPE_STRING,'validate' => 'isString'),
            'id_step1_source' =>          array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'id_step2_source' =>          array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'id_step3_source' =>          array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'id_cart_source' =>           array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'id_order_source' =>          array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'date_add' =>                 array('type' => self::TYPE_DATE,  'validate' => 'isDate', 'required' => true),
            'position' =>                 array('type' => self::TYPE_INT,   'validate' => 'isInt'),
        ),
    );
}
?>