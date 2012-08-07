<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
//$installer = $this;

$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales/quote_payment'), 'installment_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER);

$installer->addAttribute('quote_payment', 'installment_type_id', array('type' => 'int', 'visible' => false));

$installer->endSetup();
