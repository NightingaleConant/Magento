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
class AW_Sarp_CustomerController extends Mage_Core_Controller_Front_Action
{

    /*
      *
      * check that current subscription belong current customer
      *
      */

    public function _check()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = Mage::getModel('sarp/subscription')->load($id);
            if (Mage::getModel('customer/session')->getId() != $model->getCustomerId()) {
                Mage::getSingleton('core/session')->addError(Mage::helper('sarp')->__('This subscription isn\'t belong this customer!'));
                return false;
            }
            else
                return true;
        }
    }

    public function indexAction()
    {

        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('sarp/customer');
        }
        if ($block = $this->getLayout()->getBlock('sarp_subscription_list')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->renderLayout();
    }

    public function historyAction()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $Subscription = Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id'));
        if ($block = $this->getLayout()->getBlock('sarp_subscription_payments_history')) {
            $block
                    ->setRefererUrl($this->_getRefererUrl())
                    ->setSubscription($Subscription);
        }
        if ($block = $this->getLayout()->getBlock('sarp_subscription_payments_pending')) {
            $block
                    ->setRefererUrl($this->_getRefererUrl())
                    ->setSubscription($Subscription);
        }

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('sarp/customer');
        }

        $this->renderLayout();
    }

    public function viewAction()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        ;
        if ($block = $this->getLayout()->getBlock('sarp_subscription_summary')) {
            $block
                    ->setRefererUrl($this->_getRefererUrl())
                    ->setSubscription(Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id')));
        }

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('sarp/customer');
        }

        $this->renderLayout();
    }

    public function changeAction()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }
        $subscription = Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id'));
        if (
            is_null($subscription->getCustomerId()) ||
            Mage::getSingleton('customer/session')->getCustomerId() != $subscription->getCustomerId()
        ) {
            $this->_redirect('subscriptions/customer');
            return;
        }
        
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        if ($block = $this->getLayout()->getBlock('sarp_subscription_edit')) {
            $block
                    ->setRefererUrl($this->_getRefererUrl())
                    ->setSubscription(Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id')))
                    ->setSection($this->getRequest()->getParam('section'));
        }
        $this->renderLayout();
    }

    /**
     * Saves address
     * @return
     */
    public function saveAction()
    {
        try {
            $model = Mage::getModel('sarp/order_section')
                    ->setSubscription(Mage::getSingleton('sarp/subscription')->load($this->getRequest()->getParam('id')))
                    ->setType($this->getRequest()->getParam('section'))
                    ->setDataToSave($this->getRequest()->getPost())
                    ->save();
            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('sarp')->__('Subscription has been successfully saved'));

            $this->_redirect('sarp/customer/view', array('id' => $this->getRequest()->getParam('id')));
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
            Mage::getSingleton('customer/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    /**
     * Customer cancels subscription
     * @return
     */
    public function cancelAction()
    {
        try {
            $model = Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id'));
            if ($model->getId() && Mage::getModel('customer/session')->getId() == $model->getCustomerId()) {
                $model->cancel()->save();
            } else {
                throw new AW_Sarp_Exception("Subscription not found!");
            }
            Mage::getSingleton('core/session')->addSuccess('You have successfully canceled subscription');
            $this->_redirectReferer();
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    /**
     * Customer suspends subscription
     * @return
     */
    public function suspendAction()
    {
        if (!$this->_check())
            return $this->_redirect('/');

        try {
            $model = Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id'));
            if ($model->getId()) {
                $model->setStatus(AW_Sarp_Model_Subscription::STATUS_SUSPENDED_BY_CUSTOMER)->save();
            } else {
                throw new AW_Sarp_Exception("Subscription not found!");
            }
            Mage::getSingleton('core/session')->addSuccess('You have successfully suspended subscription');
            $this->_redirectReferer();
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    /**
     * Customer re-activates subscription
     * @return
     */
    public function activateAction()
    {
        if (!$this->_check())
            return $this->_redirect('/');

        try {
            $model = Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id'));
            if ($model->getId() && ($model->getStatus() == AW_Sarp_Model_Subscription::STATUS_SUSPENDED_BY_CUSTOMER)) {
                $model->setStatus(AW_Sarp_Model_Subscription::STATUS_ENABLED)->save();
            } else {
                throw new AW_Sarp_Exception("Activation of subscription is no allowed");
            }
            Mage::getSingleton('customer/session')->addSuccess('You have successfully activated subscription');
            $this->_redirectReferer();
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
            Mage::getSingleton('customer/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    public function prolongAction()
    {

        if (!$this->_check())
            return $this->_redirect('/');

        $model = Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id'));
        $orderId = $model->getOrder()->getId();
        $order = $model->getOrder();

        $cart = Mage::getSingleton('checkout/cart');
        $cartTruncated = false;
        /* @var $cart Mage_Checkout_Model_Cart */

        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                /* Checking for subscription type to prolong */
                if (!Mage::helper('sarp')->isSubscriptionType($item)) continue;

                /* Check if order item and one of subscription items match */
                $flag = false;
                foreach ($model->getItems() as $subscriptionItem)
                    if ($item->getId() == $subscriptionItem->getOrderItem()->getId()) {
                        $flag = true;
                        break;
                    }
                if (!$flag) continue;

                $iBR = $item->getProductOptionByCode('info_buyRequest');

                $newDate = new Zend_Date($iBR['aw_sarp_subscription_start'], Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $newDate = $model->getNextSubscriptionEventDate($model->getDateExpire());

                if ($newDate->compare(new Zend_Date) == -1 || ($model->getStatus() == AW_Sarp_Model_Subscription::STATUS_CANCELED)) {
                    $newDate = new Zend_Date;
                }
                $iBR['aw_sarp_subscription_start'] = $newDate->toString(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $item->setProductOptions(array('info_buyRequest' => $iBR));

                $cart->addOrderItem($item);

            } catch (Mage_Core_Exception $e) {
                if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                    Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                }
                else {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                $this->_redirectReferer();
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e,
                                                                     Mage::helper('checkout')->__('Can not add item to shopping cart')
                );
                $this->_redirect('checkout/cart');
            }
        }

        $cart->save();
        $this->_redirect('checkout/cart');

    }

    public function reorderAction()
    {
        $model = Mage::getModel('sarp/subscription')->load($this->getRequest()->getParam('id'));
        $orderId = $model->getOrder()->getId();
        $order = $model->getOrder();

        $cart = Mage::getSingleton('checkout/cart');
        $cartTruncated = false;
        /* @var $cart Mage_Checkout_Model_Cart */

        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $iBR = $item->getProductOptionByCode('info_buyRequest');

                //$newDate = new Zend_Date($iBR['aw_sarp_subscription_start'], Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                if ($date = $model->getLastPaidDate()) {

                } else {
                    $date = $model->getDateStart();
                }

                $newDate = $model->getNextSubscriptionEventDate($date);

                if ($newDate->compare(new Zend_Date) == -1 || ($model->getStatus() == AW_Sarp_Model_Subscription::STATUS_CANCELED)) {
                    $newDate = new Zend_Date;
                }
                $iBR['aw_sarp_subscription_start'] = $newDate->toString(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $iBR['aw_sarp_order_id'] = $orderId;
                $item->setProductOptions(array('info_buyRequest' => $iBR));

                $cart->addOrderItem($item);
                $model->setStatus(AW_Sarp_Model_Subscription::STATUS_CANCELED)->save();
            } catch (Mage_Core_Exception $e) {
                if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                    Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                }
                else {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                $this->_redirectReferer();
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e,
                                                                     Mage::helper('checkout')->__('Can not add item to shopping cart')
                );
                $this->_redirect('checkout/cart');
            }
        }

        $cart->save();
        $this->_redirect('checkout/cart');

    }
}