<?php
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD `type_internal`  VARCHAR( 255 ) NOT NULL ;

");

$installer->endSetup(); 