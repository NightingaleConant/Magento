<?php
class AR_Aging_Model_Aging extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ar_aging/aging');   
    }
    
    public function updateAgingTable()
    {
        
        $sql = "
        DROP TABLE IF EXISTS `ar_aging`;
        CREATE TABLE `ar_aging` as 
                    (
                    SELECT a.entity_id AS sfo_id, a.store_id, a.base_total_paid, a.base_grand_total, (a.base_total_paid - a.base_grand_total) as btp_bgt, a.created_at, a.increment_id, a.base_total_refunded, (a.base_total_paid - a.base_total_refunded) as btp_btr, a.total_refunded, b.entity_id AS cev_id, b.value
                    FROM `sales_flat_order` a, `customer_entity_varchar` b
                    WHERE a.store_id =1
                    AND b.attribute_id = '156'
                    AND a.base_grand_total > 0.0001
                    AND a.customer_id = b.entity_id
                    AND a.customer_group_id NOT IN ( 20, 22, 15 )
                    AND a.status != 'canceled'
                    AND a.base_grand_total != a.base_total_paid
                    AND a.increment_id not like '050%'
                    AND b.entity_id   not in ('107303','107327')
                    ORDER BY `sfo_id` DESC
                    )
        ";
        
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query($sql);

        Mage::log('Cron updated the ar_aging table');
        
    }
}
?>