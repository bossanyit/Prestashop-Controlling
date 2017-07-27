<?php 

class Reports {
    const CLICK_LIMIT = 8;
    const SUB_LIMIT = 15;
    const ORDER_LIMIT = 10;
    const OTO_LIMIT = 3;
    
    public function report($date_from, $date_to = null) {
        

        if (is_null($date_to)) {
            $date_to = $date_from;
        }
        
        $sql ='
        SELECT
            position,
            ms.id_controlling_source,
            sum(ms.oto_count) as oto,
            sum(ms.oto_count) / sum(subscription_count) *100 as oto_sz,
        	sum(ms.order_count) as rendelesek,
            sum(ms.order_count) / sum(subscription_count) *100 as rendelesek_sz,
        	max(ms.avg_cart) as atlag_kosar,
        	sum(ms.income_sum) as ossz_bevetel,
        	sum(ms.income_sum)*0.4 as arres,
        	sum(ms.income_sum)*0.4 - sum(ad_expense) as fedezet,
        	cs.name as kampany,
        	sum(ad_expense) as hirdetes_ktg,
        	sum(visits) as latogatasok,
        	max(ctr) as CTR,
        	sum(ad_expense) / sum(visits) as click_cost,
        	sum(subscription_count) as feliratkozas,
            sum(subscription_count) / sum(visits) * 100  as feliratkozas_sz
        FROM
        ps_controlling_marketing_source ms
        RIGHT JOIN ps_controlling_source cs ON ms.id_controlling_source=cs.id_controlling_source
        where date between "' .$date_from . '" AND "' . $date_to . '"
        group by cs.id_controlling_source
        HAVING hirdetes_ktg > 0
        ORDER BY cs.position asc
        ';
        
        $results = Db::getInstance()->ExecuteS($sql);
//Logger::addLog('Report  sql' . $sql);    		        
        
         $this->sendSummary($results, $date_from, $date_to);
    }
    
