<?php 
require_once 'DisplayData.php';
class Margin extends DisplayData {
    

    
	public function display() {

	$this->description =$this->l('Margin pro order');
	
    	/* define the fields */
  // eg: 'field_name|asc|avg':
  // NameOfTheField | ORDERY:ASC/DESC | AGGREGATE: SUM/AVG/..
  // For Thousand separator: use an aggregate function (sum/avg/..)
        $fields = array();
	$fields[] = 'year|desc';
	$fields[] = 'month|desc';
	$fields[] = 'week|desc';
	$fields[] = 'day|desc';
	$fields[] = 'id_order|asc|sum';
	$fields[] = 'brutto|asc|sum';
	$fields[] = 'margin|asc|sum';
	$fields[] = 'shipping|asc|sum';
	$fields_ready = '';
        
        foreach ($fields as $kulcs => $ertek) {
    		$reszek = explode('|', $fields[$kulcs]);
    		$fields_ready .= "{name: '" . $kulcs . "',caption: '" . $reszek[0] . "'";
    		if ($reszek[1] != ""){
    			$fields_ready.= ", sort: {order: '".$reszek[1]."'},";      
    		}
    		if (isset($reszek [2]) && $reszek [2] != "") {
    			 $fields_ready.="dataSettings: {
                      aggregateFunc: '" . $reszek[2] . "',
                      formatFunc: function(value) {
                      value = Math.round(value);
                      return value ? value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, \"$1 \") : '';}},";
    		}	
    	    $fields_ready.= "},";
    	
    	    $this->fields = $fields_ready;
        }

    	/* define the colums, rows, data fields*/	
    	$this->fields_start =
    	    "rows    : [ 'year', 'month', 'day',  'id_order'],
            columns  : [ ],
            data     : [ 'brutto', 'margin', 'shipping' ]";	    
    	    

	    

	    /* get data bev */

	    $sql = '
SELECT  
        YEAR(date_add) as year,
	    MONTH(date_add) as month,
	    WEEKOFYEAR(date_add) as week,
	    DAY(date_add) as day,   
        id_order, 
        total_paid as brutto, 
        GetMargin(id_order) as margin, 
        GetShippingCost(id_order) as shipping,
        date_add 
FROM `ps_orders` o
WHERE date_add > "2017-01-01"
and current_state!=6 AND current_state!=7 
GROUP BY id_order, YEAR(date_add) , MONTH(date_add),DAY(date_add)
order by id_order DESC;
			';
        $result = Db::getInstance()->executeS($sql);
        
		$this->data = '';
        foreach ($result as $field => $value) {
        	$month	= str_pad($value['month']	, 2, '0', STR_PAD_LEFT);
        	$week	= str_pad($value['week']	, 2, '0', STR_PAD_LEFT);
        	$day	= str_pad($value['day']		, 2, '0', STR_PAD_LEFT);
			$value['netto']=ROUND ($value['brutto']/1.27);

            //$sql_shipping = 'call GetShippingCost('.$value['id_order'].')';
            //$shipping = Db::getInstance()->query($sql_shipping);

        	$sor 	= "[
        	'".$value['year']."',
        	'".$month."',
        	'".$week."',
        	'".$day."',
        	".$value['id_order'].",      
        	".$value['brutto'].",        	        	
			".$value['margin'].",
			".$value['shipping'].",
        	],";   
            $this->data .= $sor = str_replace(array("\r", "\n"), '', $sor);
        }	
		

        $result = Db::getInstance()->executeS($sql);	    
		
		
        Logger::addLog('display:: data0 ' . $this->data);       
	    	    
	    $rc = parent::display();

	    return $rc;
	}
}

?>