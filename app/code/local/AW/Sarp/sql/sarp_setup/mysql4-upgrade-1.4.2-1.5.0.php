<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

$installer = $this;
$installer->startSetup();
$this->_setupCache = array();
$sarpAtt = array('aw_sarp_enabled', 'aw_sarp_period', 'aw_sarp_subscription_price', 'aw_sarp_first_period_price', 'aw_sarp_include_to_group', 'aw_sarp_exclude_to_group', 'postman_notice', 'aw_sarp_shipping_cost', 'aw_sarp_anonymous_subscription');

//set att for grouped
$atts = Mage::getResourceModel('eav/entity_attribute_collection');
if (version_compare(Mage::getVersion(), '1.4.0.0', '>=')) {
    $atts->getSelect()
            ->join(array('catalog_att' => $this->getTable('catalog/eav_attribute')), 'main_table.attribute_id = catalog_att.attribute_id', array('apply_to'))
            ->where('catalog_att.apply_to LIKE \'%group%\'')
            ->where('catalog_att.apply_to NOT LIKE \'%subscription_grouped%\'');
}
else {
    $atts->getSelect()
            ->where('main_table.apply_to LIKE \'%group%\'')
            ->where('main_table.apply_to NOT LIKE \'%subscription_grouped%\'');
}
$fieldListGrouped = $atts->getColumnValues('attribute_code');
$fieldListGrouped = array_merge($fieldListGrouped, $sarpAtt);

foreach ($fieldListGrouped as $field) {
    $applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
    if (!in_array('subscription_grouped', $applyTo)) {
        $applyTo[] = 'subscription_grouped';
        $installer->updateAttribute('catalog_product', $field, 'apply_to', implode(',', $applyTo));
    }
}

//set att for configurable
$atts = Mage::getResourceModel('eav/entity_attribute_collection');
if (version_compare(Mage::getVersion(), '1.4.0.0', '>=')) {
    $atts->getSelect()
            ->join(array('catalog_att' => $this->getTable('catalog/eav_attribute')), 'main_table.attribute_id = catalog_att.attribute_id', array('apply_to'))
            ->where('catalog_att.apply_to LIKE \'%configurable%\'')
            ->where('catalog_att.apply_to NOT LIKE \'%subscription_configurable%\'');
}
else {
    $atts->getSelect()
            ->where('main_table.apply_to LIKE \'%configurable%\'')
            ->where('main_table.apply_to NOT LIKE \'%subscription_configurable%\'');
}
$fieldListGrouped = $atts->getColumnValues('attribute_code');
$fieldListGrouped = array_merge($fieldListGrouped, $sarpAtt);

foreach ($fieldListGrouped as $field) {
    $applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
    if (!in_array('subscription_configurable', $applyTo)) {
        $applyTo[] = 'subscription_configurable';
        $installer->updateAttribute('catalog_product', $field, 'apply_to', implode(',', $applyTo));
    }
}

$installer->run("
        CREATE TABLE IF NOT EXISTS `{$this->getTable('sarp/notice')}` (
            `id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
            `order_id` INT( 10 ) NOT NULL ,
            `notice` TEXT NOT NULL ,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();