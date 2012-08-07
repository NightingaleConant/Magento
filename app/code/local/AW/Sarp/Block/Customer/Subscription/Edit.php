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
class AW_Sarp_Block_Customer_Subscription_Edit extends Mage_Core_Block_Template
{

    /**
     * Returns current order
     * @return Mage_Sales_Model_Order
     */
    public function getQuote()
    {
        if (!$this->getData('quote')) {
            $this->setQuote($this->getSubscription()->getQuote());
        }
        return $this->getData('quote');
    }

    /**
     * Returns according section block
     * @param string $code
     * @return  AW_Sarp_Block_Customer_Subscription_Edit
     */
    public function setSection($code)
    {


        if (in_array($code, array('billing', 'shipping'))) {

            $this->setChild(
                'edit_section',
                $this->getLayout()->createBlock('sarp/customer_subscription_edit_' . $code)
                        ->setQuote($this->getQuote())
                        ->setSubscription($this->getSubscription())
            );


            return $this;
        }
        return $this;
    }

    public function getTitle()
    {
        return $this->getChild('edit_section')->getTitle();
    }

}