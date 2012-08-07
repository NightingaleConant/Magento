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
ini_set('memory_limit', '128M');

class AW_Sarp_Model_Web_Service_Client_Authorizenet extends AW_Sarp_Model_Web_Service_Client
{
    /** Path to Authorize.net Advanced Recurring Billing API WSDL */
    const WSDL_ARB_PATH = 'https://api.authorize.net/soap/v1/Service.asmx?WSDL';
    const SERVICE_TEST_PATH = 'https://apitest.authorize.net/soap/v1/Service.asmx';
    const SERVICE_PROD_PATH = 'https://api.authorize.net/soap/v1/Service.asmx';

    /** Date format accepted by Authorize.net API */
    const DATE_FORMAT = 'Y-MM-dd';

    /** Days units */
    const UNIT_DAYS = 'days';
    /** Months units */
    const UNIT_MONTHS = 'months';
    /** Error code: Duplicat customer */
    const ERR_CODE_DUPLICATE_CUSTOMER = 'E00039';

    /** Transaction Id in directResponse reply */
    const ORDER_TRXID = 6;

    const DEFAULT_STORE_ID = 0;

    /**
     * Returns API Service URL depending on test mode on/off
     * @return string
     */
    public function getUri()
    {
        if (Mage::getStoreConfig(AW_Sarp_Model_Payment_Method_Authorizenet::XML_PATH_AUTHORIZENET_SOAP_TEST)) {
            //$this->log("Authorize.net is in test mode");
            return self::SERVICE_TEST_PATH;
        } else {
            return self::SERVICE_PROD_PATH;
        }
    }

    public function getWsdl()
    {
        return self::WSDL_ARB_PATH;
    }

    public function getApiLoginId()
    {
        $_storeId = self::DEFAULT_STORE_ID;
        if($this->getSubscription()->getStoreId())
            $_storeId = $this->getSubscription()->getStoreId();
        return Mage::getStoreConfig(AW_Sarp_Model_Payment_Method_Authorizenet::XML_PATH_AUTHORIZENET_API_LOGIN_ID, $_storeId);
    }

    public function getTransactionKey()
    {
        $_storeId = self::DEFAULT_STORE_ID;
        if($this->getSubscription()->getStoreId())
            $_storeId = $this->getSubscription()->getStoreId();
        return Mage::getStoreConfig(AW_Sarp_Model_Payment_Method_Authorizenet::XML_PATH_AUTHORIZENET_TRANSACTION_KEY, $_storeId);
    }

    /**
     * Capture or auth only
     * @return Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE || Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE
     */
    public function getPaymentAction()
    {
        return Mage::getStoreConfig(AW_Sarp_Model_Payment_Method_Authorizenet::XML_PATH_AUTHORIZENET_PAYMENT_ACTION);
    }

