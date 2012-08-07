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
class AW_Sarp_Block_Adminhtml_Subscriptions_Edit_Tab_Payments extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('aw_sarp/subscription/payments/history.phtml');
    }


    /**
     * Returns payments collection
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    public function getPaidCollection()
    {
        if (!$this->getData('paid_collection')) {
            $this->setPaidCollection(Mage::getModel('sarp/sequence')
                                             ->getCollection()
                                             ->addSubscriptionFilter($this->getSubscription())
                                             ->addStatusFilter(AW_Sarp_Model_Sequence::STATUS_PAYED)
                                             ->setOrder('date', 'desc')
            );

        }
        return $this->getData('paid_collection');
    }


    /**
     * Returns pending payments collection
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    public function getPendingCollection()
    {
        if (!$this->getData('pending_collection')) {
            $this->setPendingCollection(Mage::getModel('sarp/sequence')
                                                ->getCollection()
                                                ->addSubscriptionFilter($this->getSubscription())
                                                ->addStatusFilter(AW_Sarp_Model_Sequence::STATUS_PENDING)
                                                ->setOrder('date', 'asc')
            );

        }
        return $this->getData('pending_collection');
    }

    public function getConvertedPrice($value, $code)
    {

        $currency = Mage::getModel('directory/currency')->load($code);
        return $currency->format($value, array(), false);
        ;

    }
    //{return Mage::app()->getStore()->formatPrice($value);}

}