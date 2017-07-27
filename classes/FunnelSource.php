<?php

require_once __DIR__ . '/Google/autoload.php';
require_once 'FunnelSourceStep.php';
require_once 'FunnelData.php';
class FunnelSource {
        /** @var string Object creation date */
    public $date_from;
    
    /** @var string campaign end */
    public $date_end;  
    
    public $id_controlling_funnel;
    
    public $update;
    
    public function __construct($date_from, $date_to) {
Logger::addLog('FUNNEL data collection start ' . $date_from);        
        $this->date_from = $date_from;
        $this->date_to = $date_to;        
    }
    
    public function collect($update = false) {
        $this->update = $update;
        
        $sql = "SELECT f.id_controlling_funnel, funnel_name, 
        	fs1.step_name as n1, fs1.funnel_source_type as st1, fs1.funnel_source as s1, fs1.id_funnel_step_source as id1,
        	fs2.step_name as n2, fs2.funnel_source_type as st2, fs2.funnel_source as s2, fs2.id_funnel_step_source as id2,
        	fs3.step_name as n3, fs3.funnel_source_type as st3, fs3.funnel_source as s3, fs3.id_funnel_step_source as id3,
        	fs4.step_name as n4, fs4.funnel_source_type as st4, fs4.funnel_source as s4,  fs4.id_funnel_step_source as id4,
        	fs4.step_name as n5, fs5.funnel_source_type as st5, fs5.funnel_source as s5,  fs5.id_funnel_step_source as id5
        
            FROM `ps_controlling_funnel` f
            inner join ps_controlling_funnel_step_source fs1 on f.id_step1_source = fs1.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs2 on f.id_step2_source = fs2.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs3 on f.id_step3_source = fs3.id_funnel_step_source
            inner join ps_controlling_funnel_step_source fs4 on f.id_cart_source = fs4.id_funnel_step_source
            inner join ps_controlling_funnel_step_source fs5 on f.id_order_source = fs5.id_funnel_step_source
            order by position";
        $funnels = Db::getInstance()->executeS($sql); 
        $next_date = date('Y-m-d', strtotime($this->date_from . ' +1 day'));
        while ($this->date_to >= $next_date) {               
            foreach ($funnels as $funnel) {
               $funnel_data = new FunnelData($funnel['id_controlling_funnel'], $next_date);
//Logger::addLog(sprintf('NEW CSATORNA %s on %s: ', $funnel['funnel_name'], $next_date));
                for ($i = 1; $i <= 5; $i++) {
                    $id = $funnel['id'.trim($i)];
    
                    if (!is_null($id) && isset($id) ) {
    
                        $source_step = new FunnelSourceStep($id);                        
                        $funnel_data = &$source_step->get_funnel_step_data($next_date, $funnel['id_controlling_funnel'], $i, $funnel_data);
                        
                    }
                    
                }
                $this->saveFunnelData($funnel['id_controlling_funnel'], $next_date, $funnel_data, $funnel['funnel_name']);
            }
            $next_date = date('Y-m-d', strtotime($next_date . ' +1 day'));
        }
    }
    
    private function saveFunnelData($id_controlling_funnel, $next_date,  &$funnel_data, $funnel_name) {
        Logger::addLog(sprintf('CSATORNA %s on %s: [reach: %d] [reach_exp %f] [step_1open: %d] [step1click %d] [step2 %d] [step3 %d] [cart %d] [order %d] ',
                $funnel_name,
                $next_date,
                $funnel_data->reach,
                $funnel_data->reach_expense,
                $funnel_data->step1_open,
                $funnel_data->step1_clicks,
                $funnel_data->step2_data,
                $funnel_data->step3_data,
                $funnel_data->cart_data,
                $funnel_data->order_data));
        $funnel_data->id_controlling_funnel = $id_controlling_funnel;
        $funnel_data->date = $next_date;
        $funnel_data->save();
    }
}

?>