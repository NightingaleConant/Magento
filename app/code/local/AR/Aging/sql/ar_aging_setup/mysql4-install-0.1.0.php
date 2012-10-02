<?php

/* create the table for the AR Aging report */

$installer = $this;

$installer->startSetup();

$installer->run("
                create table `{$installer->getTable('ar_aging')}` as 
            (
            SELECT a.entity_id AS sfo_id, a.store_id, a.base_total_paid, a.base_grand_total, (a.base_total_paid - a.base_grand_total) as btp_bgt, a.created_at, a.increment_id, a.base_total_refunded, (a.base_total_paid - a.base_total_refunded) as btp_btr, a.total_refunded, b.entity_id AS cev_id, b.value
            FROM `{$installer->getTable('sales_flat_order')}` a, `{$this->getTable('customer_entity_varchar')}` b
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
    ");

$installer->endSetup();

?>