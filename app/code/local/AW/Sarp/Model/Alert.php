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
 */class AW_Sarp_Model_Alert extends Mage_Core_Model_Abstract
{
    const TYPE_DATE_START          = 'date_start';
    const TYPE_DELIVERY            = 'delivery';
    const TYPE_DATE_EXPIRE         = 'date_expire';
    const TYPE_NEW_SUBSCRIPTION    = 'new_subscription';
    const TYPE_CANCEL_SUBSCRIPTION = 'cancel_subscription';
    const TYPE_ACTIVATION          = 'activation';
    const TYPE_SUSPENDED           = 'suspended';

    const MULTIPLIER_DAY = 24;

    const RECIPIENT_CUSTOMER = 'customer';
    const RECIPIENT_ADMIN    = 'admin';

    const SUBMIT_WITHOUT_DELETE = 1;

    /** Status enabled */
    const STATUS_ENABLED = 1;
    /** Status disabled */
    const STATUS_DISABLED = 0;

    protected function _construct()
    {
        $this->_init('sarp/alert');
    }

    /**
     * Returns events
     * @return AW_Sarp_Model_Mysql4_Alert_Event_Collection
     */
    public function getEvents()
    {
        if (!$this->getData('events')) {
            $this->setData('events', Mage::getModel('sarp/alert_event')->getCollection()->addAlertFilter($this));
        }
        return $this->getData('events');
    }

    /**
     * Return identity code for sender
     * @return string
     */
    public function getSender()
    {
        return Mage::getStoreConfig(AW_Sarp_Helper_Config::XML_PATH_GENERAL_ALERTS_SENDER);
    }

    protected function _getDate($timeAmount, Zend_Date $date)
    {

        $nowDate = new Zend_Date();
        $dateWithTimeAmount = $date->addHour($timeAmount);

        if ($timeAmount == 0) {

            $date = $date->addMinute(1);
        } elseif ($timeAmount < 0 && $nowDate->isLater($dateWithTimeAmount)) {

            $date = $nowDate->addMinute(1);
        } else {

            $date = $dateWithTimeAmount;
        }

        return $date;
    }

    /**
     * Generates events for one subscription
     * @param AW_Sarp_Model_Subscription $Subscription
     * @param object                     $woDelete [optional]
     * @return AW_Sarp_Model_Alert
     */
    public function generateSubscriptionEvents(AW_Sarp_Model_Subscription $Subscription, $woDelete = null)
    {
        // Purge events before
        if (!$Subscription->getId()) {
            return $this;
        }

        if (!$woDelete) {
            if ($Subscription->getIsNew() && ($this->getType() == self::TYPE_NEW_SUBSCRIPTION)) {
                $this->getResource()->getWriteAdapter()->delete($this->getResource()->getTable('sarp/alert_event'), 'subscription_id=' . $Subscription->getId() . " AND alert_id=" . $this->getId());
            } elseif ($this->getType() != self::TYPE_NEW_SUBSCRIPTION) {
                $this->getResource()->getWriteAdapter()->delete($this->getResource()->getTable('sarp/alert_event'), 'subscription_id=' . $Subscription->getId() . " AND alert_id=" . $this->getId());
            }
        }

        // Don't generate events for disabled alerts
        if ($this->getStatus() == self::STATUS_DISABLED) {
            return $this;
        }

        // Generate dates
        $suffix = $this->getTimeIsAfter() ? 1 : -1;
        $timeAmount = $suffix * $this->getTimeMultiplier() * $this->getTimeAmount(); // hours

        switch ($this->getType()) {
            case self::TYPE_DATE_START:
                if ($Subscription->isActive()) {
                    $date = $Subscription->getDateStart();
                    $date = $this->_getDate($timeAmount, $date);
                }
                break;

            case self::TYPE_DATE_EXPIRE:
                if (($Subscription->getIsExpiring() || $Subscription->isActive()) && !$Subscription->isInfinite()) {
                    if ($Subscription->isActive()) {
                        $date = $Subscription->getDateExpire();
                        $date = $this->_getDate($timeAmount, $date);
                    } else {
                        $date = new Zend_Date;
                    }
                }
                break;
            case self::TYPE_DELIVERY:
                if ($Subscription->isActive()) {
                    $date = $Subscription->getFlatNextDeliveryDate();
                    $date = new Zend_Date($date, AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
                    $date = $this->_getDate($timeAmount, $date);
                }
                break;
            case self::TYPE_NEW_SUBSCRIPTION:
                if ($Subscription->getIsNew()) {
                    $date = new Zend_Date;
                    $date = $this->_getDate($timeAmount, $date);
                } else {

                }
                break;
            case self::TYPE_SUSPENDED:
                if ($Subscription->getIsSuspending()) {
                    $date = new Zend_Date;
                    $date = $this->_getDate($timeAmount, $date);
                } else {

                }
                break;
            case self::TYPE_ACTIVATION:
                if ($Subscription->getIsReactivated()) {
                    $date = new Zend_Date;
                    $date = $this->_getDate($timeAmount, $date);
                } else {

                }
                break;

            case self::TYPE_CANCEL_SUBSCRIPTION:
                if ($Subscription->getIsCancelling()) {
                    $date = new Zend_Date;
                    $date = $this->_getDate($timeAmount, $date);
                } else {

                }
                break;
            default:
                throw new AW_Sarp_Exception("Alert for events type '{$this->getType()}' are not supported");
        }
        if (isset($date) && ($date instanceof Zend_Date) && ($date->compare(new Zend_Date) > 0)) {

            $Event = Mage::getModel('sarp/alert_event')
                    ->setSubscriptionId($Subscription->getId())
                    ->setRecipient($this->getRecipientAddress($Subscription))
                    ->setAlertId($this->getId())
                    ->setDate($date->toString(AW_Sarp_Model_Subscription::DB_DATETIME_FORMAT))
                    ->save();
        }
        return $this;
    }

    /**
     * Generates events for all subscriptions
     * @return AW_Sarp_Model_Alert
     */
    protected function _generateEvents()
    {
        // Purge events before
        if ($this->getId() &&
            ($this->getType() != self::TYPE_NEW_SUBSCRIPTION) &&
            ($this->getType() != self::TYPE_CANCEL_SUBSCRIPTION)
        ) {
            $this->getResource()->getWriteAdapter()->delete($this->getResource()->getTable('sarp/alert_event'), 'alert_id=' . $this->getId());
        }
        $Subscriptions = Mage::getModel('sarp/subscription')->getCollection()->addActiveFilter();
        foreach ($Subscriptions as $Subscription) {
            $this->generateSubscriptionEvents($Subscription, self::SUBMIT_WITHOUT_DELETE);
        }
        return $this;
    }

    /**
     * Returns recipient email
     * @return string
     */
    public function getRecipientAddress(AW_Sarp_Model_Subscription $Subscription)
    {
        if ($this->getRecipient() == self::RECIPIENT_CUSTOMER) {
            return $Subscription->getCustomer()->getEmail();
        } else {
            return Mage::getStoreConfig('trans_email/ident_' .
                                        $this->getRecipient()
                                        . '/email');
        }
        return $this;
    }

    /**
     * Returns recipient name
     * @return string
     */
    public function getRecipientName(AW_Sarp_Model_Subscription $Subscription)
    {
        if ($this->getRecipient() == self::RECIPIENT_CUSTOMER) {
            return $Subscription->getCustomer()->getName();
        } else {
            return Mage::getStoreConfig('trans_email/ident_' . $this->getRecipient() . '/name');
        }
        return $this;
    }

    public function _afterSave()
    {
        $this->_generateEvents();
        return parent::_afterSave();
    }

}

