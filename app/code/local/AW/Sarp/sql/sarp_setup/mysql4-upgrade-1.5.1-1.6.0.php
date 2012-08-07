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
try {
    $installer->addAttribute('catalog_product', 'aw_sarp_options_to_first_price', array(
                                                                              'backend' => '',
                                                                              'frontend' => '',
                                                                              'source' => 'eav/entity_attribute_source_boolean',
                                                                              'group' => 'Subscription',
                                                                              'label' => 'Change first period price according to selected product options',
                                                                              'input' => 'select',
                                                                              'class' => '',
                                                                              'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                                                                              'type' => 'int',
                                                                              'visible' => 1,
                                                                              'user_defined' => false,
                                                                              'default' => AW_Sarp_Model_Source_Yesnoglobal::GLOB,
                                                                              'apply_to' => 'subscription_simple,subscription_downloadable,subscription_configurable',
                                                                              'visible_on_front' => false
                                                                         ));
} catch (Exception $e)
{
}

$installer->endSetup();