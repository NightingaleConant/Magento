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
/**
 * This class uses SOAP API instead of PayPal-NVP-like API wich original Acreativellc module uses
 *
 */
class AW_Sarp_Model_Web_Service_Client_Argofire extends AW_Sarp_Model_Web_Service_Client
{

    /** ARB service api patth */
    const XML_PATH_ARB_API = 'payment/argofire/payment_server_url2';

    /** Merchant id config path. */
    const XML_PATH_MERCHANT_ID = 'payment/argofire/merchant_id';

    /** Merchant password config path. */
    const XML_PATH_MERCHANT_PASSWORD = 'payment/argofire/merchant_pass';

    /** Vendor id config path. Required for ARB payment */
    const XML_PATH_VENDOR_ID = 'payment/argofire/vendor_id';

    /** Prefix to setup functions names*/
    const URI_FUNCTION_POSTFIX = '%s';

    /** SOAP WSDL URI */
    const WSDL_ARB_URI = "https://secure.ftipgw.com/admin/ws/recurring.asmx?WSDL";

    /** Transaction type "add" for managing recurring credit cards */
    const TRANS_TYPE_ADD = 'ADD';

    /** Date format */
    const DATE_FORMAT = 'M/d/yyyy';

    /**
     * Return ARB API URI
     * This is called by AW_Sarp_Model_Web_Service_Client::getService
     * @return string
     */
    public function getUri($function = '')
    {
        $uri = chop(Mage::getStoreconfig(self::XML_PATH_ARB_API), "/");
        if ($function) {
            $uri .= sprintf(self::URI_FUNCTION_POSTFIX, urlencode($function));
        }
        return $uri;
    }

    /**
     * Return WSDL URI
     * @return string
     */
    public function getWsdl()
    {
        return self::WSDL_ARB_URI;
    }

    /**
     * Returns merchant as object
     * @return int | array | Varien_Object
     */
    public function getMerchant($ret = false)
    {
        $hash = 'merchant' . ((string)$ret);
        if (!$this->getData($hash)) {
            $data = array(
                'id' => Mage::getStoreconfig(self::XML_PATH_MERCHANT_ID),
                'password' => Mage::getStoreconfig(self::XML_PATH_MERCHANT_PASSWORD),
                'vendor_id' => Mage::getStoreconfig(self::XML_PATH_VENDOR_ID)
            );
            if ($ret == AW_Core_Model_Abstract::RETURN_INTEGER) {
                $r = $data['vendor_id'];
            } elseif ($ret == AW_Core_Model_Abstract::RETURN_ARRAY) {
                $r = $data;
            } else {
                $r = new Varien_Object($data);
            }
            $this->setData($hash, $r);
        }
        return $this->getData($hash);
    }

    /**
     * Adds recurrent payment credit card
     * @return creditCardId
     */
    public function addRecurringCreditCard()
    {

        $this->getRequest()->setDefaultData(array(
                                                 'CustomerID' => '',
                                                 'CustomerName' => '',
                                                 'FirstName' => '',
                                                 'LastName' => '',
                                                 'Title' => '',
                                                 'Department' => '',
                                                 'Street1' => '',
                                                 'Street2' => '',
                                                 'Street3' => '',
                                                 'City' => '',
                                                 'StateID' => '',
                                                 'Province' => '',
                                                 'Zip' => '',
                                                 'CountryID' => '',
                                                 'Email' => '',
                                                 'Mobile' => '',
                                                 'ContractID' => '',
                                                 'ContractName' => '',
                                                 'BillAmt' => '',
                                                 'TaxAmt' => '',
                                                 'TotalAmt' => '',
                                                 'StartDate' => '',
                                                 'EndDate' => '',
                                                 'BillingPeriod' => '',
                                                 'BillingInterval' => '',
                                                 'MaxFailures' => '',
                                                 'FailureInterval' => '',
                                                 'EmailCustomer' => '',
                                                 'EmailMerchant' => '',
                                                 'EmailCustomerFailure' => '',
                                                 'EmailMerchantFailure' => '',
                                                 'CcAccountNum' => '',
                                                 'CcExpdate' => '',
                                                 'CcNameOnCard' => '',
                                                 'CcStreet' => '',
                                                 'CcZip' => '',
                                                 'ExtData' => '',
                                                 'DayPhone' => '',
                                                 'NightPhone' => '',
                                                 'Fax' => ''
                                            ));


        return $this->_runRequest('AddRecurringCreditCard');
    }

    public function processCreditCard()
    {
        $this->getRequest()->setDefaultData(array(
                                                 'CcInfoKey' => '',
                                                 'Amount' => '',
                                                 'InvNum' => '',
                                                 'ExtData' => ''
                                            ));
        return $this->_runRequest('ProcessCreditCard');
    }


    /**
     * Runs request and returns response
     * @param string function
     * @param array $data
     * @return
     */
    protected function _runRequest($function)
    {
        $Service = $this->getService($function);
        // Initialize reuest
        $this->getRequest()
                ->attachData($this->_buildAuthRequestPart());

        $result = call_user_func(array($this->getService(), $function), $this->getRequest()->getRequestData());
        $key = $function . "Result";
        return $result->$key;

    }

    /**
     * Returns auth part of request
     * This part is common for all requests
     * @return array
     */
    protected function _buildAuthRequestPart()
    {
        $Merchant = $this->getMerchant(AW_Core_Model_Abstract::RETURN_OBJECT);
        $authData = array(
            'Username' => (string)$Merchant->getId(),
            'Password' => (string)$Merchant->getPassword(),
            'Vendor' => (string)$Merchant->getVendorId()
        );
        return $authData;
    }

    /**
     * Returns default SOAP service options
     * @return array
     */
    public function getDefaultServiceOptions()
    {
        return array(
            'soap_version' => SOAP_1_1,
            'compression' => SOAP_COMPRESSION_ACCEPT,
        );
    }

    /**
     * Format date
     * @param <type> $Date
     * @return String
     */
    public function formatDate($Date)
    {
        return $Date->toString(self::DATE_FORMAT);
    }
}