    private function sendSummary($results, $date_from, $date_to)
	{
	    $id_lang = Context::getContext()->language->id;
	    $id_shop = Context::getContext()->shop->id;
	    $items = '';
	    $sum = array();
		if (!is_null($results)) {

            $key = 0;
            foreach ($results as  $value)
			{
			    $items .=
					'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
						<td style="padding: 0.6em 0.4em;width: 10%;">'.$value['kampany'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.number_format($value['hirdetes_ktg'], 0, ',', '.').'</td>						
						<td style="padding: 0.6em 0.4em;width: 5%;">'.number_format($value['latogatasok'] , 0, ',', '.').'</td>	
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['CTR'].'</td>';
				if ($value['click_cost'] > Reports::CLICK_LIMIT) {				    
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;color:red">'.Tools::ps_round($value['click_cost'],2).'</td>';
				} else {
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.Tools::ps_round($value['click_cost'],2).'</td>';
				}
				$items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.Tools::ps_round($value['feliratkozas'], 2).'</td>';
				if ($value['feliratkozas_sz'] < Reports::SUB_LIMIT) {				    
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;color:red">'.Tools::ps_round($value['feliratkozas_sz'], 2).'</td>';
				} else {
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.Tools::ps_round($value['feliratkozas_sz'],2).'</td>';
				}
				$items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['oto'].'</td>';
				if ($value['oto_sz'] < Reports::OTO_LIMIT) {				    
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;color:red">'.Tools::ps_round($value['oto_sz'],2).'</td>';
				} else {
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.Tools::ps_round($value['oto_sz'], 2).'</td>';
				}
				$items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['rendelesek'].'</td>';
				if ($value['rendelesek_sz'] < Reports::ORDER_LIMIT) {				    
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;color:red">'.Tools::ps_round($value['rendelesek_sz'],2).'</td>';
				} else {
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.Tools::ps_round($value['rendelesek_sz'], 2).'</td>';
				}
				if ($value['fedezet'] < 0) {				    
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;color:red">'.number_format($value['fedezet'], 0, ',', '.').'</td>';
				} else {
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.number_format($value['fedezet'], 0, ',', '.').'</td>';
				}				
				if ($value['fedezet']/$value['hirdetes_ktg'] < 0) {				    
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;color:red">'.number_format($value['fedezet']/$value['hirdetes_ktg'], 2, ',', '.').'</td>';
				} else {
				    $items .= '<td style="padding: 0.6em 0.4em;width: 5%;">'.number_format($value['fedezet']/$value['hirdetes_ktg'], 2, ',', '.').'</td>';
				}
				$items .= '</tr>';
				//sum values
				@ $sum['hirdetes_ktg']    += $value['hirdetes_ktg'];
				@ $sum['latogatasok']     += $value['latogatasok'];
				@ $sum['feliratkozas']    += $value['feliratkozas'];
				@ $sum['oto']             += $value['oto'];
				@ $sum['rendelesek']      += $value['rendelesek'];
				@ $sum['fedezet']         += $value['fedezet'];

				$key++;
			}
			//Sum row
			if ($sum['hirdetes_ktg'] == 0) {
			    $sum['hirdetes_ktg'] = 1;
			}
			$items .='<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">';
			$items .='<td>Összesen</td>';
			$items .='<td>'.number_format($sum['hirdetes_ktg'], 0, ',', '.').'</td>';
			$items .='<td>'.number_format($sum['latogatasok'], 0, ',', '.').'</td>';
			$items .='<td>&nbsp;</td>';
			$items .='<td>&nbsp;</td>';
			$items .='<td>'.number_format($sum['feliratkozas'], 0, ',', '.').'</td>';
			$items .='<td>&nbsp;</td>';
			$items .='<td>'.number_format($sum['oto'], 0, ',', '.').'</td>';
			$items .='<td>&nbsp;</td>';
			$items .='<td>'.number_format($sum['rendelesek'], 0, ',', '.').'</td>';
			$items .='<td>&nbsp;</td>';
			$items .='<td>'.number_format($sum['fedezet'], 0, ',', '.').'</td>';
			$items .='<td>'.number_format($sum['fedezet']/$sum['hirdetes_ktg'], 2, ',', '.').'</td>';
			$items .='</tr>';

			 // end foreach ($products)
		
//Logger::addLog('Report items ' . $items . ' count ' . count($results));    		
    		$data = array(
    		    '{date}' => $date_from . ' - ' . $date_to,	
    			'{items}' => $items,			
    		);
    		$dir_mail = dirname(__FILE__).'/../mails/';
    		$emails = Configuration::get('CONTR_REPORT_SUBSCRIPTION');
    		$a_email = explode(', ', $emails);
    		foreach ($a_email as $email) {
    		    Mail::Send(
        			(int)$id_lang,
        			'report_daily',
        			Mail::l('Report - ads: ' . $date_from . ' - ' . $date_to, (int)$id_lang),
        			$data,
        			$email,
        			$email,
        			null, null, null, null, $dir_mail, true, (int)$id_shop);
    	    }	
	    }    
	}
	
	public function reportFunnel($date_from, $date_to = null) {
        

        if (is_null($date_to)) {
            $date_to = $date_from;
        }
        $funnel_definition = '
        SELECT f.id_controlling_funnel, funnel_name, 
        	fs1.step_name, fs1.funnel_source_type, fs1.funnel_source,
        	fs2.step_name, fs2.funnel_source_type, fs2.funnel_source,
        	fs3.step_name, fs3.funnel_source_type, fs3.funnel_source,
        	fs4.step_name, fs4.funnel_source_type, fs4.funnel_source 
        
            FROM `ps_controlling_funnel` f
            left join ps_controlling_funnel_step_source fs1 on f.id_step1_source = fs1.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs2 on f.id_step2_source = fs2.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs3 on f.id_step3_source = fs3.id_funnel_step_source
            left join ps_controlling_funnel_step_source fs4 on f.id_cart_source = fs4.id_funnel_step_source
            order by position
        ';
        
        
        $sql ='SELECT funnel_name, 
                sum(fd.reach) as eleres,
                sum(fd.reach_expense) as eleres_ktg,
                sum(fd.step1_clicks) as kattintas,
                sum(fd.step1_clicks)/ sum(reach) as kattintas_sz,                
                sum(fd.step2_data) as feliratkozas,
                sum(fd.step3_data) as oto,
                sum(fd.step3_data) / sum(step2_data) as oto_sz, 
                sum(cart_data) as kosarba,
                sum(order_data) as rendeles,
                sum(order_data) / sum(cart_data) as rendeles_sz,
                sum(order_data) / sum(fd.step1_clicks) as rendeles_sz_sz
               FROM `ps_controlling_funnel` f
                left join ps_controlling_funnel_data fd on f.id_controlling_funnel = fd.id_controlling_funnel
                    and fd.date between "' .$date_from . '" AND "' . $date_to . '"
               GROUP BY funnel_name
               ORDER BY position
        ';
        $results = Db::getInstance()->ExecuteS($sql);
        $this->sendSummaryFunnel($results, $date_from, $date_to);
    }
    
