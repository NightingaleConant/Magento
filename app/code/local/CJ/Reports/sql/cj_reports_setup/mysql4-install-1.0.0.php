<?php
/* @var $installer CJ_Reports_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();


$installer->run("

CREATE TABLE `{$this->getTable('ar_report_history')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) DEFAULT NULL,
  `increment_id` varchar(50) DEFAULT NULL COMMENT 'Order Increment Id',
  `billing_name` varchar(255) DEFAULT NULL COMMENT 'Billing Name',
  `customer_group` varchar(255) DEFAULT NULL,
  `order_type` varchar(255) DEFAULT NULL,
  `total_paid` decimal(12,4) DEFAULT NULL,
  `030_days` decimal(12,4) DEFAULT NULL,
  `3160_days` decimal(12,4) DEFAULT NULL,
  `6190_days` decimal(12,4) DEFAULT NULL,
  `91120_days` decimal(12,4) DEFAULT NULL,
  `121180_days` decimal(12,4) DEFAULT NULL,
  `181240_days` decimal(12,4) DEFAULT NULL,
  `241_days` decimal(12,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

");

$installer->endSetup();