    /**
     * Creates CIM Account at authorize.net
     * @return int AccountId
     */
    public function createCIMAccount()
    {

        $this->getRequest()
                ->reset()
                ->setData(array(
                               'merchantAuthentication' => array(
                                   'name' => trim($this->getApiLoginId()),
                                   'transactionKey' => trim($this->getTransactionKey())
                               ),
                               'profile' => array(
                                   'merchantCustomerId' => $this->getPayment()->getQuote()->getCustomerId(),
                                   'email' => $this->getPayment()->getQuote()->getCustomerEmail(),
                                   'paymentProfiles' => array(
                                       'CustomerPaymentProfileType' => array(
                                           'customerType' => 'individual',
                                           'billTo' => array(
                                               'firstName' => $this->getPayment()->getQuote()->getBillingAddress()->getFirstname(),
                                               'lastName' => $this->getPayment()->getQuote()->getBillingAddress()->getLastname(),
                                               'address' => $this->getPayment()->getQuote()->getBillingAddress()->getStreet(-1),
                                               'state' => $this->getPayment()->getQuote()->getBillingAddress()->getRegion(),
                                               'city' => $this->getPayment()->getQuote()->getBillingAddress()->getCity(),
                                               'company' => $this->getPayment()->getQuote()->getBillingAddress()->getCompany(),
                                               'country' => $this->getPayment()->getQuote()->getBillingAddress()->getCountry(),
                                               'zip' => $this->getPayment()->getQuote()->getBillingAddress()->getPostcode(),
                                               'phoneNumber' => $this->getPayment()->getQuote()->getBillingAddress()->getTelephone(),
                                               'faxNumber' => $this->getPayment()->getQuote()->getBillingAddress()->getFax(),
                                           ),
                                           'payment' => $this->_convertPayment($this->getPayment())
                                       )
                                   )
                               ),
                               'validationMode' => 'liveMode'
                          ));


        if ($result = $this->_runRequest('CreateCustomerProfile', array(self::ERR_CODE_DUPLICATE_CUSTOMER))) {

            if ($result instanceof AW_Sarp_Model_Web_Service_Client_Authorizenet_Error) {

                // Error occured
                if ($result->getCode() == self::ERR_CODE_DUPLICATE_CUSTOMER) {
                    // Duplictae customer
                    if (preg_match("/\s+([0-9]+)\s+/", $result->getText(), $matches)) {
                        $customerId = $matches[1];
                        $res = $this->loadCIMAccount($customerId);
                        if ($this->_addAdditionalCard($res))
                            $res = $this->loadCIMAccount($customerId);
                        return $res;

                    } else {
                        throw AW_Sarp_Exception("Customer duplicate responded but can't find customer id in reply");
                    }
                }
            } else {
                return $this->loadCIMAccount($result->customerProfileId);
            }
        }

    }

    protected function _addAdditionalCard(&$cimAccount)
    {
        $matches = $this->recursiveWalk($cimAccount, 'cardNumber');
        foreach ($matches as $key => $value)
        {
            $matches[$key] = substr($value, 4, 4);
        }
        $creditCardNumberShort = substr(Mage::getSingleton('customer/session')->getSarpCcNumber(), -4);
        if (!in_array($creditCardNumberShort, $matches)) {
            $newPayment = array(
                'merchantAuthentication' => array(
                    'name' => trim($this->getApiLoginId()),
                    'transactionKey' => trim($this->getTransactionKey())
                ),
                'customerProfileId' => $this->getCIMCustomerProfileId($cimAccount),
                'paymentProfile' => array(
                    'billTo' => array(
                        'firstName' => $this->getPayment()->getQuote()->getBillingAddress()->getFirstname(),
                        'lastName' => $this->getPayment()->getQuote()->getBillingAddress()->getLastname(),
                        'address' => $this->getPayment()->getQuote()->getBillingAddress()->getStreet(-1),
                        'state' => $this->getPayment()->getQuote()->getBillingAddress()->getRegion(),
                        'city' => $this->getPayment()->getQuote()->getBillingAddress()->getCity(),
                        'company' => $this->getPayment()->getQuote()->getBillingAddress()->getCompany(),
                        'country' => $this->getPayment()->getQuote()->getBillingAddress()->getCountry(),
                        'zip' => $this->getPayment()->getQuote()->getBillingAddress()->getPostcode(),
                        'phoneNumber' => $this->getPayment()->getQuote()->getBillingAddress()->getTelephone()
                    ),
                    'payment' => $this->_convertPayment($this->getPayment())
                ),
                'validationMode' => 'liveMode'
            );

            $this->getRequest()->reset()->setData($newPayment);
            $this->_runRequest('CreateCustomerPaymentProfile');
            return true;
        }
        return false;
    }

    public function loadCIMAccount($id)
    {
        $this->getRequest()
                ->reset()
                ->setData(array(
                               'merchantAuthentication' => array(
                                   'name' => trim($this->getApiLoginId()),
                                   'transactionKey' => trim($this->getTransactionKey())
                               ),
                               'customerProfileId' => $id
                          ));
        $result = $this->_runRequest('GetCustomerProfile');
        return $result;
    }


