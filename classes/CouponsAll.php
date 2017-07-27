<?php 
require_once 'DisplayData.php';
class CouponsAll extends DisplayData {
    
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
    $fields[] = 'coupon|asc';
    $fields[] = 'bevetel|asc|sum';
		$fields[] = 'db|asc|sum';
		$fields[] = 'coupon_cat|asc';


        
        $fields_ready = '';
        foreach ($fields as $kulcs => $ertek) {
            		$reszek = explode('|', $fields[$kulcs]);
            		$fields_ready .= "{name: '" . $kulcs . "',caption: '" . $reszek[0] . "'";
            		if ($reszek[1] != ""){
            			$fields_ready.= ", sort: {order: '".$reszek[1]."'},";      
            		}
            		if (isset($reszek [2]) && $reszek [2] != "") {
            		     if ( $reszek[0] == 'reg_sz' || $reszek[0] == 'rend_sz') {
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
        
        
     
    	/* define the colums, rows, data fields*/	
    	$this->fields_start =
    	     "rows     : [ 'coupon_cat', 'coupon' ],
            columns  : [ 'year', 'month', 'day' ],
            data     : [ 'db' ]";	    

	    /* Get Order Amounts and Date for the marketing cupons */

$sql = '
SELECT
	YEAR (o.date_add) AS year,
	MONTH(o.date_add) AS month,
	WEEK (o.date_add) AS week,
	DAY  (o.date_add) AS day,
	CONCAT_WS("-", cr.code, ccr.id_cart_rule) AS coupon,
	cr.id_cart_rule AS coupon_id,
	SUM(o.total_paid_real) AS bevetel,
	COUNT(*) AS db
FROM
	`ps_cart_cart_rule` ccr
	INNER JOIN ps_cart_rule cr ON cr.id_cart_rule = ccr.id_cart_rule
	INNER JOIN ps_orders o ON o.id_cart = ccr.id_cart
WHERE
	o.current_state!=6 AND
	o.current_state!=7 AND
	YEAR (o.date_add)=2017 AND
	cr.code NOT LIKE "ANDIO%"
GROUP BY
	year,month,day, ccr.id_cart_rule
';


        $result = Db::getInstance()->executeS($sql);
 
        $this->data = '';
        foreach ($result as $field => $value) {
        	$month	= str_pad($value['month'], 2, '0', STR_PAD_LEFT);
        	$week	= str_pad($value['week'], 2, '0', STR_PAD_LEFT);
        	$day	= str_pad($value['day'], 2, '0', STR_PAD_LEFT);  //
        	$coupon_first_letters = explode('-', $value['coupon']);
        	$coupon_first_letters = substr($coupon_first_letters[0],0,4); 
        	
        	$sor 	= "[
        	'".$value['year']."',
        	'".$month."',
        	'".$week."',        	
        	'".$day."',
            '".$value['coupon']."',           	
            ".$value['bevetel'].",           	
        	".$value['db'].",
        	'".$coupon_first_letters."'
       	   	

        	],";   
            $this->data .= $sor = str_replace(array("\r", "\n"), '', $sor);
        }
        Logger::addLog('display:: FunnelData  ' . $this->data);       
	    	    
	    $rc = parent::display();

	    return $rc;
	}
}

?>