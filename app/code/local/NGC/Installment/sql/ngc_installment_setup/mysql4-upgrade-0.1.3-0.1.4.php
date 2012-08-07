<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$installer->addAttribute('sales/order', 'installment_type_id', array('type'=>'int'));

$installer->endSetup();