    /**
     * Runs authOnly transaction
     * @return
     */
    public function createAuthOnlyTransaction()
    {
        $this->getRequest()
                ->reset()
                ->setData(array(
                               'merchantAuthentication' => array(
                                   'name' => trim($this->getApiLoginId()),
                                   'transactionKey' => trim($this->getTransactionKey())
                               ),
                               'transaction' => array(
                                   'profileTransAuthOnly' => array(
                                       'amount' => $this->getOrder()->getGrandTotal(),
                                       'customerProfileId' => $this->getSubscription()->getRealId(),
                                       'customerPaymentProfileId' => $this->getSubscription()->getRealPaymentId(),
                                       'order' => array(
                                           'invoiceNumber' => $this->getOrder()->getIncrementId(),
                                           'description' => Mage::helper('sarp')->__("Subscription #%s payment", $this->getSubscription()->getId())
                                       )
                                   )
                               )
                          ));
        if ($result = $this->_runRequest('CreateCustomerProfileTransaction')) {
        }
        return ($result);
    }

    /**
     * Runs auth and capture transaction
     * @return
     */
    public function createAuthCaptureTransaction()
    {
        $this->getRequest()
                ->reset()
                ->setData(array(
                               'merchantAuthentication' => array(
                                   'name' => trim($this->getApiLoginId()),
                                   'transactionKey' => trim($this->getTransactionKey())
                               ),
                               'transaction' => array(
                                   'profileTransAuthCapture' => array(
                                       'amount' => $this->getOrder()->getBaseGrandTotal(),
                                       'customerProfileId' => $this->getSubscription()->getRealId(),
                                       'customerPaymentProfileId' => $this->getSubscription()->getRealPaymentId(),
                                       'order' => array(
                                           'invoiceNumber' => $this->getOrder()->getIncrementId(),
                                           'description' => Mage::helper('sarp')->__("Subscription #%s payment", $this->getSubscription()->getId())
                                       )
                                   )
                               )
                          ));
        if ($result = $this->_runRequest('CreateCustomerProfileTransaction')) {
        }
        return ($result);
    }

    /**
     * Runs transaction for subscription
     * @return
     */
    public function createTransaction()
    {
        if ($this->getPaymentAction() == Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE) {
            return $this->createAuthCaptureTransaction();
        } else {
            return $this->createAuthOnlyTransaction();
        }
    }

    /**
     * Runs method with specified name
     * @param object $name
     * @return
     */
    protected function _runRequest($name, $notFatalErrors = null)
    {
        try {
            $result = call_user_func(array($this->getService(), $name), $this->getRequest()->getData());
            if ($result) {
                $resultName = $name . 'Result';
                if (strtolower($result->$resultName->resultCode) == 'error') {
                    if (is_array($notFatalErrors)) {
                        if (in_array($result->$resultName->messages->MessagesTypeMessage->code, $notFatalErrors)) {
                            return $this->getError($result->$resultName);
                        }
                    }
                    $data = $this->getRequest()->getData();
                    // Unset security info
                    unset($data['merchantAuthentication']);
                    self::recursive_unset($data, 'creditCard');
                    throw new AW_Core_Exception("Authorize.net[{$resultName}] responded error #{$result->$resultName->messages->MessagesTypeMessage->code} '{$result->$resultName->messages->MessagesTypeMessage->text}'",
                        print_r($data, 1)
                    );
                    return false;
                } else {
                    // All okay
                    if (isset($result->$resultName->directResponse)) {
                        $dataResponse = preg_split('/[^a-z0-9_  .]{1}/i', $result->$resultName->directResponse);
                        // Set transaction ID
                        $result->$resultName->transactionId = @$dataResponse[self::ORDER_TRXID];
                    }
                    return $result->$resultName;
                }
            } else {
                throw new AW_Core_Exception("Authorize.net returned empty result");
            }
        } catch (Exception $e) {
            if ($e instanceof AW_Sarp_Exception) {

            } elseif ($e instanceof AW_Core_Exception) {
                throw new Mage_Checkout_Exception($e->getMessage());
            } else {
                throw $e;
            }
        }
    }


