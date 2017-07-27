<?php


class AdminExpensesController extends ModuleAdminController {
	public function __construct() {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'ControllingExpenses';
        $this->table = 'controlling_expenses';
        $this->lang = false;  
        
        $this->fields_list = array(
            'id_controlling_expenses'    => array(
                'title' => $this->l('ID'),
                'type'  => 'hidden',
                'class' => 'fixed-width-xs'
            ),
            'expense_type'    => array(
                'title' => $this->l('Expense type'),
                'filter_key' => 'expense_type',
                'havingFilter' => true
            ),
            'supplier'    => array(
                'title' => $this->l('Supplier'),
                'filter_key' => 'id_controlling_supplier',
                'havingFilter' => true
            ),
            'sum_tax_inclusive' => array(
                'title' => $this->l('Expense'),
                 'type' => 'price',
                 'align' => 'text-right',
            ),
            'date' => array(
                'title' => $this->l('Date'),
                'orderby' => true,
                'type' => 'date',
                'align' => 'text-right',
            ),
           
            'manual' => array(
                'title' => $this->l('Manual expense'),
                'class' => 'fixed-width-xs'
            ),
        );
        
        parent::__construct();
    }
    
    public function init()
    {
        parent::init();

        if (Tools::isSubmit('addcontrolling_supplier')) {
            $this->table = 'controlling_supplier';
            $this->className = 'ControllingSupplier';
            $this->identifier = 'id_controlling_supplier';
            $this->lang = false;

            $this->action = 'new';
            $this->display = 'add';
        }
    }
    
    /**
     * AdminController::renderList() override
     * @see AdminController::renderList()
     */
    public function renderList()
    {
        // removes links on rows
        $this->list_no_link = true;

        // adds actions on rows
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        // query: select
        $this->_select = '
			id_controlling_expenses,
			et.name as expense_type, 
			sup.name as supplier,
			sum_tax_inclusive,
			date';

        // query: join
        $this->_join = '
			LEFT JOIN `'._DB_PREFIX_.'controlling_expense_type` et ON (et.id_expense_type = a.id_expense_type)
			LEFT JOIN `'._DB_PREFIX_.'controlling_supplier` sup ON (sup.id_controlling_supplier = a.id_controlling_supplier)';
	    $this->_orderby = 'a.date DESC';
        $this->_use_found_rows = false;

        return parent::renderList();
    }
    
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_expense'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new manual expense', null, null, false),
                'icon' => 'process-icon-new'
            );

            $this->page_header_toolbar_btn['new_supplier'] = array(
                'href' => self::$currentIndex.'&addcontrolling_supplier&token='.$this->token,
                'desc' => $this->l('Add new supplier', null, null, false),
                'icon' => 'process-icon-new'
            );
       
        }

        parent::initPageHeaderToolbar();
    }    
    
    /**
     * AdminController::renderForm() override
     * @see AdminController::renderForm()
     */
    public function renderForm()
    {
        
        if (Tools::isSubmit('addcontrolling_supplier')) {
            
            // gets the expense types
            $query = new DbQuery();
            $query->select('id_expense_type, name');
            $query->from('controlling_expense_type');
            $query->orderBy('name');        
            $expense_types = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            
            // sets the fields of the form
            $this->fields_form = array(
                'legend' => array(
                    'title' => $this->l('New supplier'),
                    'icon' => 'icon-pencil'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_supplier',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Supplier name'),
                        'name' => 'name',
                        'maxlength' => 20,
                        'required' => true,
                        'hint' => $this->l('Name of the supplier'),
                    ),
                    
                    array(
                        'type' => 'select',
                        'label' => $this->l('Expense type'),
                        'name' => 'id_expense_type',
                        'required' => false,
                        'default_value' => '',
                        'options' => array(
                            'query' => $expense_types,
                            'id' => 'id_expense_type',
                            'name' => 'name',
                        ),
                        'hint' => $this->l('Expense types')
                    ),                
                ),    
            );
            
        } else if (Tools::isSubmit('add'.$this->table)) {
            /** @var Warehouse $obj */
            // loads current warehouse
            if (!($obj = &$this->loadObject(true))) {
                return;
            }
    
            // gets the manager of the warehouse
            $query = new DbQuery();
            $query->select('id_controlling_supplier, name');
            $query->from('controlling_supplier');
            $query->orderBy('name');        
            $supplier_array = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            
            // gets the manager of the warehouse
            $query = new DbQuery();
            $query->select('id_expense_type, name');
            $query->from('controlling_expense_type');
            $query->orderBy('id_inner_nr');        
            $expense_types = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    
            // sets the title of the toolbar
            if (Tools::isSubmit('add'.$this->table)) {
                $this->toolbar_title = $this->l('Expenses: create a manual expense');
                $legend = $this->l('New manual expense');
            } else {
                $this->toolbar_title = $this->l('Expenses: edit a manual expense');
                $legend = $this->l('Edit expense');
            }
            
            $bank_transaction = $this->l('bank transaction');
            $cod = $this->l('cod');
            $bank_card = $this->l('bank card');
            $payments = array(
                array('id' => $bank_transaction, 'name' => $bank_transaction),
                array('id' => $code, 'name' => $cod),
                array('id' => $bank_card, 'name' => $bank_card),
            );
            
            // sets the fields of the form
            $this->fields_form = array(
                'legend' => array(
                    'title' => $legend,
                    'icon' => 'icon-pencil'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_controlling_expense',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Expense type'),
                        'name' => 'id_expense_type',
                        'required' => true,
                        'default_value' => 0,
                        'options' => array(
                            'query' => $expense_types,
                            'id' => 'id_expense_type',
                            'name' => 'name',
                        ),
                        'hint' => $this->l('Expense types')
                    ),
                    
                    array(
                        'type' => 'select',
                        'label' => $this->l('Supplier'),
                        'name' => 'id_controlling_supplier',
                        'required' => false,
                        'default_value' => '',
                        'options' => array(
                            'query' => $supplier_array,
                            'id' => 'id_controlling_supplier',
                            'name' => 'name',
                        ),
                        'hint' => $this->l('Supplier name')
                    ),
                    
                    array(
                         'type' => 'text',
                        'label' => $this->l('Brutto expense'),
                        'name' => 'sum_tax_inclusive',
                        'maxlength' => 7,
                        'required' => true,
                        'hint' => $this->l('Expense sum.'),
                    ),
                    
                    array(
                        'type' => 'text',
                        'label' => $this->l('Invoice nr'),
                        'name' => 'invoice_nr',
                        'maxlength' => 20,
                        'required' => false,
                        'hint' => $this->l('Invoice nr - optional'),
                    ),
                    
                    
                    array(
                        'type' => 'date',
                        'label' => $this->l('Date Invoice'),
                        'name' => 'date_invoice',
                        'maxlength' => 10,
                        'required' => true,
                        'hint' => $this->l('Invoice date.'),
                        ) ,                 
                    
                    array(
                        'type' => 'date',
                        'label' => $this->l('Date Service'),
                        'name' => 'date',
                        'maxlength' => 10,
                        'required' => true,
                        'hint' => $this->l('Service date.'),
                    ),

                    
                    array(
                        'type' => 'select',
                        'label' => $this->l('Payment type'),
                        'name' => 'payment_type',
                        'required' => false,
                        'default_value' => '',
                        'options' => array(
                            'query' => $payments,
                            'id' => 'id',
                            'name' => 'name',
                        ),
                        'hint' => $this->l('Payment type')
                    ),
                    
                )
             );

        }
        
        $this->fields_form['submit'] = array(
            'title' => $this->l('Save'),
        );
            
        return parent::renderForm();
    } 
    
    /**
     * AdminController::postProcess() override
     * @see AdminController::postProcess()
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitAddcontrolling_supplier')) {
            //if (!($obj = &$this->loadObject(true))) {
            //    return;
            //}
            $this->addSupplier();

            // hack for enable the possibility to update a warehouse without recreate new id
            $this->deleted = false;
        } else {
            parent::postProcess();
        }
            
    }  
    
    /**
     * @see AdminController::processAdd();
     */
    public function processAdd()
    {
     
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            if (!($obj = &$this->loadObject(true))) {
                return;
            }

            $this->addExpense();
            //$obj->manual = 1;

            // hack for enable the possibility to update  without recreate new id
            $this->deleted = false;

        }
        
        
    }
    
