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
class AW_Sarp_Block_Customer_Subscription_List extends Mage_Core_Block_Template
{


    /**
     * Returns current customer
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Returns collection of subscriptions that are assigned to current customer
     * @return AW_Sarp_Model_Mysql4_Subscrtiption_Collection
     */
    public function getCollection()
    {
        if (!$this->getData('collection')) {
            $this->setCollection(
                Mage::getModel('sarp/subscription')->getCollection()->addCustomerFilter($this->getCustomer())->setOrder('date_start', 'DESC')
            );
        }
        return $this->getData('collection');
    }

    /**
     * Returns toolbar with pages and so on
     * @return Mage_Page_Block_Html_Pager
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Prepares block layout
     * @return
     */
    protected function _prepareLayout()
    {
        $toolbar = $this->getLayout()->createBlock('page/html_pager', 'customer_sarp.toolbar')
                ->setCollection($this->getCollection());
        $this->setChild('toolbar', $toolbar);
        return parent::_prepareLayout();
    }

    public function getSubscriptionStatusLabel(AW_Sarp_Model_Subscription $subscription)
    {
        return Mage::getModel('sarp/source_subscription_status')->getLabel($subscription->getStatus());
    }

    public function getPendingPaymentBlock()
    {
        return $this->getLayout()->createBlock('sarp/customer_subscription_payments_pending', 'customer_sarp.payments_pending' . rand(0, 999999999));
    }

    /**
     * Get back url in account dashboard
     *
     * This method is copypasted in:
     * Mage_Wishlist_Block_Customer_Wishlist  - because of strange inheritance
     * Mage_Customer_Block_Address_Book - because of secure url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }

}