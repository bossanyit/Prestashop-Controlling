<?php 
require_once 'DisplayData.php';
class CampaignData extends DisplayData {
    
	public function display() {
    	/* define the fields */
  // eg: 'field_name|asc|avg':
  // NameOfTheField | ORDERY:ASC/DESC | AGGREGATE: SUM/AVG/..
  // For Thousand separator: use an aggregate function (sum/avg/..)
    $fields = array();
    $fields[] = 'year|desc';
    $fields[] = 'month|desc';
    $fields[] = 'week|desc';
    $fields[] = 'day|desc';
    $fields[] = 'oto|asc|sum';
    $fields[] = 'oto_kosar|asc|avg';
	$fields[] = 'rend|asc|sum';
	$fields[] = 'rend_sz|asc|avg';
	$fields[] = 'atlag_kosar|asc|avg';
	$fields[] = 'ossz|asc|sum';
	$fields[] = 'arres|asc|sum';
	$fields[] = 'fedezet|asc|sum';
	$fields[] = 'kampany|asc';
	$fields[] = 'ad|asc|sum';
	$fields[] = 'visit|asc|sum';
	$fields[] = 'CTR|asc|avg';
	$fields[] = 'sub|asc|sum';
	$fields[] = 'sub_sz|asc|avg';
        
        $fields_ready = '';
        foreach ($fields as $kulcs => $ertek) {
            		$reszek = explode('|', $fields[$kulcs]);
            		$fields_ready .= "{name: '" . $kulcs . "',caption: '" . $reszek[0] . "'";
            		if ($reszek[1] != ""){
            			$fields_ready.= ", sort: {order: '".$reszek[1]."'},";      
            		}
            		if (isset($reszek [2]) && $reszek [2] != "") {
            		     if ($reszek[0] == 'CTR' || $reszek[0] == 'sub_sz' || $reszek[0] == 'rend_sz') {
  	    			 		$fields_ready.="dataSettings: {
  	                          aggregateFunc: '" . $reszek[2] . "',
  	                          formatFunc: function(value) {
  	                            value = Number((value).toFixed(2));
  	                            console.log('".$reszek [0]."' + value);
  	                            return value;}},";

            		     } else {
                			 $fields_ready.="dataSettings: {
                                  aggregateFunc: '" . $reszek[2] . "',
                                  formatFunc: function(value) {
                                  value = Math.round(value);
                                  return value ? value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, \"$1 \") : '';}},";
                                
                         }
            		}	
            	    $fields_ready.= "},";
            	
            	    $this->fields = $fields_ready;
        }
        
        
        /*foreach ($fields as $kulcs => $ertek) {
	    		$reszek = explode('|', $fields[$kulcs]);
	    		$fields_ready .= "{name: '" . $kulcs . "',caption: '" . $reszek[0] . "'";
	    		if (isset($reszek[1]) ) {
	    			$fields_ready.= ", sort: {order: '".$reszek[1]."'},";      
	    		}
	    		
                

	    		
	    		if (isset($reszek [2]) && $reszek [2] != "") {
	    			 if ($reszek[0] == 'CTR') {
	    			 		$fields_ready.="dataSettings: {
	                          aggregateFunc: '" . $reszek[2] . "',
	                          formatFunc: function(value) {
	                            value = Number((value).toFixed(2));
	                            console.log('".$reszek [0]."' + value);
	                            return value;
	                          }
	                       },";    			
	    			 } else { 
	    			 		$fields_ready.="dataSettings: {
    	                      aggregateFunc: '" . $reszek[2] . "',
    	                      formatFunc: function(value) {
        	                      value = Math.round(value);
        	                      return value ? value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, \"$1 \") : '';
        	                  }
        	               },";    		
	                 }	
	    	         $fields_ready.= "},";
	    	     
	    	
	    	        $this->fields = $fields_ready;
	            }
      	}*/
    	/* define the colums, rows, data fields*/	
    	$this->fields_start =
    	    "rows    : [ 'year', 'month', 'week', 'kampany' ],
            columns  : [  ],
            data     : [ 'ad', 'visit', 'CTR', 'sub', 'sub_sz', 'oto', 'rend','rend_sz','ossz','arres' ,'fedezet' ]";	    
    	    

	    
	    /* get data */
	    $sql = '
SELECT
    position,
    ms.id_controlling_source,
    YEAR(date) as year,
	MONTH(date) as month,
	WEEKOFYEAR(date) as week,
    DAY(date) as day,
    sum(ms.oto_count) as oto,
    max(ms.oto_avg) as oto_kosar,
	sum(ms.order_count) as rend,
    sum(ms.order_count / subscription_count) as rend_sz,
	max(ms.avg_cart) as atlag_kosar,
	sum(ms.income_sum) as ossz,
	sum(ms.income_sum)*0.4 as arres,
	sum(ms.income_sum)*0.4 - sum(ad_expense) as fedezet,
	cs.name as kampany,
	sum(ad_expense) as ad,
	sum(visits) as visit,
	max(ctr) as CTR,
	sum(subscription_count) as sub,
    sum(subscription_count/ visits)  as sub_sz
FROM
ps_controlling_marketing_source ms
RIGHT JOIN ps_controlling_source cs ON ms.id_controlling_source=cs.id_controlling_source
where date >= "2016-01-01" and position < 20
group by year, month, week, day, cs.id_controlling_source
ORDER BY year desc, month, week, day, cs.position asc 
'; //where cs.id_source = 1
        $result = Db::getInstance()->executeS($sql);
 
        $this->data = '';
        foreach ($result as $field => $value) {
        	$month	= str_pad($value['month'], 2, '0', STR_PAD_LEFT);
        	$week	= str_pad($value['week'], 2, '0', STR_PAD_LEFT);
        	$day	= str_pad($value['day'], 2, '0', STR_PAD_LEFT);  //
        	$sor 	= "[
        	'".$value['year']."',
        	'".$month."',
        	'".$week."',        	
        	'".$day."',
        	".$value['oto'].",
        	".$value['oto_kosar'].",
        	".$value['rend'].",
        	".$value['rend_sz'].",
        	".$value['atlag_kosar'].",
        	".$value['ossz'].",
        	".$value['arres'].",
        	".$value['fedezet'].",     
          	'".$value['kampany']."',        	
        	".$value['ad'].",
        	".$value['visit'].",
        	".$value['CTR'].",
        	".$value['sub'].",        	   	
        	".$value['sub_sz'].",        	   	

        	],";   
            $this->data .= $sor = str_replace(array("\r", "\n"), '', $sor);
        }
        Logger::addLog('display:: MarketingData  ' . $this->data);       
	    	    
	    $rc = parent::display();

	    return $rc;
	}
}

?>