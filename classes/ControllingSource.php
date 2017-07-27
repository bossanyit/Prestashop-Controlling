<?php

require_once 'ControllingSourceGA.php';

use Facebook\Facebook;
use FacebookAds\Api as Api;
use FacebookAds\Object\Campaign as Campaign;
use FacebookAds\Object\InsightsPresets as InsightsPresets;  
use FacebookAds\Http\Exception\EmptyResponseException;
require_once __DIR__ . '/Google/autoload.php';

class ControllingSource extends ObjectModel {
    public $id;
    
    public $position;
    
    public $collection_rank;
    
    public $name;
    
    public $callback = null;    
    
    /** @var string Object creation date */
    public $date_add = null;
    
    /** @var string campaign end */
    public $date_end = null;    
    
    public $id_campaign_fb = 0;    
    
    public $id_ga = 0;    
    
    public $page_filter = null;
    
    public $update;
    
    public $id_ad_source;
    public $id_reg_source;
    public $id_oto_source;
    public $id_order_source;
    public $source_data = null;
    public $entry_source = null;

    const ENTRY_TYPE_COUPON = 1;
    const ENTRY_TYPE_REQUEST_URI = 2;
    const ENTRY_TYPE_REFERRER = 3;
    
    public $collect_fb;
    public $collect_db;
    public $collect_ga;
    public $collect_adw;
    public $orders_collected;
    public $collect_data = true;
    
