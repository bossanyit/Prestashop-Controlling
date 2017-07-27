<?php 
require_once 'DisplayData.php';
class MarketingData extends DisplayData {
    
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
    $fields[] = 'kampany|asc';
    $fields[] = 'oto|asc|sum';
    $fields[] = 'oto_cart|asc|avg';
    $fields[] = 'oto_sum|asc|avg';
	$fields[] = 'rend|asc|sum';
	$fields[] = 'atlag_kosar|asc|avg';
	$fields[] = 'ossz|asc|sum';
	$fields[] = 'arres|asc|sum';
	$fields[] = 'fedezet|asc|sum';
	
        
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
    	    "rows    : [ 'year', 'month', 'day', 'kampany' ],
            columns  : [  ],
            data     : [ 'oto', 'oto_cart', 'oto_sum', 'rend', 'atlag_kosar', 'ossz','arres' ,'fedezet' ]";	    
    	    

	    
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
    sum(ms.oto_avg) as oto_cart,
    sum(ms.oto_sum) as oto_sum,
	sum(ms.order_count) as rend,
	max(ms.avg_cart) as atlag_kosar,
	sum(ms.income_sum) as ossz,
	sum(ms.income_sum)*0.4 as arres,
	sum(ms.income_sum)*0.4 - sum(ad_expense) as fedezet,
	cs.name as kampany
FROM
ps_controlling_marketing_source ms
RIGHT JOIN ps_controlling_source cs ON ms.id_controlling_source=cs.id_controlling_source
where date >= "2016-01-01"
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
          	'".$value['kampany']."',  
        	".$value['oto'].",
        	".$value['oto_cart'].",
        	".$value['oto_sum'].",
        	".$value['rend'].",
        	".$value['atlag_kosar'].",
        	".$value['ossz'].",
        	".$value['arres'].",
        	".$value['fedezet'].",           	
        	],";   
            $this->data .= $sor = str_replace(array("\r", "\n"), '', $sor);
        }
        Logger::addLog('display:: MarketingData  ' . $this->data);       
	    	    
	    $rc = parent::display();

	    return $rc;
	}
}

?>