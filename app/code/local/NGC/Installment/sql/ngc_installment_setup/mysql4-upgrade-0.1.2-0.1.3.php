<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('installment_transaction')} DROP PRIMARY KEY;
ALTER TABLE {$this->getTable('installment_transaction')} ADD id int UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id);
ALTER TABLE {$this->getTable('installment_master')} DROP PRIMARY KEY;
ALTER TABLE {$this->getTable('installment_master')} ADD id int UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id);

");

$installer->endSetup();
