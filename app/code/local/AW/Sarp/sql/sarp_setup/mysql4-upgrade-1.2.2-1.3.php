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

/* $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("
ALTER TABLE {$this->getTable('sarp/alert')} ADD `status` TINYINT( 4 ) NOT NULL DEFAULT '1';
ALTER TABLE {$this->getTable('sarp/alert')} ADD `store_ids` TEXT NOT NULL;
ALTER TABLE {$this->getTable('sarp/alert')} ADD INDEX ( `status` ) ;
");

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/protxDirect')} (
  `id` int(11) NOT NULL auto_increment,
  `subscription_id` int(11) NOT NULL,
  `vendor_tx_code` varchar(100) NOT NULL,
  `vps_tx_id` varchar(100) NOT NULL,
  `tx_auth_no` varchar(100) NOT NULL,
  `security_key` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `FK_sarp_subscription_protx` (`subscription_id`),
  CONSTRAINT `FK_sarp_subscription_protx` FOREIGN KEY (`subscription_id`) REFERENCES {$this->getTable('sarp/subscription')} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
");

$this->addAttribute('catalog_product', 'aw_sarp_download_by_status', array(
                                                                          'backend' => '',
                                                                          'frontend' => '',
                                                                          'source' => 'eav/entity_attribute_source_boolean',
                                                                          'group' => 'Subscription',
                                                                          'label' => 'Download access based on the subscription status: Yes/No',
                                                                          'input' => 'select',
                                                                          'class' => '',
                                                                          'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                          'type' => 'int',
                                                                          'visible' => 1,
                                                                          'user_defined' => false,
                                                                          'default' => '0',
                                                                          'apply_to' => 'subscription_downloadable',
                                                                          'visible_on_front' => false
                                                                     ));

$this->addAttribute('catalog_product', 'aw_sarp_shipping_cost', array(
                                                                     'backend' => '',
                                                                     'frontend' => '',
                                                                     'source' => 'eav/entity_attribute_source_boolean',
                                                                     'group' => 'Subscription',
                                                                     'label' => 'Shipping cost',
                                                                     'input' => 'text',
                                                                     'class' => '',
                                                                     'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                     'type' => 'text',
                                                                     'visible' => 1,
                                                                     'user_defined' => false,
                                                                     'required' => 0,
                                                                     'default' => '0',
                                                                     'apply_to' => 'subscription_simple, subscription_downloadable',
                                                                     'visible_on_front' => false
                                                                ));

$fieldList = array('price', 'special_price', 'special_from_date', 'special_to_date',
                   'minimal_price', 'cost', 'tier_price', 'tax_class_id',
                   'links_purchased_separately', 'samples_title', 'links_title');

$fieldList = array_merge($fieldList, array('aw_sarp_enabled', 'aw_sarp_period',
                                          'aw_sarp_subscription_price', 'aw_sarp_first_period_price',
                                          'aw_sarp_include_to_group', 'aw_sarp_exclude_to_group', 'postman_notice'));

$this->_setupCache = array();

foreach ($fieldList as $field) {
    $applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
    if (!in_array('subscription_downloadable', $applyTo)) {
        $applyTo[] = 'subscription_downloadable';
        $installer->updateAttribute('catalog_product', $field, 'apply_to', implode(',', $applyTo));
    }
}

$installer->endSetup();