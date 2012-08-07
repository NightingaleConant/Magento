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
class AW_Sarp_Model_Observer extends Varien_Object
{

    /** Separator for detecting unique combiantion of period - dates */
    const HASH_SEPARATOR = ":::";

    /**
     * Saves payment information in session
     * This workaround is used for correct functionality at Magento 1.4+
     * @param object $observer
     */
    public function savePaymentInfoInSession($observer)
    {
        try
        {
            if (!AW_Sarp_Model_Subscription::isIterating()) {
                $quote = $observer->getEvent()->getQuote();
                if (!$quote->getPaymentsCollection()->count())
                    return;
                $Payment = $quote->getPayment();
                if ($Payment && $Payment->getMethod()) {
                    if ($Payment->getMethodInstance() instanceof Mage_Payment_Model_Method_Cc) {
                        // Credit Card number
                        if ($Payment->getMethodInstance()->getInfoInstance() && ($ccNumber = $Payment->getMethodInstance()->getInfoInstance()->getCcNumber())) {
                            $ccCid = $Payment->getMethodInstance()->getInfoInstance()->getCcCid();
                            $ccType = $Payment->getMethodInstance()->getInfoInstance()->getCcType();
                            $ccExpMonth = $Payment->getMethodInstance()->getInfoInstance()->getCcExpMonth();
                            $ccExpYear = $Payment->getMethodInstance()->getInfoInstance()->getCcExpYear();
                            Mage::getSingleton('customer/session')->setSarpCcNumber($ccNumber);
                            Mage::getSingleton('customer/session')->setSarpCcCid($ccCid);
                        }
                    }
                }
            }
        } catch (Exception $e)
        {
            //throw($e);
        }
    }

    public function salesOrderItemSaveAfter($observer)
    {
        $orderItem = $observer->getEvent()->getItem();

        $product = Mage::getModel('catalog/product')
                ->setStoreId($orderItem->getOrder()->getStoreId())
                ->load($orderItem->getProductId());

        if (method_exists($product->getTypeInstance(), 'prepareOrderItemSave'))
            $product->getTypeInstance()->prepareOrderItemSave($product, $orderItem);
    }

