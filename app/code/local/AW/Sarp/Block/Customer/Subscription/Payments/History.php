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
class AW_Sarp_Block_Customer_Subscription_Payments_History extends Mage_Core_Block_Template
{

    /**
     * Returns payments collection
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    protected $_activeLink = false;

    public function getCollection()
    {
        if (!$this->getData('collection')) {

            $this->setCollection(Mage::getModel('sarp/sequence')
                                         ->getCollection()
                                         ->addSubscriptionFilter($this->getSubscription())
                                         ->addStatusFilter(AW_Sarp_Model_Sequence::STATUS_PAYED)
                                         ->setOrder('date', 'desc')
            );

        }
        return $this->getData('collection');
    }

    /**
     * Returns next payment date
     * @return Zend_Date
     */
    public function getLastPaidDate()
    {
        foreach ($this->getCollection() as $Item) {
            return new Zend_Date($Item->getDate(), AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
        }
    }

    public function setActive($path)
    {
        $this->_activeLink = $this->_completePath($path);
        var_dump($this->_activeLink);
        return $this;
    }

    protected function _completePath($path)
    {
        $path = rtrim($path, '/');
        switch (sizeof(explode('/', $path))) {
            case 1:
                $path .= '/index';
            // no break

            case 2:
                $path .= '/index';
        }
        return $path;
    }

}