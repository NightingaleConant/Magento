<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('eav/attribute');
$cols = $installer->getConnection()->fetchCol($fieldsSql);

if (!in_array('show_on_grid', $cols))
{
    $installer->run("ALTER TABLE `{$this->getTable('eav/attribute')}` ADD `show_on_grid` TINYINT( 1 )  UNSIGNED NOT NULL");
}

$installer->endSetup();