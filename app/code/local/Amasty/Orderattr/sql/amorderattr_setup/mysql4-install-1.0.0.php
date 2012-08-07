<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('eav/attribute');
$cols = $installer->getConnection()->fetchCol($fieldsSql);

if (!in_array('is_visible_on_front', $cols))
{
    $installer->run("ALTER TABLE `{$this->getTable('eav/attribute')}` ADD `is_visible_on_front` TINYINT( 1 )  UNSIGNED NOT NULL");
}
if (!in_array('sorting_order', $cols))
{
    $installer->run("ALTER TABLE `{$this->getTable('eav/attribute')}` ADD `sorting_order` SMALLINT( 1 ) UNSIGNED NOT NULL");
}
if (!in_array('checkout_step', $cols))
{
    $installer->run("ALTER TABLE `{$this->getTable('eav/attribute')}` ADD `checkout_step` TINYINT( 1 )  UNSIGNED NOT NULL");
}

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('amorderattr/order_attribute')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
");

$installer->endSetup(); 