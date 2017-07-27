<?php
/*
    Send reminder email to user if we waiting for his bank transfer for X days
*/
class Reminder{
    
    /*
        @param  $X                  integer, days we have been waiting for the bank transfer
        @return $id_order_array     array containing the id_orders of $X day old orders with 'waiting for bank transfer' status (id_status=10)
    */
    public function getOrderIds($X, $STATE=10)
    {
        $sql ="
        SELECT
            id_order, date_add
        FROM
            ps_orders
        WHERE 
            current_state = '".$STATE."' AND
            DATE(date_add) = CURDATE() - INTERVAL ".$X." DAY
        ";
        
        $results = Db::getInstance()->ExecuteS($sql);
        
        $id_order_array = array();
        foreach($results as $key =>$value){
            $id_order_array[] = $value['id_order'];
        }
        return $id_order_array;
    }

    public function sendEmail($id_order_array = null, $email_name = null)
    {
        if ($id_order_array == null){return false;}
        if ($email_name == null){    return false;}

        //Get html email file
        $html = file_get_contents('./mails/hu/'.$email_name.'.html',FILE_USE_INCLUDE_PATH);
        
        //GET <TITLE></TITLE> content for email Subject field 
        @ preg_match_all('@<title>.*?</title>@s',$html, $array_titles);
        @ $title = trim(substr($array_titles[0][0],7,strlen($array_titles[0][0])-15));
        
        foreach($id_order_array as $key => $id_order){
            
            $sql ="
                SELECT
                    o.id_customer,
                    o.id_address_delivery,
                    o.date_add,
                    o.total_paid,
                    a.lastname,
                    a.firstname,
                    a.address1,
                    a.address2,
                    a.postcode,
                    a.city,
                    c.lastname  AS c_lastname,
                    c.firstname AS c_firstname,
                    c.email     AS email
                FROM
                    ps_orders o
                    INNER JOIN  ps_address  a ON a.id_address  = o.id_address_delivery
                    INNER JOIN  ps_customer c ON c.id_customer = o.id_customer
                WHERE 
                    o.id_order='".$id_order."'
            ";
            $r = Db::getInstance()->ExecuteS($sql);            
            $r = $r[0];//just 1 row
            
            //refresh content
            $content = $html;
            
            //REPLACE the variables
                //user 
            $content = str_replace('{lastname}',                $r['c_lastname'],                                       $content);
            $content = str_replace('{firstname}',               $r['c_firstname'],                                      $content);
                //order                                                                                                     
            $content = str_replace('{delivery_lastname}',       $r['lastname'],                                         $content);
            $content = str_replace('{delivery_firstname}',      $r['firstname'],                                        $content);
            $content = str_replace('{delivery_postal_code}',    $r['postcode'],                                         $content);
            $content = str_replace('{delivery_city}',           $r['city'],                                             $content);
            $content = str_replace('{delivery_address1}',       $r['address1'],                                         $content);
            $content = str_replace('{delivery_address2}',       $r['address2'],                                         $content);
                //andio                                                     
            $content = str_replace('{bankwire_owner}',          'e-Claritas Online Kft',                                $content);
            $content = str_replace('{bankwire_details}',        '16200182-11526010-00000000',                           $content);
            $content = str_replace('{bankwire_address}',        'Andió',                                                $content);
            $content = str_replace('{total_paid}',              number_format($r['total_paid'],0,'',' ').' Ft',         $content);
            $content = str_replace('{id_order}',                $id_order,                                              $content);
            
            //Demonstration
            print $content;
            
            /*** Uncomment to TEST ***/
            //$r['email'] = 'bossanyi.tibor@andio.biz';
            //$r['email'] = 'gerendas.laszlo@andio.biz';
            
            /*** Uncomment to ACTIVATE ***/
            //SEND!!!
		    Mail::SendNoTemplate($title, $content, $r['email'], null, null, null, null, null, null, null );
        }
    }
    
    public function sendEmail_Survey($id_order_array = null, $email_name = null)
    {
        if ($id_order_array == null){return false;}
        if ($email_name == null){    return false;}

        //Get html email file
        $html = file_get_contents('./mails/hu/'.$email_name.'.html',FILE_USE_INCLUDE_PATH);
        
        //GET <TITLE></TITLE> content for email Subject field 
        @ preg_match_all('@<title>.*?</title>@s',$html, $array_titles);
        @ $title = trim(substr($array_titles[0][0],7,strlen($array_titles[0][0])-15));
        
        foreach($id_order_array as $key => $id_order){
            
            $sql ="
                SELECT
                    o.id_customer,
                    o.id_address_delivery,
                    o.date_add,
                    o.total_paid,
                    a.lastname,
                    a.firstname,
                    a.address1,
                    a.address2,
                    a.postcode,
                    a.city,
                    c.lastname  AS c_lastname,
                    c.firstname AS c_firstname,
                    c.email     AS email
                FROM
                    ps_orders o
                    INNER JOIN  ps_address  a ON a.id_address  = o.id_address_delivery
                    INNER JOIN  ps_customer c ON c.id_customer = o.id_customer
                WHERE 
                    o.id_order='".$id_order."'
            ";
            $r = Db::getInstance()->ExecuteS($sql);            
            $r = $r[0];//just 1 row
            
            //refresh content
            $content = $html;
            
            //REPLACE the variables
                //user 
            $content = str_replace('{lastname}',                $r['c_lastname'],                                       $content);
            $content = str_replace('{firstname}',               $r['c_firstname'],                                      $content);
                //order                                                                                                     
            $content = str_replace('{delivery_lastname}',       $r['lastname'],                                         $content);
            $content = str_replace('{delivery_firstname}',      $r['firstname'],                                        $content);
            $content = str_replace('{delivery_postal_code}',    $r['postcode'],                                         $content);
            $content = str_replace('{delivery_city}',           $r['city'],                                             $content);
            $content = str_replace('{delivery_address1}',       $r['address1'],                                         $content);
            $content = str_replace('{delivery_address2}',       $r['address2'],                                         $content);
                //andio                                                     
            $content = str_replace('{bankwire_owner}',          'e-Claritas Online Kft',                                $content);
            $content = str_replace('{bankwire_details}',        '16200182-11526010-00000000',                           $content);
            $content = str_replace('{bankwire_address}',        'Andió',                                                $content);
            $content = str_replace('{total_paid}',              number_format($r['total_paid'],0,'',' ').' Ft',         $content);
            $content = str_replace('{id_order}',                $id_order,                                              $content);
            $content = str_replace('{survey_url}',              'http://natur-haztartas.hu/l/wp-content/landing/campaigns/felmeres/velemeny.php?oid='.$id_order,                                              $content);
            
            //Demonstration
            print $content;
            
            /*** Uncomment to TEST ***/
            //$r['email'] = 'bossanyi.tibor@andio.biz';
            //$r['email'] = 'gerendas.laszlo@andio.biz';
            
            /*** Uncomment to ACTIVATE ***/
            //SEND!!!
		    Mail::SendNoTemplate($title, $content, $r['email'], null, null, null, null, null, null, null );
        }
    }

}


