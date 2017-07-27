<?php 
require_once 'DisplayData.php';
class IncomeProductCat extends DisplayData {
    
	public function display() {
	
	$this->description ='';
	
  /* define the fields */
  $fields = array(); 
  // eg: 'field_name|asc|avg':
  // NameOfTheField | ORDERY:ASC/DESC | AGGREGATE: SUM/AVG/..
  // For Thousand separator: use an aggregate function (sum/avg/..)
    	$fields[] = 'year|desc';
    	$fields[] = 'month|desc';
    	$fields[] = 'week|desc';
    	$fields[] = 'day|desc';
      $fields[] = 'brutto|asc|sum';
      $fields[] = 'netto|asc|sum';
      $fields[] = 'rendeles|asc|sum';
      $fields[] = 'termek|asc|sum';
      $fields[] = 'kategoria|asc';
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
                      return value ? value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, \"$1 \") : '';}},";    		}	     
    	    $fields_ready.= "},";
    	
    	    $this->fields = $fields_ready;
        }

    	/* define the colums, rows, data fields*/	
    	$this->fields_start =
    	      "rows    : [ 'kategoria' ],
            columns  : [ 'year','month' ],
            data     : [ 'brutto' ]";
            


////////////////////////////////////////////////////////////////////////////
/* get data All exept Mediline */

///* get Csak napok Detail */
$sql_01 = '
SELECT 
cat_lang.name AS kategoria,
COUNT(od.unit_price_tax_incl) AS termek,
SUM(od.unit_price_tax_incl*od.product_quantity) AS brutto,
cat.id_category AS kat_id,
YEAR(ord.date_add) AS year,
MONTH(ord.date_add) AS month,
WEEKOFYEAR(ord.date_add) AS week,
DAY(ord.date_add) AS day
FROM 
ps_order_detail od
INNER JOIN ps_category_product cp ON od.product_id =cp.id_product
INNER JOIN ps_orders ord ON ord.id_order = od.id_order AND ord.current_state NOT IN (6,7) AND ord.id_customer <>13110
INNER JOIN ps_category cat ON cat.id_category=cp.id_category AND cat.id_category IN (57, 48, 60, 123)  AND cat.active=1
INNER JOIN ps_category_lang cat_lang ON cat_lang.id_category=cat.id_category AND cat_lang.id_lang=2
GROUP BY
year,month,day
';
$result_01 = Db::getInstance()->executeS($sql_01);
$this->data = ''; $value_01='';
foreach ($result_01 as $field_01 => $value_01) {
	$detail_nap[$value_01['year']][$value_01['month']][$value_01['day']] = $value_01['brutto'];
}	
///* get Csak napok Order */
$sql_02 = 'SELECT 
YEAR(date_add) AS year,
MONTH(date_add) AS month,
WEEKOFYEAR(date_add) AS week,
DAY(date_add) AS day,
SUM( total_paid_tax_incl ) AS brutto
FROM  ps_orders
WHERE current_state !=6 AND current_state !=7 AND id_customer <>13110
GROUP BY year, month, day';
$result_02 = Db::getInstance()->executeS($sql_02);
$this->data = ''; $value='';
foreach ($result_02 as $field_02 => $value_02) {
	$order_nap[$value_02['year']][$value_02['month']][$value_02['day']] = $value_02['brutto'];
}       	

$sql = '
SELECT 
cat_lang.name AS kategoria,
product_name as termek,
COUNT(od.unit_price_tax_incl) AS rendeles,
SUM(od.unit_price_tax_incl*od.product_quantity) AS brutto,
cat.id_category AS kat_id,
YEAR(ord.date_add) AS year,
MONTH(ord.date_add) AS month,
WEEKOFYEAR(ord.date_add) AS week,
DAY(ord.date_add) AS day
FROM 
ps_order_detail od
INNER JOIN ps_category_product cp ON od.product_id =cp.id_product
INNER JOIN ps_orders ord ON ord.id_order = od.id_order AND ord.current_state NOT IN (6,7) AND ord.id_customer <>13110
INNER JOIN ps_category cat ON cat.id_category=cp.id_category AND cat.id_category IN (57, 48, 60, 123)  AND cat.active=1
INNER JOIN ps_category_lang cat_lang ON cat_lang.id_category=cat.id_category AND cat_lang.id_lang=2
GROUP BY
kategoria,
year,month,day
';
$result = Db::getInstance()->executeS($sql);
$this->data = ''; $value='';
        foreach ($result as $field => $value) {
//////////////////////////////////////////////////////////////////////////
				$kulonbseg = 
						($order_nap[$value['year']][$value['month']][$value['day']]-$detail_nap[$value['year']][$value['month']][$value['day']])
						*($value['brutto']/$detail_nap[$value['year']][$value['month']][$value['day']]);
				$value['brutto'] += $kulonbseg;

//////////////////////////////////////////////////////////
					//$value['brutto'] += $szetoszt*$value['brutto']/$kat_income_all;
        	$month	= str_pad($value['month'], 2, '0', STR_PAD_LEFT);
        	$week   = str_pad($value['week'] , 2, '0', STR_PAD_LEFT);
        	$day    = str_pad($value['day']  , 2, '0', STR_PAD_LEFT);
			    $value['netto']=ROUND ($value['brutto']/1.27);

        	$sor 	= "[
        	'".$value['year']."',
        	'".$month."',
        	'".$week."',
        	'".$day."',
        	".$value['brutto'].",
        	".$value['netto'].",
					".$value['rendeles'].",
					'".$value['termek']."',
					'".$value['kategoria']."',
        	],";   
            $this->data .= $sor = str_replace(array("\r", "\n"), '', $sor);
            $value='';
        }

