<?php
$installer = $this;
$installer->startSetup();
$installer->run("

    CREATE TABLE `custom_dailycost` (
        `dailycost_id` int(11) NOT NULL AUTO_INCREMENT,
        `store_id` smallint(5) NOT NULL,
        `sku` varchar(64) NOT NULL,
        `cost` decimal(12,4) NULL,
        `created_at` date NOT NULL,
        PRIMARY KEY (`dailycost_id`),
        INDEX `store_sku` (`store_id`, `sku`, `created_at`)
    ) ENGINE=InnoDB;
    
");
$installer->endSetup();
?>