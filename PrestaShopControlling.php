<?php
/* Prestashop plugin: Controlling system
 * Each pivot table is filterable by day, week, month, year
 * The data will be collected each night from Facebook, Google, and Prestashop DB via cron
 * Data for the administration:
 *   - income by time interval
 *   - income by product categories
 *   - income by source (by from which ad, campaign or landing page)
 *   - expenses by time interval
 *   - margin by order
 * Data for the Marketing department
 *   - all marketing data source
 *   - campaign data source
 *   - funnel data
 *   - coupons
 *   - product by time interval
 *
 * @author    Tibor Bossanyi
 * @website   
 * @contact   tibor.bossanyi@gmail.com
 * @copyright 
 * @license   
*/

include dirname(__FILE__) . '/classes/ControllingSource.php';
include dirname(__FILE__) . '/classes/ControllingMarketingSource.php';
include dirname(__FILE__) . '/classes/ControllingExpenses.php';
include dirname(__FILE__) . '/classes/ControllingSupplier.php';
include dirname(__FILE__) . '/classes/Reports.php';
include dirname(__FILE__) . '/classes/FunnelSource.php';
include dirname(__FILE__) . '/classes/Funnel.php';
include dirname(__FILE__) . '/classes/SourceEntryPoint.php';
include dirname(__FILE__) . '/classes/ControllingSourceFB.php';


class PrestaShopControlling extends Module {

	function __construct() {
		$this->name          = 'andiocontrolling';
		$this->tab           = 'andio';
		$this->version       = '1.2';
		$this->displayName   = 'Andio controlling system';
		$this->description   = 'data controlling';
		parent::__construct();
	}

	public function install() {
	    Configuration::updateValue('CONTR_LAST_COLLECT', '1970-01-01');
		$this->install_tabs = array(
			array('name' => $this->l('Controlling System'),'class'=>'AdminControlling','parent'=>0),
			array('name' => $this->l('Collect data'),'class'=>'AdminSourceCollect','parent'=>'AdminControlling'),
			array('name' => $this->l('Expenses'),'class'=>'AdminExpenses','parent'=>'AdminControlling'),
			array('name' => $this->l('Administration'),'class'=>'AdminAdministration','parent'=>'AdminControlling'),
			array('name' => $this->l('Marketing'),'class'=>'AdminMarketing','parent'=>'AdminControlling'),
			array('name' => $this->l('Service'),'class'=>'AdminService','parent'=>'AdminControlling'),
			array('name' => $this->l('ControllingSettings'),'class'=>'AdminControllingSettings','parent'=>'AdminControlling'),
			array('name' => $this->l('Funnel Definitions'),'class'=>'AdminFunnel','parent'=>'AdminControlling'),
			array('name' => $this->l('Source Definitions'),'class'=>'AdminSource','parent'=>'AdminControlling'),
		);
		foreach($this->install_tabs as $t){
			$tabs &= $this->installModuleTab($t['class'],$t['name'],$t['parent']);
		}
		return parent::install();
	}
	
    public  function installModuleTab($tabClass, $tabName, $tabParent = 0)
	{
		$id_lang_default = Configuration::get('PS_LANG_DEFAULT');
		$tab = new Tab();
		$tab->name = array($id_lang_default => $tabName);
		$tab->class_name = $tabClass;
		$tab->module = $this->name;
		$tab->id_parent = $tabParent === 0 ? 0 : Tab::getIdFromClassName($tabParent);

		return $tab->save();
	}

	public  function uninstallModuleTab($tabClass)
	{
		$idTab = Tab::getIdFromClassName($tabClass);

		if($idTab != 0)
		{
			$tab = new Tab($idTab);
			$tab->delete();

		}
		return true;
	}

	public function uninstall() {
		$this->uninstall_tabs = array('AdminControlling','AdminAdministration','AdminMarketing','AdminService','AdminControllingSettings', 'AdminSourceCollect', 'AdminFunnel', 'AdminSource');

		return parent::uninstall();
	}

	
    function getContent() {		
		$output = '<h2>'.$this->displayName.' (v. '.$this->version.')</h2>
		<p>'.$this->description.'</p>';

		if(Tools::isSubmit('buttonReport')) {	
		    $today = date('Y-m-d');      
		    $yesterday = date('Y-m-d',strtotime($today . "-1 days"));  	
            $report = new Reports();
            $report->report($yesterday);
            
            // weekly
		    $yesterday = date('Y-m-d',strtotime($today . "-1 days"));  
		    $week = 	date('Y-m-d',strtotime($today . "-8 days"));  
            $report = new Reports();
            $report->report($week, $yesterday);
            
		}
		
		if(Tools::isSubmit('buttonReportFunnel')) {	            
            // weekly
            $today = date('Y-m-d');      
		    $yesterday = date('Y-m-d',strtotime($today . "-1 days"));  
		    $week = 	date('Y-m-d',strtotime($today . "-8 days"));  
            $report = new Reports();
            $report->reportFunnel($yesterday, $today);
            
		}
		
		if(Tools::isSubmit('buttonCollection')) {	            
            // weekly
            $today = date('Y-m-d');      
		    $yesterday = date('Y-m-d',strtotime($today . "-1 days"));  
		    $week = 	date('Y-m-d',strtotime($today . "-8 days"));  
		    
		    $db_collect = Tools::getValue('db_collect');
		    $fb_collect = Tools::getValue('fb_collect');
		    $ga_collect = Tools::getValue('ga_collect');
		    $adw_collect = Tools::getValue('adw_collect');
		    
		    
		    ControllingSource::collectAllData($db_collect, $fb_collect, $ga_collect, $adw_collect, $yesterday, $yesterday);
		}

		
		$output .= '<fieldset><legend>'.$this->l('Tests').'</legend>';
		$output .= '    <form method="post" action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'"" >';
        $output .= '        <input type="submit" class="button pointer" name="buttonReport" value="'.$this->l('Report daily').'" />    
                        </form>
                        
                    </fieldset>';
		
        $output .= '<fieldset><legend>'.$this->l('Funnel').'</legend>';
		$output .= '    <form method="post" action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'"" >';
        $output .= '        <input type="submit" class="button pointer" name="buttonReportFunnel" value="'.$this->l('Report funnel').'" />    
                        </form>';
                        
        $output .= '<fieldset><legend>'.$this->l('Collection').'</legend>';
		$output .= '<form method="post" action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'"" >';
        $output .= '<label><input type="checkbox" name="db_collect" value="1" checked="checked"/>Collect DB</label><br />';		
        $output .= '<label><input type="checkbox" name="fb_collect" value="1" checked="checked"/>Collect Fb</label><br />';
        $output .= '<label><input type="checkbox" name="ga_collect" value="1" checked="checked"/>Collect GA</label><br />';
        $output .= '<label><input type="checkbox" name="adw_collect" value="0" />Collect ADW</label><br />';
        $output .= '<input type="submit" class="button pointer" name="buttonCollection" value="'.$this->l('Collect source').'" />';
        $output .= '</form>';
        $output .= '</fieldset>';	
        return $output;                    	
	}
}


