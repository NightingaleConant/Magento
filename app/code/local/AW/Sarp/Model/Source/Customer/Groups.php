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
class AW_Sarp_Model_Source_Customer_Groups extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    protected static $options;
    protected static $options_by_id;

    /**
     * Returns groups as array
     * @return array
     */
    public function getAllOptions()
    {
        if (!self::$options) {
            self::$options_by_id = array();
            $_options = array();
            $groups = $this->getCollection();
            $groups->addItem(Mage::getModel('customer/group')->setId(-1)->setCustomerGroupCode('--- No change ---'));
            foreach ($groups as $Group) {
                $_options[] = array('value' => $Group->getId(), 'label' => $Group->getCustomerGroupCode());
            }
            self::$options = $_options;
        }
        return self::$options;
    }

    /**
     * Returns groups colection
     * @return Mage_Customer_Model_Entity_Group_Collection
     */
    public function getCollection()
    {
        return Mage::getModel('customer/group')->getCollection();
    }
}