    /**
     * Assigns subscription of product to current user
     * @param object $observer
     * @return
     */
    public function assignSubscriptionToCustomer($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        /*add delivery notice*/
        $notice = Mage::getSingleton('customer/session')->getPostmanNotice();
        if ($notice != '') {
            $postman = Mage::getModel('sarp/notice')->load(null);
            $postman->setOrderId($order->getId())
                    ->setNotice($notice)
                    ->save();
        }

        $items = $order->getItemsCollection();
        $paymentMethod = $observer->getEvent()->getOrder()->getPayment()->getMethod();

        $period_date_hashs = array();

        if (!Mage::getSingleton('sarp/subscription')->getId()) {
            /**
             * Joins set of products to one subscription if this can be done
             */
            foreach ($items as $item)
            {
                if (Mage::helper('sarp')->isSubscriptionType($item)) {
                    $Options = $this->_getOrderItemOptionValues($item);
                    $period_type = $Options->getAwSarpSubscriptionType();
                    if (preg_match('/[\d]{4}-[\d]{2}-[\d]{2}/', $Options->getAwSarpSubscriptionStart()))
                        $date = new Zend_Date($Options->getAwSarpSubscriptionStart());
                    else
                        $date = new Zend_Date($Options->getAwSarpSubscriptionStart(), Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                    $date_start = $date->toString(AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
                    if ($period_type > 0) {
                        if (!isset($period_date_hashs[$period_type . self::HASH_SEPARATOR . $date_start])) {
                            $period_date_hashs[$period_type . self::HASH_SEPARATOR . $date_start] = array();
                        }
                        $period_date_hashs[$period_type . self::HASH_SEPARATOR . $date_start][] = $item;
                    }
                }
            }

            foreach ($period_date_hashs as $hash => $OrderItems)
            {
                $Options = $this->_getOrderItemOptionValues($item);

                list($period_type, $date_start) = explode(self::HASH_SEPARATOR, $hash);

                $Subscription = Mage::getModel('sarp/subscription')
                        ->setCustomer(Mage::getModel('customer/customer')->load($order->getCustomerId()))
                        ->setPrimaryQuoteId($quote->getId())
                        ->setDateStart($date_start)
                        ->setStatus(Mage::helper('sarp')->isOrderStatusValidForActivation($order->getState())
                                            ? AW_Sarp_Model_Subscription::STATUS_ENABLED
                                            : AW_Sarp_Model_Subscription::STATUS_SUSPENDED)
                        ->setPeriodType($period_type)
                        ->initFromOrderItems($OrderItems, $order)
                        ->save();
                // Run payment method trigger
                $Subscription
                        ->getMethodInstance($paymentMethod)
                        ->onSubscriptionCreate($Subscription, $order, $quote);

            }
        }
        Mage::getSingleton('customer/session')->setSarpCcNumber(null);
        Mage::getSingleton('customer/session')->setSarpCcCid(null);


        /*

        /** This is for not grouping subscriptions logic
        foreach($items as $item){
            if($item->getProductType() == 'subscription_simple'){
                $Options = $this->_getOrderItemOptionValues($item);
                $Product = Mage::getModel('catalog/product')->load($item->getProductId());

                $sku = $this->getProductSku($Product, $item->getProductOptionByCode('options'));
                $save_options = serialize($item->getProductOptionByCode('options'));

                $date_start = Mage::app()->getLocale()->date($Options->getAwSarpDateStart())->toString('Y-MM-dd');
                $qty = $item->getQtyToInvoice();

                $period_type = $Options->getAwSarpSubscriptionType();
                $status = intval($period_type > 0);
                if($status){

                    Mage::getModel('sarp/subscription')
                        ->setCustomer($this->getCustomer())
                        ->setProductSku($sku)
                        ->setProductId($Product->getId())
                        ->setDateStart($date_start)
                        ->setProductQty($qty)
                        ->setProductOptions($save_options)
                        ->setStatus($status)
                        ->setPeriodType($period_type)
                        ->setPaymentMethod($paymentMethod)
                        ->setPrimaryOrderId($order->getId())
                        ->setPrimaryOrderItemId($item->getId())
                        ->save();
                }
            }
        }*/
    }

    /**
     * Returns product SKU for specified options set
     * @param Mage_Catalog_Model_Product $Product
     * @param mixed                      $options
     * @return string
     */
    public function getProductSku(Mage_Catalog_Model_Product $Product, $options = null)
    {
        if ($options) {
            $productOptions = $Product->getOptions();
            foreach ($options as $option)
            {
                if ($ProductOption = @$productOptions[$option['option_id']]) {
                    $values = $ProductOption->getValues();
                    if (($value = $values[$option['option_value']]) && $value->getSku()) {
                        return $value->getSku();
                    }
                }
            }
        }
        return $Product->getSku();
    }

    /**
     * Returns current customer
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (!$this->getData('customer')) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $this->setCustomer($customer);
        }
        return $this->getData('customer');
    }

    /**
     * Return sales item as object
     * @param Mage_Sales_Model_Order_Item $item
     * @return Varien_Object
     */
    protected function _getOrderItemOptionValues(Mage_Sales_Model_Order_Item $item)
    {
        $buyRequest = $item->getProductOptionByCode('info_buyRequest');
        $obj = new Varien_Object;
        $obj->setData($buyRequest);
        return $obj;
    }

    /**
     * Activates or suspends subscription on order status change
     * @param object $observer
     * @return
     */
    public function updateSubscriptionStatus($observer)
    {
        $Order = $observer->getOrder();
        $status = $Order->getStatus();

        $items = Mage::getModel('sarp/subscription_item')->getCollection()->addOrderFilter($Order);
        $items->getSelect()->group('subscription_id');

        /**
         * this is for primary order
         */
        if ($items->count()) {

            foreach ($items as $item)
            {
                $Subscription = Mage::getModel('sarp/subscription')->load($item->getSubscriptionId());
                // If order is complete now and subscription is suspeneded - activate subscription
                if (Mage::helper('sarp')->isOrderStatusValidForActivation($status) && ($Subscription->getStatus() == AW_Sarp_Model_Subscription::STATUS_SUSPENDED)) {
                    if (($status == Mage_Sales_Model_Order::STATE_PROCESSING) && !Mage::helper('sarp')->isSubscriptionItemInvoiced($item)) {
                     Mage::getModel('sarp/subscription_flat')
                            ->load($Subscription->getId(), 'subscription_id')
                            ->setFlatLastOrderStatus($Subscription->getLastOrder()->getStatus())
                            ->save();
                     continue;
                    }
                    $Subscription->setStatus(AW_Sarp_Model_Subscription::STATUS_ENABLED)->save();

                } elseif ($Subscription->isActive() && !Mage::helper('sarp')->isOrderStatusValidForActivation($status))
                {
                    $Subscription->setStatus(AW_Sarp_Model_Subscription::STATUS_SUSPENDED)->save();
                }
                else
                    Mage::getModel('sarp/subscription_flat')
                            ->load($Subscription->getId(), 'subscription_id')
                            ->setFlatLastOrderStatus($Subscription->getLastOrder()->getStatus())
                            ->save();
            }
        }

        /**
         * If payment failed(e.g. order status is not completed), suspend affected subscription
         */

        if ($Sequence = Mage::getModel('sarp/sequence')->load($Order->getId(), 'order_id')) {

            if ($Sequence->getId()) {
                $Subscription = $Sequence->getSubscription();

                // If order is complete now and subscription is suspeneded - activate subscription
                if (Mage::helper('sarp')->isOrderStatusValidForActivation($status)) {
                    $Sequence->setStatus(AW_Sarp_Model_Sequence::STATUS_PAYED)->save();
                    $Subscription
                            ->setStatus(AW_Sarp_Model_Subscription::STATUS_ENABLED)
                            ->setFlagNoSequenceUpdate(1)
                            ->save()
                            ->setFlagNoSequenceUpdate(0);
                } elseif ($Subscription->isActive() && !Mage::helper('sarp')->isOrderStatusValidForActivation($status))
                {
                    $Subscription
                            ->setStatus(AW_Sarp_Model_Subscription::STATUS_SUSPENDED)
                            ->setFlagNoSequenceUpdate(1)
                            ->save()
                            ->setFlagNoSequenceUpdate(0);
                }
            }
        }
    }


    /**
     * Checks if guest checkout is available
     * @param object $e
     * @return
     */
    public function checkGuestChecoutAvail($e)
    {
        $result = $e->getResult();
        $quote = $e->getQuote();

        $currentCustomerId = Mage::getSingleton("customer/session")->getCustomer()->getId();

        $avail = true;
        if (!$currentCustomerId) {
            foreach ($quote->getItemsCollection() as $item)
            {
                $product = $item->getProduct();
                if ($product->getCustomOption('aw_sarp_subscription_type')
                    && $product->getCustomOption('aw_sarp_subscription_type')->getValue() > 0
                    && !$item->getParentItemId()
                    && !Mage::helper('sarp')->checkoutAllowedForGuest($product)
                ) {
                    $avail = false;
                    break;
                }
            }
        }
        $result->setIsAllowed($avail);
    }


    /**
     * Puts postman notice in session
     * @param object $observer
     * @return
     */
    public function catchPostmanNotice($observer)
    {
        $val = strip_tags($observer->getRequest()->getPost('aw_sarp_postman_notice'));
        Mage::getSingleton('customer/session')->setPostmanNotice($val);
    }

    /**
     * Attaches postman notice to order
     * @param object $observer
     * @return
     */
    public function addPostmanNotice($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $notice = Mage::getSingleton('customer/session')->getPostmanNotice();
        $order->setData('postman_notice', $notice);
        $order->setData('postman_notice_is_formated', true);

    }

    /**
     * Checks if subscription is suspended. Remove download links if product attribute 'Download access
     * based on the subscription status' is true
     * @param object $observer
     * @return
     */
    public function sarpSubscriptionSuspend($observer)
    {
        $subscription = $observer->getEvent()->getSubscription();
        $purchasedLinks = Mage::getResourceModel('downloadable/link_purchased_item_collection');

        $orders = Mage::getModel('sarp/sequence')->getOrdersBySubscription($subscription);
        $orders[] = $subscription->getOrder();
        foreach ($orders as $order)
        {
            foreach ($order->getAllItems() as $item)
            {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if ($product->getTypeId() != AW_Sarp_Model_Product_Type_Downloadable_Subscription::PRODUCT_TYPE_DOWLOADABLE)
                    continue;
                if ($product->getAwSarpDownloadByStatus()) {
                    foreach ($purchasedLinks as $link)
                        if ($link->getOrderItemId() == $item->getId())
                            $link->setStatus(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING_PAYMENT)->save();
                }
            }
        }
    }

    /**
     * Checks if subscription is reactivated. Add download links
     * @param object $observer
     * @return
     */
    public function sarpSubscriptionReactivate($observer)
    {
        $subscription = $observer->getEvent()->getSubscription();
        $purchasedLinks = Mage::getResourceModel('downloadable/link_purchased_item_collection');

        $orders = Mage::getModel('sarp/sequence')->getOrdersBySubscription($subscription);
        $orders[] = $subscription->getOrder();
        foreach ($orders as $order)
        {
            foreach ($order->getAllItems() as $item)
            {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if ($product->getTypeId() != AW_Sarp_Model_Product_Type_Downloadable_Subscription::PRODUCT_TYPE_DOWLOADABLE)
                    continue;
                foreach ($purchasedLinks as $link)
                    if ($link->getOrderItemId() == $item->getId())
                        $link->setStatus(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE)->save();
            }
        }
    }

    /**
     * returns all sequences with given status for particular subscription
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    public function getSequenceItems($subscription, $status)
    {
        return Mage::getModel('sarp/sequence')
                ->getCollection()
                ->addSubscriptionFilter($subscription)
                ->addStatusFilter($status);
    }

    public function paymentIsAvailable($observer)
    {
        $method = $observer->getMethodInstance();
        $quote = $observer->getQuote();

        if (is_null($quote)) {
           return;
        }

        if (!$quote instanceof Mage_Sales_Model_Quote) {
            $observer->getResult()->isAvailable = false;
            return;
        }

        $haveSarpItems = false;
        foreach ($quote->getAllItems() as $item)
        {
            $sarpSubscriptionType = $item->getProduct()->getCustomOption('aw_sarp_subscription_type');
            if (Mage::helper('sarp')->isSubscriptionType($item) && !is_null($sarpSubscriptionType)) {
                $haveSarpItems = true;
                break;
            }
        }

        if ($haveSarpItems && !Mage::getModel('sarp/subscription')->hasMethodInstance($method->getCode()))
            $observer->getResult()->isAvailable = false;
    }
}