/* Only Mediline */
///* get Csak napok Detail */
$sql_01 = '
SELECT 
cat_lang.name AS kategoria,
COUNT(od.unit_price_tax_incl) AS termek,
SUM(od.unit_price_tax_incl*od.product_quantity) AS brutto,
cat.id_category AS kat_id,
YEAR(ord.date_add) AS year,
MONTH(ord.date_add) AS month,
WEEKOFYEAR(ord.date_add) AS week,
DAY(ord.date_add) AS day
FROM 
ps_order_detail od
INNER JOIN ps_category_product cp ON od.product_id =cp.id_product
INNER JOIN ps_orders ord ON ord.id_order = od.id_order AND ord.current_state NOT IN (6,7) AND ord.id_customer =13110
INNER JOIN ps_category cat ON cat.id_category=cp.id_category AND cat.id_category IN (57, 48, 60, 123)  AND cat.active=1
INNER JOIN ps_category_lang cat_lang ON cat_lang.id_category=cat.id_category AND cat_lang.id_lang=2
GROUP BY
year,month,day
';
$result_01 = Db::getInstance()->executeS($sql_01);
//$this->data = ''; $value_01='';
foreach ($result_01 as $field_01 => $value_01) {
	$detail_nap[$value_01['year']][$value_01['month']][$value_01['day']] = $value_01['brutto'];
}	
///* get Csak napok Order */
$sql_02 = 'SELECT 
YEAR(date_add) AS year,
MONTH(date_add) AS month,
WEEKOFYEAR(date_add) AS week,
DAY(date_add) AS day,
SUM( total_paid_tax_incl ) AS brutto
FROM  ps_orders
WHERE current_state !=6 AND current_state !=7 AND id_customer =13110
GROUP BY year, month, day';
$result_02 = Db::getInstance()->executeS($sql_02);
//$this->data = ''; $value='';
foreach ($result_02 as $field_02 => $value_02) {
	$order_nap[$value_02['year']][$value_02['month']][$value_02['day']] = $value_02['brutto'];
}       	

$sql = '
SELECT 
cat_lang.name AS kategoria,
product_name AS termek,
COUNT(od.unit_price_tax_incl) AS rendeles,
SUM(od.unit_price_tax_incl*od.product_quantity) AS brutto,
cat.id_category AS kat_id,
YEAR(ord.date_add) AS year,
MONTH(ord.date_add) AS month,
WEEKOFYEAR(ord.date_add) AS week,
DAY(ord.date_add) AS day
FROM 
ps_order_detail od
INNER JOIN ps_category_product cp ON od.product_id =cp.id_product
INNER JOIN ps_orders ord ON ord.id_order = od.id_order AND ord.current_state NOT IN (6,7) AND ord.id_customer =13110
INNER JOIN ps_category cat ON cat.id_category=cp.id_category AND cat.id_category IN (57, 48, 60, 123)  AND cat.active=1
INNER JOIN ps_category_lang cat_lang ON cat_lang.id_category=cat.id_category AND cat_lang.id_lang=2
GROUP BY
kategoria,
year,month,day
';
$result = Db::getInstance()->executeS($sql);
//$this->data = ''; $value='';
        foreach ($result as $field => $value) {
//////////////////////////////////////////////////////////////////////////
				$kulonbseg = 
						($order_nap[$value['year']][$value['month']][$value['day']]-$detail_nap[$value['year']][$value['month']][$value['day']])
						*($value['brutto']/$detail_nap[$value['year']][$value['month']][$value['day']]);
				$value['brutto'] += $kulonbseg;

//////////////////////////////////////////////////////////
					//$value['brutto'] += $szetoszt*$value['brutto']/$kat_income_all;
        	$month	= str_pad($value['month'], 2, '0', STR_PAD_LEFT);
        	$week   = str_pad($value['week'] , 2, '0', STR_PAD_LEFT);
        	$day    = str_pad($value['day']  , 2, '0', STR_PAD_LEFT);
			    $value['netto']=ROUND ($value['brutto']/1.27);

        	$sor 	= "[
        	'".$value['year']."',
        	'".$month."',
        	'".$week."',
        	'".$day."',
        	".$value['brutto'].",
        	".$value['netto'].",
					".$value['rendeles'].",
					'".$value['termek']."',
					'Mediline',
        	],";   
            $this->data .= $sor = str_replace(array("\r", "\n"), '', $sor);
            $value='';
        }












        
        
        
        
        
     
        
        	
		
        Logger::addLog('display:: data0 ' . $this->data);       
	    	    
	    $rc = parent::display();

	    return $rc;
	}
}

?>