    var  $FB_SDK_DIR ;
    var  $FB_SDKAD_DIR;         
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'controlling_source',
        'primary' => 'id_controlling_source',
        'multilang' => false,
        'fields' => array(
            'position' =>                array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),
            'name' =>                    array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'callback' =>                array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),            
            'date_add' =>                array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_end' =>                array('type' => self::TYPE_DATE),
            'id_campaign_fb' =>          array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),  
            'id_ga' =>                   array('type' => self::TYPE_INT,   'validate' => 'isInt'),          
            'page_filter' =>             array('type' => self::TYPE_STRING, 'validate' => 'isString'), 
            'collection_rank' =>         array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),           
            'id_ad_source' =>            array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),   
            'id_reg_source' =>           array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),   
            'id_oto_source' =>           array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),   
            'id_order_source' =>         array('type' => self::TYPE_INT,   'validate' => 'isInt', 'required' => true),   
        ),
    );
    
    public function __construct($id, $db_collect, $fb_collect, $ga_collect, $adw_collect, &$orders_collected, $collect = true ) {              
        parent::__construct($id);
        
        $this->collect_db = $db_collect;
        $this->collect_fb = $fb_collect;
        $this->collect_ga = $ga_collect;
        $this->collect_adw = $adw_collect;        
        $this->orders_collected = &$orders_collected;  
        $this->collect_data = $collect;
        
        $this->FB_SDK_DIR = __DIR__ . '/Facebook';
        $this->FB_SDKAD_DIR = __DIR__ . '/FacebookAds';        
        
        $loader_fb = include $this->FB_SDK_DIR . '/autoload.php';
        $loader_fb_ad = include $this->FB_SDKAD_DIR . '/autoload.php';            
    }
    
    public static function collectAllData($db_collect, $fb_collect, $ga_collect, $adw_collect, $date_from, $date_to) {
        $sql = 'select * from ps_controlling_source 
            where
            (date_end is null or date_end = "0000-00-00" OR date_end  >= "'.$date_to.'" )
            order by collection_rank asc';
        $sources = Db::getInstance()->executeS($sql);   
        
        $orders_collected = array();
        $next_date = $date_from; //date('Y-m-d', strtotime($date_from . ' +1 day'));
        
        $count_orders = 0;
        while ($date_to >= $next_date) { 

            foreach ($sources as $source) {
                $controlling_source = new ControllingSource($source['id_controlling_source'], $db_collect, $fb_collect, $ga_collect, $adw_collect, $orders_collected);              
                $controlling_source->collect($next_date);
            } 
            $count_orders += count($orders_collected);
            unset($orders_collected); 
            $orders_collected = array();            
            $next_date = date('Y-m-d', strtotime($next_date . ' +1 day'));
Logger::addLog('Collecting from ' .$next_date . ' (to ' .$date_to . ')');            
        }        
        return $count_orders;
    }
    
    public function collect ($date) {

        $this->source_data = new ControllingMarketingSource($date, $this->id);              
        if ($this->id_ad_source > 0) {
            $this->collectByType($this->id_ad_source, $date, 1);
        }
        if ($this->id_reg_source > 0) {
            $this->collectByType($this->id_reg_source, $date, 2);
        }
        if ($this->id_oto_source > 0) {
            $this->collectByType($this->id_oto_source, $date, 3);
        }        
        if ($this->id_order_source > 0) {
            $this->collectByType($this->id_order_source, $date, 4);
        }        
Logger::addLog('DATE: ' . $date . 
            ' SOURCE ' . $this->name . 
            ' Visits ' . $this->source_data->visits . 
            ' REG ' . $this->source_data->subscription_count . 
            ' OTO ' . $this->source_data->oto_count . 
            ' ORDER ' . $this->source_data->order_count);
        $this->source_data->save();
    }
    
    private function saveDBData() {
        $sum = 0;
        $count = 0;
        foreach ($this->orders_collected as $orders) {
            if ($orders['id_controlling_source'] == $this->id) {
                $sum += $orders['total'];
                $count++;
            }
        }
        $this->source_data->order_count = $count;
        $this->source_data->income_sum = $sum;
        if ($count > 0) {
            $this->source_data->avg_cart = Tools::ps_round($sum / $count, 2);
        } else  {
            $this->source_data->avg_cart = 0;
        }
    }
    
    private function saveOTOData($orders) {
        $sum = 0;
        $count = 0;
        foreach ($orders as $orders) {
            $sum += $orders['total'];
            $count++;
        }
        $this->source_data->oto_count = $count;
        $this->source_data->oto_sum = $sum;
        $this->source_data->oto_avg = Tools::ps_round($sum / $count, 2);
    }
    
    private function collectByType($id_entry_source, $date, $index_field) {
        if ($id_entry_source == 0) {
            return;
        }
        $this->entry_source = new SourceEntryPoint($id_entry_source);
        $orders = null;
        switch($this->entry_source->id_entry_type) {
            case 1: // coupon (db)
                if ($this->collect_db) {
                    $orders = $this->getCouponOrdersNew($this->entry_source->entry_name, $date);
                }
                break;
            case 2: // referer (db)
                if ($this->collect_db) {
                    $orders = $this->getRefererOrdersNew($this->entry_source->entry_name, $date);
                }
                break;
            case 3: // uri (db)
                if ($this->collect_db) {
                    $orders = $this->getUriOrdersNew($this->entry_source->entry_name, $date);
                }
                break;
            case 4: // callback (db)
                if ($this->collect_db) {
                    $callable = $this->entry_source->entry_name;       
                    if (is_callable(array($this, $callable)) ) {                        
                       $orders = $this->$callable($this->id, $date) ;
                    }
                }
                
                break;                                
            case 5: // ga api
            case 9:
                if ($this->collect_ga) {
                    $this->collectUtmData($this->entry_source->entry_name, $date);
                }
                break;                 
            case 6: // facebook api
                if ($this->collect_fb) {
                    $this->collectAndSaveFBData($date);
                }
                
                break;       
            case 7: // adwords api
                if ($this->collect_adw) {
                }
                
                break;            
            case 8: // funnel source
                // not implemented
                break;              
                       
            case 10: // db (db)
                if ($this->collect_db) {
                    $entry      = unserialize($this->entry_source->entry_name);
    	            $coupons    = $entry['coupons'];
    	            $referer    = $entry['referer'];
    	            $uri        = $entry['uri'];
    	            $products   = $entry['products'];
                    $orders1 = $this->getCouponOrdersNew($coupons, $date);
                    $orders2 = $this->getRefererOrdersNew($referer, $date);
                    $orders3 = $this->getUriOrdersNew($uri, $date);
                    $orders4 = $this->getProductOrdersNew($products, $date);
                    $orders = array_merge_recursive($orders1, $orders2, $orders3, $orders4);
                }
                break;   
            case 11: // Interspire
                $this->collectIemSubscriptions($this->entry_source->entry_name, $date);
                break;
            case 12: // Interspire
                $entry      = unserialize($this->entry_source->entry_name);
                $campaignid = $entry['metrics'];
                $statusid = $entry['dimensions'];
                Logger::addLog('before collectIemCampaignSubscriptions campaign ' . $campaignid . ' status ' . $statusid);
                $this->collectIemCampaignSubscriptions($campaignid, $statusid, $date);
                break;                
            default: 
                $message = sprintf('SOURCE data collect: wrong entry source (%d) id_entry_source: %d', $entry_source->id_entry_type, $id_entry_source);
                Logger::addLog($message);
                throw new PrestaShopException($message);
                break;
        }
        
        if ($orders ) {
            if ($index_field == 4) {
               foreach ($orders as $order) {
                    // save all orders in the array
                    if (!$this->existsOrder($order['id_order'])) {
                        $this->orders_collected[] = array(
                            'id_order' => $order['id_order'],
                            'total' => $order['total'],
                            'id_controlling_source' => $this->id
                        );
                    }
               }
               $this->saveDBData();
            } elseif ($index_field == 3) {
                $this->saveOTOData($orders);
            }
        }
    }
    
    private function existsOrder($id_order)  {
        $exists = false;
        foreach($this->orders_collected as $orders) {
            if ($orders['id_order'] == $id_order) {
                $exists = true;
                break;
            }
        }
        return $exists;
    }        
    
    public function getCouponOrdersNew($coupons, $date_from) {
        if (empty($coupons) || $coupons == "" ) return array();                
        
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        
        $sql = "SELECT distinct o.id_order, total_paid_tax_incl as total FROM `ps_cart_cart_rule` ccr INNER JOIN ps_cart_rule cr ON cr.id_cart_rule = ccr.id_cart_rule INNER JOIN ps_orders o ON o.id_cart = ccr.id_cart WHERE cr.id_cart_rule in (" . $coupons. ")  and o.date_add BETWEEN '".$date_from."' AND '".$date_to."'";
                	 
        /*
        $sql = "SELECT distinct o.id_order, total_paid_tax_incl as total FROM  `ps_orders` o
                inner join ps_order_cart_rule ocr on o.id_order = ocr.id_order
                where ocr.id_cart_rule in (" . $coupons. ")  and o.date_add between  '".$date_from."' AND '".$date_to."'";
        */
        return $this->getOrdersNew($sql);               
    }
    
    public function getProductOrdersNew($products, $date_from) {
        if (empty($products) || $products == "" ) return array();                
        
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        
        $sql = "select distinct o.id_order, total_paid_tax_incl as total FROM  `ps_orders` o
                inner join ps_order_detail ocd on o.id_order = ocd.id_order
                where ocd.product_id in (" . $products. ")  and o.date_add between  '".$date_from."' AND '".$date_to."'";

        return $this->getOrdersNew($sql);               
    }
    
    public function getRefererOrdersNew($uri, $date_from) {
        if (empty($uris)) return array();
        
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        $uris = explode(",", $uri);
        
        $sql = "SELECT distinct o.id_order, total_paid_tax_incl as total FROM  `ps_orders` o	
        		INNER JOIN ps_guest g ON g.id_customer = o.id_customer
        		INNER JOIN ps_connections co  ON co.id_guest = g.id_guest
        		INNER JOIN ps_connections_source cos ON cos.id_connections = co.id_connections
        		where";
		   for ($i = 0; $i < count($uris);$i++) {
    		    if ($i == 0) {
    		        $sql .= " cos.http_referer like '".$uris[$i]."%'";
    		    } else {
    		        $sql .= " OR cos.http_referer like '".$uris[$i]."%'";
    		    }
		   } 
		$sql .= " and o.date_add between  '".$date_from."' AND '".$date_to."'";   
        $sql .= "order by o.date_add";
        return $this->getOrdersNew($sql);
    }
    
    public function getUriOrdersNew($uri, $date_from) {
        if (empty($uris)) return array();
        
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        $uris = explode(",", $uri);
        
        $sql = "SELECT distinct o.id_order, total_paid_tax_incl as total FROM  `ps_orders` o
        		INNER JOIN ps_guest g ON g.id_customer = o.id_customer
        		INNER JOIN ps_connections co  ON co.id_guest = g.id_guest
        		INNER JOIN ps_connections_source cos ON cos.id_connections = co.id_connections
        		where";
		   for ($i = 0; $i < count($uris);$i++) {
    		    if ($i == 0) {
    		        $sql .= " cos.request_uri like '".$uris[$i]."%'";
    		    } else {
    		        $sql .= " OR cos.request_uri like '".$uris[$i]."%'";
    		    }
		   }
		$sql .= " and o.date_add between  '".$date_from."' AND '".$date_to."'";
        $sql .= "order by o.date_add";
        return $this->getOrdersNew($sql);
    }    
    
    public function getOrdersNew($sql) {
        $results = Db::getInstance()->executeS($sql);       

        $orders = array();       
        foreach ($results as $result) {
            $orders[] = array (
                'id_order' => $result['id_order'],
                'total' => $result['total']
            );
        }   
        
        return $orders; 
    }    
    
    public function getAllEntryPoints($type = 0) {
        if ($type == 0)
            $sql = 'select entry_name from ps_controlling_source_entrypoint where id_controlling_source = ' . $this->id;
        else 
            $sql = 'select entry_name from ps_controlling_source_entrypoint where id_controlling_source = ' . $this->id . ' and id_entry_type = ' . $type ;
        return Db::getInstance()->executeS($sql);    
        
           
    }
    
    public function collectData($date_from, $date_to, $update = false) {

        $this->update = $update;
        //Logger::addLog('Start source: ' .$this->name . ' collection DB: ' . $this->collect_db);
        
        if ($this->collect_db) {
            Logger::addLog("DB Collect ");
            $this->db_collect($date_from, $date_to);
        } 

        if ($this->collect_fb) {
            Logger::addLog("FB Collect ");
            $this->fb_collect($date_from, $date_to);
        }
        
        if ($this->collect_ga) {
            Logger::addLog("GA Collect update: " .$this->update );
            $this->ga_collect($date_from, $date_to);
        }
    }
    
    private function db_collect($date_from, $date_to) {
        $date_to = date('Y-m-d', strtotime($date_to . ' +1 day'));
        $date_from = date('Y-m-d', strtotime($date_from . ' +1 day'));
        
        $coupons = $this->getAllEntryPoints(ControllingSource::ENTRY_TYPE_COUPON);
        $orders1 = $this->getCouponOrders($coupons, $date_from, $date_to);
//Logger::addLog('source: ' .$this->name . ' coupons ' . count($orders1));        
        $uris = $this->getAllEntryPoints(ControllingSource::ENTRY_TYPE_REQUEST_URI);
        $orders2 = $this->getUriOrders($uris, $date_from, $date_to);
//Logger::addLog('source: ' .$this->name . ' Uri ' . count($orders2));                
        $referers = $this->getAllEntryPoints(ControllingSource::ENTRY_TYPE_REFERRER);
        $orders3 = $this->getRefererOrders($referers, $date_from, $date_to);
//Logger::addLog('source: ' .$this->name . ' referer ' . count($orders3));        
        
        $orders = array_merge_recursive($orders1, $orders2, $orders3);
        
        
//Logger::addLog('source: ' .$this->name . ' SUM ' . count($orders)); 
        if ($this->update)  {
            $this->checkExistsingData($date_from, $date_to, 'order_count');
        }         
        $this->saveCollectedDBData($orders);
   
    }
    
    public function getCouponOrders($coupons, $date_from, $date_to) {
        if (empty($coupons)) return array();
        
        $coupon_array = "";
        $i = 0;
        foreach($coupons as $coupon ) {
            if ($i == 0) {
                $coupon_array .= "'" . $coupon['entry_name'] . "'";
            } else {
                $coupon_array .= ", '" . $coupon['entry_name'] . "'";
            }
            $i++;
        }
        
        //$coupon_array = "'" . implode("', '", $coupons['entry_name']) . "'";
//Logger::addLog(' ' .$this->name . ' coupon_array ' .$coupon_array);
        $sql="
            SELECT
            	o.id_order,
            	total_paid_tax_incl as total,
            	o.date_add as date_add
            FROM `ps_cart_cart_rule` ccr
            	INNER JOIN ps_cart_rule cr ON cr.id_cart_rule = ccr.id_cart_rule
            	INNER JOIN ps_orders o ON o.id_cart = ccr.id_cart
            WHERE
            	ccr.id_cart_rule in (" . $coupon_array. ")  and
            	o.date_add between  '".$date_from."' AND '".$date_to."'";
        /*
        $sql = "SELECT o.id_order, total_paid_tax_incl as total, date_add FROM `ps_orders` o
                inner join ps_order_cart_rule ocr on o.id_order = ocr.id_order
                where ocr.id_cart_rule in (" . $coupon_array. ")  and o.date_add between  '".$date_from."' AND '".$date_to."'";
        */
//Logger::addLog(' ' .$this->name . ' sqly ' .$sql);               
        return $this->getOrders($sql);        
    }
    
    public function getUriOrders($uris, $date_from, $date_to) {
        if (empty($uris)) return array();
        $sql = "SELECT distinct id_order, total_paid_tax_incl as total, o.date_add FROM `ps_orders` o		
		INNER JOIN ps_guest g ON g.id_customer = o.id_customer
		INNER JOIN ps_connections co  ON co.id_guest = g.id_guest
		INNER JOIN ps_connections_source cos ON cos.id_connections = co.id_connections
		where";
		   for ($i = 0; $i < count($uris);$i++) {
    		    if ($i == 0) {
    		        $sql .= " cos.request_uri like '".$uris['entry_name'][$i]."%'";
    		    } else {
    		        $sql .= " OR cos.request_uri like '".$uris['entry_name'][$i]."%'";
    		    }
		   }
		$sql .= " and o.date_add between  '".$date_from."' AND '".$date_to."'";
        $sql .= "order by o.date_add";
        return $this->getOrders($sql);
    }
    
    public function getRefererOrders($uris, $date_from, $date_to) {
        if (empty($uris)) return array();
        $sql = "SELECT distinct id_order, total_paid_tax_incl as total, o.date_add FROM `ps_orders` o		
		INNER JOIN ps_guest g ON g.id_customer = o.id_customer
		INNER JOIN ps_connections co  ON co.id_guest = g.id_guest
		INNER JOIN ps_connections_source cos ON cos.id_connections = co.id_connections
		where";
		   for ($i = 0; $i < count($uris);$i++) {
    		    if ($i == 0) {
    		        $sql .= " cos.http_referer like '".$uris[$i]['entry_name']."%'";
    		    } else {
    		        $sql .= " OR cos.http_referer like '".$uris[$i]['entry_name']."%'";
    		    }
		   } 
		$sql .= " and o.date_add between  '".$date_from."' AND '".$date_to."'";   
        $sql .= "order by o.date_add";
        return $this->getOrders($sql);
    }

    public function getOrders($sql) {
        $results = Db::getInstance()->executeS($sql);       

        $orders = array();       
        foreach ($results as $result) {
            $orders[] = array (
                'id_order' => $result['id_order'],
                'total' => $result['total'],
                'date' => date('Y-m-d', strtotime($result['date_add']))
            );
        }   
        
        return $orders; 
    }
    
    public function saveCollectedDBData($orders) {        
        $date = '';
        $marketing_source = null;
        $count_order = 0;  
        $orders = ControllingSource::array_sort($orders, 'date');   
           
        foreach ($orders as $order) {
            
            if ($this->collect_data) {
                // save all orders in the array
                $this->orders_collected[] = array(
                    'id_order' => $order['id_order'],
                    'date' => $order['date'],
                );
            }
            
            $count_order++;
            if ($order['date'] != $date) {
//Logger::addLog('save collection NEW DATE ' . $order['date']);                
                
                 // save the existing data
                if (isset($marketing_source) ) {
                    $this->save2($marketing_source, $count_order);
                }               

                $marketing_source = new ControllingMarketingSource($order['date'], $this->id);
                $marketing_source->id_controlling_source = $this->id;                    
                $marketing_source->date = $order['date']; 
                $marketing_source->income_sum = $order['total'];
                $date = $order['date'];

                // new day
            } else {
                $marketing_source->income_sum += $order['total'];     
//Logger::addLog('save collection adding on date ' .$date . ' total ' . $order['total'] . ' sum ' . $marketing_source->income_sum );                             
            }
        }
        
        // save the last, not saved object
        if (isset($marketing_source) && $count_order > 0 ) {
            $this->save2($marketing_source, $count_order);
        }   
        
        
    }
    
    public function save2(&$marketing_source, &$count_order) {
        // the very first time save is not available
        $marketing_source->avg_cart = $marketing_source->income_sum / $count_order;
        $marketing_source->order_count = $count_order;
        $marketing_source->margin_sum = $marketing_source->income_sum * 0.4;
//Logger::addLog('save collection before data AVG ' . $marketing_source->avg_cart . ' sum ' . $marketing_source->income_sum );      

        if (!$this->this_day_saved($marketing_source->id_controlling_source, $marketing_source->date)) {
            $marketing_source->save();
        }
        $count_order = 0;
        

        unset($marketing_source);
    }
    
    public function this_day_saved($id_controlling_source, $date) {
        $sql = 'SELECT income_sum FROM `ps_controlling_marketing_source` WHERE id_controlling_source = '.$id_controlling_source.' and date = "'.$date.'"';
        $value = Db::getInstance()->getValue($sql); 
Logger::addLog('this day saved for source ' . $id_controlling_source . ' ON ' .$date . ': ' . $value);        
        return $value > 0;
    }
    
    public static function array_sort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();
    
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
    
            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }
    
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
    
        return $new_array;
    }
    
    private function fb_collect($date_from, $date_to) {
        if ($this->id_campaign_fb > 0) {
            $ga = new ControllingSourceFB($date_from, $date_to, $this->id, $this->id_campaign_fb);
            $ga->collectAndSaveFBData($this->update);
        }
        
 
                
    }
    
    private function ga_collect($date_from, $date_to) { 
        if ($this->id_ga > 0 || $this->page_filter != '') {
            $ga = new ControllingSourceGA($date_from, $date_to, $this->id, $this->id_ga, $this->page_filter);
            $ga->collectAndSaveGAData($this->update);
        }
    }

    
    private function checkExistsingData($date_from, $date_to, $data_field) {
        $sql = "SELECT ".$data_field." FROM `ps_controlling_marketing_source` WHERE id_controlling_source = ".$this->id." and date between '".$date_from."' and '".$date_to."' and ".$data_field." > 0";
        $count = Db::getInstance()->getValue($sql);      
        if ($count > 0) {
            $sql = "DELETE FROM `ps_controlling_marketing_source` WHERE id_controlling_source = ".$this->id." and date between '".$date_from."' and '".$date_to."' and ".$data_field." > 0";
            $result = Db::getInstance()->execute($sql);   
        }
    }    
    