    private function sendSummaryFunnel($results, $date_from, $date_to)
	{
	    $id_lang = Context::getContext()->language->id;
	    $id_shop = Context::getContext()->shop->id;
	    $items = '';
		if (!is_null($results)) {

            $key = 0;
            foreach ($results as  $value)
			{
			    $items .=
					'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
						<td style="padding: 0.6em 0.4em;width: 40%;">'.$value['funnel_name'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['eleres'].'</td>						
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['eleres_ktg'].'</td>	
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['kattintas'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['kattintas_sz'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['feliratkozas'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['oto'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['oto_sz'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['kosarba'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['rendeles'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['rendeles_sz'].'</td>
						<td style="padding: 0.6em 0.4em;width: 5%;">'.$value['rendeles_sz_sz'].'</td>';
			
				$items .= '</tr>';
				$key++;
			} // end foreach ($products)
		
//Logger::addLog('Report items ' . $items . ' count ' . count($results));    		
    		$data = array(
    		    '{date}' => $date_from . ' - ' . $date_to,	
    			'{items}' => $items,			
    		);
    		$dir_mail = dirname(__FILE__).'/../mails/';
    		$emails = Configuration::get('CONTR_REPORT_SUBSCRIPTION');
    		$a_email = explode(', ', $emails);
    		foreach ($a_email as $email) {
    		    Mail::Send(
        			(int)$id_lang,
        			'report_funnel',
        			Mail::l('Report funnel: ' . $date_from . ' - ' . $date_to, (int)$id_lang),
        			$data,
        			$email,
        			$email,
        			null, null, null, null, $dir_mail, true, (int)$id_shop);
    	    }	
	    }    
	}    
    
    public function reportMonthlyGoal()
	{
	    $id_lang = Context::getContext()->language->id;
	    $id_shop = Context::getContext()->shop->id;
	    $items = '';
	    
	    $days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $resultsReal_ossz_bevetel = 0;
        $resultsReal_atlag_kosar = 0;
        $resultsReal_rendelesek = 0;
        $resultsPlan_ossz_bevetel = 0;

        $resultsReal = $this->getSourceData(true, false);
        if (!is_null($resultsReal)) {
            foreach($resultsReal as $result) {
                $resultsReal_ossz_bevetel = $result['ossz_bevetel'];
                $resultsReal_atlag_kosar = $result['atlag_kosar'];
                $resultsReal_rendelesek = $result['rendelesek'];
                break;
            }
        }
        $resultsPlan = $this->getSourceData(true, true);
        if (!is_null($resultsPlan)) {    
            foreach($resultsPlan as $result) {
                $resultsPlan_ossz_bevetel = $result['ossz_bevetel'];
                break;
            }
        }
        $today = date('Y-m-d');
        $yesterday = date('d',strtotime($today . "-1 days"));  
        $prediction = $resultsReal_ossz_bevetel / $yesterday * $days;
        
        Logger::addLog("Osszesen " . $resultsReal_ossz_bevetel . " " . $resultsPlan_ossz_bevetel);
        $item_data = array(
                        'name' => "Hirdetesek",
                        'ossz_bevetel' => $resultsReal_ossz_bevetel,
                        'atlag_kosar' => $resultsReal_atlag_kosar,
                        'rendelesek' => $resultsReal_rendelesek,
                        'goal' => $resultsPlan_ossz_bevetel,
                        'prediction' => $prediction,
                     ); 	   
        $key = 0;              
        $items = $this->assignDataToItems($item_data, $items, $key);
        
        // OSSZESEN
        $key++;

        $resultsReal_ossz_bevetel = 0;
        $resultsReal_atlag_kosar = 0;
        $resultsReal_rendelesek = 0;
        $resultsPlan_ossz_bevetel = 0;
        
        $resultsReal = $this->getSourceData(false, false);
        if (!is_null($resultsReal)) {    
            foreach($resultsReal as $result) {
                $resultsReal_ossz_bevetel = $result['ossz_bevetel'];
                $resultsReal_atlag_kosar = $result['atlag_kosar'];
                $resultsReal_rendelesek = $result['rendelesek'];
                break;
            }
        }
        $resultsPlan = $this->getSourceData(false, true);
        if (!is_null($resultsPlan)) {    
            foreach($resultsPlan as $result) {
                $resultsPlan_ossz_bevetel = $result['ossz_bevetel'];
                break;
            }
        }
        $prediction = $resultsReal_ossz_bevetel / $yesterday * $days;
        Logger::addLog("Osszesen " . $resultsReal_ossz_bevetel . " " . $resultsPlan_ossz_bevetel);
        
        $item_data = array(
                        'name' => "Osszesen",
                        'ossz_bevetel' => $resultsReal_ossz_bevetel,
                        'atlag_kosar' => $resultsReal_atlag_kosar,
                        'rendelesek' => $resultsReal_rendelesek,
                        'goal' => $resultsPlan_ossz_bevetel,
                        'prediction' => $prediction,
                     ); 	
        $items = $this->assignDataToItems($item_data, $items, $key);

	    
		//if (!is_null($resultsReal)) {		
    		$data = array(
    		    '{date}' => date('Y-m-d'),	
    			'{items}' => $items,			
    		);
    		$dir_mail = dirname(__FILE__).'/../mails/';
    		$emails = Configuration::get('CONTR_REPORT_SUBSCRIPTION');
    		$a_email = explode(', ', $emails);
    		foreach ($a_email as $email) {
    		    Mail::Send(
        			(int)$id_lang,
        			'report_goal',
        			Mail::l('Report monthly goal', (int)$id_lang),
        			$data,
        			$email,
        			$email,
        			null, null, null, null, $dir_mail, true, (int)$id_shop);
    	    }	
	    //}    
	}
	
    public function reportTaskPrio()
	{
	    $date_to = date('Y-m-d');
    	$date_from = (new DateTime('first day of this month'))->format('Y-m-d'); //date('Y-m-d',strtotime($today . "-30 days"));  	
    	$week = date('Y-m-d',strtotime($date_to . "-7 days"));  	
	    $sql ='
        SELECT
            position,
            ms.id_controlling_source,
        	sum(ms.order_count) as rendelesek,
            sum(ms.order_count) / sum(subscription_count) *100 as rendelesek_sz,
        	max(ms.avg_cart) as atlag_kosar,
        	sum(ms.income_sum) as ossz_bevetel,
        	sum(ms.income_sum)*0.4 as arres,
        	sum(ms.income_sum)*0.4 - sum(ad_expense) as fedezet,
        	cs.name as kampany,
        	sum(ad_expense) as hirdetes_ktg,
        	sum(visits) as latogatasok,
        	max(ctr) as CTR,
        	sum(ad_expense) / sum(visits) as click_cost,
        	sum(subscription_count) as feliratkozas,
            sum(subscription_count) / sum(visits) * 100  as feliratkozas_sz
        FROM
        ps_controlling_marketing_source ms
        RIGHT JOIN ps_controlling_source cs ON ms.id_controlling_source=cs.id_controlling_source
        where date between "' .$week . '" AND "' . $date_to . '"
        group by cs.id_controlling_source
        HAVING hirdetes_ktg > 0
        ORDER BY cs.position asc
        ';
        
        $results = Db::getInstance()->executeS($sql);
        
        
        $item_data = array();
        $items = '';
        foreach ($results as $source) {
            $weight = 0;
            $bottle_neck = 0;
            $source_name = $source['kampany'];
            $monthly_income = $this->getIncomeMonth($source['id_controlling_source'], $date_from, $date_to);
            $this->getWeight($source['fedezet'], $source['CTR'], $source['feliratkozas_sz'], $source['rendelesek_sz'], $weight, $bottle_neck);
            $remain = $this->getMonthlyRemain($source['id_controlling_source'], $monthly_income, $date_from);
            $prio = $weight * $remain;
            
            $item_data[] = array(
                            'campaign' => $source_name,
                            'weight' => $weight, 
                            'bottle_neck' => $bottle_neck,
                            'ease' => 1,
                            'remain' => $remain,
                            'prio' => $prio);
        }

        $item_data = $this->record_sort($item_data, 'prio');
        //ksort($item_data);
        $key = 0;
        foreach($item_data as $item) {
            $items = $this->assignDataToTaskItems($item, $items, $key++);
        }

	    $id_lang = Context::getContext()->language->id;
	    $id_shop = Context::getContext()->shop->id;
        
		$data = array(
		    '{date}' => date('Y-m-d'),	
			'{items}' => $items,			
		);
		$dir_mail = dirname(__FILE__).'/../mails/';
		$emails = Configuration::get('CONTR_REPORT_SUBSCRIPTION');
		$a_email = explode(', ', $emails);
		foreach ($a_email as $email) {
		    Mail::Send(
    			(int)$id_lang,
    			'report_tasks',
    			Mail::l('Napi feladatprioritas lista', (int)$id_lang),
    			$data,
    			$email,
    			$email,
    			null, null, null, null, $dir_mail, true, (int)$id_shop);
	    }	
  
	}	
	
	private function getIncomeMonth($id_controlling_source, $date_from, $date_to) {
	    $sql ='
        SELECT
        	sum(ms.income_sum) as ossz_bevetel
        FROM
        ps_controlling_marketing_source ms
        RIGHT JOIN ps_controlling_source cs ON ms.id_controlling_source=cs.id_controlling_source
        where date between "' .$date_from . '" AND "' . $date_to . '"
        and cs.id_controlling_source = ' . $id_controlling_source . '
        ';
        
        return Db::getInstance()->getValue($sql);
	    
	}
	
	private function record_sort($records, $field, $reverse=false)
    {
        $hash = array();
        
        foreach($records as $key => $record)
        {
            $hash[$record[$field].$key] = $record;
        }
        
        ($reverse)? krsort($hash) : ksort($hash);
        
        $records = array();
        
        foreach($hash as $record)
        {
            $records []= $record;
        }
        
        return $records;
    }
	private function getWeight($fedezet, $ctr, $subscription, $order, &$weight, &$bottle_neck) {
	    if ($fedezet < 0) {
	        $weight = 3;
	        $bottle_neck = 'ROI';
	    } else {
	        $distance_min = $ctr / Reports::CLICK_LIMIT;
	        $bottle_neck = 'CTR';
	        $weight = 1;
	        $distance = $subscription / Reports::SUB_LIMIT;
	        if ($distance < $distance_min) {
	            $bottle_neck = 'Feliratkozás';
	        }
	        $distance = $order / Reports::ORDER_LIMIT;
	        if ($distance < $distance_min) {
	            $bottle_neck = 'Rendelés';
	        }
	            
	    }
	    
	}
	
	private function getMonthlyRemain($id_controlling_source, $income, $date) {
	    $sql = '
	        SELECT income_sum FROM `ps_controlling_source_plan` WHERE id_controlling_source = ' .$id_controlling_source .' 
            and date = "'.$date.'"
	    ';

	    $plan = Db::getInstance()->getValue($sql);
	    $days = date('d');
	    $remain = round(($income / $days),0) - round($plan / 30, 0);
//echo "source " .$id_controlling_source . " days " . $days . " plan " . $plan . " bevetel " . $income .  " remain " . $remain  . "<br/>" ;	    	    
        return $remain;
	}
	
	private function assignDataToTaskItems($data, &$items, $key) {
	    $items .=
					'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
						<td style="padding: 0.6em 0.4em;width: 40%;">'.$data['campaign'].'</td>
						<td style="padding: 0.6em 0.4em;width: 10%;">'.number_format ($data['weight'], 0, '.', ' ').'</td>						
						<td style="padding: 0.6em 0.4em;width: 10%;color:red">'.$data['bottle_neck'].'</td>	
						<td style="padding: 0.6em 0.4em;width: 10%;">'.$data['ease'].'</td>';
        if ($data['remain'] < 0) {
    		$items .= '		<td style="padding: 0.6em 0.4em;width: 10%;color:red">'.number_format ($data['remain'], 0, '.', ' ').'</td>
	    					<td style="padding: 0.6em 0.4em;width: 10%;color:red">'.number_format ($data['prio'], 0, '.', ' ').'</td>';           
        } else {
    		$items .= '		<td style="padding: 0.6em 0.4em;width: 10%">'.number_format ($data['remain'], 0, '.', ' ').'</td>
        					<td style="padding: 0.6em 0.4em;width: 10%">'.number_format ($data['prio'], 0, '.', ' ').'</td>';
        }
			
	    $items .= '</tr>';
	    return $items;
	}
	
	private function assignDataToItems($data, &$items, $key) {
	    $items .=
					'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
						<td style="padding: 0.6em 0.4em;width: 40%;">'.$data['name'].'</td>
						<td style="padding: 0.6em 0.4em;width: 10%;">'.number_format ($data['ossz_bevetel'], 0, '.', ' ').'</td>						
						<td style="padding: 0.6em 0.4em;width: 10%;">'.number_format ($data['atlag_kosar'], 0, '.', ' ').'</td>	
						<td style="padding: 0.6em 0.4em;width: 10%;">'.$data['rendelesek'].'</td>
						<td style="padding: 0.6em 0.4em;width: 10%;">'.number_format ($data['goal'], 0, '.', ' ').'</td>
						<td style="padding: 0.6em 0.4em;width: 10%;">'.number_format ($data['prediction'], 0, '.', ' ').'</td>';
			
	    $items .= '</tr>';
	    return $items;
	}
	

	
	private function getSourceData($ad = true, $plan = true) {
	    $date_from = date('Y-m-d', strtotime('first day of this month'));
        $date_to = date('Y-m-d', strtotime('last day of this month'));
        
        $sql ='SELECT
            	sum(ms.order_count) as rendelesek,
            	sum(ms.income_sum) / sum(ms.order_count) as atlag_kosar,
            	sum(ms.income_sum) as ossz_bevetel
            FROM ';
        if ($plan) {    
            $sql .= 'ps_controlling_source_plan ms';
        } else {
            $sql .= 'ps_controlling_marketing_source ms';
        }
            
        $sql .= '   RIGHT JOIN ps_controlling_source cs ON ms.id_controlling_source=cs.id_controlling_source';
        if ($ad) {
            $sql .= ' where date between "'.$date_from.'" and "'.$date_to.'" and ad_expense > 0';
        } else {
            $sql .= ' where date between "'.$date_from.'" and "'.$date_to.'"';
             $sql .= ' having ossz_bevetel > 0
                      ORDER BY cs.position asc
        ';
        }
       
        
        Logger::addLog("report goal " .$sql);
        $results = Db::getInstance()->ExecuteS($sql);
        return $results;
	}        
	
    public function reportMargin($date_from, $date_to = null) {
        

        if (is_null($date_to)) {
            $date_to = $date_from;
        }
               
        $sql ='select id_order, total_paid, GetMargin(id_order) as margin from ps_orders
            where date_add BETWEEN "' . $date_from . '" and  "' . $date_to . '" and total_paid <> 0
            ORDER by id_order
        ';
        $results = Db::getInstance()->ExecuteS($sql);
        $this->sendSummaryMargin($results, $date_from, $date_to);
    }
    
    public function getMarginSummary($date_from, $date_to) {
        $data = array(
            'total_paid' => 0,
            'margin' => 0,
            'count' => 0,
        );

        if (is_null($date_to)) {
            $date_to = $date_from;
        }
               
        $sql ='select id_order, total_paid, GetMargin(id_order) as margin from ps_orders
            where date_add between "' . $date_from . '" and  "' . $date_to . '"
            ORDER by id_order
        ';
        $results = Db::getInstance()->ExecuteS($sql);

        foreach ($results as  $row)
		{
		    $data['total_paid'] += $row['total_paid'];
		    $data['margin'] += $row['margin'];
		    $data['count']++;				
		} // end foreach ($products)     
		
		$percent = $data['total_paid'] == 0 ? 0: $data['margin']/$data['total_paid'];
		$key = 0;
        $items =
			'<tr style="background-color: #8ed97e;">
			    <td style="padding: 0.6em 0.4em;width: 15%;"></td>
				<td style="padding: 0.6em 0.4em;width: 17%;">'.number_format ($data['total_paid'], 2, '.', ' ').'</td>						
				<td style="padding: 0.6em 0.4em;width: 17%;">'.number_format ($data['margin'], 2, '.', ' ').'</td>	
				<td style="padding: 0.6em 0.4em;width: 17%;">'.number_format ($percent, 2, '.', ' ').'</td>
				<td style="padding: 0.6em 0.4em;width: 17%;">'.number_format ($data['margin']/1.27, 2, '.', ' ').'</td>';
	
		$items .= '</tr>';		   
		return $items;
    }    
    
    public function getProductsNoWholesale($date_from, $date_to) {
               
        $sql ='select a.id_order, p.id_product, IFNULL(CONCAT(pl.name, " : ", al.name), pl.name) as product_name, IFNULL(pa.wholesale_price, p.wholesale_price) as wholesale_price from ps_orders a
				inner join ps_order_detail od on od.id_order = a.id_order
                inner join ps_product p on p.id_product = od.product_id
				inner join ps_product_lang  pl ON pl.id_product = p.id_product  AND pl.id_lang = 2
                left join ps_product_attribute pa ON pa.id_product = p.id_product
                left join ps_product_attribute_combination pac ON pac.id_product_attribute = pa.id_product_attribute   
                left join ps_attribute atr ON atr.id_attribute = pac.id_attribute
                left join ps_attribute_lang al ON al.id_attribute = atr.id_attribute AND al.id_lang = 2
            where a.date_add between "'.$date_from.'" and "'.$date_to.'"
            ORDER by p.id_product
        ';
        $results = Db::getInstance()->ExecuteS($sql);

        $key = 0;
        $items = 0;
        foreach ($results as  $row)
		{
            if ($row['wholesale_price']==0) {
    		    $items .=
    			'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
    			    <td style="padding: 0.6em 0.4em;width: 15%;">'.$row['id_order'].'</td>
    			    <td style="padding: 0.6em 0.4em;width: 15%;">'.$row['id_product'].'</td>
    				<td style="padding: 0.6em 0.4em;width: 35%;">'.$row['product_name'].'</td>';
    	
    		    $items .= '</tr>';	
    		}
		    $key = 0;		
		} // end foreach ($products)     
		
		
		return $items;
    }    
    
    private function sendSummaryMargin($results, $date_from, $date_to)
	{
	    $id_lang = Context::getContext()->language->id;
	    $id_shop = Context::getContext()->shop->id;
	    $items = '';
	    
	    $margin_summary = $this->getMarginSummary($date_from, $date_to);
	    $no_wholesale = $this->getProductsNoWholesale($date_from, $date_to);
	    
		if (!is_null($results)) {

            $key = 0;
            foreach ($results as  $row)
			{
			    $percent = $row['total_paid'] == 0? 0: $row['margin']/$row['total_paid'];
			    $items .=
					'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
						<td style="padding: 0.6em 0.4em;width: 15%;">'.$row['id_order'].'</td>
						<td style="padding: 0.6em 0.4em;width: 17%;">'.number_format ($row['total_paid'], 2, '.', ' ').'</td>						
						<td style="padding: 0.6em 0.4em;width: 17%;">'.number_format ($row['margin'], 2, '.', ' ').'</td>	
						<td style="padding: 0.6em 0.4em;width: 17%;">'.number_format ($percent, 2, '.', ' ').'</td>
						<td style="padding: 0.6em 0.4em;width: 17%;">'.number_format ($row['margin']/1.27, 2, '.', ' ').'</td>';
			
				$items .= '</tr>';
				$key++;
			} // end foreach ($products)
		
    		$data = array(
    		    '{date}' => $date_from . ' - ' . $date_to,	
    		    '{margin_sum}' => $margin_summary,	
    			'{items}' => $items,			
    			'{no_wholesale}' => $no_wholesale,		
    		);
    		$dir_mail = dirname(__FILE__).'/../mails/';
    		$email = 'bossanyi.tibor@andio.biz';

		
		    Mail::Send(
    			(int)$id_lang,
    			'report_margin',
    			Mail::l('Report margin: ' . $date_from . ' - ' . $date_to, (int)$id_lang),
    			$data,
    			$email,
    			$email,
    			null, null, null, null, $dir_mail, true, (int)$id_shop);
	    
	    }    
	}    	
}


?>
    
