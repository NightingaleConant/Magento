<?php
class NightingaleConant_OrderPlacedBy_Model_OrderPlacedBy extends Mage_Reports_Model_Mysql4_Order_Collection
{
    function __construct() {
        parent::__construct();
        $this->setResourceModel('sales/order_item');
        $this->_init('sales/order_item','item_id');
   }
 
    public function setDateRange($from, $to) {
        $this->_reset();
        $this->getSelect()
             ->joinInner(array(
                 'i' => $this->getTable('sales/order_item')),
                 'i.order_id = main_table.entity_id'
                 )
             ->where('i.parent_item_id is null')
             ->where("i.created_at BETWEEN '".$from."' AND '".$to."'")
             ->where('main_table.state = \'complete\'')
             ->columns(array('ordered_qty' => 'count(distinct `main_table`.`entity_id`)'));
        // uncomment next line to get the query log:
        // Mage::log('SQL: '.$this->getSelect()->__toString());
        return $this;
    }
 
    public function setStoreIds($storeIds)
    {
        return $this;
    }
 
}
?>