private function getService()
    {
      // Creates and returns the Analytics service object.
    
      // Load the Google API PHP Client Library.
      require_once __DIR__ . '/Google/autoload.php';
    
      // Use the developers console and replace the values with your
      // service account email, and relative location of your key file.
      $service_account_email = 'tiborbossanyi@reporting1-1211.iam.gserviceaccount.com';
      $key_file_location = __DIR__ . '/Reporting1-9dcd1f09b6f8.p12';
    
      // Create and configure a new client object.
      $client = new Google_Client();
      $client->setApplicationName("AndioAnalytics");
      $analytics = new Google_Service_Analytics($client);
    
      // Read the generated client_secrets.p12 key.
      $key = file_get_contents($key_file_location);
      $cred = new Google_Auth_AssertionCredentials(  
          $service_account_email,
          array(Google_Service_Analytics::ANALYTICS_READONLY),
          $key
      );
      $client->setAssertionCredentials($cred);
      if($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion($cred);
      }
    
      return $analytics;
    }
    
    private function getFirstprofileId(&$analytics) {
      // Get the user's first view (profile) ID.
    
      // Get the list of accounts for the authorized user.
        $accounts = $analytics->management_accounts->listManagementAccounts();
    
        if (count($accounts->getItems()) > 0) {
            $items = $accounts->getItems();
            $firstAccountId = $items[0]->getId();
        
            // Get the list of properties for the authorized user.
            $properties = $analytics->management_webproperties
                ->listManagementWebproperties($firstAccountId);
        
            if (count($properties->getItems()) > 0) {
              $items = $properties->getItems();
              $firstPropertyId = $items[0]->getId();
        
              // Get the list of views (profiles) for the authorized user.
              $profiles = $analytics->management_profiles
                  ->listManagementProfiles($firstAccountId, $firstPropertyId);
                  
              //echo "Account " .$firstAccountId . '<br/>';
        
                if (count($profiles->getItems()) > 0) {
                    $items = $profiles->getItems();
            
                    // Return the first view (profile) ID.
                    return $items[0]->getId();
            
                } else {
                    throw new Exception('No views (profiles) found for this user.');
                }
            } else {
                throw new Exception('No properties found for this user.');
            }
        } else {
            throw new Exception('No accounts found for this user.');
        }
    }    
    
    public function collectIemSubscriptions ($listid, $date) {
        $iem = new iem_api();	
        $this->source_data->subscription_count = $iem->getSubscriptions($date, $listid);  
    }
    
    public function collectIemCampaignSubscriptions ($campaignid, $statusid, $date) {
        $iem = new iem_api();	
        $this->source_data->subscription_count = $iem->getCampaignSubscriptions($date, $campaignid, $statusid);          
    }    
    
    public function collectUtmData($entry_name, $date_from) {
        $conversions = 0;
        $analytics = $this->getService();
        $profile = $this->getFirstProfileId($analytics);            
        
        // get data 
        $next_date = $date_from;
        $results = $this->getResultsUtm($analytics, $profile, $next_date, $entry_name);
        if (isset($results)) {
            $rows = $results->getRows();
            $conversions = 0;
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    if ($row[1] > 0) {
                        $conversions+=$row[1];
                    }
                }
            }

            $this->source_data->subscription_count = $conversions;  
            
        }  else {
            Logger::addLog('NO UTM data on ' .$next_date);
        }                  
        return $conversions;
    }    
    
    private function getResultsUtm(&$analytics, $profileId, $date, $filter) {
        $source = unserialize($filter);
        try {
            $optParams = array(
            'dimensions' => $source['dimensions'],
            'filters' => $source['filter'],
            'max-results' => '10000');
Logger::addLog('UTM DEF: dimension ' .$source['dimensions'] . ", filter: " . $source['filter']);            
            return $analytics->data_ga->get(
              'ga:22560769',
              $date,
              $date,
              $source['metrics'],
            $optParams);
            
        } catch (apiServiceException $e) {
            // Handle API service exceptions.
            $error = $e->getMessage();
        } catch (Google_Service_Exception $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }    
    }    
    
    public function collectAndSaveFBData($next_date) {
        
        try {
            Api::init(ControllingSourceFB::FB_APP_ID, ControllingSourceFB::FB_APP_SECRET, ControllingSourceFB::FB_ACCESS_TOKEN);
            
            // get data from each day
            //$next_date = $this->date_from;
            $this->collectDataFacebookDay($next_date);
        } catch (AuthorizationException $e) {
            error_log($e->message . " subcode " . $e->subcode . " " . $e->error_user_title . " " . $e->error_user_msg);
        } catch (EmptyResponseException $e) {
        }
    }
    
    private function collectDataFacebookDay($start_date) {

        $ids = explode(",", trim($this->entry_source->entry_name));
        
        $this->source_data->reach = 0; 
        $this->source_data->visits = 0;
        $this->source_data->ad_expense = 0;
        $this->source_data->ctr = 0;

        try {
            foreach($ids as $id) {
    //Logger::addLog('Get FB data campaign id "' . $id . '" on ' . $start_date);          
                $campaign = new Campaign($id);
    //Logger::addLog('Campaing ok');
                $params = array(
                  'time_range' => array(
                    'since' => $start_date,
                    'until' => $start_date,
                  ),
                );
                $fields = array('reach', 'website_clicks','ctr', 'spend') ;
            
                $insights = $campaign->getInsights($fields, $params);
    //Logger::addLog('Insights ok');            
                $body =  $insights->getResponse()->getContent();
    //Logger::addLog('getResponse ok');            
        
                if (isset($body['data'][0])) {
                    $reach = $body['data'][0]['reach'];
                    $expense = $body['data'][0]['spend'];
                    $visits = $body['data'][0]['website_clicks'];
                    $ctr = $body['data'][0]['ctr'];
                    
                    Logger::addLog('FB data on ' .$start_date . ' expense ' . $expense . ' visits ' . $visits . ' CTR ' . $ctr);
                    $this->source_data->reach += $reach;
                    $this->source_data->visits += $visits;
                    $this->source_data->ad_expense += $expense;
                    $this->source_data->ctr += $this->source_data->visits / $this->source_data->reach;
                }
            }
        } catch (EmptyResponseException $e) {
            Logger::addLog('No FB activity on ' .$start_date );
        }
    }         
    
    private function collectMediline($id_controlling_source, $date_from) {
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        Logger::addLog('Collect data for Mediline');   
        $sql = 'SELECT distinct id_order, total_paid_tax_incl as total, o.date_add FROM ps_orders o
                inner join ps_customer_group g on o.id_customer = g.id_customer
                where id_group = 7
                and o.current_state <> 6 and o.current_state <> 7';
         $sql .= " and o.date_add between  '".$date_from."' AND '".$date_to."'";      

         $results = $this->getOrdersNew($sql);      
                
         if (count($results) >0) {
            return $results;           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $date_from."' AND '".$date_to);
            return null;
         }   
    }    
    
    private function collectRetailer($id_controlling_source, $date_from) {    
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        $sql = 'SELECT distinct id_order, total_paid_tax_incl as total, o.date_add FROM ps_orders o
                inner join ps_customer_group g on o.id_customer = g.id_customer
                where id_group = 5
                and o.current_state <> 6 and o.current_state <> 7';
         $sql .= " and o.date_add between  '".$date_from."' AND '".$date_to."'";
         $results = $this->getOrdersNew($sql);      
                
         if (count($results) >0) {
            return $results;           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $date_from."' AND '".$date_to);
            return null;
         }          
      
    }
    
    private function collectMainCustomer($id_controlling_source, $date_from) {
Logger::addLog('Collect data for Main Customer');   
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        $sql = 'SELECT distinct id_order, total_paid_tax_incl as total, o.date_add, o.id_customer FROM ps_orders o 
        inner join ps_customer_group g on o.id_customer = g.id_customer and g.id_group <> 5 and g.id_group <>7
        where o.id_customer in         
        (select id_customer from ps_orders o2 where o.id_customer = o2.id_customer having count(o2.id_customer) >= 5)';
        $sql .= " and o.date_add between  '".$date_from."' AND '".$date_to."'";
        $results = $this->getOrdersNew($sql);      
                
         if (count($results) >0) {
            return $results;           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $date_from."' AND '".$date_to);
            return null;
         }   
    }    
    
    private function collectCustomer($id_controlling_source, $date_from) {
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        $sql = 'SELECT distinct id_order, total_paid_tax_incl as total, o.date_add,  o.id_customer FROM ps_orders o 
        inner join ps_customer_group g on o.id_customer = g.id_customer and g.id_group <> 5 and g.id_group <>7
        where o.id_customer in         
        (select id_customer from ps_orders o2 where o.id_customer = o2.id_customer having count(o2.id_customer) >= 2 and count(o2.id_customer) < 5)';
        $sql .= " and o.date_add between  '".$date_from."' AND '".$date_to."'";
        $results = $this->getOrdersNew($sql);      
                
         if (count($results) >0) {
            return $results;           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $date_from."' AND '".$date_to);
            return null;
         }   
    }        
    
    private function collectAdhoc($id_controlling_source, $date_from) {

          $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
          ControllingSource::array_sort($this->orders_collected, 'id_order');          
          $order_array = $this->get_order_ids();
Logger::addLog('Nem egyeb db: ' .count($this->orders_collected));
          $results = array();
          if ($order_array != '') {
              $sql = 'SELECT date_format(date_add, "%Y-%m-%d") as date_add, sum(total_paid) as total, avg(total_paid) as kosar, id_order FROM `ps_orders` o
                        WHERE o.date_add between  "'.$date_from.'" AND "'.$date_to.'"
                        and id_order not in ('.$order_array.')
                        group by date_add
                        order by date_add';
Logger::addLog('Nem egyeb sql: ' .$sql);              
             $results = $this->getOrdersNew($sql);      
          }
                
         if (count($results) > 0) {
            return $results;           
         } else {
            Logger::addLog("No data for " .$id_controlling_source . " between " . $date_from."' AND '".$date_to);
            return null;
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
}
?>