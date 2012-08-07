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
 */class AW_Sarp_Model_Payment_Method_ProtxDirect extends AW_Sarp_Model_Payment_Method_Abstract
{

    /** Credit card verification (Yes/No) */
    const CREDIT_CARD_VERIFICATON = 'payment/protxDirect/useccv';

    /** Vendor */
    const VENDOR = 'payment/protxDirect/vendor';

    /** Merchant password config path. */
    const CURRENCY = 'payment/protxDirect/currency';

    /** Mode (simulator/test/live) */
    const MODE = 'payment/protxDirect/mode';

    /** Repeat type */
    const REPEAT = 'repeat';

    /* Protocol version */
    const PROTOCOL_VERSION = '2.23';

    /* Urls for sending requests */
    const SIMULATOR_REPEAT_URL = 'https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorRepeatTx';
    const TEST_REPEAT_URL = 'https://test.sagepay.com/gateway/service/repeat.vsp';
    const LIVE_REPEAT_URL = 'https://live.sagepay.com/gateway/service/repeat.vsp';

    /* Paths for order statuses (ok and fail) */
    const XML_PATH_PROTXDIRECT_ORDER_STATUS_OK = 'payment/protxDirect/order_status';
    const XML_PATH_PROTXDIRECT_ORDER_STATUS_FAILED = 'payment/protxDirect/fail_order_status';

    public static $VendorTxCode;
    public static $VPSTxID;
    public static $TxAuthNo;
    public static $SecurityKey;

    /**
     * This function is run when subscription is created and new order creates
     * @param AW_Sarp_Model_Subscription $Subscription
     * @param Mage_Sales_Model_Order     $Order
     * @param Mage_Sales_Model_Quote     $Quote
     * @return AW_Sarp_Model_Payment_Method_Argofire
     */
    public function onSubscriptionCreate(AW_Sarp_Model_Subscription $Subscription, Mage_Sales_Model_Order $Order, Mage_Sales_Model_Quote $Quote)
    {
        $this->createSubscription($Subscription, $Order, $Quote);
        return $this;
    }

    /**
     * Creates subscription at paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return
     */
    public function createSubscription(AW_Sarp_Model_Subscription $Subscription, $Order, $Quote)
    {
        Mage::getModel('sarp/protxDirect')
                ->setSubscriptionId($Subscription->getId())
                ->setVendorTxCode(self::$VendorTxCode)
                ->setVpsTxId(self::$VPSTxID)
                ->setTxAuthNo(self::$TxAuthNo)
                ->setSecurityKey(self::$SecurityKey)
                ->save();

        return $this;

    }

    /**
     * Processes payment for specified order
     * @param Mage_Sales_Model_Order $Order
     * @return
     */
    public function processOrder(Mage_Sales_Model_Order $PrimaryOrder, Mage_Sales_Model_Order $Order = null)
    {


        $amount = $Order->getGrandTotal();
        $increment = $Order->getIncrementId();
        $VendorTxCode = $increment . "-" . date("y-m-d-H-i-s", time()) . "-" . rand(0, 1000000);

        $model = Mage::getModel('sarp/protxDirect')->load($this->getSubscription()->getId(), 'subscription_id');

        $data = array(
            'VPSProtocol' => self::PROTOCOL_VERSION,
            'TxType' => self::REPEAT,
            'Vendor' => Mage::getStoreConfig(self::VENDOR),
            'VendorTxCode' => $VendorTxCode,
            'Amount' => $amount,
            'Currency' => $Order->getOrderCurrencyCode(),
            'Description' => 'Order',
            'RelatedVPSTxId' => $model->getVpsTxId(),
            'RelatedVendorTxCode' => $model->getVendorTxCode(),
            'RelatedSecurityKey' => $model->getSecurityKey(),
            'RelatedTxAuthNo' => $model->getTxAuthNo()
        );

        $ready = array();
        foreach ($data as $key => $value)
            $ready[] = $key . '=' . $value;
        $str = implode('&', $ready);

        switch (Mage::getStoreConfig(self::MODE))
        {
            case 'test':
                $url = self::TEST_REPEAT_URL;
                break;
            case 'live':
                $url = self::LIVE_REPEAT_URL;
                break;
            default:
                $url = self::SIMULATOR_REPEAT_URL;
        }

        $ready = $this->requestPost($url, $str);

        if (empty($ready)) {
            throw new AW_Sarp_Exception($this->__("Order cannot be completed. Unknown error"));
        }

        if ($ready['Status'] != 'OK') {
            throw new AW_Sarp_Exception($ready['Status'] . " - " . $ready['StatusDetail']);
        }
    }

    /**
     * Sending post request via curl
     * @param string $url
     * @param string $str
     * @return array
     */
    function requestPost($url, $data)
    {
        // Set a one-minute timeout for this script
        set_time_limit(60);

        // Initialise output variable
        $output = array();

        // Open the cURL session
        $curlSession = curl_init();

        // Set the URL
        curl_setopt($curlSession, CURLOPT_URL, $url);
        // No headers, please
        curl_setopt($curlSession, CURLOPT_HEADER, 0);
        // It's a POST request
        curl_setopt($curlSession, CURLOPT_POST, 1);
        // Set the fields for the POST
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, $data);
        // Return it direct, don't print it out
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        // This connection will timeout in 30 seconds
        curl_setopt($curlSession, CURLOPT_TIMEOUT, 30);
        //The next two lines must be present for the kit to work with newer version of cURL
        //You should remove them if you have any problems in earlier versions of cURL
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 1);

        //Send the request and store the result in an array

        $rawresponse = curl_exec($curlSession);
        //Store the raw response for later as it's useful to see for integration and understanding
        $response = split(chr(10), $rawresponse);
        // Check that a connection was made
        if (curl_error($curlSession)) {
            // If it wasn't...
            $output['Status'] = "FAIL";
            $output['StatusDetail'] = curl_error($curlSession);
        }

        // Close the cURL session
        curl_close($curlSession);

        // Tokenise the response
        for ($i = 0; $i < count($response); $i++) {
            // Find position of first "=" character
            $splitAt = strpos($response[$i], "=");
            // Create an associative (hash) array with key/value pairs ('trim' strips excess whitespace)
            $output[trim(substr($response[$i], 0, $splitAt))] = trim(substr($response[$i], ($splitAt + 1)));
        } // END for ($i=0; $i<count($response); $i++)

        // Return the output
        return $output;
    }
}
