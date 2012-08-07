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
class AW_Sarp_Model_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{


    public function reset()
    {
        $this->_session->clear();
        $this->_session->setQuote(null);
        return $this;
    }


    /**
     * Initialize creation data from existing order
     *
     * @param Mage_Sales_Model_Order $order
     * @return unknown
     */
    public function initFromOrder(Mage_Sales_Model_Order $order)
    {

        if (!$order->getReordered()) {
            $this->getSession()->setOrderId($order->getId());
        } else {
            $this->getSession()->setReordered($order->getId());
        }

        /**
         * Check if we edit quest order
         */
        $this->getSession()->setCurrencyId($order->getOrderCurrencyCode());
        if ($order->getCustomerId()) {
            $this->getSession()->setCustomerId($order->getCustomerId());
        } else {
            $this->getSession()->setCustomerId(false);
        }

        $this->getSession()->setStoreId($order->getStoreId());
        $this->getSession()->getStore();//need for initializing store

        foreach ($order->getItemsCollection(
            Mage::helper('sarp')->getAllSubscriptionTypes(),
            true
        ) as $orderItem) {
            /* @var $orderItem Mage_Sales_Model_Order_Item */

            if (!$this->hasSubscriptionOptions($orderItem)) {
                continue;
            }


            if (is_array($this->getItemIdFilter()) && sizeof($this->getItemIdFilter())) {
                // If itemId filter is set - ignore not matching entries
                if (array_search($orderItem->getId(), $this->getItemIdFilter()) === false) {
                    continue;
                }
            }

            if (!$orderItem->getParentItem()) {
                if ($order->getReordered()) {
                    $qty = $orderItem->getQtyOrdered();
                }
                else {
                    $qty = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped() - $orderItem->getQtyInvoiced();
                }

                if ($qty > 0) {
                    $item = $this->initFromOrderItem($orderItem, $qty);
                    if (is_string($item)) {
                        Mage::throwException($item);
                    }
                }
            }
        }
        $this->_initBillingAddressFromOrder($order);
        $this->_initShippingAddressFromOrder($order);

        $this->setShippingMethod($order->getShippingMethod());
        $this->getQuote()->getShippingAddress()->setShippingDescription($order->getShippingDescription());
        $this->getQuote()->getBillingAddress()->setShippingDescription($order->getShippingDescription());


        $this->getQuote()->getPayment()->addData($order->getPayment()->getData());


        $orderCouponCode = $order->getCouponCode();
        if ($orderCouponCode) {
            $this->getQuote()->setCouponCode($orderCouponCode);
        }

        if ($this->getQuote()->getCouponCode()) {
            //$this->getQuote()->collectTotals();
        }

        Mage::helper('core')->copyFieldset(
            'sales_copy_order',
            'to_edit',
            $order,
            $this->getQuote()
        );

        Mage::dispatchEvent('sales_convert_order_to_quote', array(
                                                                 'order' => $order,
                                                                 'quote' => $this->getQuote()
                                                            ));

        if (!$order->getCustomerId()) {
            $this->getQuote()->setCustomerIsGuest(true);
        }

        if ($this->getSession()->getUseOldShippingMethod(true)) {
            /*
             * if we are making reorder or editing old order
             * we need to show old shipping as preselected
             * so for this we need to collect shipping rates
             */
            $this->collectShippingRates();
        } else {
            
            /*
             * if we are creating new order then we don't need to collect
             * shipping rates before customer hit appropriate button
             */
            $this->collectRates();
        }

        $this->saveQuote();

        // Make collect rates when user click "Get shipping methods and rates" in order creating
        // $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        // $this->getQuote()->getShippingAddress()->collectShippingRates();

        /** Check stock */

        foreach ($this->getQuote()->getItemsCollection() as $item) {
            $this->checkQuoteItemQty($item);
            if ($item->getProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                $item->setMessage(Mage::helper('adminhtml')->__('This product is currently disabled'));
                $item->setHasError(true);
            }

            if ($item->getHasError()) {
                throw new AW_Sarp_Exception($item->getMessage());
            }
        }


        return $this;
    }

