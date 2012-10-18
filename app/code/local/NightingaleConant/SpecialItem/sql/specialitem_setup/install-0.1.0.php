<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `custom_specialitem` (
        `specialitem_id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,        
        `product_sku` varchar(255) NULL,
        `item_sku` varchar(255) NULL,
        `promo_code` varchar(255) NULL,
        `order_type` varchar(255) NULL,
        `customer_group` varchar(255) NULL,
        `active` boolean NOT NULL DEFAULT 0,
        `created_at` datetime NULL,
        `updated_at` datetime NULL
    ) ENGINE=InnoDB;
    
");
$installer->endSetup();
?>
