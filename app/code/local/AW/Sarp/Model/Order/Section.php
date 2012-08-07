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
class AW_Sarp_Model_Order_Section extends Varien_Object
{


    /**
     * Saves section. That means that new order must be created and assigned to subscription
     * @return
     */
    public function save()
    {
        if (!$this->getSubscription()) {
            throw new AW_Sarp_Exception("No subscription assigned for section editing");
        }
        if (!($save_data = $this->getDataToSave())) {
            throw new AW_Sarp_Exception("Can't save empty subscription section data");
        }


        $this->getSectionInstance()->preset($this->getSubscription()->getOrder(), $save_data)->save();
    }

    /**
     * Returns current section controller
     * @return AW_Sarp_Model_Order_Section
     */
    public function getSectionInstance()
    {
        if (!$this->getData('section_instance')) {
            $this->setSectionInstance(Mage::getModel('sarp/order_section_' . $this->getType()));
            if (!$this->getData('section_instance')) {
                throw new AW_Sarp_Exception("No instance for section type '{$this->getType()}' found");
            }
        }
        return $this->getData('section_instance');
    }

    /**
     * Returns current set customer
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (!$this->getData('customer')) {
            $this->setCustomer(Mage::getSingleton('customer/session')->getCustomer());
        }
        return $this->getData('customer');
    }
}