    /**
     * Initialize creation data from existing order Item
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return Mage_Sales_Model_Quote_Item | string
     */
    public function initFromOrderItem(Mage_Sales_Model_Order_Item $orderItem, $qty = 1)
    {
        if (!$orderItem->getId()) {
            return $this;
        }

        $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getSession()->getStoreId())
                ->load($orderItem->getProductId());

        if ($product->getId()) {
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new Varien_Object($info);
            $product->setSkipCheckRequiredOption(true);

            $info->setQty($orderItem->getQtyOrdered());

            $item = $this->getQuote()->addProduct($product, $info);
            if (is_string($item)) {
                return $item;
            }
            //$item->setQty($qty);
            if ($additionalOptions = $orderItem->getProductOptionByCode('additional_options')) {
                $item->addOption(new Varien_Object(
                                     array(
                                          'product' => $item->getProduct(),
                                          'code' => 'additional_options',
                                          'value' => serialize($additionalOptions)
                                     )
                                 ));
            }
            Mage::dispatchEvent('sales_convert_order_item_to_quote_item', array(
                                                                               'order_item' => $orderItem,
                                                                               'quote_item' => $item
                                                                          ));

            return $item;
        }

        return $this;
    }

    /**
     * Check Quote item qty. If qty is not enougth for order, error flag and message added to $quote item
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     */
    protected function checkQuoteItemQty($quoteItem)
    {
        $qty = $quoteItem->getQty();

        if (($options = $quoteItem->getQtyOptions()) && $qty > 0) {
            $qty = $quoteItem->getProduct()->getTypeInstance(true)->prepareQuoteItemQty($qty, $quoteItem->getProduct());
            $quoteItem->setData('qty', $qty);

            foreach ($options as $option)
            {
                $optionQty = $qty * $option->getValue();
                $increaseOptionQty = ($quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd()
                        : $qty) * $option->getValue();

                $stockItem = $option->getProduct()->getStockItem();
                /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                    $quoteItem
                            ->setHasError(true)
                            ->setMessage('Stock item for Product in option is not valid');
                    return;
                }

                $result = $stockItem->checkQuoteItemQty($optionQty, $optionQty, $option->getValue());

                if ($result->getHasError()) {
                    $quoteItem
                            ->setHasError(true)
                            ->setMessage($result->getQuoteMessage());
                }
            }
        }
        else
        {
            $stockItem = $quoteItem->getProduct()->getStockItem();
            /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                Mage::throwException(Mage::helper('cataloginventory')->__('Stock item for Product is not valid'));
            }

            /**
             * When we work with subitem (as subproduct of bundle or configurable product)
             */
            if ($quoteItem->getParentItem()) {
                $rowQty = $quoteItem->getParentItem()->getQty() * $qty;
                /**
                 * we are using 0 because original qty was processed
                 */
                $qtyForCheck = 0;
            }
            else {
                $increaseQty = $quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty;
                $rowQty = $qty;
                $qtyForCheck = $qty;
            }
            $result = $stockItem->checkQuoteItemQty($rowQty, $qtyForCheck, $qty);

            if ($result->getHasError()) {
                $quoteItem
                        ->setHasError(true)
                        ->setMessage($result->getQuoteMessage());
            }
        }
    }


    /**
     * Creates new order
     * @param Mage_Sales_Model_Order      $Order
     * @return
     */
