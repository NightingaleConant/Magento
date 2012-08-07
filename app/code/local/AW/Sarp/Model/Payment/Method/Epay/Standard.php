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
class AW_Sarp_Model_Payment_Method_Epay_Standard extends AW_Sarp_Model_Payment_Method_Abstract
{

    const XML_PATH_EPAY_MERCHANT_NUMBER = 'payment/epay_standard/merchantnumber';
    const XML_PATH_EPAY_INSTANT_CAPTURE = 'payment/epay_standard/instantcapture';
    const XML_PATH_EPAY_AUTH_MAIL = 'payment/epay_standard/authmail';
    const XML_PATH_EPAY_AUTH_SMS = 'payment/epay_standard/authsms';
    const XML_PATH_EPAY_ORDER_STATUS_AFTER_PAYMENT = 'payment/epay_standard/order_status_after_payment';


    const WEB_SERVICE_MODEL = 'sarp/web_service_client_epay';


    public function __construct()
    {
        $this->_initWebService();
    }

    protected function _initWebService()
    {
        $service = Mage::getModel(self::WEB_SERVICE_MODEL);

        $service
                ->setMerchantNumber(Mage::getStoreConfig(self::XML_PATH_EPAY_MERCHANT_NUMBER))
                ->setIsInstantCapture(Mage::getStoreConfig(self::XML_PATH_EPAY_INSTANT_CAPTURE))
                ->setEmail(Mage::getStoreConfig(self::XML_PATH_EPAY_AUTH_MAIL))
                ->setSms(Mage::getStoreConfig(self::XML_PATH_EPAY_AUTH_MAIL));

        $this->setWebService($service);

    }

    /**
     * Returns all data from epay module for order
     * @return Varien_Object
     */
    public function getEpayData($Order)
    {
        $order_id = $this->getOrder($Order)->getIncrementId();
        if (!$this->getData('epay_data_' . $order_id)) {
            $data = array();
            $select = clone Mage::getResourceSingleton('sarp/subscription')->getReadConnection()->select();
            try {
                $result = $select
                        ->from(
                    array('epay_order_status'),
                    array('*')
                )
                        ->where('orderid=?', $order_id)
                        ->query()
                        ->fetchObject();
                foreach ($result as $prop => $value) {
                    $data[$prop] = $value;
                }
                $this->setData('epay_data_' . $order_id, new Varien_Object($data));
            } catch (Exception $e) {
                throw new AW_Sarp_Exception("Error while retrieving data from ePay module", "Order #$order_id", AW_Sarp_Model_Log::LOG_LEVEL_WARN);
            }
        }
        return $this->getData('epay_data_' . $order_id);
    }


    /**
     * Returns service subscription service id for specified order item
     * @param mixed $Order
     * @return int
     */
    public function getSubscriptionId($Order)
    {
        return $this->getEpayData($Order)->getSubscriptionid();
    }

    /**
     * Returns currency code for primary order
     * @param Mage_Sales_Model_Order $Order
     * @return int
     */
    public function getCurrencyCode($Order)
    {
        return $this->getEpayData($Order)->getCur();
    }

    /**
     * Processes payment for specified order
     * @param Mage_Sales_Model_Order $Order
     * @return
     */
    public function processOrder(Mage_Sales_Model_Order $PrimaryOrder, Mage_Sales_Model_Order $Order = null)
    {
        $sId = $this->getSubscriptionId($PrimaryOrder);

        $this->getWebService()->getRequest()
                ->reset()
                ->setSubscriptionid($sId)
                ->setOrderid($Order->getIncrementId())
                ->setAmount($Order->getGrandTotal() * 100)
                ->setCurrency($this->getCurrencyCode($PrimaryOrder))
                ->setGroup('');

        $result = $this->getWebService()->authorizeSubscription();
        // Include record to epay_orders_status
        $this->_saveOrderStatus($PrimaryOrder, $Order, 1);
    }

    /**
     * Saves order status to epay tables
     * @return AW_Sarp_Model_Web_Service_Client_Epay
     */
    protected function _saveOrderStatus(Mage_Sales_Model_Order $PrimaryOrder, Mage_Sales_Model_Order $Order = null, $status = 1)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->insert('epay_order_status', array(
                                                 'orderid' => $Order->getIncrementId(),
                                                 'tid' => $this->getWebService()->getResponse()->getTransactionid(),
                                                 'status' => $status,
                                                 'amount' => $this->getWebService()->getRequest()->getAmount(),
                                                 'cur' => $this->getWebService()->getRequest()->getCurrency(),
                                                 'date' => Mage::app()->getLocale()->date()->toString('YMMdd'),
                                                 'fraud' => $this->getWebService()->getRequest()->getFraud(),
                                                 'cardid' => $this->getEpayData($PrimaryOrder)->getCardid(),
                                                 'transfee' => '0'

                                            )
        );
    }


}
