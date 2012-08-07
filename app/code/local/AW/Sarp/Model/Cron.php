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
class AW_Sarp_Model_Cron extends Varien_Object
{

    protected static $isRun;
    protected static $subscriptionIterated;

    public static $isCronSession = null;

    /**
     * Run cron jobs
     * @return
     */
    public function process()
    {
        if (!self::$isRun) {
            self::$isCronSession = 1;
            if (!self::cleanState()) self::processTodaySubscriptions();
            self::processAlerts();
            self::markExpiredSubscriptions();
            self::$isRun = 1;
            self::$isCronSession = 0;
        }
    }


    /**
     * Processes all today subscriptions
     * @return
     */
    public function processTodaySubscriptions()
    {
        // Get all active subscriptions

        foreach ($this->getTodayPendingSubscriptions() as $subscription) {
            //	try{
            $subscription->payForDate(new Zend_Date);
            return;
            //	}catch(Core_Exception $e){
            //		throw  $e;
            //	}
        }
    }

    /**
     * Returns all active subscriptions for today
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    public function getTodayPendingSubscriptions()
    {
        $collection = Mage::getModel('sarp/subscription')
                ->getCollection()
                ->addActiveFilter()
                ->addTodayFilter();
        return $collection;
    }

    /**
     * Sends alerts matching rules
     * @return
     */
    public function processAlerts()
    {
        $events = Mage::getModel('sarp/alert_event')->getCollection()->addNowFilter()->addPendingFilter();

        foreach ($events as $event) {
            $event->send();
        }
    }

    /**
     * Gets expired subscriptions and marks them as expired
     * @return
     */
    public function markExpiredSubscriptions()
    {
        $collection = Mage::getModel('sarp/subscription')
                ->getCollection()
                ->addActiveFilter();
        foreach ($collection as $Subscription) {
            if ($Subscription->getDateExpire()->compare(new Zend_Date, Zend_Date::DATE_SHORT) < 0) {
                try {
                    throw new AW_Sarp_Exception("Subscription {$Subscription->getId()} marked as expired(" . $Subscription->getDateExpire() . ")");
                } catch (exception $e) {
                }
                $Subscription->setStatus(AW_Sarp_Model_Subscription::STATUS_EXPIRED)->save();
            }
        }
    }

    /**
     * searches for overdued
     * Returns true if any changes has been made
     * @return bool
     */
    public function cleanState()
    {
        $today = new Zend_Date;
        $collection = Mage::getModel('sarp/subscription')
                ->getCollection()
                ->addLessTodayFilter()
                ->addActiveFilter();

        foreach ($collection as $subscription)
        {
            $coll = Mage::getModel('sarp/sequence')
                    ->getCollection()
                    ->addSubscriptionFilter($subscription)
                    ->addStatusFilter(AW_Sarp_Model_Sequence::STATUS_PENDING);


            foreach ($coll as $sequence)
            {
                $past = new Zend_Date($sequence->getDate(), AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
                if ($past->compare($today, Zend_Date::DATE_SHORT) < 0) {
                    Mage::helper('awcore/logger')->log($this, "overdue payment on " . $past->toString() . " for subscription ." . $subscription->getId() . " detected");

                    return $subscription->payBySequence($sequence); /* only one fix on each cron execution */
                } else
                {
                    Mage::helper('awcore/logger')->log($this, "{$past->toString()}");
                }
            }
        }
        return false;
    }
}
