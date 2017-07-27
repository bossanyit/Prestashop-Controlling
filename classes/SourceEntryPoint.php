<?php


class SourceEntryPoint extends ObjectModel {
    public $id;
    public $entry_name  = null;
    public $id_controlling_source  = 0;
    public $id_entry_type  = null;
    public $dimensions;
    public $filter;
    public $referer;
    public $uri;
    public $products;
    
    

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'controlling_source_entrypoint',
        'primary' => 'id_entry',
        'multilang' => false,
        'fields' => array(            
            'id_controlling_source' =>   array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'entry_name' =>              array('type' => self::TYPE_STRING,'validate' => 'isString'),
            'id_entry_type' =>           array('type' => self::TYPE_INT,   'validate' => 'isInt'),
        ),
    );
}
?>