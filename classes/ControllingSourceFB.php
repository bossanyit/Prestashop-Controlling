<?php

use Facebook\Facebook;
use FacebookAds\Api as Api;
use FacebookAds\Object\Campaign as Campaign;
use FacebookAds\Object\InsightsPresets as InsightsPresets;  
        
class ControllingSourceFB {
    public $date_from;
    
    /** @var string campaign end */
    public $date_end;  
    
    public $id_controlling_source;
    
    public $id_campaign;    
    
    public $update;
    
    var  $FB_SDK_DIR ;
    var  $FB_SDKAD_DIR;
    
    const FB_ACCESS_TOKEN = 'EAAQtFgm41CYBALIshX5KASevUx196ovhLKwjZCvqiZCZCQQIeRnTVJZAF0mLFkH76xC4NU0UrQMxC0xua8ZBLBy8frBYcQWPw3qnaOL3ZA0D10ZCpQZBHpOWyTFZAdDtOX4ZAV2UKX5lp2EtFpXihR0Kkqlct84zFLuHIZD';
    const FB_APP_ID = '1175472582480934';
    const FB_APP_SECRET = '43552e21125756e939d451991e5e2712';   
    
    public function __construct($date_from, $date_to, $id_controlling_source, $id_campaign) {
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->id_controlling_source = $id_controlling_source;
        $this->id_campaign = $id_campaign;
       
        $this->FB_SDK_DIR = __DIR__ . '/Facebook';
        $this->FB_SDKAD_DIR = __DIR__ . '/FacebookAds';        
        
        $loader_fb = include $this->FB_SDK_DIR . '/autoload.php';
        $loader_fb_ad = include $this->FB_SDKAD_DIR . '/autoload.php';        

    }
    
    public function collectAndSaveFBData($update = false) {
        $this->update = $update;
        try {
            Api::init(ControllingSourceFB::FB_APP_ID, ControllingSourceFB::FB_APP_SECRET, ControllingSourceFB::FB_ACCESS_TOKEN);
            if ($this->date_from > $this->date_to) {
                return;
            }
            
            if (!isset($this->date_from) ||$this->date_from == '' || $this->date_from == '1970-01-01') {
                $this->date_from = '2015-12-31';
            }
            // get data from each day
            $next_date = date('Y-m-d', strtotime($this->date_from . ' +1 day'));
            while ($this->date_to >= $next_date) {
                $this->collectDataFacebookDay($next_date);
                $next_date = date('Y-m-d', strtotime($next_date . ' +1 day'));
            }
        } catch (AuthorizationException $e) {
            error_log($e->message . " subcode " . $e->subcode . " " . $e->error_user_title . " " . $e->error_user_msg);
        }
    }
    
    private function collectDataFacebookDay($start_date) {
        if (!$this->update && $this->this_day_saved($this->id_controlling_source, $start_date)) {
            Logger::addLog('FB Source ' .$this->id_controlling_source . ' data has been collected already on ' .$start_date);
            return;
        }
        $campaign = new Campaign($this->id_campaign);
        $params = array(
          'time_range' => array(
            'since' => $start_date,
            'until' => $start_date,
          ),
        );
        $fields = array('website_clicks','ctr','spend', 'cost_per_unique_click') ;
    
        $insights = $campaign->getInsights($fields, $params);
        $body =  $insights->getResponse()->getContent();
        //print_r($body['data']);
        
        /*foreach ($body['data'][0] as $field => $value) {
            //echo $field . '=>' . $value . '<br />';
            //Logger::addLog('Collect FB on ' .$start_date . $field . '=>' . $value );
        } */ 
        if (isset($body['data'][0])) {
            $expense = $body['data'][0]['spend'];
            $visits = $body['data'][0]['website_clicks'];
            $ctr = $body['data'][0]['ctr'];
            
            Logger::addLog('FB data on ' .$start_date . ' expense ' . $expense . ' visits ' . $visits . ' CTR ' . $ctr);
            $this->save($expense, $visits, $ctr, $start_date);
        }
    }     
    
    private function save($expense, $visits, $ctr, $date) {
        if ($this->update)  {
            $this->checkExistsingData($this->id_controlling_source, 'ad_expense', $date);
        }          
        $marketing_source = new ControllingMarketingSource($date, $this->id_controlling_source);
        $marketing_source->id_controlling_source = $this->id_controlling_source;  
        $marketing_source->ad_expense = $expense;
        $marketing_source->visits = $visits;
        $marketing_source->ctr = $ctr;
        $marketing_source->date = $date;         
        $marketing_source->save();        
    }       
    
    public function this_day_saved($id_controlling_source, $date) {
        $sql = 'SELECT ad_expense FROM `ps_controlling_marketing_source` WHERE id_controlling_source = '.$id_controlling_source.' and date = "'.$date.'"';
        $value = Db::getInstance()->getValue($sql); 
        return $value > 0;
    }    
  
    private function checkExistsingData($id_controlling_source, $data_field, $date) {
        return false;
        $sql = "SELECT ".$data_field." FROM `ps_controlling_marketing_source` WHERE id_controlling_source = ".$id_controlling_source." and date = '".$date."' and ".$data_field." > 0";
        $count = Db::getInstance()->getValue($sql);     
Logger::addLog('check FB data on ' . $date  . ' count: ' . $count);      
        if ($count > 0) {
            $sql = "DELETE FROM `ps_controlling_marketing_source` WHERE id_controlling_source = ".$id_controlling_source." and date = '".$date."' and ".$data_field." > 0";
            $result = Db::getInstance()->execute($sql);   
        }
    }           
}

?>