<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('eav/attribute');
$cols = $installer->getConnection()->fetchCol($fieldsSql);

if (!in_array('parent_dropdown', $cols))
{
    $installer->run("ALTER TABLE `{$this->getTable('eav/attribute')}` ADD `parent_dropdown` INT( 10 )  UNSIGNED NOT NULL");
}

$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('eav/attribute_option');
$cols = $installer->getConnection()->fetchCol($fieldsSql);

if (!in_array('parent_option_id', $cols))
{
    $installer->run("ALTER TABLE `{$this->getTable('eav/attribute_option')}` ADD `parent_option_id` INT( 10 )  UNSIGNED NOT NULL");
}

$installer->endSetup();