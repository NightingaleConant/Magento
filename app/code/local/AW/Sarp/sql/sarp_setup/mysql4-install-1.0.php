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

CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/alert')} (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL default 'expiration',
  `recipient` varchar(255) NOT NULL default 'customer',
  `time_is_after` int(4) NOT NULL default '0',
  `time_multiplier` tinyint(2) NOT NULL default '1',
  `time_amount` int(11) NOT NULL,
  `email_template` varchar(255) NOT NULL default 'sarp_template',
  PRIMARY KEY  (`id`),
  KEY `event_type` (`type`,`time_multiplier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/alert_event')} (
  `id` int(11) NOT NULL auto_increment,
  `alert_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `status` varchar(64) NOT NULL default 'pending',
  PRIMARY KEY  (`id`),
  KEY `alert_id` (`alert_id`,`date`,`subscription_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/subscription_flat')} (
  `item_id` int(11) NOT NULL auto_increment,
  `subscription_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `flat_last_order_amount` float NOT NULL,
  `flat_last_order_currency_code` varchar(5) NOT NULL,
  `flat_last_order_status` varchar(64) NOT NULL,
  `products_text` text NOT NULL,
  `products_sku` text NOT NULL,
  `flat_date_expire` date default NULL,
  `has_shipping` tinyint(1) NOT NULL default '1',
  `flat_next_payment_date` date default NULL,
  `flat_next_delivery_date` date default NULL,
  PRIMARY KEY  (`item_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `last_order_amount` (`flat_last_order_amount`,`flat_last_order_status`),
  KEY `date_expire` (`flat_date_expire`),
  KEY `flat_last_order_currency_code` (`flat_last_order_currency_code`),
  KEY `has_shipping` (`has_shipping`),
  KEY `next_payment` (`flat_next_payment_date`),
  KEY `flat_next_delivery_date` (`flat_next_delivery_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/log')} (
  `id` int(11) NOT NULL auto_increment,
  `module` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` mediumtext NOT NULL,
  `level` tinyint(4) NOT NULL default '1',
  `code` int(6) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `module` (`module`,`level`,`code`,`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/period')} (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `period_type` enum('day','week','month','year') NOT NULL default 'day',
  `period_value` int(11) NOT NULL default '1',
  `expire_type` enum('day','week','month','year') NOT NULL default 'month',
  `expire_value` int(11) NOT NULL default '1',
  `excluded_weekdays` varchar(32) NOT NULL,
  `payment_offset` int(5) NOT NULL default '0',
  `sort_order` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `period_type` (`period_type`,`period_value`,`expire_type`,`expire_value`),
  KEY `excluded_weekdays` (`excluded_weekdays`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/sequence')} (
  `id` int(11) NOT NULL auto_increment,
  `subscription_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` varchar(255) character set latin1 NOT NULL default 'pending',
  `order_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `subscription_id` (`subscription_id`,`date`),
  KEY `status` (`status`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/subscription')} (
  `id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL,
  `date_start` date NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  `period_type` int(11) NOT NULL,
  `primary_quote_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `customer_id` (`customer_id`,`date_start`,`status`),
  KEY `period_type` (`period_type`),
  KEY `primary_quote_id` (`primary_quote_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS {$this->getTable('sarp/subscription_item')} (
  `id` int(11) NOT NULL auto_increment,
  `subscription_id` int(11) NOT NULL,
  `primary_order_id` int(11) NOT NULL,
  `primary_order_item_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `subscription_id` (`subscription_id`,`primary_order_id`,`primary_order_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



ALTER TABLE {$this->getTable('sarp/alert_event')}
  ADD CONSTRAINT `FK_ALERT_ID` FOREIGN KEY (`alert_id`) REFERENCES {$this->getTable('sarp/alert')} (`id`) ON DELETE CASCADE;


ALTER TABLE {$this->getTable('sarp/subscription_flat')}
  ADD CONSTRAINT `FK_FLAT_SUBSCRIPTION_ID` FOREIGN KEY (`subscription_id`) REFERENCES {$this->getTable('sarp/subscription')} (`id`) ON DELETE CASCADE;


ALTER TABLE {$this->getTable('sarp/sequence')}
  ADD CONSTRAINT `FK_SEQUENCE_SUBSCRIPTION_ID` FOREIGN KEY (`subscription_id`) REFERENCES {$this->getTable('sarp/subscription')} (`id`) ON DELETE CASCADE;

ALTER TABLE {$this->getTable('sarp/subscription_item')}
  ADD CONSTRAINT `FK_SUBSCRIPTION_ID` FOREIGN KEY (`subscription_id`) REFERENCES {$this->getTable('sarp/subscription')} (`id`) ON DELETE CASCADE;	
");


$typeId = Mage::getModel('eav/entity_type')->loadByCode('order')->getEntityTypeId();

$installer->run("
    DELETE FROM `{$this->getTable('eav/attribute')}` WHERE `attribute_code`='postman_notice' limit 1;
");


$setup = $this;


$setup->addAttribute('catalog_product', 'aw_sarp_enabled', array(
                                                                'backend' => '',
                                                                'frontend' => '',
                                                                'source' => 'eav/entity_attribute_source_boolean',
                                                                'group' => 'Subscription',
                                                                'label' => 'Enable',
                                                                'input' => 'select',
                                                                'class' => '',
                                                                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                'type' => 'int',
                                                                'visible' => 1,
                                                                'user_defined' => false,
                                                                'default' => '0',
                                                                'apply_to' => 'subscription_simple',
                                                                'visible_on_front' => false
                                                           ));


$setup->addAttribute('catalog_product', 'aw_sarp_period', array(
                                                               'backend' => 'eav/entity_attribute_backend_array',
                                                               'frontend' => '',
                                                               'source' => 'sarp/source_subscription_periods',
                                                               'group' => 'Subscription',
                                                               'label' => 'Period type(s)',
                                                               'input' => 'multiselect',
                                                               'required' => 0,
                                                               'class' => '',
                                                               'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                               'visible' => true,
                                                               'user_defined' => false,
                                                               'default' => '-1',
                                                               'apply_to' => 'subscription_simple',
                                                               'visible_on_front' => false
                                                          ));

$setup->addAttribute('catalog_product', 'aw_sarp_has_shipping', array(
                                                                     'backend' => '',
                                                                     'frontend' => '',
                                                                     'source' => 'eav/entity_attribute_source_boolean',
                                                                     'group' => 'Subscription',
                                                                     'label' => 'Enable shipping',
                                                                     'input' => 'select',
                                                                     'required' => 0,
                                                                     'class' => '',
                                                                     'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                     'type' => 'int',
                                                                     'visible' => 1,
                                                                     'user_defined' => false,
                                                                     'default' => '1',
                                                                     'apply_to' => 'subscription_simple',
                                                                     'visible_on_front' => false
                                                                ));

$setup->addAttribute('catalog_product', 'aw_sarp_subscription_price', array(
                                                                           'backend' => '',
                                                                           'source' => '',
                                                                           'group' => 'Subscription',
                                                                           'label' => 'Subscription Price',
                                                                           'input' => 'text',
                                                                           'class' => 'validate-float',
                                                                           'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                           'visible' => 1,
                                                                           'default_value' => 1,
                                                                           'required' => 0,
                                                                           'user_defined' => false,
                                                                           'apply_to' => 'subscription_simple',
                                                                           'visible_on_front' => false
                                                                      ));

$setup->addAttribute('catalog_product', 'aw_sarp_first_period_price', array(
                                                                           'backend' => '',
                                                                           'source' => '',
                                                                           'group' => 'Subscription',
                                                                           'label' => 'First period price',
                                                                           'input' => 'text',
                                                                           'class' => 'validate-float',
                                                                           'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                           'visible' => 1,
                                                                           'default_value' => 1,
                                                                           'required' => 0,
                                                                           'user_defined' => false,
                                                                           'apply_to' => 'subscription_simple',
                                                                           'visible_on_front' => false
                                                                      ));


$setup->addAttribute('catalog_product', 'aw_sarp_include_to_group', array(
                                                                         'backend' => '',
                                                                         'source' => 'sarp/source_customer_groups',
                                                                         'group' => 'Subscription',
                                                                         'label' => 'On subscription, move to group',
                                                                         'input' => 'select',
                                                                         'class' => 'validate-float',
                                                                         'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                         'visible' => 1,
                                                                         'default_value' => 0,
                                                                         'required' => 0,
                                                                         'user_defined' => false,
                                                                         'apply_to' => 'subscription_simple',
                                                                         'visible_on_front' => false
                                                                    ));


$setup->addAttribute('catalog_product', 'aw_sarp_exclude_to_group', array(
                                                                         'backend' => '',
                                                                         'source' => 'sarp/source_customer_groups',
                                                                         'group' => 'Subscription',
                                                                         'label' => 'On unsubscription/expiry, move to group',
                                                                         'input' => 'select',
                                                                         'class' => 'validate-float',
                                                                         'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                         'visible' => 1,
                                                                         'default_value' => 0,
                                                                         'required' => 0,
                                                                         'user_defined' => false,
                                                                         'apply_to' => 'subscription_simple',
                                                                         'visible_on_front' => false
                                                                    ));

$setup->addAttribute($typeId, 'postman_notice', array(
                                                     'backend' => '',
                                                     'frontend' => '',
                                                     'label' => 'Postman Notice',
                                                     'input' => 'text',
                                                     'type' => 'text',
                                                     'visible' => 1,
                                                     'user_defined' => false,
                                                     'default' => '',
                                                     'visible_on_front' => false
                                                ));

$this->_setupCache = array();

$fieldList = array('price', 'special_price', 'special_from_date', 'special_to_date',
                   'minimal_price', 'cost', 'tier_price', 'weight', 'tax_class_id');
foreach ($fieldList as $field) {
    $applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
    if (!in_array('subscription_simple', $applyTo)) {
        $applyTo[] = 'subscription_simple';
        $installer->updateAttribute('catalog_product', $field, 'apply_to', implode(',', $applyTo));
    }
}


$installer->endSetup();