//    public function _initFromPrimaryOrder(Mage_Sales_Model_Order $order) {
//
//        if (!$order->getReordered()) {
//            $this->getSession()->setOrderId($order->getId());
//        } else {
//            $this->getSession()->setReordered($order->getId());
//        }
//
//        /**
//         * Check if we edit quest order
//         */
//        $this->getSession()->setCurrencyId($order->getOrderCurrencyCode());
//        if ($order->getCustomerId()) {
//            $this->getSession()->setCustomerId($order->getCustomerId());
//        } else {
//            $this->getSession()->setCustomerId(false);
//        }
//
//        $this->getSession()->setStoreId($order->getStoreId());
//
//
//        call_user_func_array(array($order, 'getItemsCollection'), Mage::helper('sarp')->getAllSubscriptionTypes());
//        $this->initFromOrder($order);
//
//        $this->_initBillingAddressFromQuote($this->getPrimaryQuote());
//        $this->_initShippingAddressFromQuote($this->getPrimaryQuote());
//
//        $this->setShippingMethod($order->getShippingMethod());
//        $this->getQuote()->getShippingAddress()->setShippingDescription($order->getShippingDescription());
//
//        $this->getQuote()->getPayment()->addData($order->getPayment()->getData());
//
//        if ($this->getQuote()->getCouponCode()) {
//            $this->getQuote()->collectTotals();
//        }
//
//        Mage::helper('core')->copyFieldset(
//            'sales_copy_order',
//            'to_edit',
//            $order,
//            $this->getQuote()
//        );
//
//        Mage::dispatchEvent('sales_convert_order_to_quote', array(
//            'order' => $order,
//            'quote' => $this->getQuote()
//        ));
//
//        if (!$order->getCustomerId()) {
//            $this->getQuote()->setCustomerIsGuest(true);
//        }
//
//        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
//        $this->getQuote()->getShippingAddress()->collectShippingRates();
//
//        $this->collectShippingRates();
//        $this->collectRates();
//
//        $this->getQuote()->save();
//
//        return $this;
//    }

    /**
     * Initializes from specified quote. If quote is empty(e.g. deleted) restore from order
     * @param object $quote
     * @return
     */
    protected function _initBillingAddressFromQuote($quote)
    {
        if (!$quote->getBillingAddress()->getCountry()) return;
        $this->getQuote()->getBillingAddress()->setCustomerAddressId('');

        Mage::helper('core')->copyFieldset(
            'sales_copy_order_billing_address',
            'to_order',
            $quote->getBillingAddress(),
            $this->getQuote()->getBillingAddress()
        );
    }

    /**
     * Initializes from specified quote. If quote is empty(e.g. deleted) restore from order
     * @param object $quote
     * @return
     */
    protected function _initShippingAddressFromQuote($quote)
    {
        if (!$quote->getShippingAddress()->getCountry()) return;
        $this->getQuote()->getShippingAddress()->setCustomerAddressId('');
        Mage::helper('core')->copyFieldset(
            'sales_copy_order_shipping_address',
            'to_order',
            $quote->getShippingAddress(),
            $this->getQuote()->getShippingAddress()
        );
    }


    /**
     * Validate quote data before order creation
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _validate()
    {
        $customerId = $this->getSession()->getCustomerId();
        if (is_null($customerId)) {
            Mage::throwException(Mage::helper('adminhtml')->__('Please select a custmer'));
        }

        if (!$this->getSession()->getStore()->getId()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Please select a store'));
        }
        $items = $this->getQuote()->getAllItems();

        $errors = array();
        if (count($items) == 0) {
            $errors[] = Mage::helper('adminhtml')->__('You need to specify order items');
        }

        if (!$this->getQuote()->isVirtual()) {
            if (!$this->getQuote()->getShippingAddress()->getShippingMethod()) {
                $errors[] = Mage::helper('adminhtml')->__('Shipping method must be specified');
            }
        }

        if (!$this->getQuote()->getPayment()->getMethod()) {
            $errors[] = Mage::helper('adminhtml')->__('Payment method must be specified');
        } else {
            $method = $this->getQuote()->getPayment()->getMethodInstance();
            if (!$method) {
                $errors[] = Mage::helper('adminhtml')->__('Payment method instance is not available');
            } else {
                if (!$method->isAvailable($this->getQuote())) {
                    $errors[] = Mage::helper('adminhtml')->__('Payment method is not available');
                } else {
                    try {
                        $method->validate();
                    } catch (Mage_Core_Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }


        return $this;
    }

    /**
     * Test if order item must be included in
     * @param Mage_Sales_Model_Order_Item $OrderItem
     */
    public function hasSubscriptionOptions($OrderItem)
    {

        $options = $OrderItem->getProductOptions();

        if (isset($options['info_buyRequest'])) {
            $periodTypeId = @$options['info_buyRequest']['aw_sarp_subscription_type'];
        } else {
            $periodTypeId = -1;
        }

        return $periodTypeId > 0;
    }
}
