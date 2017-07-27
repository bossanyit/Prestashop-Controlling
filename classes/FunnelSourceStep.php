<?php

use Facebook\Facebook;
use FacebookAds\Api as Api;
use FacebookAds\Object\Campaign as Campaign;
use FacebookAds\Object\InsightsPresets as InsightsPresets;  
require_once __DIR__ . '/Google/autoload.php';

class FunnelSourceStep extends ObjectModel {
    public $id;
    
    public $step_name;
    
    public $funnel_source_type;
    
    public $funnel_source;
    
    public $dimensions;
    
    public $filter;
    
    public $date_from;
    
    public $index_field;
    
    public $object_data = null;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'controlling_funnel_step_source',
        'primary' => 'id_funnel_step_source',
        'multilang' => false,
        'fields' => array(
            'step_name'  =>               array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'funnel_source_type' =>       array('type' => self::TYPE_INT,   'validate' => 'isInt'),
            'funnel_source' =>            array('type' => self::TYPE_STRING, 'validate' => 'isString')
        ),
    );


    var  $FB_SDK_DIR ;
    var  $FB_SDKAD_DIR;
    
    public function __construct($id) {
        
        parent::__construct($id);
        if (!isset($id) || trim($id) == '' || $id == 0) {
            $this->id = 0;
        }
        
        $this->dimensions = '';
        $this->filter = '';
       
        $this->FB_SDK_DIR = __DIR__ . '/Facebook';
        $this->FB_SDKAD_DIR = __DIR__ . '/FacebookAds';        
        
        $loader_fb = include $this->FB_SDK_DIR . '/autoload.php';
        $loader_fb_ad = include $this->FB_SDKAD_DIR . '/autoload.php';        
    }
    
    public function get_funnel_step_data($date_from, $id_funnel, $index_field, &$object) {
        $this->date_from = $date_from;
        $this->index_field = $index_field;
        $this->object_data = $object;
        switch($this->funnel_source_type) {
            case 0: // email, get email tracking event from GA
                $this->collectEmailTrackingData();
                break;
            case 1: // facebook ad    
                $this->collectAndSaveFBData();
                break;
            case 2: // manual data
                break;            
            case 3: // adwords ad
                break;           
            case 4: // website visit from google analytics
                $this->collectPageViewsData();
                break;            
            case 5: // utm event from google analyitcs
                $this->collectUtmData();
                break;            
            case 6: // order data from coupons
                $this->collectOrderData();
                break;                 
            default:
                $message = sprintf('FUNNEL step %s (%d) source type %d is unknown', $this->step_name, $this->id, $this->funnel_source_type);
                Logger::addLog($message);
                throw new PrestaShopException($message);
                break;
        }
        return $this->object_data;
    }
    
    public function collectAndSaveFBData() {
        
        try {
            Api::init(ControllingSourceFB::FB_APP_ID, ControllingSourceFB::FB_APP_SECRET, ControllingSourceFB::FB_ACCESS_TOKEN);
            
            // get data from each day
            $next_date = $this->date_from;
            $this->collectDataFacebookDay($next_date);
        } catch (AuthorizationException $e) {
            error_log($e->message . " subcode " . $e->subcode . " " . $e->error_user_title . " " . $e->error_user_msg);
        }
    }
    
    private function collectDataFacebookDay($start_date) {

        $campaign = new Campaign($this->funnel_source);
        $params = array(
          'time_range' => array(
            'since' => $start_date,
            'until' => $start_date,
          ),
        );
        $fields = array('reach', 'website_clicks','ctr','spend', 'cost_per_unique_click') ;
    
        $insights = $campaign->getInsights($fields, $params);
        $body =  $insights->getResponse()->getContent();
        //print_r($body['data']);
        
        /*foreach ($body['data'][0] as $field => $value) {
            //echo $field . '=>' . $value . '<br />';
            //Logger::addLog('Collect FB on ' .$start_date . $field . '=>' . $value );
        } */ 
        if (isset($body['data'][0])) {
            $reach = $body['data'][0]['reach'];
            $expense = $body['data'][0]['spend'];
            $visits = $body['data'][0]['website_clicks'];
            $ctr = $body['data'][0]['ctr'];
            
            Logger::addLog('FB data on ' .$start_date . ' expense ' . $expense . ' visits ' . $visits . ' CTR ' . $ctr);
            $this->object_data->reach = $reach;
            $this->object_data->reach_expense = $expense;
            $this->object_data->step1_clicks = $visits;
        }
    }     
    
    public function collectEmailTrackingData() {
//Logger::addLog('Get GA email tracking events...');
        $conversions = 0;
        $analytics = $this->getService();
        $profile = $this->getFirstProfileId($analytics);          
        
        // get data from each day
        $next_date = $this->date_from;
        $results = $this->getResultsEvents($analytics, $profile, $next_date, $this->funnel_source);
        if (isset($results)) {
            $rows = $results->getRows();
            $conversions = 0;
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    if ($row[2] > 0) {
                        $conversions+=$row[2];
                    }
                }
            }
           //Logger::addLog('SOURCE EVENT count ' . count($rows) );
            if ($conversions > 0) {
                //Logger::addLog('SOURCE GA emal events on ' .$next_date . ": " . $conversions);
                $this->setObjectData($conversions);
            }
            
        }   else {
            //Logger::addLog('NO Evnet information on ' .$next_date);
        }
        return $conversions;
    }
    
    public function collectUtmData() {
        $conversions = 0;
        $analytics = $this->getService();
        $profile = $this->getFirstProfileId($analytics);            
        
        // get data 
        $next_date = $this->date_from;
        $results = $this->getResultsUtm($analytics, $profile, $next_date, $this->funnel_source);
        if (isset($results)) {
            $rows = $results->getRows();
            $conversions = 0;
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    if ($row[3] > 0) {
                        $conversions+=$row[3];
                    }
                }
            }
            //Logger::addLog('SOURCE UTM on count ' . count($rows) );
            //if ($conversions > 0) {
                //Logger::addLog('SOURCE UTM on ' .$next_date . ": " . $conversions);
                $this->setObjectData($conversions);  
            //}
            
        }  else {
            Logger::addLog('NO UTM data on ' .$next_date);
        }                  
        return $conversions;
    }
    
    public function collectPageViewsData() {
        $conversions = 0;
        $analytics = $this->getService();
        $profile = $this->getFirstProfileId($analytics);
        
        // get data from each day
        $next_date = $this->date_from;

        if (strpos($this->funnel_source,'ga:pagePath') != false) {
            $results = $this->getResultsPageTrackingByPath($analytics, $profile, $next_date, $this->funnel_source);
        } else {
            $results = $this->getResultsPageTrackingByTitle($analytics, $profile, $next_date, $this->funnel_source);
        }
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
            
            if ($conversions > 0) {
                Logger::addLog('SOURCE GA pageviews on ' .$next_date . ": " . $conversions);
                //$this->save($conversions, $next_date);   
                $this->setObjectData($conversions); 
            }
            
        }                    
        return $conversions;
    }
    
    private function getResultsPageTrackingByTitle(&$analytics, $profileId, $date, $filter) {

        try {
            $optParams = array(
            'dimensions' => 'ga:pageTitle',
            'filters' => $filter,
            'max-results' => '10000');
            
            return $analytics->data_ga->get(
              'ga:22560769',
              $date,
              $date,
              'ga:uniquePageviews',
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
    
    private function getResultsPageTrackingByPath(&$analytics, $profileId, $date, $filter) {

        try {
            $optParams = array(
            'dimensions' => 'ga:pagePath',
            'filters' => $filter,
            'max-results' => '10000');
            
            return $analytics->data_ga->get(
              'ga:22560769',
              $date,
              $date,
              'ga:uniquePageviews',
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
    
    private function getResultsUtm(&$analytics, $profileId, $date, $filter) {
        $source = unserialize($filter);
        try {
            $optParams = array(
            'dimensions' => $source['dimensions'],
            'filters' => $source['filter'],
            'max-results' => '10000');
//Logger::addLog('UTM DEF: dimension ' .$dimensions . ", filter: " . $filter);            
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
    
    private function getResultsEvents(&$analytics, $profileId, $date, $filter) {

        try {
            $dimensions = 'ga:eventCategory,ga:eventAction';
            $optParams = array(
            'dimensions' => $dimensions,
            'filters' => $filter,
            'max-results' => '10000');
//Logger::addLog('Event DEF: dimension ' .$dimensions . " -  filter: " . $filter);                        
            return $analytics->data_ga->get(
              'ga:22560769',
              $date,
              $date,
              'ga:totalEvents',
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
    
    private function getResults(&$analytics, $profileId, $date) {
        if (!$this->update && $this->this_day_saved($this->id_source, $date)) {
            return null;
        }
        try {
            $optParams = array(
            'dimensions' => 'ga:goalCompletionLocation',
            'max-results' => '1000');
            
            return $analytics->data_ga->get(
              'ga:22560769',
              $date,
              $date,
              'ga:goal'.$this->id_goal.'Completions',
            $optParams);
            
            return $results;
        } catch (apiServiceException $e) {
            // Handle API service exceptions.
            $error = $e->getMessage();
        } catch (Google_Service_Exception $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }    
    
    private function setObjectData($data)  {
        switch ($this->index_field) {
            case 1:
                $this->object_data->step1_clicks = $data;
                break;
            case 2:
                $this->object_data->step2_data = $data;
                break;
            case 3:
                $this->object_data->step3_data = $data;
                break;
            case 4:
                $this->object_data->cart_data = $data;
                break;
            case 5:
                $this->object_data->order_data = $data;
                break;
            default:
                $message = sprintf('FUNNEL data save: wrong index field data (%d)', $this->index_field);
                Logger::addLog($message);
                throw new PrestaShopException($message);
                break;
        }                    
                
    }
    
    private function collectOrderData() {
        $rc = $this->getCouponOrders($this->funnel_source, $this->date_from);
    }
    
    public function getCouponOrders($coupons, $date_from) {
        if (empty($coupons) || $coupons == "" ) return array();                
        
        $date_to = date('Y-m-d', strtotime($date_from . ' +1 day'));
        
        $sql = "SELECT count(o.id_order) as orders FROM `ps_orders` o
                inner join ps_order_cart_rule ocr on o.id_order = ocr.id_order
                where ocr.id_cart_rule in (" . $coupons. ")  and o.date_add between  '".$date_from."' AND '".$date_to."'";

        $orders = Db::getInstance()->getValue($sql); 
        $this->setObjectData($orders);
              
    }
}
?>