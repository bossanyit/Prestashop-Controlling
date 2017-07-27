<?php

/* The class name is identical with the file name */
class IncomeTimeInterval {
        
        public function __construct($obj, $module_name) {
            Logger::addLog('construct...');   
        }        
        
        /* This function always must exist
           The display() function will be called be the dynamic object creation
          */
        public function display() {
        // $tpl = 'pivot.tpl';

  // eg: 'field_name|asc|avg':
  // NameOfTheField | ORDERY:ASC/DESC | AGGREGATE: SUM/AVG/..
  // For Thousand separator: use an aggregate function (sum/avg/..)
  
	$fields = array();
	$fields[] = 'uj_ev|desc';
	$fields[] = 'uj_ho|desc';
	$fields[] = 'uj_nap|desc';
	$fields[] = 'kosarertek|asc|avg';
	$fields[] = 'rendelesek|asc|sum';
	$fields[] = 'bevetel|asc|sum';
	$fields[] = 'netto_bevetel|asc|sum';
	
	$fields_start=
	"rows    : [ 'uj_ev', 'uj_ho'  ],
    columns  : [  ],
    data     : [ 'bevetel','netto_bevetel', 'rendelesek'],";

       //js include
    $ir='';
		$ir.='<!-- Kell -->
	    <link rel="stylesheet" type="text/css" href="http://natur-haztartas.hu/modules/andiocontrolling/css/orb.min.css" /> 
		<script type="text/javascript" src="http://natur-haztartas.hu/modules/andiocontrolling/js/react-0.12.2.min.js"></script>
	    <script type="text/javascript" src="http://natur-haztartas.hu/modules/andiocontrolling/js/orb.min.js"></script>
		<!--
		<script type="text/javascript" src="http://natur-haztartas.hu/modules/andiocontrolling/js/main.js"></script>
		-->
		';
	
	$fields_ready = '';
    foreach ($fields as $kulcs => $ertek){
		$reszek = explode('|', $fields[$kulcs]);
		$fields_ready .= "{name: '".$kulcs."',caption: '".$reszek[0]."'";
		if (isset($reszek[1]) && $reszek[1]!=""){
			$fields_ready.= ", sort: {order: '".$reszek[1]."'},";      
		}
		if (isset($reszek[2]) && $reszek[2]!=""){
			 $fields_ready.="dataSettings: {
                  aggregateFunc: '".$reszek[2]."',
                      formatFunc: function(value) {
                      value = Math.round(value);
                      return value ? value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, \"$1 \") : '';}},";
		}	
	$fields_ready.= "},";
	}
	$dataSource = "demo";
	//js data start
	$ir.= '
	<script type="text/javascript">
	(function() {
		window.orb = window.orb || {};
		window.orb.'.$dataSource.' = window.orb.'.$dataSource.' || {};
	    window.orb.'.$dataSource.'.data = [
		';
	
		########## Database Info ##########
	$db_set['name']			= "naturhaz_ps";
	$db_set['localhost']	= "localhost";
	$db_set['user']			= "naturhaz_ps";
	$db_set['password']		= "andio2009";



	$kapcsolat = mysql_connect( $db_set['localhost'], $db_set['user'], $db_set['password'] );
	if ( ! $kapcsolat ) die( "<meta http-equiv=\"refresh\" content=\"1; URL=".$PHP_SELF."> Egy pillanat...frisstits" );
	mysql_select_db( $db_set['name'], $kapcsolat) or die ( "Nem lehet megnyitni az adatbázist:" );//.mysql_error()
	mysql_query("SET NAMES 'utf8'");
/*	
	$eredmeny = mysql_query( "
	SELECT *,
		YEAR(ps_orders.date_add) as d_ev,
		MONTH(ps_orders.date_add) as d_ho, DAY(ps_orders.date_add) as d_nap,
		ROUND(total_paid_tax_incl,0) as total_paid_tax_incl,
		ps_order_state_lang.name AS current_state
	FROM ps_orders, ps_order_state_lang, ps_order_detail
	WHERE
		ps_orders.current_state = ps_order_state_lang.id_order_state AND
		ps_order_state_lang.id_lang=2 AND
		ps_orders.id_order = ps_order_detail.id_order
	", $kapcsolat );
*/
$eredmeny = mysql_query( "
SELECT
	YEAR(date_add) as uj_ev,
	MONTH(date_add) as uj_ho,
	DAY(date_add) as uj_nap,
	ROUND(AVG(total_paid_tax_incl),0) as kosarertek,
	COUNT(*) as rendelesek,
	ROUND(SUM(total_paid_tax_incl),0) as bevetel

FROM `ps_orders`
WHERE current_state!=6 AND current_state!=7 
GROUP BY YEAR(date_add) , MONTH(date_add),DAY(date_add)
", $kapcsolat );

	//Lekérdezés
while ($sor = mysql_fetch_array( $eredmeny )){
	$sor['uj_nap']  = str_pad($sor['uj_nap'], 2, '0', STR_PAD_LEFT);
	$sor['uj_ho'] = str_pad($sor['uj_ho'], 2, '0', STR_PAD_LEFT);
	$netto_bevetel = $sor['bevetel']/1.27;
	$sor['netto_bevetel'] = round($netto_bevetel,0);

	$sor = "[
	'".$sor['uj_ev']."',
	'".$sor['uj_ho']."',
	'".$sor['uj_nap']."',
	".$sor['kosarertek'].",
	".$sor['rendelesek'].",
	".$sor['bevetel'].",
	".$sor['netto_bevetel'].",
	],";   
	$ir.= $sor = str_replace(array("\r", "\n"), '', $sor);
	}
	//js data end
	$ir.= '    ];
	}());
	</script>';
	

	//js main start
$ir.="
<script>
(function() {
 
var config = function() {
    return {
        width: 1110,
        height: 830,
    	dataSource: orb.demo.data,
    	dataHeadersLocation: 'columns',
        theme: 'blue',
        toolbar: {
            visible: false
        },
    	grandTotal: {
    		rowsvisible: true,
    		columnsvisible: true
    	},
    	subTotal: {
    		visible: true,
            collapsed: true
    	},
        fields: [
           ".$fields_ready."
 
			
        ], 
        ".$fields_start."

    };
};

window.onload = function() {
    var pgridElem = document.getElementById('demo-pgrid');
    var sideMenuElement = document.getElementById('sidenav');
    var topMenuButton = document.getElementById('linkstoggle');
    var topMenuElement = document.getElementById('headerlinks');

    if(pgridElem) {
        new orb.pgridwidget(config()).render(pgridElem);
    }

    if(sideMenuElement) {
        new toggler({
            menu: sideMenuElement,
            onOpen: function(elem, compactMode) {
                elem.style.overflow = 'auto';
                elem.style.height = 'auto';

                if(compactMode) {
                    var menuHeight = elem.offsetHeight;
                    elem.style.height = Math.min((getWindowSize().height - 74 - 24), menuHeight) + 'px';
                }
            },
            onClose: function(elem) {
                elem.style.overflow = 'hidden';
                elem.style.height = '30px';
            },
            isCompactMode: function() {
                return getStyle(sideMenuElement, 'cursor') === 'pointer';
            }
        });
    }

    if(topMenuElement) {
        new toggler({
            button: topMenuButton,
            menu: topMenuElement,
            onOpen: function(elem) {
                topMenuElement.style.height = 'auto';
                topMenuButton.style.borderRadius = '3px 3px 0 0';
            },
            onClose: function(elem) {
                topMenuElement.style.height = '27px';
                topMenuButton.style.borderRadius = '3px';
            },
            isCompactMode: function() {
                return getStyle(topMenuButton.parentNode, 'display') === 'block';
            }
        });
    }
};

var togglers = [];

function toggler(options) {

    var self = this;

    this.options = options;
    
    this.openMenu = function(force) {
        if(force || self.collapsed) {

            // close all open menus except current one
            for(var i = 0; i < togglers.length; i++) {
                if(togglers[i] != self) {
                    togglers[i].closeMenu();
                }
            }

            self.collapsed = false;
            self.options.onOpen(self.options.menu, self.options.isCompactMode());
        }
        self.options.menu.scrollTop = 0;
    };

    this.closeMenu = function() {
        if(!self.collapsed && self.options.isCompactMode()) {
            self.collapsed = true;
            self.options.onClose(self.options.menu);
        }
        self.options.menu.scrollTop = 0;
    }

    this.ensureMenu = function() {
        if(!self.options.isCompactMode()) {
            self.openMenu(true);
        } else {
            self.closeMenu();
        }
    }

    function init() {

        togglers.push(self);

        addEventListener(window, 'resize', self.ensureMenu);
        addEventListener(document, 'click', self.closeMenu);

        self.options.button = self.options.button || self.options.menu;

        addEventListener(self.options.button, 'click', function(e) {
            if(self.collapsed) {
                self.openMenu();

                if(e.stopPropagation) {
                    e.stopPropagation();
                } else {
                    e.cancelBubble = true;
                }

                if(e.preventDefault) {
                    e.preventDefault();
                } else {
                    e.returnValue = false;
                }
            }
        });

        self.collapsed = self.options.isCompactMode();
    }

    init();
}

function addEventListener(element, eventName, callback) {
    if (element.addEventListener) {
        element.addEventListener(eventName, callback);
    }
    else {
        element.attachEvent('on' + eventName, callback);
    }
}

function getWindowSize() {
    var win = window,
        d = document,
        e = d.documentElement,
        g = d.getElementsByTagName('body')[0],
        w = win.innerWidth || e.clientWidth || g.clientWidth,
        h = win.innerHeight|| e.clientHeight|| g.clientHeight;
    return { width: w, height: h};
}

function getStyle(element, styleProp) {
    if(element && styleProp) {
        if (element.currentStyle) {
            return element.currentStyle[styleProp];
        } else if (window.getComputedStyle) {
            return window.getComputedStyle(element, null).getPropertyValue(styleProp);
        }
    }
    return null;
};

}());
</script>";
	
	
	
	

		$ir.= '<script>
		  // instantiate and show the pivot grid
		  new orb.pgridwidget(config).
		         .render(document.getElementById("demo-pgrid"));
		</script>

		<!-- Itt jelenik meg -->
		<div id="demo-pgrid"></div>';
            return $ir;
        }
        
     
}
?>