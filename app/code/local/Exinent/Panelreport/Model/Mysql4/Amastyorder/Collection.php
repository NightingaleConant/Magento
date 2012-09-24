<?php
class Exinent_Panelreport_Model_Mysql4_Amastyorder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('panelreport/amastyorder');
    }
    
    protected  function _initSelect()
    {
        $this->getSelect()->from( array('main_table'=> $this->getTable('panelreport/amastyorder') , array('promo_code, order_id, entity_id')));
        
        $this->getSelect()->joinLeft(
			    array('sfo'=> $this->getResource()->getTable('sales/order')), 'main_table.order_id = sfo.entity_id', 
			    array('sfo.entity_id', 'sfo.store_id', 'sfo.created_at', 'count(main_table.order_id) as total_orders', 'SUM(sfo.base_subtotal) AS gross_total'
                                  , 'SUM(sfo.shipping_amount) AS shipping_amount', 'SUM(sfo.tax_amount) AS tax_amount' , 'SUM(sfo.base_subtotal - sfo.tax_amount) as grs_sls'
                                  , 'SUM(sfo.total_refunded) AS total_refunded', 'SUM(sfo.total_refunded) * 100/SUM(sfo.base_subtotal - sfo.tax_amount) AS return_percent'
                                  , 'SUM(sfo.adjustment_positive) as adjustment_positive', ' SUM(sfo.adjustment_positive) * 100/SUM(sfo.base_subtotal - sfo.tax_amount) as adjustment_percent'
                                  , 'SUM(sfo.base_total_paid) as base_total_paid', 'SUM(sfo.base_total_paid)*100/SUM(sfo.base_subtotal - sfo.tax_amount) as base_total_percent'
                                  )
			    );
        /*$this->getSelect()->joinLeft(
                            array('sfop'=> $this->getResource()->getTable('sales/order_payment')), 'main_table.order_id = sfop.entity_id',
                            array(
                                'SUM( IF(sfop.method = "writeoff", sfop.amount_ordered, 0) ) AS write_off'
                            )
                            );*/
	$this->getSelect()->joinLeft(array('ckc'=>'custom_key_code'), 'main_table.promo_code = ckc.ckc_key_code', array('ckc.ckc_key_code_name'));
	//$this->getSelect()->where('main_table.promo_code LIKE ?', '%B6001B99%');
        $this->getSelect()->group('main_table.promo_code');
        //->limit('20 , 10');
        
        return $this;
    }
    
    protected function _joinFields($from = '', $to = '')
    {
        $this->addFieldToFilter('sfo.created_at' , array("from" => $from, "to" => $to, "datetime" => true));
        //Mage::log($this->getSelect()->__toString());
        return $this;
    }
    
    public function setDateRange($from, $to)
    {
        $this->_reset()->_joinFields($from, $to);
        return $this;
    }
    
    public function load($printQuery = false, $logQuery = false)
    {
        if($this->isLoaded()){
            return $this;
        }
        parent::load($printQuery, $logQuery);
        return $this;
    }
    
    public function setStoreIds($storeIds)
    {
        return $this;
    }
    
    public function getReportFull($from, $to)
    {
        return $this->_model->getReportFull($this->timeShift($from), $this->timeShift($to));
    }
    
    public function timeShift($datetime)
    {
        return Mage::app()->getLocale()->utcDate(null, $datetime, true, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }
    
}
?>