    /**
     * Creates ARB subscription
     * This is not currently used because CIM is more useful for Sarp
     * @return AW_Sarp_Model_Web_Service_Client_Authorizenet
     */
    public function createSubscription()
    {

        if ($lastPaidDate = $this->getSubscription()->getLastPaidDate()) {
            $start_date = $this->getSubscription()->getNextSubscriptionEventDate($lastPaidDate);
        } else {
            foreach (Mage::getModel('sarp/sequence')
                    ->getCollection()
                    ->addSubscriptionFilter($this->getSubscription())
                    ->addStatusFilter(AW_Sarp_Model_Sequence::STATUS_PENDING)
                    ->setOrder('date', 'asc') as $Item) {
                $start_date = new Zend_Date($Item->getDate(), AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
            }
        }


        $this->getRequest()
                ->reset()
                ->setData(array(
                               'merchantAuthentication' => array(
                                   'name' => trim($this->getApiLoginId()),
                                   'transactionKey' => trim($this->getTransactionKey())
                               ),
                               'subscription' => array(
                                   'name' => $this->getSubscriptionName(),
                                   'paymentSchedule' => array(
                                       'interval' => $this->_convertPeriod($this->getSubscription()->getPeriod()),
                                       'startDate' => $start_date->toString(self::DATE_FORMAT),
                                       'totalOccurrences' => $this->_getTotalOccurrences($this->getSubscription())
                                   ),
                                   'amount' => $this->getSubscription()->getFlatLastOrderAmount(),
                                   'payment' => $this->_convertPayment($this->getPayment()),
                                   'customer' => array(
                                       'id' => $this->getPayment()->getQuote()->getCustomerId(),
                                       'email' => $this->getPayment()->getQuote()->getCustomerEmail()
                                   ),
                                   'billTo' => array(
                                       'firstName' => $this->getPayment()->getQuote()->getBillingAddress()->getFirstname(),
                                       'lastName' => $this->getPayment()->getQuote()->getBillingAddress()->getLastname(),
                                       'address' => $this->getPayment()->getQuote()->getBillingAddress()->getStreet(-1),
                                       'state' => $this->getPayment()->getQuote()->getBillingAddress()->getRegion(),
                                       'city' => $this->getPayment()->getQuote()->getBillingAddress()->getCity(),
                                       'company' => $this->getPayment()->getQuote()->getBillingAddress()->getCompany(),
                                       'country' => $this->getPayment()->getQuote()->getBillingAddress()->getCountry(),
                                       'zip' => $this->getPayment()->getQuote()->getBillingAddress()->getPostcode(),
                                   )
                               )
                          )
        );

        try {
            $result = $this->getService()->ARBCreateSubscription($this->getRequest()->getData());
            if ($result) {
                if (strtolower($result->ARBCreateSubscriptionResult->resultCode) == 'error') {
                    // payment processor error occured
                    throw new AW_Core_Exception("Authorize.net responded error #{$result->ARBCreateSubscriptionResult->messages->MessagesTypeMessage->code} #{$result->ARBCreateSubscriptionResult->messages->MessagesTypeMessage->text}",
                        null,
                            "Payment details:
                            " . print_r($this->getRequest()->getData(), 1) . ""
                    );
                } else {
                    //Subscription created ok
                    $this->log("Successfully created subscription #{$result->ARBCreateSubscriptionResult->subscriptionId}");
                }
                //print_r($result);
            } else {
                throw new AW_Sarp_Exception("Authorize.net returned empty result");
            }
        } catch (Exception $e) {
            if ($e instanceof AW_Sarp_Exception) {

            } elseif ($e instanceof AW_Core_Exception) {
                throw new Mage_Checkout_Exception($e->getMessage());
            } else {
                throw $e;
            }
        }

    }

    /**
     * Returns how much occurences will be generated by authorize.net
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return
     */
    protected function _getTotalOccurrences(AW_Sarp_Model_Subscription $Subscription)
    {
        if ($Subscription->isInfinite()) {
            return 9999;
        } else {
            // Calculate how much subscription events are generated
            return Mage::getModel('sarp/sequence')->getCollection()->addSubscriptionFilter($Subscription)->count();
        }
    }

    /**
     * Converts SARP period to ARB period array
     * @param AW_Sarp_Model_Period $Period
     * @return array
     */
    protected function _convertPeriod(AW_Sarp_Model_Period $Period)
    {
        $unitMultiplier = 1;
        $unit = $Period->getPeriodValue();
        switch ($Period->getPeriodType()) {
            case AW_Sarp_Model_Source_Periods::PERIOD_WEEKS:
                $unitMultiplier = 7;
                $unit = self::UNIT_DAYS;
                break;
            case  AW_Sarp_Model_Source_Periods::PERIOD_YEARS:
                $unitMultiplier = 12;
                $unit = self::UNIT_MONTHS;
                break;
            case  AW_Sarp_Model_Source_Periods::PERIOD_MONTHS:
                $unitMultiplier = 1;
                $unit = self::UNIT_MONTHS;
                break;
            case AW_Sarp_Model_Source_Periods::PERIOD_DAYS:
                $unitMultiplier = 1;
                $unit = self::UNIT_DAYS;
                break;
        }
        return array(
            'length' => $unitMultiplier * $Period->getPeriodValue(),
            'unit' => $unit
        );
    }

    /**
     * Coverts payment instance to authorize.net array
     * @param Mage_Sales_Model_Quote_Payment $Payment
     * @return array
     */
    protected function _convertPayment(Mage_Sales_Model_Quote_Payment $Payment)
    {
        $cardNumber = Mage::getSingleton('customer/session')->getSarpCcNumber();
        $cid = Mage::getSingleton('customer/session')->getSarpCcCid();
        $an_payment = array(
            'creditCard' => array(
                'cardNumber' => $cardNumber,
                'expirationDate' => $Payment->getMethodInstance()->getInfoInstance()->getCcExpYear() . "-" . $this->_addZero($Payment->getMethodInstance()->getInfoInstance()->getCcExpMonth()),
                'cardCode' => $cid
            )
        );

        return $an_payment;
    }

    /**
     * Adds leading zero if needed
     * @param int $digit
     * @return str
     */
    protected function _addZero($digit)
    {
        if ($digit < 10) {
            return '0' . $digit;
        } else {
            return $digit;
        }
    }

    /**
     * Unsets specified key in array
     * @param array $array
     * @param mixed $unwanted_key
     * @return
     */
    public static function recursive_unset(&$array, $unwanted_key)
    {
        unset($array[$unwanted_key]);
        foreach ($array as &$value) {
            if (is_array($value)) {
                self::recursive_unset($value, $unwanted_key);
            }
        }
    }

    /**
     * If error occured returns error code and message
     * @param object $result
     * @return
     */
    public function getError($result)
    {
        $obj = new AW_Sarp_Model_Web_Service_Client_Authorizenet_Error;

        if (strtolower($result->resultCode) == 'error') {
            $obj->setCode($result->messages->MessagesTypeMessage->code)->setText($result->messages->MessagesTypeMessage->text);
        }
        return $obj;
    }

    public function getCIMCustomerProfileId($array)
    {
        foreach ($array as $k => $v) {

            if ($k == 'customerProfileId') {
                return $v;
            }
            if ($v instanceof stdClass) {
                $val = $this->getCIMCustomerProfileId($v);
                if ($val) return $val;
            }
        }
        return null;
    }

    public function getCIMCustomerPaymentProfileId($CIMInfo)
    {
        $items = $this->recursiveWalk($CIMInfo, 'customerPaymentProfileId', true);
        foreach ($items as $item)
        {
            $matches = $this->recursiveWalk($item, 'cardNumber');
            foreach ($matches as $key => $value)
            {
                $matches[$key] = substr($value, 4, 4);
            }
            $creditCardNumberShort = substr(Mage::getSingleton('customer/session')->getSarpCcNumber(), -4);
            if (in_array($creditCardNumberShort, $matches)) return $item->customerPaymentProfileId;
        }
    }

    public function recursiveWalk($array, $needed, $returnParent = false)
    {
        $matches = array();
        $this->_recursiveWalk($array, $needed, $matches, $returnParent);
        return $matches;
    }

    protected function _recursiveWalk($array, $needed, &$matches, $returnParent)
    {
        foreach ($array as $k => $v) {
            if ($k === $needed) {
                if ($returnParent == true)
                    $matches[] = $array;
                else
                    $matches[] = $v;
            }
            if ($v instanceof stdClass || is_array($v)) {
                $this->_recursiveWalk($v, $needed, $matches, $returnParent);
            }
        }
    }
}