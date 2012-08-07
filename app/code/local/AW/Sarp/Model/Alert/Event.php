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
class AW_Sarp_Model_Alert_Event extends AW_Core_Model_Abstract
{

    /** Status "Pending" for alert event */
    const STATUS_PENDING = 'pending';
    /** Status "Processing" for alert event */
    const STATUS_PROCESSING = 'processing';
    /** Status "Sent" for alert event */
    const STATUS_SENT = 'sent';
    /** Status "Failed" for alert event */
    const STATUS_FAILED = 'failed';

    protected function _construct()
    {
        $this->_init('sarp/alert_event');
    }

    /**
     * Retrieves associated Alert
     * @throws AW_Sarp_Exception
     * @return AW_Sarp_Model_Alert
     */
    public function getAlert()
    {
        if (!$this->getData('alert')) {
            if (!$this->getAlertId()) {
                throw new AW_Sarp_Exception("Can't load Alert for event #{$this->getId()}");
            }
            $this->setData('alert', Mage::getModel('sarp/alert')->load($this->getAlertId()));
        }
        return $this->getData('alert');
    }

    /**
     * Retrieves associated Subscription
     * @throws AW_Sarp_Exception
     * @return AW_Sarp_Model_Subscription
     */
    public function getSubscription()
    {
        if (!$this->getData('subscription')) {
            if (!$this->getSubscriptionId()) {
                throw new AW_Sarp_Exception("Can't load Subscription for event #{$this->getId()}");
            }
            $this->setData('subscription', Mage::getModel('sarp/subscription')->load($this->getSubscriptionId()));
        }
        return $this->getData('subscription');
    }


    /**
     * Sends notifications on alert
     * @return void
     */
    public function send()
    {
        try {
            $Alert = $this->getAlert();

            if ($this->getSubscription()->getId()) {
                $this->setStatus(self::STATUS_PROCESSING)->save();
                // Send transactional mail

                $mailTemplate = Mage::getModel('core/email_template');

                $this
                        ->getSubscription()
                        ->setData('next_delivery_date', Mage::helper('core')->formatDate($this->getSubscription()->getFlatNextDeliveryDate()))
                        ->setData('next_payment_date', Mage::helper('core')->formatDate($this->getSubscription()->getFlatNextPaymentDate()))
                        ->setData('first_delivery_date', Mage::helper('core')->formatDate($this->getSubscription()->getDateStart()))
                        ->setData('expire_date', Mage::helper('core')->formatDate($this->getSubscription()->getDateExpire()));


                $mailTemplate
                        ->setDesignConfig(array('area' => ($Alert->getRecipient() != 'customer') ? 'adminhtml'
                                                  : 'frontend'))
                        ->sendTransactional(
                    $Alert->getEmailTemplate(),
                    $Alert->getSender(),
                    $Alert->getRecipientAddress($this->getSubscription()),
                    $Alert->getRecipientName($this->getSubscription()), // Name
                    array('alert' => $Alert, 'event' => $this, 'subscription' => $this->getSubscription())
                );

                $this->setStatus(self::STATUS_SENT)->save();
            } else {
                $this->log("Can't send alert event for alert{$this->getAlert()->getId()}", null, "Subscription doesn't exist");
                $this->setStatus(self::STATUS_FAILED)->save();
            }
        } catch (Exception $e) {
            $this->setStatus(self::STATUS_FAILED)->save();
            $this->log("Can't send alert event for alert{$this->getAlert()->getId()}", null, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Return notification rendered HTML
     * @deprecated
     * @return string
     */
    public function getNotificationHtml()
    {

        $blockType = $this->getAlert()->getRecipient() == AW_Sarp_Model_Alert::RECIPIENT_CUSTOMER
                ? 'sarp/customer_alert_notification' : 'sarp/adminhtml_alert_notification';

        return Mage::app()->getLayout()->createBlock($blockType)
                ->setAlert($this->getAlert())
                ->setSubscription($this->getSubscription())
                ->setCustomer($this->getSubscription()->getCustomer())
                ->setEvent($this)->toHtml();
    }

}