/**
     * @see AdminController::processUpdate();
     */
    public function processUpdate()
    {
        // loads object
        if (!($object = $this->loadObject(true))) {
            return;
        }

        return parent::processUpdate();
    }    
    
    private function addExpense() {
        $expense = new ControllingExpenses();
        $expense->id_expense_type = Tools::getValue('id_expense_type', null);
        $expense->id_controlling_supplier = Tools::getValue('id_controlling_supplier', null);
        $expense->sum_tax_inclusive = Tools::getValue('sum_tax_inclusive', null);
        $expense->sum_tax_exclusive = Tools::getValue('sum_tax_inclusive', null);
        $expense->invoice_nr = Tools::getValue('invoice_nr', null);
        $expense->date = Tools::getValue('date', null);
        $expense->date_invoice = Tools::getValue('date_invoice', null);
        $expense->date_payment = Tools::getValue('date', null);
        $expense->payment_type = Tools::getValue('payment_type', null);
        $expense->manual = 1;
        $expense->save();
    }
    
    
    private function addSupplier() {
        $supplier = new ControllingSupplier();
        $supplier->id_expense_type = Tools::getValue('id_expense_type', null);
        $supplier->name = Tools::getValue('name', null);

        $supplier->save();
    }    
    
/**
     * @see AdminController::processDelete();
     */
    public function processDelete()
    {
        if (Tools::isSubmit('delete'.$this->table)) {
           
            // check if the object exists and can be deleted
            if (!($obj = $this->loadObject(true))) {
                return;
            } elseif ($obj->manual == 0) { // not possible : products
                $this->errors[] = $this->l('It is not possible to delete a this expense. Only manually created expenses can be deleted');
                return;            
            } else {
                // else, it can be deleted
                return parent::processDelete();
            }
        }
    }    
      
} 